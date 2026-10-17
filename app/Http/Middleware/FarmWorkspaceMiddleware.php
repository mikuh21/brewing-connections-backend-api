<?php

namespace App\Http\Middleware;

use App\Models\Establishment;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class FarmWorkspaceMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !in_array($user->role, ['farm_owner', 'cafe_owner'], true)) {
            return redirect()
                ->route('login')
                ->with('error', 'You are not authorized to access that page.');
        }

        $expectedType = $user->role === 'cafe_owner' ? 'roaster' : 'farm';
        $columns = Schema::getColumnListing('establishments');
        $hasOwnerId = in_array('owner_id', $columns, true);
        $hasUserId = in_array('user_id', $columns, true);

        $query = Establishment::query()
            ->where('type', $expectedType)
            ->whereNull('deleted_at');

        if ($hasOwnerId && $hasUserId) {
            $query->where(function ($ownerQuery) use ($user) {
                $ownerQuery->where('owner_id', $user->id)
                    ->orWhere('user_id', $user->id);
            });
        } elseif ($hasOwnerId) {
            $query->where('owner_id', $user->id);
        } elseif ($hasUserId) {
            $query->where('user_id', $user->id);
        } else {
            return redirect()
                ->route('login')
                ->with('error', 'You are not authorized to access that page.');
        }

        if (!$query->exists()) {
            return redirect()
                ->route('login')
                ->with('error', 'You are not authorized to access that workspace.');
        }

        return $next($request);
    }
}
