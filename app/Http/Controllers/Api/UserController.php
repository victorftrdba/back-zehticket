<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Services\UserService;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    private $userService;

    public function __construct()
    {
        $this->userService = new UserService;
    }

    public function user(Request $request)
    {
        $this->authorize('1');

        return response()->json($request->user());
    }

    public function login(Request $request)
    {
        return $this->userService->login($request);
    }

    public function register(Request $request)
    {
        return $this->userService->register($request);
    }

    public function logout(Request $request)
    {
        $this->authorize('3');

        return $this->userService->logout($request);
    }
}