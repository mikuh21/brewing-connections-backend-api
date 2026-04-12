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

        // Overview counts for consumers (no query filters)
        $totalConsumers = User::where('role', 'consumer')->count();
        $activeConsumers = User::where('role', 'consumer')->where('status', 'active')->count();
        $deactivatedConsumers = User::where('role', 'consumer')->where('status', 'deactivated')->count();

        $query = User::where('role', 'consumer');

        if ($filter === 'active') {
            $query->where('status', 'active');
        } elseif ($filter === 'deactivated') {
            $query->where('status', 'deactivated');
        } elseif ($filter === 'last_30_days') {
            $query->where('created_at', '>=', now()->subDays(30));
        }

        $consumers = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.registrations', compact(
            'consumers',
            'totalConsumers',
            'activeConsumers',
            'deactivatedConsumers',
            'filter'
        ));
    }

    public function deactivate(User $user): RedirectResponse
    {
        $user->status = 'deactivated';
        $user->deactivated_at = now();
        $user->save();

        return back()->with('success', 'Consumer has been deactivated successfully.');
    }
}
