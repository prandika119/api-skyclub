<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Request;

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register (RegisterRequest $request): Response{
        $data = $request->validated();
        $data['password'] = bcrypt($data['password']);
        $user = User::create($data);
        return response([
            'message' => 'User created successfully'
        ], 201);
    }

    /**
     * Login a user.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();
        if (!auth()->attempt($data)) {
            return response()->json([
                'message' => "Unauthorize",
                "errors" => ["message" => "Username or Password Wrong"]
            ], 401);
        }
        /* @var User $user */
        $user = auth()->user();
        $token = $user->createToken('authToken')->plainTextToken;
        if (!$user->wallet) {
            $user->wallet()->create([
                'balance' => 0
            ]);
        }
        return response()->json([
            "message" => "Login Success",
            "data" => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'profile_photo' => $user->profile_photo,
                    'wallet' => $user->wallet->balance
                ],
                'token' => $token
            ]
        ]);
    }

    /**
     * Logout a user.
     */
    public function logout(Request $request): JsonResponse
    {
        $user = auth()->user();
        $user->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout Succesfully'], 200);
    }

}
