<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UserService {
    /**
     * Recebe dados para logar
     */
    public function login(array $data): JsonResponse
    {
        $user = User::where('email', $data['email'])->first();

        if (Auth::attempt($data)) {
            return response()->json([
                'token' => $user->createToken($user->name)->plainTextToken,
            ]);
        }

        return response()->json([
            'error' => 'Autenticação com erro.',
        ], 401);
    }

    /**
     * Recebe dados para realizar registro do usuário
     */
    public function register(array $data): JsonResponse
    {
        $user = User::create($data);

        $user->roles()->attach([2]);

        return response()->json(['success' => $user], 201);
    }

    /**
     * Realiza logout e limpeza dos tokens
     */
    public function logout($user): JsonResponse
    {
        $user->tokens()->delete();

        return response()->json(['logged' => false]);
    }
}
