<?php

namespace App\Http\Controllers\Api\TourOperations;

use App\Http\Controllers\Controller;
use App\Models\TourOperations\CancelledTour;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class CancelledTourController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = CancelledTour::query();

            if ($request->has('refund_status')) {
                $query->byRefundStatus($request->refund_status);
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

            if ($request->has('destination')) {
                $query->byDestination($request->destination);
            }

            if ($request->has('assigned_team')) {
                $query->byTeam($request->assigned_team);
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

            $sortBy = $request->get('sort_by', 'cancellation_date');
            $sortOrder = $request->get('sort_order', 'desc');

            if (in_array($sortBy, ['cancellation_date', 'destination', 'refund_status', 'status'])) {
                $query->orderBy($sortBy, $sortOrder);
            }

            $perPage = $request->get('per_page', 15);
            $tours = $query->paginate($perPage);

            $tours->getCollection()->transform(function ($tour) {
                return [
                    'id' => $tour->id,
                    'tour_id' => $tour->tour_id,
                    'cancellation_person' => $tour->cancellation_person,
                    'cancellation_reason' => $tour->cancellation_reason,
                    'refund_status' => $tour->refund_status,
                    'refund_status_display' => $tour->refund_status_display,
                    'cancellation_date' => $tour->cancellation_date,
                    'formatted_cancellation_date' => $tour->formatted_cancellation_date,
                    'days' => $tour->days,
                    'pax' => $tour->pax,
                    'with_coordinator' => $tour->with_coordinator,
                    'with_coordinator_display' => $tour->with_coordinator_display,
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
                    'notes' => $tour->notes,
                    'lead_guest' => $tour->lead_guest,
                    'contact' => $tour->contact,
                    'destination' => $tour->destination,
                    'created_at' => $tour->created_at,
                    'updated_at' => $tour->updated_at
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Cancelled tours retrieved successfully',
                'data' => $tours
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve cancelled tours',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'cancellation_person' => 'nullable|string|max:255',
                'cancellation_reason' => 'nullable|string',
                'refund_status' => 'nullable|in:Pending,Processing,Completed,Not Applicable',
                'cancellation_date' => 'required|date',
                'days' => 'required|integer|min:1',
                'pax' => 'required|integer|min:1',
                'with_coordinator' => 'required|in:With,None',
                'pickup_point' => 'required|string|max:255',
                'balance' => 'nullable|numeric|min:0',
                'payment_status' => 'required|in:Partially Paid,Fully Paid',
                'accommodation' => 'nullable|string|max:255',
                'room_setup' => 'nullable|string|max:255',
                'booked_accommodation' => 'nullable|boolean',
                'van_details_sent' => 'nullable|boolean',
                'assigned_team' => 'nullable|string|max:255',
                'status' => 'required|string|max:50',
                'notes' => 'nullable|string',
                'lead_guest' => 'required|string|max:255',
                'contact' => 'required|string|max:100',
                'destination' => 'required|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $tour = CancelledTour::create($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Cancelled tour created successfully',
                'data' => $tour
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create cancelled tour',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $tour = CancelledTour::find($id);

            if (!$tour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cancelled tour not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Cancelled tour retrieved successfully',
                'data' => $tour
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve cancelled tour',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $tour = CancelledTour::find($id);

            if (!$tour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cancelled tour not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'cancellation_person' => 'nullable|string|max:255',
                'cancellation_reason' => 'nullable|string',
                'refund_status' => 'nullable|in:Pending,Processing,Completed,Not Applicable',
                'cancellation_date' => 'sometimes|required|date',
                'days' => 'sometimes|required|integer|min:1',
                'pax' => 'sometimes|required|integer|min:1',
                'with_coordinator' => 'sometimes|required|in:With,None',
                'pickup_point' => 'sometimes|required|string|max:255',
                'balance' => 'nullable|numeric|min:0',
                'payment_status' => 'sometimes|required|in:Partially Paid,Fully Paid',
                'accommodation' => 'nullable|string|max:255',
                'room_setup' => 'nullable|string|max:255',
                'booked_accommodation' => 'nullable|boolean',
                'van_details_sent' => 'nullable|boolean',
                'assigned_team' => 'nullable|string|max:255',
                'status' => 'sometimes|required|string|max:50',
                'notes' => 'nullable|string',
                'lead_guest' => 'sometimes|required|string|max:255',
                'contact' => 'sometimes|required|string|max:100',
                'destination' => 'sometimes|required|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $tour->update($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Cancelled tour updated successfully',
                'data' => $tour
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update cancelled tour',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $tour = CancelledTour::find($id);

            if (!$tour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cancelled tour not found'
                ], 404);
            }

            $tour->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cancelled tour deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete cancelled tour',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateRefundStatus(Request $request, string $id): JsonResponse
    {
        try {
            $tour = CancelledTour::find($id);

            if (!$tour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cancelled tour not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'refund_status' => 'required|in:Pending,Processing,Completed,Not Applicable'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $tour->update(['refund_status' => $request->refund_status]);

            return response()->json([
                'success' => true,
                'message' => 'Refund status updated successfully',
                'data' => [
                    'id' => $tour->id,
                    'refund_status' => $tour->refund_status,
                    'refund_status_display' => $tour->refund_status_display
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update refund status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}