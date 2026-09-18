<?php

namespace App\Services;

use App\Models\BulkOrder;
use App\Models\CoffeeTrailMarkerView;
use App\Models\CouponPromo;
use App\Models\CouponPromoRedemption;
use App\Models\Establishment;
use App\Models\EstablishmentVariety;
use App\Models\Order;
use App\Models\Product;
use App\Models\Rating;
use App\Models\Recommendation;
use App\Models\RecommendationSnapshot;
use App\Models\RecommendationSnapshotItem;
use App\Models\ResellerProduct;
use Illuminate\Support\Facades\DB;

class EstablishmentDeletionService
{
    public function delete(Establishment $establishment): void
    {
        DB::transaction(function () use ($establishment) {
            $establishmentId = (int) $establishment->getKey();

            $productIds = Product::query()
                ->where('establishment_id', $establishmentId)
                ->pluck('id')
                ->all();

            if (!empty($productIds)) {
                ResellerProduct::query()->whereIn('product_id', $productIds)->delete();
                Order::query()->whereIn('product_id', $productIds)->delete();
                BulkOrder::query()->whereIn('product_id', $productIds)->delete();
                Rating::query()->whereIn('product_id', $productIds)->delete();
                Product::query()->whereIn('id', $productIds)->delete();
            }

            $ratingIds = Rating::query()->where('establishment_id', $establishmentId)->pluck('id')->all();
            if (!empty($ratingIds)) {
                Rating::query()->whereIn('id', $ratingIds)->forceDelete();
            }

            $promoIds = CouponPromo::query()->where('establishment_id', $establishmentId)->pluck('id')->all();
            if (!empty($promoIds)) {
                CouponPromoRedemption::query()->whereIn('coupon_promo_id', $promoIds)->delete();
                CouponPromo::query()->whereIn('id', $promoIds)->forceDelete();
            }

            $snapshotIds = RecommendationSnapshot::query()->where('establishment_id', $establishmentId)->pluck('id')->all();
            if (!empty($snapshotIds)) {
                RecommendationSnapshotItem::query()->whereIn('recommendation_snapshot_id', $snapshotIds)->delete();
                RecommendationSnapshot::query()->whereIn('id', $snapshotIds)->delete();
            }

            Recommendation::query()->where('establishment_id', $establishmentId)->delete();
            CoffeeTrailMarkerView::query()->where('establishment_id', $establishmentId)->delete();
            CouponPromoRedemption::query()->where('establishment_id', $establishmentId)->delete();
            EstablishmentVariety::query()->where('establishment_id', $establishmentId)->delete();

            $establishment->forceDelete();
        });
    }
}
