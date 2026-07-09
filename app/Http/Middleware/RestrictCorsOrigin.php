<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictCorsOrigin
{
    public function handle(Request $request, Closure $next): Response
    {
        $origin = $request->headers->get('Origin');

        if (! $origin || ! $request->is('api/*', 'sanctum/csrf-cookie')) {
            return $next($request);
        }

        $allowedOrigins = config('cors.allowed_origins', []);

        if (! is_array($allowedOrigins)) {
            $allowedOrigins = [];
        }

        if (! in_array($origin, $allowedOrigins, true)) {
            return response('', 403);
        }

        return $next($request);
    }
}
