<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tour;
use App\Models\Destination;
use App\Models\PackageDestination;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TourController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Tour::with(['destination', 'combinedDestinations.destination']);

            if ($request->has('destination_id')) {
                $query->byDestination($request->destination_id);
            }

            if ($request->has('package_type') && $request->package_type !== 'all') {
                $query->byType($request->package_type);
            }

            if ($request->has('tour_type')) {
                $query->byTourType($request->tour_type);
            }

            if ($request->has('category')) {
                $query->byCategory($request->category);
            }

            if ($request->has('search')) {
                $query->search($request->search);
            }

            if ($request->get('active_only', false)) {
                $query->active();
            }

            if ($request->get('featured_only', false)) {
                $query->featured();
            }

            $sortBy = $request->get('sort_by', 'display_order');
            $sortOrder = $request->get('sort_order', 'asc');

            if (in_array($sortBy, ['title', 'created_at', 'updated_at', 'display_order'])) {
                if ($sortBy === 'display_order') {
                    $query->ordered();
                } else {
                    $query->orderBy($sortBy, $sortOrder);
                }
            } else {
                $query->ordered();
            }

            $perPage = $request->get('per_page', 15);
            $tours = $query->paginate($perPage);

            $tours->getCollection()->transform(function ($tour) {
                return [
                    'id' => $tour->id,
                    'title' => $tour->title,
                    'slug' => $tour->slug,
                    'duration' => $tour->duration,
                    'subtitle' => $tour->subtitle,
                    'description' => $tour->description,
                    'inclusions' => $tour->inclusions,
                    'exclusions' => $tour->exclusions,
                    'destination_id' => $tour->destination_id,
                    'destination_name' => $tour->destination->name ?? null,
                    'destination_slug' => $tour->destination->slug ?? null,
                    'package_type' => $tour->package_type,
                    'tour_type' => $tour->tour_type,
                    'tour_type_display' => $tour->formatted_tour_type,
                    'frontend_category' => $tour->frontend_category,
                    'image' => $tour->image,
                    'image_url' => $tour->image_url,
                    'image_alt' => $tour->image_alt,
                    'active' => $tour->active,
                    'featured' => $tour->featured,
                    'display_order' => $tour->display_order,
                    'combined_destinations' => $tour->package_type === 'combined' ? $tour->combined_destinations_list : null,
                    'formatted_created_at' => $tour->formatted_created_at,
                    'formatted_updated_at' => $tour->formatted_updated_at,
                    'created_at' => $tour->created_at,
                    'updated_at' => $tour->updated_at
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Tours retrieved successfully',
                'data' => $tours
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve tours',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255|unique:packages,slug',
                'duration' => 'nullable|string|max:50',
                'subtitle' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'inclusions' => 'nullable|string',
                'exclusions' => 'nullable|string',
                'destination_id' => 'nullable|exists:destinations,id',
                'package_type' => 'required|in:single,combined',
                'tour_type' => 'nullable|string|max:100',
                'frontend_category' => 'nullable|string|max:100',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'image_alt' => 'nullable|string|max:255',
                'active' => 'nullable|boolean',
                'featured' => 'nullable|boolean',
                'display_order' => 'nullable|integer|min:0',
                'combined_destinations' => 'nullable|array|required_if:package_type,combined',
                'combined_destinations.*' => 'exists:destinations,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();

            if (empty($data['slug'])) {
                $data['slug'] = $this->generateUniqueSlug($data['title']);
            }

            if (empty($data['image_alt']) && !empty($data['title'])) {
                $data['image_alt'] = $data['title'] . ' - Tour Package Image';
            }

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $fileName = time() . '_' . Str::slug($data['title']) . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('tours', $fileName, 'public');
                $data['image'] = $imagePath;
            }

            $tour = Tour::create($data);

            if ($data['package_type'] === 'combined' && !empty($data['combined_destinations'])) {
                foreach ($data['combined_destinations'] as $index => $destinationId) {
                    PackageDestination::create([
                        'package_id' => $tour->id,
                        'destination_id' => $destinationId,
                        'display_order' => $index + 1
                    ]);
                }
            }

            $tour->load(['destination', 'combinedDestinations.destination']);

            return response()->json([
                'success' => true,
                'message' => 'Tour created successfully',
                'data' => $tour
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create tour',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $tour = Tour::with(['destination', 'combinedDestinations.destination'])->find($id);

            if (!$tour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tour not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Tour retrieved successfully',
                'data' => $tour
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve tour',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $tour = Tour::find($id);

            if (!$tour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tour not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'slug' => 'sometimes|string|max:255|unique:packages,slug,' . $id,
                'duration' => 'nullable|string|max:50',
                'subtitle' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'inclusions' => 'nullable|string',
                'exclusions' => 'nullable|string',
                'destination_id' => 'nullable|exists:destinations,id',
                'package_type' => 'sometimes|required|in:single,combined',
                'tour_type' => 'nullable|string|max:100',
                'frontend_category' => 'nullable|string|max:100',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'image_alt' => 'nullable|string|max:255',
                'active' => 'nullable|boolean',
                'featured' => 'nullable|boolean',
                'display_order' => 'nullable|integer|min:0',
                'combined_destinations' => 'nullable|array',
                'combined_destinations.*' => 'exists:destinations,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();

            if ($request->hasFile('image')) {
                if ($tour->image) {
                    $tour->deleteImage();
                }

                $image = $request->file('image');
                $fileName = time() . '_' . Str::slug($data['title'] ?? $tour->title) . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('tours', $fileName, 'public');
                $data['image'] = $imagePath;
            }

            $tour->update($data);

            if (isset($data['package_type']) && $data['package_type'] === 'combined' && isset($data['combined_destinations'])) {
                PackageDestination::where('package_id', $tour->id)->delete();

                foreach ($data['combined_destinations'] as $index => $destinationId) {
                    PackageDestination::create([
                        'package_id' => $tour->id,
                        'destination_id' => $destinationId,
                        'display_order' => $index + 1
                    ]);
                }
            }

            $tour->load(['destination', 'combinedDestinations.destination']);

            return response()->json([
                'success' => true,
                'message' => 'Tour updated successfully',
                'data' => $tour
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update tour',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $tour = Tour::find($id);

            if (!$tour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tour not found'
                ], 404);
            }

            if ($tour->image) {
                $tour->deleteImage();
            }

            $tour->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tour deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete tour',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function toggleActive(string $id): JsonResponse
    {
        try {
            $tour = Tour::find($id);

            if (!$tour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tour not found'
                ], 404);
            }

            $tour->toggleActive();

            return response()->json([
                'success' => true,
                'message' => 'Tour status updated successfully',
                'data' => [
                    'id' => $tour->id,
                    'active' => $tour->active
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update tour status',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function toggleFeatured(string $id): JsonResponse
    {
        try {
            $tour = Tour::find($id);

            if (!$tour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tour not found'
                ], 404);
            }

            $tour->toggleFeatured();

            return response()->json([
                'success' => true,
                'message' => 'Tour featured status updated successfully',
                'data' => [
                    'id' => $tour->id,
                    'featured' => $tour->featured
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update tour featured status',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function getDestinations(): JsonResponse
    {
        try {
            $destinations = Destination::active()->orderBy('name')->get();

            return response()->json([
                'success' => true,
                'message' => 'Destinations retrieved successfully',
                'data' => $destinations
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve destinations',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    private function generateUniqueSlug(string $title, int $excludeId = null): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        $query = Tour::where('slug', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;

            $query = Tour::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }

        return $slug;
    }
}