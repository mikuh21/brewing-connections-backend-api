<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use Illuminate\Http\RedirectResponse;

class EstablishmentController extends Controller
{
    /**
     * Display a listing of all establishments with overview statistics.
     */
    public function index()
    {
        // Get counts for overview cards
        $totalCount = Establishment::whereNull('deleted_at')->count();
        $farmCount = Establishment::where('type', 'farm')->whereNull('deleted_at')->count();
        $cafeCount = Establishment::where('type', 'cafe')->whereNull('deleted_at')->count();
        $roasterCount = Establishment::where('type', 'roaster')->whereNull('deleted_at')->count();

        // Get all establishments with eager-loaded relationships
        $establishments = Establishment::whereNull('deleted_at')
            ->with(['varieties', 'owner'])
            ->withAvg('reviews', 'overall_rating')
            ->get();

        return view('admin.establishments', compact(
            'establishments',
            'totalCount',
            'farmCount',
            'cafeCount',
            'roasterCount'
        ));
    }

    /**
     * Soft delete an establishment.
     */
    public function destroy(Establishment $establishment): RedirectResponse
    {
        $establishment->delete();

        return redirect()->back()->with('success', 'Establishment deleted successfully.');
    }
}
