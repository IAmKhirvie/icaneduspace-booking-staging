<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleOnly
{
    /**
     * Usage: ->middleware('role:admin') or ->middleware('role:staff')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->guest(route('login'));
        }

        // super_admin can use admin paths too
        if (in_array('admin', $roles, true)) {
            $roles[] = 'super_admin';
        }

        if (! $user->hasAnyRole($roles)) {
            // Drop them onto whatever dashboard their role matches.
            if ($user->hasAnyRole(['admin', 'super_admin'])) return redirect('/admin/dashboard');
            if ($user->hasRole('staff'))                    return redirect('/staff/dashboard');
            return redirect('/dashboard');
        }

        return $next($request);
    }
}
