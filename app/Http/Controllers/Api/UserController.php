<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\LoginUserRequest;
use App\Http\Requests\User\RegisterUserRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private UserService $userService;

    public function __construct()
    {
        $this->userService = new UserService;
    }

    public function user(Request $request): JsonResponse
    {
        $this->authorize('1');

        return response()->json($request->user());
    }

    public function login(LoginUserRequest $request): JsonResponse
    {
        return $this->userService->login($request->validated());
    }

    public function register(RegisterUserRequest $request): JsonResponse
    {
        return $this->userService->register($request->validated());
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authorize('3');

        return $this->userService->logout($request->user());
    }
}
