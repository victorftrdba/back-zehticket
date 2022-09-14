<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserService {
    public function login(array $data): JsonResponse
    {
        $user = User::where('email', $data['email'])->first();

        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'error' => 'E-mail não verificado.'
            ], 401);
        }

        if (Auth::attempt($data)) {
            return response()->json([
                'token' => $user->createToken($user->name)->plainTextToken,
            ]);
        }

        return response()->json([
            'error' => 'Autenticação com erro.',
        ], 401);
    }

    public function register(array $data): JsonResponse
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->roles()->attach([2]);

        return response()->json(['success' => $user], 201);
    }

    public function logout($user): JsonResponse
    {
        $user->tokens()->delete();

        return response()->json(['logged' => false]);
    }
}
