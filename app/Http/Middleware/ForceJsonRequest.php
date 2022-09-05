<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ForceJsonRequest
{
    public function handle(Request $request, Closure $next): JsonResponse
    {
        if (Str::contains($request->headers->get('Accept'), 'application/json')) {
            return $next($request);
        }

        return response()->json([
            'error' => 'Trying to make a request from WEB to an API'
        ], 500);
    }
}