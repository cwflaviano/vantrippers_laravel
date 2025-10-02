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
Route::post('register', [AuthenticationController::class, 'register']); // create user for api access
Route::post('login', [AuthenticationController::class, 'login']); // login to get access token per user

# secure user routes
Route::middleware('auth:sanctum')->group(function () {
    # get all users with access token
    Route::get('user', [AuthenticationController::class, 'userInfo']);
    # logout user
    Route::post('logout', [AuthenticationController::class, 'logOut']);
});


# =======================================================================================================
# Terms routes
Route::middleware('auth:sanctum')
    ->prefix('terms')
    ->group(function(){
        /**
         * use http://domain/api/terms/get
         * returns terms: id, category, details, created_at with success message
         */
        Route::get('/get', [TermsController::class, 'getTerms']);

        /**
         * use http://domain/api/terms/create
         * accepts JSON: category, details
         * returns success message
         */
        Route::post('/create', [TermsController::class, 'addTerms']);

        /**
         * use http://domain/api/terms/edit
         * accepts JSON: id, category, details
         * returns success message after updating term by id
         */
        Route::post('/edit', [TermsController::class, 'editTerms']);

        /**
         * use http://domain/api/terms/delete/{id}
         * accepts URL param: id
         * returns success message after deleting term by id
         */
        Route::delete('/delete/{id}', [TermsController::class, 'deleteTerms']);
    });


# Packages routes
Route::middleware('auth:sanctum')
    ->prefix('invoice-packages')
    ->group(function() {
        /**
         * use http://domain/api/invoice-packages/get
         * return unfiltered invoce packages
         * return JSON: id, sku, quantiy, category, items, item_full_details, price, created_at
         */
        Route::get('/get', [InvoicePackageController::class, 'getInvoicePackages']);

        /**
         * use http://domain/api/invoice-packages/create
         * accepts JSON: sku, quantity, category, items, item_full_details, price
         * returns success message with status code 201
         */
        Route::post('/create', [InvoicePackageController::class, 'addInvoicePackage']);

        /**
         * use http://domain/api/invoice-packages/edit
         * accepts JSON: id, sku, quantity, category, items, item_full_details, price
         * creates invoice package and returns success message
         */
        Route::post('/edit', [InvoicePackageController::class, 'editInvoicePackage']);

        /**
         * use http://domain/api/invoice-packages/delete/{id}
         * accepts URL param: id
         * returns success message after deleting invoice package by id
         */
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

        /**
         * use http://domain/api/itineraries/delete-sub-category/{id}
         * accepts URL param: id
         * returns success message after deleting sub-category by id
         */
        Route::delete('/delete-itinerary/{id}', [ItinerariesController::class, 'deleteItinerary']);
    });




