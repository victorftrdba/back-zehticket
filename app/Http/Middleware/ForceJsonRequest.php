<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ForceJsonRequest
{
    public function handle(Request $request, Closure $next): JsonResponse
    {
        if ($request->headers->get('Accept') === 'application/json') {
            return $next($request);
        }

        return response()->json([
            'error' => 'Trying to make a request from WEB to an API'
        ], 500);
    }
}