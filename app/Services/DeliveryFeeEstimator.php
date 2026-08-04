<?php

namespace App\Services;

use App\Models\Product;

class DeliveryFeeEstimator
{
    /**
     * Estimate delivery fee based on product weight (grams) and quantity.
     *
     * @param Product $product
     * @param int $quantity
     * @return float
     */
    public static function estimateForProduct(Product $product, int $quantity = 1): float
    {
        $perUnitGrams = null;

        // Use explicit weight attribute if available
        if (isset($product->weight_grams) && is_numeric($product->weight_grams) && $product->weight_grams > 0) {
            $perUnitGrams = (int) $product->weight_grams;
        }

        // Try to parse from unit (e.g. '250g', '0.5kg')
        if ($perUnitGrams === null && !empty($product->unit)) {
            $parsed = static::parseUnitToGrams((string) $product->unit);
            if ($parsed !== null) {
                $perUnitGrams = $parsed;
            }
        }

        // Fallback to 500g when unknown
        if ($perUnitGrams === null) {
            $perUnitGrams = 500;
        }

        $totalGrams = $perUnitGrams * max(1, (int) $quantity);

        return static::computeFeeByWeight($totalGrams);
    }

    public static function parseUnitToGrams(string $unit): ?int
    {
        $raw = strtolower(trim($unit));

        // match grams e.g. '250g' or '250 g'
        if (preg_match('/^(\d+(?:\.\d+)?)\s*(g|grams?)$/', $raw, $m)) {
            return (int) round((float) $m[1]);
        }

        // match kg e.g. '0.5kg' or '1 kg'
        if (preg_match('/^(\d+(?:\.\d+)?)\s*(kg)$/', $raw, $m)) {
            return (int) round((float) $m[1] * 1000);
        }

        return null;
    }

    public static function computeFeeByWeight(int $totalGrams): float
    {
        if ($totalGrams <= 0) {
            return 0.0;
        }

        if ($totalGrams <= 500) {
            return (float) random_int(85, 95);
        }

        if ($totalGrams <= 1000) {
            return (float) random_int(155, 165);
        }

        $kg = (int) ceil($totalGrams / 1000);
        $base = random_int(155, 165);
        return (float) ($base * $kg);
    }
}
