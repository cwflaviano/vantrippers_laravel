<?php

namespace App\Http\Controllers\Api\TourOperations;

use App\Http\Controllers\Controller;
use App\Models\TourOperations\LuzonJoiner;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class LuzonJoinerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = LuzonJoiner::query();

            if ($request->has('destination')) {
                $query->byDestination($request->destination);
            }

            if ($request->has('status')) {
                $query->byStatus($request->status);
            }

            if ($request->has('payment_status')) {
                $query->byPaymentStatus($request->payment_status);
            }

            if ($request->has('with_coordinator')) {
                if ($request->with_coordinator === 'true') {
                    $query->withCoordinator();
                } else {
                    $query->withoutCoordinator();
                }
            }

            if ($request->has('assigned_team')) {
                $query->byTeam($request->assigned_team);
            }

            if ($request->has('accommodation_booked')) {
                if ($request->accommodation_booked === 'true') {
                    $query->accommodationBooked();
                }
            }

            if ($request->has('van_details_sent')) {
                if ($request->van_details_sent === 'true') {
                    $query->vanDetailsSent();
                }
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('lead_guest', 'like', "%{$search}%")
                      ->orWhere('destination', 'like', "%{$search}%")
                      ->orWhere('contact', 'like', "%{$search}%")
                      ->orWhere('assigned_team', 'like', "%{$search}%");
                });
            }

            $sortBy = $request->get('sort_by', 'travel_dates');
            $sortOrder = $request->get('sort_order', 'desc');

            if (in_array($sortBy, ['travel_dates', 'destination', 'status', 'payment_status'])) {
                $query->orderBy($sortBy, $sortOrder);
            }

            $perPage = $request->get('per_page', 15);
            $tours = $query->paginate($perPage);

            $tours->getCollection()->transform(function ($tour) {
                return [
                    'id' => $tour->id,
                    'travel_dates' => $tour->travel_dates,
                    'destination' => $tour->destination,
                    'days' => $tour->days,
                    'pax' => $tour->pax,
                    'with_coordinator' => $tour->with_coordinator,
                    'with_coordinator_display' => $tour->with_coordinator_display,
                    'lead_guest' => $tour->lead_guest,
                    'contact' => $tour->contact,
                    'pickup_point' => $tour->pickup_point,
                    'balance' => $tour->balance,
                    'formatted_balance' => $tour->formatted_balance,
                    'payment_status' => $tour->payment_status,
                    'payment_status_display' => $tour->payment_status_display,
                    'accommodation' => $tour->accommodation,
                    'room_setup' => $tour->room_setup,
                    'booked_accommodation' => $tour->booked_accommodation,
                    'van_details_sent' => $tour->van_details_sent,
                    'assigned_team' => $tour->assigned_team,
                    'status' => $tour->status,
                    'status_display' => $tour->status_display,
                    'notes' => $tour->notes,
                    'created_at' => $tour->created_at,
                    'updated_at' => $tour->updated_at
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Luzon joiner tours retrieved successfully',
                'data' => $tours
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve Luzon joiner tours',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'travel_dates' => 'required|string|max:100',
                'destination' => 'required|string|max:255',
                'days' => 'required|integer|min:1',
                'pax' => 'required|integer|min:1',
                'with_coordinator' => 'required|in:With,None',
                'lead_guest' => 'required|string|max:255',
                'contact' => 'required|string|max:100',
                'pickup_point' => 'required|string|max:255',
                'balance' => 'nullable|numeric|min:0',
                'payment_status' => 'required|in:Partially Paid,Fully Paid',
                'accommodation' => 'nullable|string|max:255',
                'room_setup' => 'nullable|string|max:255',
                'booked_accommodation' => 'nullable',
                'van_details_sent' => 'nullable',
                'assigned_team' => 'nullable|string|max:255',
                'status' => 'required|string|max:50',
                'notes' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get max ID and add 1 (id field doesn't have AUTO_INCREMENT)
            $maxId = LuzonJoiner::max('id') ?? 0;
            $data = array_merge(['id' => $maxId + 1], $validator->validated());

            // Convert boolean values to YES/NO for database ENUM fields
            if (isset($data['booked_accommodation'])) {
                if ($data['booked_accommodation'] === 'YES' || $data['booked_accommodation'] === 'NO') {
                    // Already in correct format
                } else {
                    $data['booked_accommodation'] = in_array($data['booked_accommodation'], [true, 1, '1', 'true'], true) ? 'YES' : 'NO';
                }
            }
            if (isset($data['van_details_sent'])) {
                if ($data['van_details_sent'] === 'YES' || $data['van_details_sent'] === 'NO') {
                    // Already in correct format
                } else {
                    $data['van_details_sent'] = in_array($data['van_details_sent'], [true, 1, '1', 'true'], true) ? 'YES' : 'NO';
                }
            }

            $tour = LuzonJoiner::create($data);
            $tour = LuzonJoiner::find($data['id']);

            return response()->json([
                'success' => true,
                'message' => 'Luzon joiner tour created successfully',
                'data' => $tour
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create Luzon joiner tour',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $tour = LuzonJoiner::find($id);

            if (!$tour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Luzon joiner tour not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Luzon joiner tour retrieved successfully',
                'data' => $tour
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve Luzon joiner tour',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $tour = LuzonJoiner::find($id);

            if (!$tour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Luzon joiner tour not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'travel_dates' => 'sometimes|required|string|max:100',
                'destination' => 'sometimes|required|string|max:255',
                'days' => 'sometimes|required|integer|min:1',
                'pax' => 'sometimes|required|integer|min:1',
                'with_coordinator' => 'sometimes|required|in:With,None',
                'lead_guest' => 'sometimes|required|string|max:255',
                'contact' => 'sometimes|required|string|max:100',
                'pickup_point' => 'sometimes|required|string|max:255',
                'balance' => 'nullable|numeric|min:0',
                'payment_status' => 'sometimes|required|in:Partially Paid,Fully Paid',
                'accommodation' => 'nullable|string|max:255',
                'room_setup' => 'nullable|string|max:255',
                'booked_accommodation' => 'nullable',
                'van_details_sent' => 'nullable',
                'assigned_team' => 'nullable|string|max:255',
                'status' => 'sometimes|required|string|max:50',
                'notes' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();

            // Convert boolean values to YES/NO for database ENUM fields
            if (isset($data['booked_accommodation'])) {
                if ($data['booked_accommodation'] === 'YES' || $data['booked_accommodation'] === 'NO') {
                    // Already in correct format
                } else {
                    $data['booked_accommodation'] = in_array($data['booked_accommodation'], [true, 1, '1', 'true'], true) ? 'YES' : 'NO';
                }
            }
            if (isset($data['van_details_sent'])) {
                if ($data['van_details_sent'] === 'YES' || $data['van_details_sent'] === 'NO') {
                    // Already in correct format
                } else {
                    $data['van_details_sent'] = in_array($data['van_details_sent'], [true, 1, '1', 'true'], true) ? 'YES' : 'NO';
                }
            }

            $tour->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Luzon joiner tour updated successfully',
                'data' => $tour
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update Luzon joiner tour',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $tour = LuzonJoiner::find($id);

            if (!$tour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Luzon joiner tour not found'
                ], 404);
            }

            $tour->delete();

            return response()->json([
                'success' => true,
                'message' => 'Luzon joiner tour deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Luzon joiner tour',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, string $id): JsonResponse
    {
        try {
            $tour = LuzonJoiner::find($id);

            if (!$tour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Luzon joiner tour not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'status' => 'required|string|max:50'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $tour->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => 'Tour status updated successfully',
                'data' => [
                    'id' => $tour->id,
                    'status' => $tour->status,
                    'status_display' => $tour->status_display
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update tour status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}