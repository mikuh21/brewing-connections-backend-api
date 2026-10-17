<?php

namespace App\Http\Middleware;

use App\Models\Establishment;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
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

        $roasterQuery = Establishment::query()
            ->where('type', 'roaster')
            ->whereNull('deleted_at');
        $hasOwnerId = Schema::hasColumn('establishments', 'owner_id');
        $hasUserId = Schema::hasColumn('establishments', 'user_id');

        if ($hasOwnerId && $hasUserId) {
            $roasterQuery->where(function ($query) use ($user) {
                $query->where('owner_id', $user->id)
                    ->orWhere('user_id', $user->id);
            });
        } elseif ($hasOwnerId) {
            $roasterQuery->where('owner_id', $user->id);
        } elseif ($hasUserId) {
            $roasterQuery->where('user_id', $user->id);
        } else {
            $roasterQuery->whereRaw('1 = 0');
        }

        $ownsRoaster = $roasterQuery->exists();

        if ($ownsRoaster) {
            return redirect()
                ->route('farm-owner.dashboard')
                ->with('status', 'You are viewing your roaster workspace.');
        }

        return $next($request);
    }
}
