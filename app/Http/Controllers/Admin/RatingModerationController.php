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
        $activeTab = $request->query('tab');

        if (!in_array($activeTab, ['cafe', 'farm-products'], true)) {
            $activeTab = $request->has('farm_product_page') ? 'farm-products' : 'cafe';
        }

        $cafeRatingsQuery = Rating::with([
            'user',
            'establishment' => function ($query) {
                $query->withTrashed();
            },
        ])->whereNotNull('establishment_id');

        $farmProductRatingsQuery = Rating::with([
            'user',
            'product.establishment' => function ($query) {
                $query->withTrashed();
            },
            'product.seller',
        ])
            ->whereNotNull('product_id')
            ->whereHas('product', function ($query) {
                $query->where('seller_type', 'farm_owner');
            });

        if ($filter === 'this_week') {
            $dateRange = [
                now()->startOfWeek(),
                now()->endOfWeek()
            ];
        } elseif ($filter === 'this_month') {
            $dateRange = [
                now()->startOfMonth(),
                now()->endOfMonth()
            ];
        } else {
            $dateRange = null;
        }

        // Load all ratings for both tabs; period filtering is handled instantly in the view.
        $cafeRatings = $cafeRatingsQuery
            ->orderBy('created_at', 'desc')
            ->get();

        $farmProductRatings = $farmProductRatingsQuery
            ->orderBy('created_at', 'desc')
            ->get();

        $totalRatings = Rating::count();

        // Most positive cafe
        $mostPositive = Rating::selectRaw('establishment_id, AVG(overall_rating) as avg_rating')
            ->whereNotNull('establishment_id')
            ->groupBy('establishment_id')
            ->orderBy('avg_rating', 'desc')
            ->first();

        if ($mostPositive) {
            $mostPositive->establishment = Establishment::withTrashed()->find($mostPositive->establishment_id);
        }

        $recentFarmProductRating = Rating::with([
            'product.establishment' => function ($query) {
                $query->withTrashed();
            },
            'product.seller',
        ])
            ->whereNotNull('product_id')
            ->whereHas('product', function ($query) {
                $query->where('seller_type', 'farm_owner');
            });

        if ($dateRange) {
            $recentFarmProductRating->whereBetween('created_at', $dateRange);
        }

        $recentFarmProductRating = $recentFarmProductRating
            ->latest('created_at')
            ->first();

        return view('admin.rating-moderation', compact(
            'cafeRatings',
            'farmProductRatings',
            'filter',
            'activeTab',
            'totalRatings',
            'mostPositive',
            'recentFarmProductRating'
        ));
    }

    public function destroy(Rating $rating)
    {
        $rating->delete(); // soft delete — sets deleted_at timestamp, does NOT remove the row

        return back()->with('rating_deleted', true);
    }
}