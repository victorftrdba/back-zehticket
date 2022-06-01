<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\EventController;

// Rotas usuário
Route::middleware('auth:sanctum')->get('/user', [UserController::class, 'user']);
Route::post('/login', [UserController::class, 'login'])->name('login');
Route::post('/register', [UserController::class, 'register']);
Route::middleware('auth:sanctum')->post('/logout', [UserController::class, 'logout']);

// Rotas evento
Route::middleware('auth:sanctum')->post('/new-event', [EventController::class, 'store']);
Route::get('/show-events', [EventController::class, 'findAll']);
Route::get('/show-events/{id}', [EventController::class, 'show']);
Route::middleware('auth:sanctum')->post('/buy-ticket', [EventController::class, 'buyTicket']);
Route::middleware('auth:sanctum')->get('/show-user-events', [EventController::class, 'showUserEvents']);
