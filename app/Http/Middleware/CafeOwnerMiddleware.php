<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CafeOwnerMiddleware
{
    /**
     * Restrict cafe-owner routes to actual cafe-owner accounts.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->role !== 'cafe_owner') {
            return redirect()
                ->route('login')
                ->with('error', 'You are not authorized to access that page.');
        }

        return $next($request);
    }
}
