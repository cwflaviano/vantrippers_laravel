<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VantripperINV\Packages;

class PackageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $packages = Packages::all();
        if ($packages->isEmpty())
            return response()->json(['status' => false, 'message' => 'No packages found'], 404);

        return response()->json([
            'status' => true,
            'packages' => $packages
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'sku' => 'required|string|max:255',
                'quantity' => 'required|integer|min:1',
                'category' => 'required|string|max:255',
                'items' => 'required|string',
                'items_full_details' => 'nullable|string',
                'price' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $package = Packages::create($validator->validated());

            return response()->json([
                'status' => true,
                'message' => 'Package created successfully',
                'package' => $package
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create package',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $package = Packages::find($id);

            if (!$package) {
                return response()->json([
                    'status' => false,
                    'message' => 'Package not found'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'package' => $package
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve package',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $package = Packages::find($id);

            if (!$package) {
                return response()->json([
                    'status' => false,
                    'message' => 'Package not found'
                ], 404);
            }

            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'sku' => 'sometimes|required|string|max:255',
                'quantity' => 'sometimes|required|integer|min:1',
                'category' => 'sometimes|required|string|max:255',
                'items' => 'sometimes|required|string',
                'items_full_details' => 'nullable|string',
                'price' => 'sometimes|required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $package->update($validator->validated());

            return response()->json([
                'status' => true,
                'message' => 'Package updated successfully',
                'package' => $package
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update package',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $package = Packages::find($id);

            if (!$package) {
                return response()->json([
                    'status' => false,
                    'message' => 'Package not found'
                ], 404);
            }

            $package->delete();

            return response()->json([
                'status' => true,
                'message' => 'Package deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete package',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
