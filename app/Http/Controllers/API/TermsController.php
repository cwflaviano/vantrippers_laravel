<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VantripperInvoice\Terms;

class TermsController extends Controller
{
    // Retrieve all terms from the database
    public function getTerms()
    {
        $terms = Terms::all();

        return response()->json([
            'status' => 'success',
            'terms' => $terms
        ]);
    }

    // Create new terms
    public function addTerms(Request $request) 
    {
        if(empty($request->category) || empty($request->details)) {
            return response()->json([
                'status' => 'Null',
                'message' => 'Category and details are required.'
            ], 400);
        }


        try {
            $term = Terms::create([
                'category' => $request->category,
                'details'  => $request->details
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Term created successfully',
                'term'    => $term
            ], 201); // Created

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to create term',
                'error'   => $e->getMessage()
            ], 500); // Server error
        }
    }

    // update terms
    public function editTerms(Request $request) 
    {
        if(empty($request->id)) {
            return response()->json([
                'status' => 'Null',
                'message' => 'No Id provided'
            ]);
        }

        if(empty($request->category)) {
            return response()->json([
                'status' => 'Null',
                'message' => 'Empty category not allowed'
            ]);
        }

        if(empty($request->details)) {
            return response()->json([
                'status' => 'Null',
                'message' => 'Empty details not allowed'
            ]);
        }


        try {

            $term = Terms::find($request->id);

            if(!$term) {
                return response()->json([
                    'status' => 'Not Found',
                    'message' => 'Term not found'
                ]);
            }

            $term->update([
                'category' => $request->category,
                'details'  => $request->details
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Successfully updated term',
                'term' => $term
            ]);
        }
        catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update terms',
                'error' => $e->getMessage()
            ]);
        }
    }

    // permanently delete terms
    public function deleteTerms(int $id) 
    {
        if(empty($id)) {
            return response()->json([
                'status' => 'Null',
                'message' => 'No Id provided'
            ]);
        }

        try {
            $term = Terms::find($id);

            if(!$term) {
                return response()->json([
                    'status' => 'Not Found',
                    'message' => 'Term not found'
                ]);
            }

            $term->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Successfully deleted term',
                'term' => $term
            ]);
        }
        catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete term',
                'error' => $e->getMessage()
            ]);
        }
    }
}
