<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    private const SUPPORTED = ['en', 'ko'];

    public function handle(Request $request, Closure $next)
    {
        $locale = $request->session()->get('locale');

        if (in_array($locale, self::SUPPORTED, true)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
