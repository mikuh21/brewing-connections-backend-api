<?php

namespace App\Http\Controllers\CafeOwner;

use App\Http\Controllers\Controller;
use App\Models\CouponPromo;
use App\Models\CouponPromoRedemption;
use App\Models\Establishment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

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

    public function redeemScan(Request $request)
    {
        $userId = Auth::id();
        $establishmentId = $this->resolveOwnedEstablishmentId((int) $userId);

        if (!$establishmentId) {
            return response()->json([
                'status' => 'error',
                'message' => 'No establishment found for this account.',
            ], 403);
        }

        $validated = $request->validate([
            'qr_data' => 'required|string|max:4096',
        ]);

        [$promoToken, $consumerUserId] = $this->parseRedeemPayload((string) $validated['qr_data']);

        if ($promoToken === '') {
            return response()->json([
                'status' => 'invalid',
                'message' => 'Invalid QR payload.',
            ], 422);
        }

        if (!$consumerUserId) {
            return response()->json([
                'status' => 'invalid_consumer',
                'message' => 'Consumer identity is missing from QR payload.',
            ], 422);
        }

        $consumer = User::query()->select(['id', 'role'])->find($consumerUserId);
        if (!$consumer || strtolower((string) $consumer->role) !== 'consumer') {
            return response()->json([
                'status' => 'invalid_consumer',
                'message' => 'Invalid consumer for this QR code.',
            ], 422);
        }

        try {
            $result = DB::transaction(function () use ($establishmentId, $promoToken, $consumerUserId, $userId) {
                $promo = CouponPromo::query()
                    ->where('establishment_id', $establishmentId)
                    ->where('qr_code_token', $promoToken)
                    ->lockForUpdate()
                    ->first();

                if (!$promo) {
                    return [
                        'status' => 'invalid',
                        'message' => 'Promo not found for this cafe.',
                    ];
                }

                $today = Carbon::today();
                if ($promo->status !== 'active' || Carbon::parse($promo->valid_until)->lt($today)) {
                    return [
                        'status' => 'invalid',
                        'message' => 'This promo is no longer active.',
                    ];
                }

                if ((int) $promo->used_count >= (int) $promo->max_usage) {
                    if ($promo->status !== 'expired') {
                        $promo->status = 'expired';
                        $promo->save();
                    }

                    return [
                        'status' => 'maxed_out',
                        'message' => 'Promo usage limit has been reached.',
                    ];
                }

                $existing = CouponPromoRedemption::query()
                    ->where('coupon_promo_id', $promo->id)
                    ->where('consumer_user_id', $consumerUserId)
                    ->first();

                if ($existing) {
                    return [
                        'status' => 'already_redeemed',
                        'message' => 'This consumer has already redeemed this promo.',
                        'redeemed_at' => optional($existing->redeemed_at)->toIso8601String(),
                    ];
                }

                $redeemedAt = now();
                CouponPromoRedemption::query()->create([
                    'coupon_promo_id' => $promo->id,
                    'consumer_user_id' => $consumerUserId,
                    'establishment_id' => $establishmentId,
                    'scanned_by_user_id' => $userId,
                    'redeemed_at' => $redeemedAt,
                ]);

                $promo->used_count = (int) $promo->used_count + 1;
                if ($promo->used_count >= (int) $promo->max_usage) {
                    $promo->status = 'expired';
                }
                $promo->save();

                return [
                    'status' => 'success',
                    'message' => 'Promo redeemed successfully.',
                    'promo_id' => $promo->id,
                    'used_count' => (int) $promo->used_count,
                    'max_usage' => (int) $promo->max_usage,
                    'redeemed_at' => $redeemedAt->toIso8601String(),
                ];
            });

            $httpStatus = $result['status'] === 'success' ? 200 : 422;

            return response()->json([
                ...$result,
                'summary' => $this->buildCouponSummaryCounts((int) $establishmentId),
            ], $httpStatus);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'status' => 'error',
                'message' => 'Unable to redeem this promo right now.',
            ], 500);
        }
    }

    protected function parseRedeemPayload(string $payload): array
    {
        $raw = trim($payload);
        $promoToken = '';
        $consumerUserId = null;

        if (Str::startsWith($raw, '{')) {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $promoToken = trim((string) (
                    $decoded['promo_token']
                    ?? $decoded['qr_code_token']
                    ?? $decoded['qr_data']
                    ?? $decoded['token']
                    ?? ''
                ));

                $consumerUserId = (int) ($decoded['consumer_id'] ?? $decoded['user_id'] ?? 0);
                if ($consumerUserId <= 0) {
                    $consumerUserId = null;
                }
            }
        }

        if ($promoToken === '') {
            $promoToken = $raw;
        }

        return [$promoToken, $consumerUserId];
    }

    protected function buildCouponSummaryCounts(int $establishmentId): array
    {
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

        return [
            'active' => $activeCoupons,
            'expired' => $expiredCoupons,
            'draft' => $draftCoupons,
        ];
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
