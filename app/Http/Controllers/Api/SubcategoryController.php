<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VantripperINV\Subcategories;
use Illuminate\Http\Request;

class SubcategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $subcategories = Subcategories::all();

        if ($subcategories->isEmpty())
            return response()->json(['status' => false, 'message' => 'No subcategories found'], 404);

        return response()->json([
            'status' => true,
            'subcategories' => $subcategories
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'category_id' => 'required|integer|exists:categories,id',
                'subcategory_name' => 'required|string|max:255',
                'details' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $subcategory = Subcategories::create($request->all());

            return response()->json([
                'status' => true,
                'message' => 'Subcategory created successfully',
                'subcategory' => $subcategory
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create subcategory',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $subcategory = Subcategories::find($id);

            if (!$subcategory) {
                return response()->json([
                    'status' => false,
                    'message' => 'Subcategory not found'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'subcategory' => $subcategory
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve subcategory',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $subcategory = Subcategories::find($id);

            if (!$subcategory) {
                return response()->json([
                    'status' => false,
                    'message' => 'Subcategory not found'
                ], 404);
            }

            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'category_id' => 'sometimes|required|integer|exists:categories,id',
                'subcategory_name' => 'sometimes|required|string|max:255',
                'details' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $subcategory->update($request->all());

            return response()->json([
                'status' => true,
                'message' => 'Subcategory updated successfully',
                'subcategory' => $subcategory
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update subcategory',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $subcategory = Subcategories::find($id);

            if (!$subcategory) {
                return response()->json([
                    'status' => false,
                    'message' => 'Subcategory not found'
                ], 404);
            }

            $subcategory->delete();

            return response()->json([
                'status' => true,
                'message' => 'Subcategory deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete subcategory',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
