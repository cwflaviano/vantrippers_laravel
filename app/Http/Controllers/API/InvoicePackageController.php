<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\VantripperInvoice\Invoice_Packages;
use Illuminate\Http\Request;

class InvoicePackageController extends Controller
{
    # get all invoice packages without filters
    public function getInvoicePackages() 
    {
        $invoicePackages = Invoice_Packages::all();
        return response()->json([
            'status' => 'success',
            'packages' => $invoicePackages
        ]);
    }

    # fetch invoice pacakges with pagination
    public function paginatedInvoicePackages(Request $request) 
    {
        $query = Invoice_Packages::query();
        
        if($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('sku', 'LIKE', "%{$search}%")
                  ->orWhere('category', 'LIKE', "%{$search}%")
                  ->orWhere('items', 'LIKE', "%{$search}%")
                  ->orWhere('price', 'LIKE', "%{$search}%")
                  ->orWhere('created_at', 'LIKE', "%{$search}%")
                  ->orWhere('id', 'LIKE', "%{$search}%");
            });
        }

        $sortBy = $request->get('sortBy', 'created_at');
        $sortDir = $request->get('sortDir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $perPage = $request->get('per_page', 10);
        $invoicePackages = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'packages' => $invoicePackages->items(),    
            'pagination' => [
                'total' => $invoicePackages->total(),
                'per_page' => $invoicePackages->perPage(),
                'current_page' => $invoicePackages->currentPage(),
                'last_page' => $invoicePackages->lastPage()
            ]
        ]);
    }

    # add and create new invoic package and store in database
    public function addInvoicePackage(Request $request) 
    {
        if(empty($request->sku) && empty($request->items) && $request->price > 0) {
            return response()->json([
                'status' => 'Null',
                'message' => 'SKU or Items or Price is not provided'
            ], 400);
        }

        try {
            $invoicePackage = Invoice_Packages::create([
                'sku' => $request->sku,
                'quantity' => $request->quantity ?? 1,
                'category' => $request->category ?? null,
                'items' => $request->items,
                'item_full_details' => $request->item_full_details ?? null,
                'price' => $request->price
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Invoice Package created successfully',
                'invoice_package' => $invoicePackage
            ], 201); 
        }
        catch(\Exception $e) 
        {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create invoice package',
                'error' => $e->getMessage()
            ], 500); // server error
        }
    }

    # edit / update invoice package 
    public function editInvoicePackage(Request $request)
    {
        if(empty($request->id)) {
            return response()->json([
                'status' => 'Null',
                'message' => 'No Id provided'
            ], 400);
        }
        if(empty($request->sku)) {
            return response()->json([
                'status' => 'Null',
                'message' => 'Empty SKU not allowed'
            ], 400);
        }
        if(empty($request->items)) {
            return response()->json([
                'status' => 'Null',
                'message' => 'Empty Items not allowed'
            ], 400);
        }
        if(empty($request->category)) {
            return response()->json([
                'status' => 'Null',
                'message' => 'Empty Category not allowed'
            ], 400);
        }
        if(!$request->price >0) {
            return response()->json([
                'status' => 'Null',
                'message' => 'Price must be greater than zero'
            ], 400);
        }

        try {
            $invoicePackage = Invoice_Packages::find($request->id);

            if(!$invoicePackage) {
                return response()->json([
                    'status' => 'Null',
                    'message' => 'No invoice package found with the provided Id'
                ], 404);
            }

            $invoicePackage->update([
                'sku' => $request->sku,
                'quantity' => $request->quantity ?? $invoicePackage->quantity,
                'category' => $request->category ?? $invoicePackage->category,
                'items' => $request->items,
                'item_full_details' => $request->item_full_details ?? $invoicePackage->item_full_details,
                'price' => $request->price
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Invoice package updated successfully',
                'invoice_package' => $invoicePackage
            ]);
        }
        catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update invoice package',
                'error' => $e->getMessage()
            ], 500); // server error
        }
    }

    # delete invoice package by id
    public function deleteInvoicePackage(int $id) 
    {
        if(empty($id)) {
            return response()->json([
                'status' => 'Null',
                'message' => 'No Id provided'
            ], 400);
        }

        try {
           $invoicePackage = Invoice_Packages::find($id);
           
           if(!$invoicePackage) {
                return response()->json([
                    'status' => 'Not Found',
                    'message' => 'No invoice package found'
                ], 404);
           }

           $invoicePackage->delete();

           return response()->json([
            'status' => 'success',
            'message' => 'Successfully deleted invoice package'
           ]);
        }
        catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete invoice package',
                'error' => $e->getMessage()
            ], 500); // server error
        }
    }
}
