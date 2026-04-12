<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use App\Models\Establishment;
use Illuminate\Http\Request;

class RatingModerationController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'all');

        $query = Rating::with([
            'user',
            'establishment' => function ($query) {
                $query->withTrashed();
            },
        ]);

        if ($filter === 'this_week') {
            $query->whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ]);
        } elseif ($filter === 'this_month') {
            $query->whereBetween('created_at', [
                now()->startOfMonth(),
                now()->endOfMonth()
            ]);
        }

        $ratings = $query->orderBy('created_at', 'desc')->paginate(3)->appends(request()->query());

        $totalRatings = Rating::count();

        // Most positive cafe
        $mostPositive = Rating::selectRaw('establishment_id, AVG(overall_rating) as avg_rating')
            ->groupBy('establishment_id')
            ->orderBy('avg_rating', 'desc')
            ->first();

        if ($mostPositive) {
            $mostPositive->establishment = Establishment::withTrashed()->find($mostPositive->establishment_id);
        }

        // Least positive cafe
        $leastPositive = Rating::selectRaw('establishment_id, AVG(overall_rating) as avg_rating')
            ->groupBy('establishment_id')
            ->orderBy('avg_rating', 'asc')
            ->first();

        if ($leastPositive) {
            $leastPositive->establishment = Establishment::withTrashed()->find($leastPositive->establishment_id);
        }

        return view('admin.rating-moderation', compact('ratings', 'filter', 'totalRatings', 'mostPositive', 'leastPositive'));
    }

    public function destroy(Rating $rating)
    {
        $rating->delete(); // soft delete — sets deleted_at timestamp, does NOT remove the row

        return back()->with('rating_deleted', true);
    }
}