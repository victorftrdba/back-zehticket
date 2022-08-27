<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\EventController;

Route::controller(UserController::class)->group(function () {
    Route::post('/login', 'login')->name('login');
    Route::post('/register', 'register');
    Route::middleware('auth:sanctum')->get('/user', 'user');
    Route::middleware('auth:sanctum')->post('/logout', 'logout');
});

Route::controller(EventController::class)->group(function () {
    Route::get('/show-events', 'findAll');
    Route::get('/show-events/{id}', 'show');
    Route::middleware('auth:sanctum')->get('/show-user-events', 'showUserEvents');
    Route::middleware('auth:sanctum')->post('/buy-ticket', 'buyTicket');
});