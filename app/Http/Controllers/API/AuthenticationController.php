<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthenticationController extends Controller
{

    #register
    public function register(Request $request) 
    {
        $request->validate([
            'name' => 'required|min:3',
            'email' => 'required|email',
            'password' => 'required|min:3'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        return response()->json(['message' => 'User registered successfully'], 201);
    }

    # login
    public function login(Request $request)
    {   
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if(!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid login details'], 401);
        }

        $token = $request->user()->createToken('API Token')->plainTextToken;

        return response()->json(['token' => $token]);
    }

    # user information
    public function userInfo(Request $request) 
    {
        return response()->json($request->user());
    }


    # logout
    public function logOut(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }
}
