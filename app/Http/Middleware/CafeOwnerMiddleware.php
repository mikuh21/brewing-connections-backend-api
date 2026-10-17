<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CafeOwnerMiddleware
{
    /**
     * Restrict cafe-only pages to actual cafe owners.
     * Roaster accounts have their own dedicated role and workspace.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || $request->user()->role !== 'cafe_owner') {
            return redirect()
                ->route('login')
                ->with('error', 'You are not authorized to access that page.');
        }

        return $next($request);
    }
}
