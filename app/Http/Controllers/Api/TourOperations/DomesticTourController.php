<?php

namespace App\Http\Controllers\Api\TourOperations;

use App\Http\Controllers\Controller;
use App\Models\TourOperations\DomesticTour;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class DomesticTourController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = DomesticTour::query();

            if ($request->has('destination')) {
                $query->byDestination($request->destination);
            }

            if ($request->has('status')) {
                $query->byStatus($request->status);
            }

            if ($request->has('payment_status')) {
                $query->byPaymentStatus($request->payment_status);
            }

            if ($request->has('handled_by')) {
                $query->byHandledBy($request->handled_by);
            }

            if ($request->has('accommodation_booked')) {
                if ($request->accommodation_booked === 'true') {
                    $query->accommodationBooked();
                }
            }

            if ($request->has('coordinated_with_supplier')) {
                if ($request->coordinated_with_supplier === 'true') {
                    $query->coordinatedWithSupplier();
                }
            }

            if ($request->has('transfer_details_sent')) {
                if ($request->transfer_details_sent === 'true') {
                    $query->transferDetailsSent();
                }
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('lead_guest', 'like', "%{$search}%")
                      ->orWhere('destination', 'like', "%{$search}%")
                      ->orWhere('contact', 'like', "%{$search}%")
                      ->orWhere('handled_by', 'like', "%{$search}%");
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
                    'lead_guest' => $tour->lead_guest,
                    'contact' => $tour->contact,
                    'pickup_details' => $tour->pickup_details,
                    'balance' => $tour->balance,
                    'formatted_balance' => $tour->formatted_balance,
                    'payment_status' => $tour->payment_status,
                    'payment_status_display' => $tour->payment_status_display,
                    'accommodation' => $tour->accommodation,
                    'booked_accommodation' => $tour->booked_accommodation,
                    'coordinated_with_supplier' => $tour->coordinated_with_supplier,
                    'coordinated_with_supplier_display' => $tour->coordinated_with_supplier_display,
                    'hotel_balance' => $tour->hotel_balance,
                    'formatted_hotel_balance' => $tour->formatted_hotel_balance,
                    'transfer_details_sent' => $tour->transfer_details_sent,
                    'handled_by' => $tour->handled_by,
                    'status' => $tour->status,
                    'status_display' => $tour->status_display,
                    'notes' => $tour->notes,
                    'created_at' => $tour->created_at,
                    'updated_at' => $tour->updated_at
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Domestic tours retrieved successfully',
                'data' => $tours
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve domestic tours',
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
                'lead_guest' => 'required|string|max:255',
                'contact' => 'required|string|max:100',
                'pickup_details' => 'nullable|string',
                'balance' => 'nullable|numeric|min:0',
                'payment_status' => 'required|in:Partially Paid,Fully Paid',
                'accommodation' => 'nullable|string|max:255',
                'booked_accommodation' => 'nullable|boolean',
                'coordinated_with_supplier' => 'nullable|boolean',
                'hotel_balance' => 'nullable|numeric|min:0',
                'transfer_details_sent' => 'nullable|boolean',
                'handled_by' => 'nullable|string|max:255',
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

            $tour = DomesticTour::create($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Domestic tour created successfully',
                'data' => $tour
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create domestic tour',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $tour = DomesticTour::find($id);

            if (!$tour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Domestic tour not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Domestic tour retrieved successfully',
                'data' => $tour
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve domestic tour',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $tour = DomesticTour::find($id);

            if (!$tour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Domestic tour not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'travel_dates' => 'sometimes|required|string|max:100',
                'destination' => 'sometimes|required|string|max:255',
                'days' => 'sometimes|required|integer|min:1',
                'pax' => 'sometimes|required|integer|min:1',
                'lead_guest' => 'sometimes|required|string|max:255',
                'contact' => 'sometimes|required|string|max:100',
                'pickup_details' => 'nullable|string',
                'balance' => 'nullable|numeric|min:0',
                'payment_status' => 'sometimes|required|in:Partially Paid,Fully Paid',
                'accommodation' => 'nullable|string|max:255',
                'booked_accommodation' => 'nullable|boolean',
                'coordinated_with_supplier' => 'nullable|boolean',
                'hotel_balance' => 'nullable|numeric|min:0',
                'transfer_details_sent' => 'nullable|boolean',
                'handled_by' => 'nullable|string|max:255',
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

            $tour->update($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Domestic tour updated successfully',
                'data' => $tour
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update domestic tour',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $tour = DomesticTour::find($id);

            if (!$tour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Domestic tour not found'
                ], 404);
            }

            $tour->delete();

            return response()->json([
                'success' => true,
                'message' => 'Domestic tour deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete domestic tour',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, string $id): JsonResponse
    {
        try {
            $tour = DomesticTour::find($id);

            if (!$tour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Domestic tour not found'
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