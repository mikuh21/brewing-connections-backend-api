<?php

namespace App\Services;

use Illuminate\Support\Collection;

class MapDetailsService
{
    public static function products(Collection $products): array
    {
        return $products
            ->filter(fn ($product) => filled($product->name))
            ->map(fn ($product) => [
                'id' => (int) $product->id,
                'name' => (string) $product->name,
                'description' => $product->description,
                'category' => $product->category,
                'price_per_unit' => $product->price_per_unit,
                'unit' => $product->unit,
                'image_url' => $product->image_url,
                'is_active' => (bool) ($product->is_active ?? false),
            ])
            ->values()
            ->all();
    }

    public static function resellerProducts(Collection $resellerProducts): array
    {
        return $resellerProducts
            ->filter(fn ($resellerProduct) => $resellerProduct->product && filled($resellerProduct->product->name))
            ->map(function ($resellerProduct) {
                $product = $resellerProduct->product;

                return [
                    'id' => (int) $product->id,
                    'name' => (string) $product->name,
                    'description' => $product->description,
                    'category' => $product->category,
                    'price_per_unit' => $resellerProduct->reseller_price ?? $product->price_per_unit,
                    'unit' => $product->unit,
                    'image_url' => $product->image_url,
                    'is_active' => (bool) ($product->is_active ?? false),
                ];
            })
            ->values()
            ->all();
    }
}
