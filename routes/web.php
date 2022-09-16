<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/verify-email', function (Request $request) {
    User::find($request->get('id'))->markEmailAsVerified();
    return view('confirmed');
})->name('verification.verify');
