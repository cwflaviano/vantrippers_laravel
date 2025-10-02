<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TermsController;
use App\Http\Controllers\Api\PackageController;
use App\Http\Controllers\Api\TourController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\SubcategoryController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SubmissionController;
use App\Http\Controllers\Api\AdminTermsController;
use App\Http\Controllers\Api\TourOperations\CompletedTourController;
use App\Http\Controllers\Api\TourOperations\CancelledTourController;
use App\Http\Controllers\Api\TourOperations\LuzonJoinerController;
use App\Http\Controllers\Api\TourOperations\LuzonExclusiveController;
use App\Http\Controllers\Api\TourOperations\DomesticTourController;
use App\Http\Controllers\Api\TermsQuestionController;

// Authentication Routes (for mobile apps and admin access)
Route::prefix('auth')->middleware('throttle:10,1')->group(function () {
    // Public authentication routes (limited to 10 per minute)
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);

    // Protected authentication routes
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
    });
});

// Packages management routes
Route::prefix('packages')->group(function () {
    // Public read-only routes
    Route::get('/items', [PackageController::class, 'index']);
    Route::get('/{id}', [PackageController::class, 'show'])->where('id', '[0-9]+');

    // Protected write routes (admin only)
    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
        Route::post('/', [PackageController::class, 'store']);
        Route::put('/{id}', [PackageController::class, 'update'])->where('id', '[0-9]+');
        Route::delete('/{id}', [PackageController::class, 'destroy'])->where('id', '[0-9]+');
    });
});

// Tours management routes
Route::prefix('tours')->group(function () {
    // Public read-only routes
    Route::get('/', [TourController::class, 'index']);
    Route::get('/{id}', [TourController::class, 'show'])->where('id', '[0-9]+');
    Route::get('/destinations', [TourController::class, 'getDestinations']);

    // Protected write routes (admin only)
    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
        Route::post('/', [TourController::class, 'store']);
        Route::put('/{id}', [TourController::class, 'update'])->where('id', '[0-9]+');
        Route::delete('/{id}', [TourController::class, 'destroy'])->where('id', '[0-9]+');
        Route::patch('/{id}/toggle-active', [TourController::class, 'toggleActive'])->where('id', '[0-9]+');
        Route::patch('/{id}/toggle-featured', [TourController::class, 'toggleFeatured'])->where('id', '[0-9]+');
    });
});

// Itineraries management routes
Route::prefix('itineraries')->group(function () {
    // Public read-only routes
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{id}', [CategoryController::class, 'show'])->where('id', '[0-9]+');
    Route::get('/subcategories', [SubcategoryController::class, 'index']);
    Route::get('/subcategories/{id}', [SubcategoryController::class, 'show'])->where('id', '[0-9]+');

    // Protected write routes (admin only)
    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{id}', [CategoryController::class, 'update'])->where('id', '[0-9]+');
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->where('id', '[0-9]+');

        Route::post('/subcategories', [SubcategoryController::class, 'store']);
        Route::put('/subcategories/{id}', [SubcategoryController::class, 'update'])->where('id', '[0-9]+');
        Route::delete('/subcategories/{id}', [SubcategoryController::class, 'destroy'])->where('id', '[0-9]+');
    });
});

// Terms and Conditions Routes
Route::prefix('terms')->group(function () {

    // Public routes (read-only for mobile apps) - limited to 60 requests per minute
    Route::middleware('throttle:60,1')->group(function () {
        Route::get('/', [TermsController::class, 'index']);
        Route::get('/{id}', [TermsController::class, 'show'])->where('id', '[0-9]+');
        Route::get('/{id}/pdf', [TermsController::class, 'downloadPdf'])->where('id', '[0-9]+');
    });

    // Protected routes (admin only - require authentication) - limited to 30 requests per minute
    Route::middleware(['auth:sanctum', 'throttle:30,1'])->group(function () {
        Route::post('/', [TermsController::class, 'store']);
        Route::put('/{id}', [TermsController::class, 'update'])->where('id', '[0-9]+');
        Route::delete('/{id}', [TermsController::class, 'destroy'])->where('id', '[0-9]+');
        Route::patch('/{id}/toggle-status', [TermsController::class, 'toggleStatus'])->where('id', '[0-9]+');
    });
});

