<?php

namespace App\Services;

use App\Models\Product;

class DeliveryFeeEstimator
{
    public static function estimateForProduct(Product $product, int $quantity = 1): float
    {
        $perUnitGrams = null;
        if (isset($product->weight_grams) && is_numeric($product->weight_grams) && $product->weight_grams > 0) {
            $perUnitGrams = (int) $product->weight_grams;
        }
        if ($perUnitGrams === null && !empty($product->unit)) {
            $perUnitGrams = static::parseUnitToGrams((string) $product->unit);
        }
        $perUnitGrams ??= 500;
        return static::computeFeeByWeight($perUnitGrams * max(1, (int) $quantity));
    }

    public static function parseUnitToGrams(string $unit): ?int
    {
        $raw = strtolower(trim($unit));
        if (preg_match('/^(\d+(?:\.\d+)?)\s*(g|grams?)$/', $raw, $m)) {
            return (int) round((float) $m[1]);
        }
        if (preg_match('/^(\d+(?:\.\d+)?)\s*(kg)$/', $raw, $m)) {
            return (int) round((float) $m[1] * 1000);
        }
        return null;
    }

    /** Generic legacy estimator retained for existing non-landing marketplace flows. */
    public static function computeFeeByWeight(int $totalGrams): float
    {
        if ($totalGrams <= 0) return 0.0;
        if ($totalGrams <= 500) return 90.0;
        if ($totalGrams <= 1000) return 160.0;
        return 160.0 * (int) ceil($totalGrams / 1000);
    }
}
