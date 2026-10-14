<?php

namespace App\Services;

use App\Models\Product;

class DeliveryFeeEstimator
{
    /**
     * Estimate a matrix-based fee using package weight and the seller/delivery route.
     */
    public static function estimateForProductAndRoute(Product $product, int $quantity, ?string $originAddress, ?string $destinationAddress): float
    {
        $perUnitGrams = null;
        if (isset($product->weight_grams) && is_numeric($product->weight_grams) && $product->weight_grams > 0) {
            $perUnitGrams = (int) $product->weight_grams;
        }
        if ($perUnitGrams === null && !empty($product->unit)) {
            $perUnitGrams = static::parseUnitToGrams((string) $product->unit);
        }
        $perUnitGrams ??= 500;
        $totalGrams = $perUnitGrams * max(1, $quantity);

        return static::computeRouteFeeByWeight($totalGrams, static::resolveZone($originAddress, $destinationAddress));
    }

    public static function resolveZone(?string $originAddress, ?string $destinationAddress): string
    {
        $origin = static::normalizeRegion($originAddress);
        $destination = static::normalizeRegion($destinationAddress);
        if (!$origin || !$destination) {
            return 'cross-region';
        }
        if ($origin['island'] !== $destination['island']) {
            return 'inter-island';
        }
        return $origin['region'] === $destination['region'] ? 'intra-region' : 'cross-region';
    }

    private static function normalizeRegion(?string $address): ?array
    {
        $value = strtolower((string) $address);
        $groups = [
            ['NCR', 'Luzon', ['metro manila', 'national capital region', 'manila city', 'quezon city', 'makati', 'pasig', 'taguig', 'paranaque', 'las pinas', 'muntinlupa', 'caloocan', 'malabon', 'navotas', 'marikina', 'mandaluyong', 'san juan city', 'valenzuela', 'pateros']],
            ['CALABARZON', 'Luzon', ['batangas', 'cavite', 'laguna', 'rizal', 'quezon province', 'calabarzon', 'lipa city']],
            ['MIMAROPA', 'Luzon', ['oriental mindoro', 'occidental mindoro', 'marinduque', 'romblon', 'palawan', 'mimaropa']],
            ['Central Luzon', 'Luzon', ['aurora', 'bataan', 'bulacan', 'nueva ecija', 'pampanga', 'tarlac', 'zambales', 'central luzon']],
            ['Ilocos', 'Luzon', ['ilocos norte', 'ilocos sur', 'la union', 'pangasinan', 'ilocos region']],
            ['Cagayan Valley', 'Luzon', ['batanes', 'cagayan', 'isabela province', 'nueva vizcaya', 'quirino', 'cagayan valley']],
            ['Cordillera', 'Luzon', ['abra', 'apayao', 'benguet', 'ifugao', 'kalinga', 'mountain province', 'cordillera']],
            ['Bicol', 'Luzon', ['albay', 'camarines norte', 'camarines sur', 'catanduanes', 'masbate', 'sorsogon', 'bicol']],
            ['Western Visayas', 'Visayas', ['aklan', 'antique', 'capiz', 'guimaras', 'iloilo', 'western visayas']],
            ['Central Visayas', 'Visayas', ['bohol', 'cebu', 'negros oriental', 'siquijor', 'central visayas']],
            ['Eastern Visayas', 'Visayas', ['biliran', 'eastern samar', 'leyte', 'northern samar', 'samar', 'southern leyte', 'eastern visayas']],
            ['Zamboanga Peninsula', 'Mindanao', ['zamboanga del norte', 'zamboanga del sur', 'zamboanga sibugay', 'zamboanga peninsula']],
            ['Northern Mindanao', 'Mindanao', ['bukidnon', 'camiguin', 'lanao del norte', 'misamis occidental', 'misamis oriental', 'northern mindanao']],
            ['Davao', 'Mindanao', ['davao de oro', 'davao del norte', 'davao del sur', 'davao occidental', 'davao oriental', 'davao region']],
            ['SOCCSKSARGEN', 'Mindanao', ['cotabato', 'sarangani', 'south cotabato', 'sultan kudarat', 'soccsksargen']],
            ['Caraga', 'Mindanao', ['agusan', 'dinagat', 'surigao del norte', 'surigao del sur', 'caraga']],
            ['BARMM', 'Mindanao', ['basilan', 'lanao del sur', 'maguindanao', 'sulu', 'tawi-tawi', 'barmm']],
        ];
        foreach ($groups as [$region, $island, $terms]) {
            foreach ($terms as $term) {
                if (str_contains($value, $term)) {
                    return ['region' => $region, 'island' => $island];
                }
            }
        }
        return null;
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

    public static function computeRouteFeeByWeight(int $totalGrams, string $zone): float
    {
        if ($totalGrams <= 0) return 0.0;
        $rates = [
            'intra-region' => ['small' => 85.0, 'medium' => 115.0, 'large' => 155.0, 'extra_kg' => 30.0],
            'cross-region' => ['small' => 95.0, 'medium' => 165.0, 'large' => 190.0, 'extra_kg' => 50.0],
            'inter-island' => ['small' => 105.0, 'medium' => 195.0, 'large' => 220.0, 'extra_kg' => 85.0],
        ];
        $rate = $rates[$zone] ?? $rates['cross-region'];
        if ($totalGrams <= 500) return $rate['small'];
        if ($totalGrams <= 1000) return $rate['medium'];
        if ($totalGrams <= 3000) return $rate['large'];
        return $rate['large'] + (ceil(($totalGrams - 3000) / 1000) * $rate['extra_kg']);
    }

    /** Existing default estimator retained for other marketplace flows. */
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
        return static::computeFeeByWeight($perUnitGrams * max(1, $quantity));
    }

    public static function computeFeeByWeight(int $totalGrams): float
    {
        if ($totalGrams <= 0) return 0.0;
        if ($totalGrams <= 500) return 90.0;
        if ($totalGrams <= 1000) return 160.0;
        return 160.0 * (int) ceil($totalGrams / 1000);
    }
}
