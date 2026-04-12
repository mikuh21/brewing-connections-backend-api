<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        $user = Auth::user();
        if (!$user) {
            return redirect('/login');
        }

        // Admin only
        if ($role === 'admin' && $user->role === 'admin') {
            return $next($request);
        }

        // Owner roles
        if ($role === 'owner' && in_array($user->role, ['farm_owner', 'cafe_owner', 'reseller'])) {
            return $next($request);
        }

        // Consumer only
        if ($role === 'consumer' && $user->role === 'consumer') {
            return $next($request);
        }

        // Reseller only
        if ($role === 'reseller' && $user->role === 'reseller') {
            return $next($request);
        }

        // Unauthorized
        Auth::logout();
        return redirect('/login')->withErrors(['email' => 'Unauthorized access.']);
    }
}
