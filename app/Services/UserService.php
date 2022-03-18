<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserService {
    public function login($request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => [
                    'Dados incorretos.'
                ],
            ]);
        }

        return response()->json([
            'token' => $user->createToken($user->name)->plainTextToken,
        ]);
    }

    public function register($request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->roles()->attach([2]);

        return response()->json(['success' => $user]);
    }

    public function logout($request)
    {
        $user = $request->user();

        $user->tokens()->delete();

        return response()->json(['logged' => false]);
    }
}