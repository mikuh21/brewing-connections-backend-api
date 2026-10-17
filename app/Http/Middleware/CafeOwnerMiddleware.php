<?php

namespace App\Http\Middleware;

use App\Models\Establishment;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CafeOwnerMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->role !== 'cafe_owner') {
            return redirect()
                ->route('login')
                ->with('error', 'You are not authorized to access that page.');
        }

        $ownsRoaster = Establishment::query()
            ->where('type', 'roaster')
            ->whereNull('deleted_at')
            ->where(function ($query) use ($user) {
                $query->where('owner_id', $user->id)
                    ->orWhere('user_id', $user->id);
            })
            ->exists();

        if ($ownsRoaster) {
            return redirect()
                ->route('farm-owner.dashboard')
                ->with('status', 'You are viewing your roaster workspace.');
        }

        return $next($request);
    }
}
