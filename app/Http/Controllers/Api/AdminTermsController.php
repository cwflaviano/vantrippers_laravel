<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TermsAndCondition;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class AdminTermsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = TermsAndCondition::query();

            if ($request->has('status')) {
                if ($request->status === 'active') {
                    $query->active();
                } elseif ($request->status === 'inactive') {
                    $query->inactive();
                }
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            }

            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            if (in_array($sortBy, ['title', 'created_at', 'updated_at'])) {
                $query->orderBy($sortBy, $sortOrder);
            }

            $perPage = $request->get('per_page', 15);
            $terms = $query->paginate($perPage);

            $terms->getCollection()->transform(function ($term) {
                return [
                    'id' => $term->id,
                    'title' => $term->title,
                    'content' => $term->content,
                    'pdf_file_path' => $term->pdf_file_path,
                    'pdf_file_name' => $term->pdf_file_name,
                    'has_pdf' => $term->has_pdf,
                    'pdf_url' => $term->pdf_url,
                    'is_active' => $term->is_active,
                    'formatted_created_at' => $term->formatted_created_at,
                    'formatted_updated_at' => $term->formatted_updated_at,
                    'created_at' => $term->created_at,
                    'updated_at' => $term->updated_at,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Terms and conditions retrieved successfully',
                'data' => $terms,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve terms and conditions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'is_active' => 'nullable|boolean',
                'pdf_file' => 'nullable|file|mimes:pdf|max:10240'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $validator->validated();
            $data['is_active'] = $data['is_active'] ?? true;

            if ($request->hasFile('pdf_file')) {
                $file = $request->file('pdf_file');
                $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
                $filePath = $file->storeAs('uploads/terms_conditions', $fileName, 'public');
                $data['pdf_file_path'] = $filePath;
                $data['pdf_file_name'] = $file->getClientOriginalName();
            }

            $term = TermsAndCondition::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Terms and conditions created successfully',
                'data' => $term,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create terms and conditions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $term = TermsAndCondition::find($id);

            if (!$term) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terms and conditions not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Terms and conditions retrieved successfully',
                'data' => $term,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve terms and conditions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $term = TermsAndCondition::find($id);

            if (!$term) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terms and conditions not found',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'content' => 'sometimes|required|string',
                'is_active' => 'nullable|boolean',
                'pdf_file' => 'nullable|file|mimes:pdf|max:10240'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $validator->validated();

            if ($request->hasFile('pdf_file')) {
                if ($term->pdf_file_path && Storage::disk('public')->exists($term->pdf_file_path)) {
                    Storage::disk('public')->delete($term->pdf_file_path);
                }

                $file = $request->file('pdf_file');
                $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
                $filePath = $file->storeAs('uploads/terms_conditions', $fileName, 'public');
                $data['pdf_file_path'] = $filePath;
                $data['pdf_file_name'] = $file->getClientOriginalName();
            }

            $term->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Terms and conditions updated successfully',
                'data' => $term,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update terms and conditions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $term = TermsAndCondition::find($id);

            if (!$term) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terms and conditions not found',
                ], 404);
            }

            if ($term->pdf_file_path && Storage::disk('public')->exists($term->pdf_file_path)) {
                Storage::disk('public')->delete($term->pdf_file_path);
            }

            $term->delete();

            return response()->json([
                'success' => true,
                'message' => 'Terms and conditions deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete terms and conditions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus(string $id): JsonResponse
    {
        try {
            $term = TermsAndCondition::find($id);

            if (!$term) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terms and conditions not found',
                ], 404);
            }

            $term->is_active = !$term->is_active;
            $term->save();

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'data' => [
                    'id' => $term->id,
                    'is_active' => $term->is_active
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function downloadPdf(string $id): JsonResponse
    {
        try {
            $term = TermsAndCondition::find($id);

            if (!$term) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terms and conditions not found',
                ], 404);
            }

            if (!$term->pdf_file_path || !Storage::disk('public')->exists($term->pdf_file_path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'PDF file not found',
                ], 404);
            }

            return response()->download(
                Storage::disk('public')->path($term->pdf_file_path),
                $term->pdf_file_name
            );
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to download PDF',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function bulkAction(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'action' => 'required|in:activate,deactivate,delete',
                'ids' => 'required|array|min:1',
                'ids.*' => 'integer|exists:terms_and_conditions,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $action = $request->action;
            $ids = $request->ids;

            $terms = TermsAndCondition::whereIn('id', $ids)->get();

            if ($terms->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No terms found with provided IDs',
                ], 404);
            }

            $processed = 0;

            foreach ($terms as $term) {
                switch ($action) {
                    case 'activate':
                        $term->update(['is_active' => true]);
                        $processed++;
                        break;
                    case 'deactivate':
                        $term->update(['is_active' => false]);
                        $processed++;
                        break;
                    case 'delete':
                        if ($term->pdf_file_path && Storage::disk('public')->exists($term->pdf_file_path)) {
                            Storage::disk('public')->delete($term->pdf_file_path);
                        }
                        $term->delete();
                        $processed++;
                        break;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Bulk {$action} completed successfully",
                'data' => [
                    'processed' => $processed,
                    'total' => count($ids)
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to perform bulk action',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}