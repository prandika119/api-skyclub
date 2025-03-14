<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    public function register (RegisterRequest $request): Response{
        $data = $request->validated();
        $data['password'] = bcrypt($data['password']);
        $user = User::create($data);
        return response([
            'message' => 'User created successfully'
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();
        dump($data);
        if (!auth()->attempt($data)) {
            return response()->json([
                'message' => "Unauthorize",
                "errors" => ["message" => "Username or Password Wrong"]
            ], 401);
        }
        $user = auth()->user();
        $token = $user->createToken('authToken')->plainTextToken;
        return response()->json([
            "message" => "Login Success",
            "data" => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'profile_photo' => $user->profile_photo
                ],
                'token' => $token
            ]
        ]);
    }

    public function get (): JsonResponse{
        $user = auth()->user();
        return response()->json([
            "message" => "Success Get This User",
            "data" => [
                "id" => $user->id,
                "name" => $user->name,
                "email" => $user->email,
                "no_telp" => $user->no_telp,
                "tema" => $user->team,
                "address" => $user->address,
                "date_of_birth" => $user->date_of_birth,
                "profile_photo" => $user->profile_photo
            ]
        ]);
    }
}
