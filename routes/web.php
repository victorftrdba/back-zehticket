<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
   return view('welcome');
});

Route::get('/verify-email', function (\Illuminate\Http\Request $request) {
   \App\Models\User::find($request->get('id'))->markEmailAsVerified();
    return view('confirmed');
})->name('verification.verify');
