<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class RegistrationController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'all');
        $accountType = $request->query('account_type', 'all');
        $search = trim((string) $request->query('search', ''));

        // Include consumers and establishment owners. Resellers and admins stay on their own pages.
        $baseQuery = User::query()->where(function ($query) {
            $query->where('role', 'consumer')
                ->orWhereIn('role', ['farm_owner', 'cafe_owner']);
        });

        $totalConsumers = (clone $baseQuery)->count();
        $activeConsumers = (clone $baseQuery)->where('status', 'active')->count();
        $deactivatedConsumers = (clone $baseQuery)->where('status', 'deactivated')->count();

        $query = clone $baseQuery;

        if ($accountType === 'farm_owners') {
            $query->where('role', 'farm_owner');
        } elseif ($accountType === 'coffee_shop_owners') {
            $query->where('role', 'cafe_owner')
                ->whereHas('establishment', fn ($q) => $q->where('type', 'cafe'));
        } elseif ($accountType === 'consumers') {
            $query->where('role', 'consumer');
        } elseif ($accountType === 'roasters') {
            // Roasters are stored as cafe_owner accounts; establishment type distinguishes them.
            $query->where('role', 'cafe_owner')
                ->whereHas('establishment', fn ($q) => $q->where('type', 'roaster'));
        }

        if ($filter === 'active') {
            $query->where('status', 'active');
        } elseif ($filter === 'deactivated') {
            $query->where('status', 'deactivated');
        } elseif ($filter === 'last_30_days') {
            $query->where('created_at', '>=', now()->subDays(30));
        }

        if ($search !== '') {
            $term = '%' . addcslashes($search, '%_\\\\') . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'ILIKE', $term)
                    ->orWhere('email', 'ILIKE', $term)
                    ->orWhere('contact_number', 'ILIKE', $term);
            });
        }

        $consumers = $query->with('establishment')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.registrations', compact(
            'consumers',
            'totalConsumers',
            'activeConsumers',
            'deactivatedConsumers',
            'filter',
            'accountType',
            'search'
        ));
    }

    public function deactivate(User $user): RedirectResponse
    {
        abort_unless(in_array($user->role, ['consumer', 'farm_owner', 'cafe_owner'], true), 404);

        $user->status = 'deactivated';
        $user->deactivated_at = now();
        $user->save();

        return back()->with('success', 'Account has been deactivated successfully.');
    }
}
