<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CouponPromo;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponPromoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $lat = is_numeric($request->query('lat')) ? (float) $request->query('lat') : null;
        $lng = is_numeric($request->query('lng')) ? (float) $request->query('lng') : null;

        $promos = CouponPromo::query()
            ->active()
            ->with(['establishment:id,name,type,latitude,longitude,image'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function (CouponPromo $promo) use ($lat, $lng) {
                $distanceKm = null;
                $establishment = $promo->establishment;

                if (
                    $lat !== null
                    && $lng !== null
                    && $establishment
                    && is_numeric($establishment->latitude)
                    && is_numeric($establishment->longitude)
                ) {
                    $distanceKm = $this->haversine(
                        $lat,
                        $lng,
                        (float) $establishment->latitude,
                        (float) $establishment->longitude
                    );
                }

                return [
                    'id' => $promo->id,
                    'establishment_id' => $promo->establishment_id,
                    'title' => $promo->title,
                    'description' => $promo->description,
                    'discount_type' => $promo->discount_type,
                    'discount_value' => $promo->discount_value,
                    'discount_text' => $this->buildDiscountText($promo),
                    'coupon_code' => $promo->qr_code_token,
                    'qr_code_token' => $promo->qr_code_token,
                    'valid_from' => optional($promo->valid_from)->toDateString(),
                    'valid_until' => optional($promo->valid_until)->toDateString(),
                    'status' => $promo->status,
                    'max_usage' => $promo->max_usage,
                    'used_count' => $promo->used_count,
                    'distance_km' => $distanceKm,
                    'establishment' => $establishment ? [
                        'id' => $establishment->id,
                        'name' => $establishment->name,
                        'type' => $establishment->type,
                        'latitude' => $establishment->latitude,
                        'longitude' => $establishment->longitude,
                        'image' => $establishment->image,
                        'is_verified' => false,
                    ] : null,
                ];
            })
            ->values();

        return response()->json([
            'promos' => $promos,
        ]);
    }

    public function verifyQr(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'qr_data' => ['required', 'string'],
        ]);

        $promo = CouponPromo::query()
            ->with(['establishment:id,name,type'])
            ->where('qr_code_token', $validated['qr_data'])
            ->first();

        if (!$promo) {
            return response()->json([
                'status' => 'invalid',
                'message' => 'Invalid or Expired QR Code',
            ], 422);
        }

        if (($promo->establishment?->type ?? null) !== 'cafe') {
            return response()->json([
                'status' => 'not_cafe',
                'message' => 'This QR code is not for a cafe promo',
            ], 422);
        }

        if ($promo->status !== 'active' || Carbon::parse($promo->valid_until)->lt(Carbon::today())) {
            return response()->json([
                'status' => 'invalid',
                'message' => 'Invalid or Expired QR Code',
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Promo Applied! ?',
            'promo' => [
                'id' => $promo->id,
                'title' => $promo->title,
                'description' => $promo->description,
                'code' => $promo->qr_code_token,
                'establishment' => [
                    'id' => $promo->establishment?->id,
                    'name' => $promo->establishment?->name,
                ],
            ],
        ]);
    }

    private function buildDiscountText(CouponPromo $promo): string
    {
        if ($promo->discount_type === 'percentage') {
            return rtrim(rtrim((string) $promo->discount_value, '0'), '.') . '% off';
        }

        if ($promo->discount_type === 'amount') {
            return 'PHP ' . number_format((float) $promo->discount_value, 2) . ' off';
        }

        return $promo->description ?: 'Exclusive in-store offer';
    }

    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) * sin($dLng / 2);

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