// Admin Routes - Protected by authentication
Route::prefix('admin')->middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {

    // Submissions Management Routes
    Route::prefix('submissions')->group(function () {
        Route::get('/', [SubmissionController::class, 'index']);
        Route::post('/', [SubmissionController::class, 'store']);
        Route::get('/{id}', [SubmissionController::class, 'show'])->where('id', '[0-9]+');
        Route::put('/{id}', [SubmissionController::class, 'update'])->where('id', '[0-9]+');
        Route::delete('/{id}', [SubmissionController::class, 'destroy'])->where('id', '[0-9]+');
        Route::patch('/{id}/archive', [SubmissionController::class, 'archive'])->where('id', '[0-9]+');
        Route::patch('/{id}/restore', [SubmissionController::class, 'restore'])->where('id', '[0-9]+');
        Route::post('/{id}/payment-receipt', [SubmissionController::class, 'uploadPaymentReceipt'])->where('id', '[0-9]+');
    });

    // Tour Operations Management Routes
    Route::prefix('tour-operations')->group(function () {

        // Completed Tours Management
        Route::prefix('completed')->group(function () {
            Route::get('/', [CompletedTourController::class, 'index']);
            Route::post('/', [CompletedTourController::class, 'store']);
            Route::get('/{id}', [CompletedTourController::class, 'show'])->where('id', '[0-9]+');
            Route::put('/{id}', [CompletedTourController::class, 'update'])->where('id', '[0-9]+');
            Route::delete('/{id}', [CompletedTourController::class, 'destroy'])->where('id', '[0-9]+');
            Route::patch('/{id}/followup-status', [CompletedTourController::class, 'updateFollowupStatus'])->where('id', '[0-9]+');
            Route::patch('/{id}/tail-end', [CompletedTourController::class, 'updateTailEnd'])->where('id', '[0-9]+');
        });

        // Cancelled Tours Management
        Route::prefix('cancelled')->group(function () {
            Route::get('/', [CancelledTourController::class, 'index']);
            Route::post('/', [CancelledTourController::class, 'store']);
            Route::get('/{id}', [CancelledTourController::class, 'show'])->where('id', '[0-9]+');
            Route::put('/{id}', [CancelledTourController::class, 'update'])->where('id', '[0-9]+');
            Route::delete('/{id}', [CancelledTourController::class, 'destroy'])->where('id', '[0-9]+');
            Route::patch('/{id}/refund-status', [CancelledTourController::class, 'updateRefundStatus'])->where('id', '[0-9]+');
        });

        // Luzon Joiner Tours Management
        Route::prefix('luzon-joiners')->group(function () {
            Route::get('/', [LuzonJoinerController::class, 'index']);
            Route::post('/', [LuzonJoinerController::class, 'store']);
            Route::get('/{id}', [LuzonJoinerController::class, 'show'])->where('id', '[0-9]+');
            Route::put('/{id}', [LuzonJoinerController::class, 'update'])->where('id', '[0-9]+');
            Route::delete('/{id}', [LuzonJoinerController::class, 'destroy'])->where('id', '[0-9]+');
            Route::patch('/{id}/status', [LuzonJoinerController::class, 'updateStatus'])->where('id', '[0-9]+');
        });

        // Luzon Exclusive Tours Management
        Route::prefix('luzon-exclusive')->group(function () {
            Route::get('/', [LuzonExclusiveController::class, 'index']);
            Route::post('/', [LuzonExclusiveController::class, 'store']);
            Route::get('/{id}', [LuzonExclusiveController::class, 'show'])->where('id', '[0-9]+');
            Route::put('/{id}', [LuzonExclusiveController::class, 'update'])->where('id', '[0-9]+');
            Route::delete('/{id}', [LuzonExclusiveController::class, 'destroy'])->where('id', '[0-9]+');
            Route::patch('/{id}/status', [LuzonExclusiveController::class, 'updateStatus'])->where('id', '[0-9]+');
        });

        // Domestic Tours Management
        Route::prefix('domestic')->group(function () {
            Route::get('/', [DomesticTourController::class, 'index']);
            Route::post('/', [DomesticTourController::class, 'store']);
            Route::get('/{id}', [DomesticTourController::class, 'show'])->where('id', '[0-9]+');
            Route::put('/{id}', [DomesticTourController::class, 'update'])->where('id', '[0-9]+');
            Route::delete('/{id}', [DomesticTourController::class, 'destroy'])->where('id', '[0-9]+');
            Route::patch('/{id}/status', [DomesticTourController::class, 'updateStatus'])->where('id', '[0-9]+');
        });
    });

    // Admin Terms Management Routes (Enhanced version with more admin features)
    Route::prefix('terms-management')->group(function () {
        Route::get('/', [AdminTermsController::class, 'index']);
        Route::post('/', [AdminTermsController::class, 'store']);
        Route::get('/{id}', [AdminTermsController::class, 'show'])->where('id', '[0-9]+');
        Route::put('/{id}', [AdminTermsController::class, 'update'])->where('id', '[0-9]+');
        Route::delete('/{id}', [AdminTermsController::class, 'destroy'])->where('id', '[0-9]+');
        Route::patch('/{id}/toggle-status', [AdminTermsController::class, 'toggleStatus'])->where('id', '[0-9]+');
        Route::get('/{id}/download-pdf', [AdminTermsController::class, 'downloadPdf'])->where('id', '[0-9]+');
        Route::post('/bulk-action', [AdminTermsController::class, 'bulkAction']);
    });

    // Terms Questions Management Routes
    Route::prefix('terms-questions')->group(function () {
        Route::get('/', [TermsQuestionController::class, 'index']);
        Route::post('/', [TermsQuestionController::class, 'store']);
        Route::get('/packages', [TermsQuestionController::class, 'getPackages']);
        Route::get('/{id}', [TermsQuestionController::class, 'show'])->where('id', '[0-9]+');
        Route::put('/{id}', [TermsQuestionController::class, 'update'])->where('id', '[0-9]+');
        Route::delete('/{id}', [TermsQuestionController::class, 'destroy'])->where('id', '[0-9]+');
    });
});

// Public Form Submission Routes (No authentication required)
Route::prefix('forms')->middleware('throttle:30,1')->group(function () {
    Route::post('/submit', [SubmissionController::class, 'store']);
});

