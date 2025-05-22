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
     * Register User
     *
     * Register a new user.
     *
     * @unauthenticated
     *
     * @param RegisterRequest $request
     *
     * @return Response
     */
    public function register (RegisterRequest $request): Response{
        $data = $request->validated();
        $data['password'] = bcrypt($data['password']);
        $user = User::create($data);
        $user->wallet()->create(['balance' => 0]);
        return response([
            'message' => 'User created successfully'
        ], 201);
    }

    /**
     * Login User
     *
     * Login a user.
     *
     * @unauthenticated
     *
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();
        if (!auth()->attempt($data)) {
            return response()->json([
                'message' => "Unauthorize",
                "errors" =>  "Username or Password Wrong"
            ], 401);
        }
        /* @var User $user */
        $user = auth()->user();
        $token = $user->createToken('authToken')->plainTextToken;
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
     * Logout User
     *
     * Logout current user.
     */
    public function logout(Request $request): JsonResponse
    {
        $user = auth()->user();
        $user->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout Succesfully'], 200);
    }

}
