<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $headers = [
            'X-Frame-Options'        => 'SAMEORIGIN',
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy'        => 'strict-origin-when-cross-origin',
            'Permissions-Policy'     => 'camera=(), microphone=(), geolocation=()',
            'X-XSS-Protection'       => '1; mode=block',
        ];

        // Alpine + Livewire compile inline expressions at runtime, which needs 'unsafe-eval'.
        $csp = "default-src 'self'; "
            ."img-src 'self' data: blob: https:; "
            ."font-src 'self' https://fonts.gstatic.com https://fonts.googleapis.com https://fonts.bunny.net data:; "
            ."style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.bunny.net https://cdn.tailwindcss.com https://cdn.jsdelivr.net; "
            ."script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com https://cdnjs.cloudflare.com https://unpkg.com https://cdn.jsdelivr.net https://challenges.cloudflare.com https://static.cloudflareinsights.com; "
            ."frame-src 'self' https://www.google.com https://maps.google.com https://challenges.cloudflare.com; "
            ."worker-src 'self' blob:; "
            ."connect-src 'self' https://challenges.cloudflare.com https://static.cloudflareinsights.com https://cloudflareinsights.com; "
            ."trusted-types * 'allow-duplicates';";

        $headers['Content-Security-Policy'] = $csp;

        // HSTS only when served over HTTPS in production
        if ($request->isSecure()) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
        }

        foreach ($headers as $key => $value) {
            if (in_array($key, ['Content-Security-Policy', 'Permissions-Policy'], true)) {
                $response->headers->set($key, $value);

                continue;
            }

            if (! $response->headers->has($key)) {
                $response->headers->set($key, $value);
            }
        }

        return $response;
    }
}
