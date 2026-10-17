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

        // Repair legacy roaster sessions on their next workspace request. Older
        // accounts may still have cafe_owner in users.role even though their
        // linked establishment is a roaster; genuine cafe owners are untouched.
        if ($user && $user->role === 'cafe_owner') {
            $legacyRoasterQuery = Establishment::query()
                ->where('type', 'roaster')
                ->whereNull('deleted_at');
            $hasOwnerId = Schema::hasColumn('establishments', 'owner_id');
            $hasUserId = Schema::hasColumn('establishments', 'user_id');

            if ($hasOwnerId && $hasUserId) {
                $legacyRoasterQuery->where(function ($ownerQuery) use ($user) {
                    $ownerQuery->where('owner_id', $user->id)
                        ->orWhere('user_id', $user->id);
                });
            } elseif ($hasOwnerId) {
                $legacyRoasterQuery->where('owner_id', $user->id);
            } elseif ($hasUserId) {
                $legacyRoasterQuery->where('user_id', $user->id);
            } else {
                $legacyRoasterQuery->whereRaw('1 = 0');
            }

            if ($legacyRoasterQuery->exists()) {
                $user->forceFill(['role' => 'roaster'])->save();
            }
        }

        if (!$user || !in_array($user->role, ['farm_owner', 'roaster'], true)) {
            return redirect()
                ->route('login')
                ->with('error', 'You are not authorized to access that page.');
        }

        $expectedType = $user->role === 'roaster' ? 'roaster' : 'farm';
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

        $routeName = (string) optional($request->route())->getName();
        $isRoasterUser = $user->role === 'roaster';
        $isFarmWorkspaceRoute = str_starts_with($routeName, 'farm-owner.');
        $isRoasterWorkspaceRoute = str_starts_with($routeName, 'roaster-owner.');

        // Keep old farm-owner links working, but send roaster GET navigation to the canonical roaster URLs.
        if ($request->isMethod('GET') && $isRoasterUser && $isFarmWorkspaceRoute) {
            $suffix = substr($routeName, strlen('farm-owner.'));
            if ($suffix === 'my-farm') {
                $suffix = 'my-roaster';
            }
            $queryParams = $request->query();
            $activeEstablishmentId = $queryParams['roaster_id'] ?? $queryParams['farm_id'] ?? null;
            unset($queryParams['farm_id']);
            if ($activeEstablishmentId !== null && $activeEstablishmentId !== '') {
                $queryParams['roaster_id'] = $activeEstablishmentId;
            }

            return redirect()->route(
                'roaster-owner.' . $suffix,
                array_merge($request->route()->parameters(), $queryParams)
            );
        }

        // Prevent farm owners from landing on the roaster-specific URL namespace.
        if ($request->isMethod('GET') && !$isRoasterUser && $isRoasterWorkspaceRoute) {
            $suffix = substr($routeName, strlen('roaster-owner.'));
            if ($suffix === 'my-roaster') {
                $suffix = 'my-farm';
            }
            $queryParams = $request->query();
            $activeEstablishmentId = $queryParams['farm_id'] ?? $queryParams['roaster_id'] ?? null;
            unset($queryParams['roaster_id']);
            if ($activeEstablishmentId !== null && $activeEstablishmentId !== '') {
                $queryParams['farm_id'] = $activeEstablishmentId;
            }

            return redirect()->route(
                'farm-owner.' . $suffix,
                array_merge($request->route()->parameters(), $queryParams)
            );
        }

        return $next($request);
    }
}
