<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\VantripperInvoice\Categories;
use App\Models\VantripperInvoice\SubCategories;

class ItinerariesController extends Controller
{

    // Fetch all itineraries (categories with their sub-categories)
    public function getItineraries() 
    {
        $itineraries = $this->getSubCategories();

        if($itineraries->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Empty Itineraries'
            ], 200);
        }

        return response()->json([
            'status' => 'success',
            'itineraries' => $itineraries
        ], 200);
    }

    // Fetch categories for dropdown
    public function fetchCategories()
    {
        $categories = $this->getCategories();

        if($categories->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No Categories Found'
            ], 200);
        }

        return response()->json([
            'status' => 'success',
            'categories' => $categories
        ], 200);
    }

    # private functions, getting categories
    private function getCategories() 
    {
        try {
            $categories = DB::connection('vantripper_invoice')
                        ->table('categories')
                        ->select('id', 'category_name')
                        ->orderBy('category_name')
                        ->get();
            return $categories;
        }
        catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch categories',
                'error' => $e->getMessage()
            ], 500); // server error
        }

    }
    
    # private functions, getting sub-categories
    private function getSubCategories() 
    {
        try {
            $subCategories = DB::connection('vantripper_invoice')
                ->table('subcategories as s')
                ->join('categories as c', 's.category_id', '=', 'c.id')
                ->select(
                    's.id',
                    'c.id as category_id',
                    'c.category_name',
                    's.subcategory_name',
                    's.details' 
                )
                ->orderBy('c.category_name')
                ->orderBy('s.subcategory_name')
                ->get();
            return $subCategories;
        }
        catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch sub-categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    
    // add / create new itinerary
    public function createCategory(Request $request) 
    {
        $category_name = trim($request->category_name);
        $description = trim($request->description);
        
        try {
            $category = Categories::create([
                'category_name' => $category_name,
                'description' => $description
            ]);

            return response()->json([
                'status' => 'success',
                'messageType' => 'success',
                'message' => 'Category created successfully',
                'category' => $category
            ], 201);
        }
        catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'messageType' => 'danger',
                'message' => 'Failed to create category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // deelte category (optional - if needed)
    public function deleteCategory(int $id) 
    {
        if(empty($id)) {
            return response()->json([
                'status' => 'Null',
                'message' => 'No Id provided'
            ], 400);
        }

        $category = Categories::find($id);
        if(!$category) {
            return response()->json([
                'status' => 'error',
                'message' => 'Category not found'
            ], 404);
        }
        try {
            $category->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Category deleted successfully'
            ], 200);
        }
        catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete category',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // add / create new sub-category (itinerary item)
    public function createSubCategory(Request $request)
    {
        $subCategory_name = trim($request->subcategory_name);
        $details = trim($request->details);
        try {
            $subCategory = SubCategories::create([
                'category_id' => $request->category_id,
                'subcategory_name' => $subCategory_name,
                'details' => $details
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Subcategory added successfully!',
                'messageType' => 'success',
                'subcategory' => $subCategory
            ], 201);
        }
        catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'messageType' => 'danger',
                'message' => 'Failed to create sub-category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // update existing itinerary(subcategory)
    public function editSubCategory(Request $request)
    {
        if(empty($request->id)) {
            return response()->json([
                'status' => 'Null',
                'message' => 'No Id provided'
            ], 400);
        }
        try {
            $subCategory = SubCategories::find($request->id);

            if (!$subCategory) {
                return response()->json([
                    'status' => 'error',
                    'messageType' => 'danger',
                    'message' => 'Subcategory not found'
                ], 404);
            }

            $subCategory->update([
                'category_id' => $request->category_id ?? $subCategory->category_id,
                'subcategory_name' => trim($request->subcategory_name) ?? $subCategory->subcategory_name,
                'details' => $request->details ?? ''
            ]);

            return response()->json([
                'status' => 'success',
                'messageType' => 'success',
                'message' => 'subcategory updated successfully',
                'subcategory' => $subCategory
            ], 200);
        }
        catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'messageType' => 'danger',
                'message' => 'Failed to update sub-category',
                'error' => $e->getMessage()
            ], 500);
        }
    }   


    // delete itinerary by param ID 
    public function deleteItinerary(int $id) 
    {
        if(empty($id)) {
            return response()->json([
                'status' => 'Null',
                'message' => 'No Id provided'
            ], 400);
        }

        try {
           $itinerary = SubCategories::find($id);
           
           if(!$itinerary) {
                return response()->json([
                    'status' => 'Not Found',
                    'message' => 'No itinerary found'
                ], 404);
           }

           $itinerary->delete();

           return response()->json([
                'status' => 'success',
                'messageType' => 'success',
                'message' => 'Itinerary deleted successfully'
           ], 200);
        }
        catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'messageType' => 'danger',
                'message' => 'Failed to delete itinerary',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
