<?php

namespace App\Http\Controllers\CafeOwner;

use App\Http\Controllers\Controller;
use App\Models\CouponPromo;
use App\Models\Establishment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CafeOwnerCouponPromoController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $establishmentId = $this->resolveOwnedEstablishmentId((int) $userId);

        $activeCoupons = 0;
        $expiredCoupons = 0;
        $draftCoupons = 0;
        $coupons = collect();

        if ($establishmentId) {
            $today = Carbon::today();

            $activeCoupons = CouponPromo::query()
                ->where('establishment_id', $establishmentId)
                ->where('status', 'active')
                ->whereDate('valid_until', '>=', $today)
                ->count();

            $expiredCoupons = CouponPromo::query()
                ->where('establishment_id', $establishmentId)
                ->where(function ($query) use ($today) {
                    $query->where('status', 'expired')
                        ->orWhereDate('valid_until', '<', $today);
                })
                ->count();

            $draftCoupons = CouponPromo::query()
                ->where('establishment_id', $establishmentId)
                ->where('status', 'draft')
                ->count();

            $coupons = CouponPromo::query()
                ->where('establishment_id', $establishmentId)
                ->select([
                    'id',
                    'title',
                    'description',
                    'discount_type',
                    'discount_value',
                    'valid_from',
                    'valid_until',
                    'max_usage',
                    'used_count',
                    'status',
                    'qr_code_token',
                ])
                ->orderByDesc('created_at')
                ->get();
        }

        $establishment = Establishment::where('owner_id', $userId)->first();

        return view('cafe-owner.coupon-promos', compact(
            'activeCoupons',
            'expiredCoupons',
            'draftCoupons',
            'coupons',
            'establishment'
        ));
    }

    public function store(Request $request)
    {
        $userId = Auth::id();
        $establishmentId = $this->resolveOwnedEstablishmentId((int) $userId);

        if (!$establishmentId) {
            return back()->with('error', 'No establishment found for this account.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after:valid_from',
            'max_usage' => 'required|integer|min:1',
            'status' => 'required|in:active,draft',
        ]);

        $status = $validated['status'];

        if ($status === 'active' && Carbon::parse($validated['valid_until'])->lt(Carbon::today())) {
            $status = 'expired';
        }

        CouponPromo::query()->create([
            'establishment_id' => $establishmentId,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'discount_type' => $validated['discount_type'],
            'discount_value' => $validated['discount_value'],
            'valid_from' => $validated['valid_from'],
            'valid_until' => $validated['valid_until'],
            'max_usage' => $validated['max_usage'],
            'status' => $status,
            'qr_code_token' => (string) Str::uuid(),
        ]);

        return back()->with('success', 'Coupon promo created successfully.');
    }

    public function update(Request $request, $id)
    {
        $userId = Auth::id();
        $establishmentId = $this->resolveOwnedEstablishmentId((int) $userId);

        if (!$establishmentId) {
            return back()->with('error', 'No establishment found for this account.');
        }

        $coupon = CouponPromo::query()
            ->where('id', $id)
            ->where('establishment_id', $establishmentId)
            ->first();

        if (!$coupon) {
            return back()->with('error', 'Coupon promo not found.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after:valid_from',
            'max_usage' => 'required|integer|min:1',
            'status' => 'required|in:active,draft',
        ]);

        $status = $validated['status'];

        if ($status === 'active' && Carbon::parse($validated['valid_until'])->lt(Carbon::today())) {
            $status = 'expired';
        }

        $coupon->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'discount_type' => $validated['discount_type'],
            'discount_value' => $validated['discount_value'],
            'valid_from' => $validated['valid_from'],
            'valid_until' => $validated['valid_until'],
            'max_usage' => $validated['max_usage'],
            'status' => $status,
        ]);

        return back()->with('success', 'Coupon promo updated successfully.');
    }

    public function destroy($id)
    {
        $userId = Auth::id();
        $establishmentId = $this->resolveOwnedEstablishmentId((int) $userId);

        if ($establishmentId) {
            CouponPromo::query()
                ->where('id', $id)
                ->where('establishment_id', $establishmentId)
                ->delete();
        }

        return back()->with('success', 'Coupon promo deleted successfully.');
    }

    protected function resolveOwnedEstablishmentId(int $userId): ?int
    {
        $query = Establishment::query();

        if (Schema::hasColumn('establishments', 'user_id')) {
            return $query->where('user_id', $userId)->value('id');
        }

        if (Schema::hasColumn('establishments', 'owner_id')) {
            return $query->where('owner_id', $userId)->value('id');
        }

        return null;
    }
}
