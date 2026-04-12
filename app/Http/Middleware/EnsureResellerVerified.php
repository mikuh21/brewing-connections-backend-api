<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureResellerVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->role !== 'reseller') {
            return redirect()->route('login');
        }

        if ($user->status === 'deactivated') {
            if ($request->routeIs('reseller.dashboard')) {
                return $next($request);
            }

            return redirect()->route('reseller.dashboard');
        }

        if ((bool) $user->is_verified_reseller) {
            return $next($request);
        }

        // Allow dashboard access so the user can see the verification-wait modal.
        if ($request->routeIs('reseller.dashboard') || $request->routeIs('reseller.notifications')) {
            return $next($request);
        }

        return redirect()->route('reseller.dashboard');
    }
}
