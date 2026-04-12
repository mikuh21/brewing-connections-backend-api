<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CouponPromo;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CouponPromoController extends Controller
{
    public function index(Request $request)
    {
        $query = CouponPromo::with(['establishment' => function ($q) {
            $q->where('type', 'cafe');
        }]);

        $currentFilter = $request->get('filter', 'all');

        if ($currentFilter === 'active') {
            $query->active();
        } elseif ($currentFilter === 'expired') {
            $query->expired();
        }
        // For 'all', no additional filter needed as SoftDeletes excludes soft-deleted

        $coupons = $query->get();

        $totalCoupons = CouponPromo::count();
        $activeCoupons = CouponPromo::active()->count();
        $expiredCoupons = CouponPromo::expired()->count();

        return view('admin.coupon-promos', compact(
            'coupons',
            'totalCoupons',
            'activeCoupons',
            'expiredCoupons',
            'currentFilter'
        ));
    }

    public function show(Request $request, $id)
    {
        if (!$request->expectsJson() && !$request->ajax()) {
            return redirect()->route('admin.coupon-promos.index');
        }

        $coupon = CouponPromo::with('establishment')->find($id);

        if (!$coupon) {
            return response()->json(['error' => 'Coupon not found'], 404);
        }

        return response()->json([
            'id' => $coupon->id,
            'title' => $coupon->title,
            'description' => $coupon->description,
            'discount_type' => $coupon->discount_type,
            'discount_value' => $coupon->discount_value,
            'qr_code_token' => $coupon->qr_code_token,
            'valid_from' => $coupon->valid_from,
            'valid_until' => $coupon->valid_until,
            'max_usage' => $coupon->max_usage,
            'used_count' => $coupon->used_count,
            'status' => $coupon->status,
            'establishment' => $coupon->establishment->name ?? null
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $coupon = CouponPromo::find($id);

        if (!$coupon) {
            return redirect()->back()->with('error', 'Coupon not found.');
        }

        $coupon->delete(); // Soft delete

        return redirect()->back()->with('success', 'Coupon promo deleted successfully.');
    }
}