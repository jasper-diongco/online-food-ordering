<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request) {
        $request->validate([
            'name' => 'required',
            // 'phone_number' => 'required',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|min:6',
            'user_type' => 'required'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
            'user_type' => $request->user_type
        ]);

        // $token = $user->createToken('app_token')->plainTextToken;

        $response = [
            'user' => $user
        ];

        return response($response, 201);
    }

    public function login(Request $request) {
        $user = User::orWhere('email', $request->email)
            ->orWhere('phone_number', $request->phone_number)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response([
                'message' => 'Invalid Credentials'
            ], 401);
        }

        $token = $user->createToken('app_token')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];

        return response($response, 200);
    }

    public function logout(Request $request) {
        // auth()->user()->tokens()->delete();

        return [
            'message' => 'Logged out'
        ];
    }
}
