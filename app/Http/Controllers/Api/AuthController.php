<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\AuthResource;
use App\Http\Resources\User as UserResource;
use Hash;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function login(Request $request)
    {

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $user = User::where('email', $request['email'])->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;
        $authUser = array_merge(['role'=>$user->role], ['access_token' => $token]);
        
        $data= [
            'account' => AuthResource::make($user),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ];
        return response()->json([
            'data' => $data,
            'message' => 'Success',
        ], 201);
    }
    
    public function currentUser()
    {
        $user = Auth::user();
        return response()->json([
            'data' =>  UserResource::make($user),
            'message' => 'Success'
        ], 200);
    }
    public function register(Request $request)
    {

        $user = User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => Hash::make($request['password']),
            'role' => 'user', // Set a default role or handle this more securely
            'title' => 'user', // Make this optional
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;
        $data= [
            'account' => AuthResource::make($user),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ];

        return response()->json([
            'data' => $data,
            'message' => 'Success',
        ], 201);
    }
  
    public function logout()
    {
        auth()->user()->tokens()->delete();
        return ['message' => 'Bạn đã thoát và token đã xóa'];
    }
}
