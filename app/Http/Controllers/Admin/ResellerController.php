<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ResellerVerifiedMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ResellerController extends Controller
{
    public function index(Request $request)
    {
        $statusFilter = $request->query('status', 'all');
        $verifiedFilter = $request->query('verified', 'all');
        $periodFilter = $request->query('period', 'all');
        $search = $request->query('search', '');

        // Base query: only resellers
        $query = User::where('role', 'reseller');

        // Apply status filter
        if ($statusFilter === 'active') {
            $query->where('status', 'active');
        } elseif ($statusFilter === 'deactivated') {
            $query->where('status', 'deactivated');
        }

        // Apply verified filter
        if ($verifiedFilter === 'verified') {
            $query->where('is_verified_reseller', true);
        } elseif ($verifiedFilter === 'unverified') {
            $query->where(function ($q) {
                $q->where('is_verified_reseller', false)
                  ->orWhereNull('is_verified_reseller');
            });
        }

        // Apply period filter
        if ($periodFilter === 'last_7_days') {
            $query->where('created_at', '>=', now()->subDays(7));
        } elseif ($periodFilter === 'last_30_days') {
            $query->where('created_at', '>=', now()->subDays(30));
        } elseif ($periodFilter === 'this_month') {
            $query->whereMonth('created_at', now()->month)
                  ->whereYear('created_at', now()->year);
        } elseif ($periodFilter === 'this_year') {
            $query->whereYear('created_at', now()->year);
        }

        // Apply search filter (ILIKE for PostgreSQL)
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%$search%")
                  ->orWhere('email', 'ILIKE', "%$search%");
            });
        }

        $resellers = $query->orderByDesc('created_at')
            ->paginate(15)
            ->appends($request->query());

        // Overview counts (from full dataset, no filters except role)
        $totalResellers = User::where('role', 'reseller')->count();
        $verifiedResellers = User::where('role', 'reseller')->where('is_verified_reseller', true)->count();
        $unverifiedResellers = User::where('role', 'reseller')
            ->where(function ($q) {
                $q->where('is_verified_reseller', false)
                  ->orWhereNull('is_verified_reseller');
            })
            ->count();
        $deactivatedResellers = User::where('role', 'reseller')->where('status', 'deactivated')->count();

        // Determine active filter for view
        $filter = 'all';
        if ($verifiedFilter === 'verified') {
            $filter = 'verified';
        } elseif ($verifiedFilter === 'unverified') {
            $filter = 'unverified';
        } elseif ($statusFilter === 'deactivated') {
            $filter = 'deactivated';
        } elseif ($periodFilter === 'last_30_days') {
            $filter = 'last_30_days';
        }

        return view('admin.resellers', [
            'resellers' => $resellers,
            'totalResellers' => $totalResellers,
            'verifiedResellers' => $verifiedResellers,
            'unverifiedResellers' => $unverifiedResellers,
            'deactivatedResellers' => $deactivatedResellers,
            'statusFilter' => $statusFilter,
            'verifiedFilter' => $verifiedFilter,
            'periodFilter' => $periodFilter,
            'search' => $search,
            'filter' => $filter,
        ]);
    }

    public function verify(User $user)
    {
        $wasVerified = (bool) $user->is_verified_reseller;

        $user->is_verified_reseller = true;
        $user->status = 'active';
        $user->save();

        if (!$wasVerified) {
            try {
                Mail::to($user->email)->send(new ResellerVerifiedMail($user));
            } catch (Throwable $e) {
                return back()->with('warning', 'Reseller was verified, but the email notification could not be sent.');
            }
        }

        return back()->with('success', 'Reseller has been verified successfully.');
    }

    public function deactivate(User $user)
    {
        $user->status = 'deactivated';
        $user->deactivated_at = now();
        $user->deactivation_notice_seen_at = null;
        $user->save();
        session()->flash('success', 'Reseller has been deactivated successfully.');
        return back();
    }
}
