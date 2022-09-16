<?php

use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::controller(UserController::class)->group(function () {
    Route::post('/login', 'login')->name('login');
    Route::post('/register', 'register');
    Route::middleware(['auth:sanctum', 'verified'])->get('/user', 'user');
    Route::middleware(['auth:sanctum', 'verified'])->post('/logout', 'logout');
});

Route::controller(EventController::class)->group(function () {
    Route::get('/show-events', 'findAll');
    Route::get('/show-events/{id}', 'show');
    Route::middleware(['auth:sanctum', 'verified'])->get('/show-user-events', 'showUserEvents');
    Route::middleware(['auth:sanctum', 'verified'])->post('/buy-ticket', 'buyTicket');
});

Route::post('/forgot-password', static function (Request $request) {
    $request->validate(['email' => 'required|email']);

    $status = Password::sendResetLink(
        $request->only('email')
    );

    return response()->json([
        'message' => $status === Password::RESET_LINK_SENT
            ? 'E-mail de recuperação de senha enviado com sucesso.'
            : 'Solicitação de redefinição de senha já solicitada ou ocorreu algum erro interno.'
    ]);
})->middleware('guest');

Route::post('/reset-password', static function (Request $request) {
    $request->validate([
        'token' => ['required', 'exists:password_resets,token'],
        'email' => ['required', 'email', 'exists:users,email'],
        'password' => ['required', 'min:8', 'string', 'confirmed'],
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        static function ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password)
            ])->setRememberToken(Str::random(60));
            $user->save();
            Password::deleteToken($user);
        }
    );

    return response()->json([
        'message' => $status === Password::PASSWORD_RESET
            ? 'Senha alterada com sucesso.'
            : 'Token já utilizado ou expirado.'
    ]);
})->middleware('guest');
