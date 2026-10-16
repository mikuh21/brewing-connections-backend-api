<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CoffeeTrailLocationCheck;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LocationCheckController extends Controller
{
    public function index(Request $request): View
    {
        $status = (string) $request->query('status', 'all');
        if (!in_array($status, ['all', 'pending', 'reviewed', 'dismissed'], true)) {
            $status = 'all';
        }

        $query = CoffeeTrailLocationCheck::query()
            ->with(['user:id,name,email', 'establishment', 'reviewer:id,name'])
            ->latest();

        if ($status !== 'all') {
            $query->where('review_status', $status);
        }

        $checks = $query->paginate(25)->withQueryString();

        $counts = [
            'all' => CoffeeTrailLocationCheck::count(),
            'pending' => CoffeeTrailLocationCheck::where('review_status', 'pending')->count(),
            'reviewed' => CoffeeTrailLocationCheck::where('review_status', 'reviewed')->count(),
            'dismissed' => CoffeeTrailLocationCheck::where('review_status', 'dismissed')->count(),
            'incorrect' => CoffeeTrailLocationCheck::where('response', 'no')->count(),
        ];

        return view('admin.location-checks', compact('checks', 'status', 'counts'));
    }

    public function updateStatus(Request $request, CoffeeTrailLocationCheck $locationCheck): RedirectResponse
    {
        $data = $request->validate([
            'review_status' => ['required', Rule::in(['reviewed', 'dismissed', 'pending'])],
        ]);

        $locationCheck->update([
            'review_status' => $data['review_status'],
            'reviewed_at' => $data['review_status'] === 'pending' ? null : now(),
            'reviewed_by' => $data['review_status'] === 'pending' ? null : $request->user()->id,
        ]);

        return back()->with('success', 'Location check updated successfully. The establishment coordinates were not changed.');
    }
}
