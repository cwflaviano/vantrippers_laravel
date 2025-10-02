<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TermsQuestion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class TermsQuestionController extends Controller
{
    /**
     * Display a listing of terms questions.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = TermsQuestion::query();

            // Filter by package_id if provided
            if ($request->has('package_id') && $request->package_id != '') {
                $query->where('package_id', $request->package_id);
            }

            // Search by question text
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('question_text', 'like', "%{$search}%")
                        ->orWhere('yes_option', 'like', "%{$search}%")
                        ->orWhere('no_option', 'like', "%{$search}%");
                });
            }

            // Sort by sort_order by default
            $sortBy = $request->get('sort_by', 'sort_order');
            $sortOrder = $request->get('sort_order', 'asc');

            if (in_array($sortBy, ['sort_order', 'created_at', 'updated_at'])) {
                $query->orderBy($sortBy, $sortOrder);
            }

            // Pagination
            $perPage = $request->get('per_page', 50);
            $questions = $query->paginate($perPage);

            // Transform data
            $questions->getCollection()->transform(function ($question) {
                return [
                    'id' => $question->id,
                    'package_id' => $question->package_id,
                    'package_name' => $question->package_name,
                    'question_text' => $question->question_text,
                    'yes_option' => $question->yes_option,
                    'no_option' => $question->no_option,
                    'sort_order' => $question->sort_order,
                    'created_at' => $question->created_at,
                    'updated_at' => $question->updated_at,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Terms questions retrieved successfully',
                'data' => $questions,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve terms questions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created terms question.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validation rules
            $validator = Validator::make($request->all(), [
                'package_id' => 'required|integer|exists:packages,id',
                'question_text' => 'required|string',
                'yes_option' => 'required|string|max:255',
                'no_option' => 'required|string|max:255',
                'sort_order' => 'nullable|integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Get the highest sort_order for this package if not provided
            $sortOrder = $request->input('sort_order');
            if ($sortOrder === null) {
                $maxOrder = TermsQuestion::where('package_id', $request->package_id)
                    ->max('sort_order');
                $sortOrder = $maxOrder ? $maxOrder + 1 : 1;
            }

            $question = TermsQuestion::create([
                'package_id' => $request->input('package_id'),
                'question_text' => $request->input('question_text'),
                'yes_option' => $request->input('yes_option'),
                'no_option' => $request->input('no_option'),
                'sort_order' => $sortOrder,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Terms question created successfully',
                'data' => [
                    'id' => $question->id,
                    'package_id' => $question->package_id,
                    'package_name' => $question->package_name,
                    'question_text' => $question->question_text,
                    'yes_option' => $question->yes_option,
                    'no_option' => $question->no_option,
                    'sort_order' => $question->sort_order,
                    'created_at' => $question->created_at,
                    'updated_at' => $question->updated_at,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create terms question',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified terms question.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $question = TermsQuestion::find($id);

            if (!$question) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terms question not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Terms question retrieved successfully',
                'data' => [
                    'id' => $question->id,
                    'package_id' => $question->package_id,
                    'package_name' => $question->package_name,
                    'question_text' => $question->question_text,
                    'yes_option' => $question->yes_option,
                    'no_option' => $question->no_option,
                    'sort_order' => $question->sort_order,
                    'created_at' => $question->created_at,
                    'updated_at' => $question->updated_at,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve terms question',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified terms question.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $question = TermsQuestion::find($id);

            if (!$question) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terms question not found',
                ], 404);
            }

            // Validation rules
            $validator = Validator::make($request->all(), [
                'package_id' => 'sometimes|required|integer|exists:packages,id',
                'question_text' => 'sometimes|required|string',
                'yes_option' => 'sometimes|required|string|max:255',
                'no_option' => 'sometimes|required|string|max:255',
                'sort_order' => 'nullable|integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Prepare data array with only provided fields
            $data = [];

            if ($request->has('package_id')) {
                $data['package_id'] = $request->input('package_id');
            }

            if ($request->has('question_text')) {
                $data['question_text'] = $request->input('question_text');
            }

            if ($request->has('yes_option')) {
                $data['yes_option'] = $request->input('yes_option');
            }

            if ($request->has('no_option')) {
                $data['no_option'] = $request->input('no_option');
            }

            if ($request->has('sort_order')) {
                $data['sort_order'] = $request->input('sort_order');
            }

            $question->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Terms question updated successfully',
                'data' => [
                    'id' => $question->id,
                    'package_id' => $question->package_id,
                    'package_name' => $question->package_name,
                    'question_text' => $question->question_text,
                    'yes_option' => $question->yes_option,
                    'no_option' => $question->no_option,
                    'sort_order' => $question->sort_order,
                    'created_at' => $question->created_at,
                    'updated_at' => $question->updated_at,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update terms question',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified terms question.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $question = TermsQuestion::find($id);

            if (!$question) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terms question not found',
                ], 404);
            }

            $question->delete();

            return response()->json([
                'success' => true,
                'message' => 'Terms question deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete terms question',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all packages for dropdown.
     *
     * @return JsonResponse
     */
    public function getPackages(): JsonResponse
    {
        try {
            $packages = \DB::connection('vantripper_tnc')->table('packages')
                ->select('*')
                ->get();

            // Map to include id and name fields
            $result = $packages->map(function ($pkg) {
                // Try different possible name columns
                $name = $pkg->name ?? $pkg->package_name ?? $pkg->title ?? 'Package ' . $pkg->id;
                return [
                    'id' => $pkg->id,
                    'name' => $name
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Packages retrieved successfully',
                'data' => $result,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve packages',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}