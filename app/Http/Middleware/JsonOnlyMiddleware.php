<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JsonOnlyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Empty body requests are allowed
        if ($request->getContent() === '') {
            return $next($request);
        }

        if (!$request->isJson()) {
            return response()->json([
                'message' => 'Only JSON requests are accepted'
            ], Response::HTTP_BAD_REQUEST);
        }

        return $next($request);
    }
}
