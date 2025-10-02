<?php

namespace App\Http\Controllers\Api\TourOperations;

use App\Http\Controllers\Controller;
use App\Models\TourOperations\CompletedTour;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class CompletedTourController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = CompletedTour::with(['customer', 'invoice']);

            if ($request->has('assigned_team')) {
                $query->byTeam($request->assigned_team);
            }

            if ($request->has('followup_status')) {
                $query->byFollowupStatus($request->followup_status);
            }

            if ($request->has('tail_end')) {
                $query->byTailEnd($request->tail_end);
            }

            if ($request->has('destination')) {
                $query->byDestination($request->destination);
            }

            if ($request->has('tour_type')) {
                $query->byTourType($request->tour_type);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('lead_guest', 'like', "%{$search}%")
                      ->orWhere('destination', 'like', "%{$search}%")
                      ->orWhere('assigned_team', 'like', "%{$search}%")
                      ->orWhere('invoice_no', 'like', "%{$search}%");
                });
            }

            $sortBy = $request->get('sort_by', 'completion_date');
            $sortOrder = $request->get('sort_order', 'desc');

            if (in_array($sortBy, ['completion_date', 'travel_dates', 'destination', 'assigned_team'])) {
                $query->orderBy($sortBy, $sortOrder);
            }

            $perPage = $request->get('per_page', 15);
            $tours = $query->paginate($perPage);

            $tours->getCollection()->transform(function ($tour) {
                return [
                    'id' => $tour->id,
                    'tour_id' => $tour->tour_id,
                    'assigned_team' => $tour->assigned_team,
                    'followup_status' => $tour->followup_status,
                    'followup_status_display' => $tour->followup_status_display,
                    'tail_end' => $tour->tail_end,
                    'tail_end_display' => $tour->tail_end_display,
                    'completion_date' => $tour->completion_date,
                    'formatted_completion_date' => $tour->formatted_completion_date,
                    'notes' => $tour->notes,
                    'customer_assigned' => $tour->customer_assigned,
                    'invoice_no' => $tour->invoice_no,
                    'travel_dates' => $tour->travel_dates,
                    'destination' => $tour->destination,
                    'tour_type' => $tour->tour_type,
                    'days' => $tour->days,
                    'pax' => $tour->pax,
                    'lead_guest' => $tour->lead_guest,
                    'customer' => $tour->customer ? [
                        'name' => $tour->customer->name,
                        'email' => $tour->customer->email
                    ] : null,
                    'invoice' => $tour->invoice ? [
                        'invoice_status' => $tour->invoice->invoice_status,
                        'total_price' => $tour->invoice->total_price,
                        'amount_due' => $tour->invoice->amount_due,
                        'payment_received' => $tour->invoice->payment_received
                    ] : null,
                    'created_at' => $tour->created_at,
                    'updated_at' => $tour->updated_at
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Completed tours retrieved successfully',
                'data' => $tours
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve completed tours',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'assigned_team' => 'required|string|max:255',
                'travel_dates' => 'required|string|max:100',
                'destination' => 'required|string|max:255',
                'tour_type' => 'required|string|max:50',
                'days' => 'required|integer|min:1',
                'pax' => 'required|integer|min:1',
                'lead_guest' => 'required|string|max:100',
                'followup_status' => 'nullable|string|max:255',
                'tail_end' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
                'invoice_no' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $tour = CompletedTour::create($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Completed tour created successfully',
                'data' => $tour
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create completed tour',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $tour = CompletedTour::with(['customer', 'invoice'])->find($id);

            if (!$tour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Completed tour not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Completed tour retrieved successfully',
                'data' => $tour
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve completed tour',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $tour = CompletedTour::find($id);

            if (!$tour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Completed tour not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'assigned_team' => 'sometimes|required|string|max:255',
                'travel_dates' => 'sometimes|required|string|max:100',
                'destination' => 'sometimes|required|string|max:255',
                'tour_type' => 'sometimes|required|string|max:50',
                'days' => 'sometimes|required|integer|min:1',
                'pax' => 'sometimes|required|integer|min:1',
                'lead_guest' => 'sometimes|required|string|max:100',
                'followup_status' => 'nullable|in:1st Follow up sent,1st Text Sent,No Follow Up',
                'tail_end' => 'nullable|in:No Review,With Review Posted,With Photos,FB Feedback Only,No Review\\, No Feedback\\, No Photo,ALL GOOD POSTED',
                'notes' => 'nullable|string',
                'invoice_no' => 'nullable|string|max:255'
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
                'message' => 'Completed tour updated successfully',
                'data' => $tour
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update completed tour',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $tour = CompletedTour::find($id);

            if (!$tour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Completed tour not found'
                ], 404);
            }

            $tour->delete();

            return response()->json([
                'success' => true,
                'message' => 'Completed tour deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete completed tour',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateFollowupStatus(Request $request, string $id): JsonResponse
    {
        try {
            $tour = CompletedTour::find($id);

            if (!$tour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Completed tour not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'followup_status' => 'required|in:1st Follow up sent,1st Text Sent,No Follow Up'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $tour->update(['followup_status' => $request->followup_status]);

            return response()->json([
                'success' => true,
                'message' => 'Follow-up status updated successfully',
                'data' => [
                    'id' => $tour->id,
                    'followup_status' => $tour->followup_status,
                    'followup_status_display' => $tour->followup_status_display
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update follow-up status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateTailEnd(Request $request, string $id): JsonResponse
    {
        try {
            $tour = CompletedTour::find($id);

            if (!$tour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Completed tour not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'tail_end' => 'required|in:No Review,With Review Posted,With Photos,FB Feedback Only,No Review\\, No Feedback\\, No Photo,ALL GOOD POSTED'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $tour->update(['tail_end' => $request->tail_end]);

            return response()->json([
                'success' => true,
                'message' => 'Tail-end status updated successfully',
                'data' => [
                    'id' => $tour->id,
                    'tail_end' => $tour->tail_end,
                    'tail_end_display' => $tour->tail_end_display
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update tail-end status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}