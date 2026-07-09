<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StaffOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user || ! $user->hasAnyRole(['admin', 'staff', 'super_admin'])) {
            abort(403, 'Staff only.');
        }
        return $next($request);
    }
}
