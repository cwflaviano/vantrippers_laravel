<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Models\Companion;
use App\Models\SubmissionAnswer;
use App\Models\PaymentReceipt;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class SubmissionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Submission::with(['companions', 'submissionAnswers.question', 'paymentReceipts']);

            if ($request->has('package_type')) {
                $query->byPackageType($request->package_type);
            }

            if ($request->has('search')) {
                $query->search($request->search);
            }

            if ($request->has('date_from') || $request->has('date_to')) {
                $query->dateRange($request->date_from, $request->date_to);
            }

            $showArchived = $request->get('show_archived', 0);
            if ($showArchived == 0) {
                $query->active();
            } elseif ($showArchived == 2) {
                $query->archived();
            }

            $perPage = $request->get('per_page', 15);
            $submissions = $query->orderBy('created_at', 'desc')->paginate($perPage);

            $submissions->getCollection()->transform(function ($submission) {
                return [
                    'id' => $submission->id,
                    'package_type' => $submission->package_type,
                    'email' => $submission->email,
                    'lead_guest' => $submission->lead_guest,
                    'fb_name' => $submission->fb_name,
                    'contact_number' => $submission->contact_number,
                    'payment_date' => $submission->formatted_payment_date,
                    'payment_amount' => $submission->formatted_payment_amount,
                    'has_payment_receipt' => $submission->has_payment_receipt,
                    'archived' => $submission->archived,
                    'formatted_created_at' => $submission->formatted_created_at,
                    'companions' => $submission->companions->pluck('full_name')->toArray(),
                    'answers' => $submission->submissionAnswers->map(function ($answer) {
                        return [
                            'question' => $answer->question->question_text ?? 'Unknown Question',
                            'answer' => $answer->answer
                        ];
                    })->toArray(),
                    'payment_receipts' => $submission->paymentReceipts->map(function ($receipt) {
                        return [
                            'file_name' => $receipt->file_name,
                            'file_url' => $receipt->file_url,
                            'file_size' => $receipt->formatted_file_size
                        ];
                    })->toArray(),
                    'created_at' => $submission->created_at,
                    'updated_at' => $submission->updated_at
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Submissions retrieved successfully',
                'data' => $submissions
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve submissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'package_type' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'lead_guest' => 'required|string|max:255',
                'contact_number' => 'required|string|max:20',
                'fb_name' => 'nullable|string|max:255',
                'payment_date' => 'nullable|date',
                'payment_amount' => 'nullable|numeric|min:0',
                'has_payment_receipt' => 'nullable|boolean',
                'companions' => 'nullable|array',
                'companions.*' => 'string|max:255',
                'answers' => 'nullable|array',
                'answers.*.question_id' => 'required_with:answers|integer',
                'answers.*.answer' => 'required_with:answers|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $submission = Submission::create($validator->validated());

            if ($request->has('companions')) {
                foreach ($request->companions as $companionName) {
                    Companion::create([
                        'submission_id' => $submission->id,
                        'full_name' => $companionName
                    ]);
                }
            }

            if ($request->has('answers')) {
                foreach ($request->answers as $answer) {
                    SubmissionAnswer::create([
                        'submission_id' => $submission->id,
                        'question_id' => $answer['question_id'],
                        'answer' => $answer['answer']
                    ]);
                }
            }

            $submission->load(['companions', 'submissionAnswers.question', 'paymentReceipts']);

            return response()->json([
                'success' => true,
                'message' => 'Submission created successfully',
                'data' => $submission
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create submission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $submission = Submission::with(['companions', 'submissionAnswers.question', 'paymentReceipts'])
                ->find($id);

            if (!$submission) {
                return response()->json([
                    'success' => false,
                    'message' => 'Submission not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Submission retrieved successfully',
                'data' => $submission
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve submission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $submission = Submission::find($id);

            if (!$submission) {
                return response()->json([
                    'success' => false,
                    'message' => 'Submission not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'package_type' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|max:255',
                'lead_guest' => 'sometimes|required|string|max:255',
                'contact_number' => 'sometimes|required|string|max:20',
                'fb_name' => 'nullable|string|max:255',
                'payment_date' => 'nullable|date',
                'payment_amount' => 'nullable|numeric|min:0',
                'has_payment_receipt' => 'nullable|boolean',
                'archived' => 'nullable|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $submission->update($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Submission updated successfully',
                'data' => $submission
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update submission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $submission = Submission::find($id);

            if (!$submission) {
                return response()->json([
                    'success' => false,
                    'message' => 'Submission not found'
                ], 404);
            }

            foreach ($submission->paymentReceipts as $receipt) {
                $receipt->deleteFile();
            }

            $submission->delete();

            return response()->json([
                'success' => true,
                'message' => 'Submission deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete submission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function archive(string $id): JsonResponse
    {
        try {
            $submission = Submission::find($id);

            if (!$submission) {
                return response()->json([
                    'success' => false,
                    'message' => 'Submission not found'
                ], 404);
            }

            $submission->archive();

            return response()->json([
                'success' => true,
                'message' => 'Submission archived successfully',
                'data' => $submission
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to archive submission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function restore(string $id): JsonResponse
    {
        try {
            $submission = Submission::find($id);

            if (!$submission) {
                return response()->json([
                    'success' => false,
                    'message' => 'Submission not found'
                ], 404);
            }

            $submission->restore();

            return response()->json([
                'success' => true,
                'message' => 'Submission restored successfully',
                'data' => $submission
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore submission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function uploadPaymentReceipt(Request $request, string $id): JsonResponse
    {
        try {
            $submission = Submission::find($id);

            if (!$submission) {
                return response()->json([
                    'success' => false,
                    'message' => 'Submission not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'receipt' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $file = $request->file('receipt');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('payment_receipts', $fileName, 'public');

            $receipt = PaymentReceipt::create([
                'submission_id' => $submission->id,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $filePath,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment receipt uploaded successfully',
                'data' => $receipt
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload payment receipt',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}