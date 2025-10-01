<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthenticationController;
use App\Http\Controllers\API\TermsController;
use App\Http\Controllers\API\InvoicePackageController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

# =======================================================================================================
# Bearer Token Creation

# secure user routes
Route::middleware('auth:sanctum')->group(function () {
    # get all routes
    Route::get('user', [AuthenticationController::class, 'userInfo']);
    # logout user
    Route::post('logout', [AuthenticationController::class, 'logOut']);
    // Route::apiResource('posts', PostController::class);
});


# =======================================================================================================
# Terms routes
/**
 * Get: https://domain/api/terms/get (JSON FORMAT)
 * Post: https://domain/api/terms/create (JSON FORMAT)
 * Post: https://domain/api/terms/edit (JSON FORMAT)
 * Delete: https://domain/api/terms/delete/{id} (URL)
 */
Route::middleware('auth:sanctum')
    ->prefix('terms')
    ->group(function(){
        Route::get('/get', [TermsController::class, 'getTerms']);
        Route::post('/create', [TermsController::class, 'addTerms']);
        Route::post('/edit', [TermsController::class, 'editTerms']);
        Route::delete('/delete/{id}', [TermsController::class, 'deleteTerms']);
    });


# Packages routes
/**
 * Get: https://domain/api/invoice-packages/get (JSON FORMAT) - will fetch all packages without filters
 * Post: https://domain/api/invoice-packages/create (JSON FORMAT)
 * Post: https://domain/api/invoice-packages/edit (JSON FORMAT)
 * Delete: https://domain/api/invoice-packages/delete/{id} (URL)
 */
Route::middleware('auth:sanctum')
    ->prefix('invoice-packages')
    ->group(function() {
        Route::get('/get', [InvoicePackageController::class, 'getInvoicePackages']);
        Route::post('/create', [InvoicePackageController::class, 'addInvoicePackage']);
        Route::post('/edit', [InvoicePackageController::class, 'editInvoicePackage']);
        Route::delete('/delete/{id}', [InvoicePackageController::class, 'deleteInvoicePackage']);
    });








