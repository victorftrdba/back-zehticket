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

        return $request->user();
    }

    public function login(Request $request)
    {
        $response = $this->userService->login($request);

        return $response;
    }

    public function register(Request $request)
    {
        $response = $this->userService->register($request);

        return $response;
    }

    public function logout(Request $request)
    {
        $this->authorize('3');

        $response = $this->userService->logout($request);

        return $response;
    }
}