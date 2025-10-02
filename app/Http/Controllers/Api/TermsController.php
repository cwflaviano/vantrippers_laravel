<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TermsAndCondition;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TermsController extends Controller
{
    /**
     * Display a listing of terms and conditions.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = TermsAndCondition::query();

            // Filter by status if provided
            if ($request->has('status')) {
                if ($request->status === 'active') {
                    $query->active();
                } elseif ($request->status === 'inactive') {
                    $query->inactive();
                }
            }

            // Search by title or content
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            }

            // Sort by created_at desc by default
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            if (in_array($sortBy, ['title', 'created_at', 'updated_at'])) {
                $query->orderBy($sortBy, $sortOrder);
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $terms = $query->paginate($perPage);

            // Transform data to include additional attributes
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

    /**
     * Store a newly created terms and conditions.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validation rules
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'is_active' => 'nullable|in:true,false,1,0',
                'pdf_file' => 'nullable|file|mimes:pdf|max:10240', // 10MB max
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Convert is_active to boolean
            $isActive = true; // default value
            if ($request->has('is_active')) {
                $isActiveValue = $request->input('is_active');
                $isActive = in_array($isActiveValue, ['true', '1', 1, true], true);
            }

            $data = [
                'title' => $request->input('title'),
                'content' => $request->input('content'),
                'is_active' => $isActive,
            ];

            // Handle PDF file upload
            if ($request->hasFile('pdf_file')) {
                $pdfPath = $this->uploadPdfFile($request->file('pdf_file'));
                $data['pdf_file_path'] = $pdfPath;
            }

            $terms = TermsAndCondition::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Terms and conditions created successfully',
                'data' => [
                    'id' => $terms->id,
                    'title' => $terms->title,
                    'content' => $terms->content,
                    'pdf_file_path' => $terms->pdf_file_path,
                    'pdf_file_name' => $terms->pdf_file_name,
                    'has_pdf' => $terms->has_pdf,
                    'pdf_url' => $terms->pdf_url,
                    'is_active' => $terms->is_active,
                    'formatted_created_at' => $terms->formatted_created_at,
                    'formatted_updated_at' => $terms->formatted_updated_at,
                    'created_at' => $terms->created_at,
                    'updated_at' => $terms->updated_at,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create terms and conditions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified terms and conditions.
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $terms = TermsAndCondition::find($id);

            if (!$terms) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terms and conditions not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Terms and conditions retrieved successfully',
                'data' => [
                    'id' => $terms->id,
                    'title' => $terms->title,
                    'content' => $terms->content,
                    'pdf_file_path' => $terms->pdf_file_path,
                    'pdf_file_name' => $terms->pdf_file_name,
                    'has_pdf' => $terms->has_pdf,
                    'pdf_url' => $terms->pdf_url,
                    'is_active' => $terms->is_active,
                    'formatted_created_at' => $terms->formatted_created_at,
                    'formatted_updated_at' => $terms->formatted_updated_at,
                    'created_at' => $terms->created_at,
                    'updated_at' => $terms->updated_at,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve terms and conditions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified terms and conditions.
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $terms = TermsAndCondition::find($id);

            if (!$terms) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terms and conditions not found',
                ], 404);
            }

            // Validation rules - make title and content optional for updates
            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'content' => 'sometimes|required|string',
                'is_active' => 'nullable|in:true,false,1,0',
                'pdf_file' => 'nullable|file|mimes:pdf|max:10240', // 10MB max
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

            // Only update title if provided
            if ($request->has('title')) {
                $data['title'] = $request->input('title');
            }

            // Only update content if provided
            if ($request->has('content')) {
                $data['content'] = $request->input('content');
            }

            // Convert is_active to boolean if provided
            if ($request->has('is_active')) {
                $isActiveValue = $request->input('is_active');
                $data['is_active'] = in_array($isActiveValue, ['true', '1', 1, true], true);
            }

            // Handle PDF file upload
            if ($request->hasFile('pdf_file')) {
                // Delete old PDF file if exists
                if ($terms->pdf_file_path) {
                    $this->deletePdfFile($terms->pdf_file_path);
                }

                $pdfPath = $this->uploadPdfFile($request->file('pdf_file'));
                $data['pdf_file_path'] = $pdfPath;
            }

            $terms->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Terms and conditions updated successfully',
                'data' => [
                    'id' => $terms->id,
                    'title' => $terms->title,
                    'content' => $terms->content,
                    'pdf_file_path' => $terms->pdf_file_path,
                    'pdf_file_name' => $terms->pdf_file_name,
                    'has_pdf' => $terms->has_pdf,
                    'pdf_url' => $terms->pdf_url,
                    'is_active' => $terms->is_active,
                    'formatted_created_at' => $terms->formatted_created_at,
                    'formatted_updated_at' => $terms->formatted_updated_at,
                    'created_at' => $terms->created_at,
                    'updated_at' => $terms->updated_at,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update terms and conditions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified terms and conditions.
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $terms = TermsAndCondition::find($id);

            if (!$terms) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terms and conditions not found',
                ], 404);
            }

            // Delete PDF file if exists
            if ($terms->pdf_file_path) {
                $this->deletePdfFile($terms->pdf_file_path);
            }

            $terms->delete();

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

    /**
     * Toggle active status of terms and conditions.
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function toggleStatus(int $id): JsonResponse
    {
        try {
            $terms = TermsAndCondition::find($id);

            if (!$terms) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terms and conditions not found',
                ], 404);
            }

            $terms->update(['is_active' => !$terms->is_active]);

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'data' => [
                    'id' => $terms->id,
                    'is_active' => $terms->is_active,
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

    /**
     * Download or view PDF file.
     * 
     * @param int $id
     * @return mixed
     */
    public function downloadPdf(int $id)
    {
        try {
            $terms = TermsAndCondition::find($id);

            if (!$terms || !$terms->pdf_file_path) {
                return response()->json([
                    'success' => false,
                    'message' => 'PDF file not found',
                ], 404);
            }

            $filePath = storage_path('app/public/' . $terms->pdf_file_path);

            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'PDF file not found on server',
                ], 404);
            }

            return response()->file($filePath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $terms->pdf_file_name . '"'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to download PDF',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload PDF file to storage.
     * 
     * @param \Illuminate\Http\UploadedFile $file
     * @return string
     */
    private function uploadPdfFile($file): string
    {
        $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
        $filePath = $file->storeAs('terms_conditions', $fileName, 'public');

        return $filePath;
    }

    /**
     * Delete PDF file from storage.
     * 
     * @param string $filePath
     * @return bool
     */
    private function deletePdfFile(string $filePath): bool
    {
        if (Storage::disk('public')->exists($filePath)) {
            return Storage::disk('public')->delete($filePath);
        }

        return false;
    }
}
