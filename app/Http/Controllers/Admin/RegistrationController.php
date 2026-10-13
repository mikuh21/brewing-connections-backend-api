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

        // Load the complete registrations dataset once so status tabs and account-type
        // dropdown can filter instantly on the client without reloading or missing other roles.
        // Resellers and administrators remain excluded by $baseQuery above.
        $consumers = (clone $baseQuery)
            ->with('establishment')
            ->orderByDesc('created_at')
            ->get();

        $filter = in_array($filter, ['all', 'active', 'deactivated', 'last_30_days'], true) ? $filter : 'all';
        $accountType = in_array($accountType, ['all', 'farm_owners', 'coffee_shop_owners', 'consumers', 'roasters'], true) ? $accountType : 'all';
        $search = trim((string) $search);

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
