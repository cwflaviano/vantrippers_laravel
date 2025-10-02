<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthenticationController;
use App\Http\Controllers\API\TermsController;
use App\Http\Controllers\API\InvoicePackageController;
use App\Http\Controllers\API\ItinerariesController;

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
 * Get: https://domain/api/terms/get (JSON FORMAT) - will fetch all terms
 * Post: https://domain/api/terms/create (JSON FORMAT) - create new term
 * Post: https://domain/api/terms/edit (JSON FORMAT) - edit existing term
 * Delete: https://domain/api/terms/delete/{id} (URL) - delete existing term by ID
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
 * Post: https://domain/api/invoice-packages/create (JSON FORMAT) - create new invoice package
 * Post: https://domain/api/invoice-packages/edit (JSON FORMAT) - edit existing invoice package
 * Delete: https://domain/api/invoice-packages/delete/{id} (URL) - delete existing invoice package by ID
 */
Route::middleware('auth:sanctum')
    ->prefix('invoice-packages')
    ->group(function() {
        Route::get('/get', [InvoicePackageController::class, 'getInvoicePackages']);
        Route::post('/create', [InvoicePackageController::class, 'addInvoicePackage']);
        Route::post('/edit', [InvoicePackageController::class, 'editInvoicePackage']);
        Route::delete('/delete/{id}', [InvoicePackageController::class, 'deleteInvoicePackage']);
    });


# Itineraries routes
Route::middleware('auth:sanctum')
    ->prefix('itineraries')
    ->group(function() {
        /**
         * use http://domain/api/itineraries/get
         * returns itineraries:
         * id, category_id, category_name, subcategory_name, details
         */
        Route::get('/get', [ItinerariesController::class, 'getItineraries']);
        
        /**
         * for dropdown
         * use http://domain/api/itineraries/get-categories
         * returns categories:
         * id, category_name
         */
        Route::get('/get-categories', [ItinerariesController::class, 'fetchCategories']);

        /**
         *  use http://domain/api/itineraries/create-category
         * accepts JSON: category_name, description
         * return success message and create new category
         */
        Route::post('/create-category', [ItinerariesController::class, 'createCategory']);

        /**
         * (optional -- if needed)
         * use http://domain/api/itineraries/delete-category/{id}
         * accepts URL param: id
         * return success message and delete category by id
         */
        Route::delete('/delete-category/{id}', [ItinerariesController::class, 'deleteCategory']);

        /**
         * use http://domain/api/itineraries/get-sub-categories
         * accepts JSON: category_id, subcategory_name, details
         * returns sub-categories: status, message, messageType, subcategories
         */
        Route::post('/create-sub-category', [ItinerariesController::class, 'createSubCategory']);
        
        /**
         * use http://domain/api/itineraries/edit-sub-category
         * accepts JSON: id, category_id, subcategory_name, details
         * returns sub-categories: status, message, messageType, subcategory
         */
        Route::post('/edit-sub-category', [ItinerariesController::class, 'editSubCategory']);
    });




