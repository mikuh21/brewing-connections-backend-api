<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class RatingController extends Controller
{
    public function index(Request $request)
    {
        $perPage = max(1, min((int) $request->query('per_page', 20), 50));

        $cafeRatings = Rating::query()
            ->whereNotNull('establishment_id')
            ->with('user', 'establishment')
            ->latest()
            ->limit($perPage)
            ->get()
            ->map(function (Rating $rating) {
                $payload = $rating->toArray();
                $payload['feed_type'] = 'cafe';
                $payload['photo_url'] = $this->resolvePhotoUrl($rating->image);

                return $payload;
            });

        $farmProductRatingsQuery = Rating::query()
            ->whereNotNull('product_id')
            ->whereHas('product', function ($query) {
                if (Schema::hasColumn('products', 'seller_type')) {
                    $query->where('seller_type', 'farm_owner');
                }
            })
            ->with(['user', 'product.establishment', 'product.seller'])
            ->when(Schema::hasColumn('products', 'seller_type'), function ($query) {
                $query->whereHas('product', function ($productQuery) {
                    $productQuery->where('seller_type', 'farm_owner');
                });
            })
            ->latest()
            ->limit($perPage)
            ->get();

        $farmProductRatings = $farmProductRatingsQuery
            ->map(function (Rating $rating) {
                $payload = $rating->toArray();
                $payload['feed_type'] = 'farm_product';
                $payload['photo_url'] = $this->resolvePhotoUrl($rating->image);
                $payload['product_name'] = $rating->product?->name;
                $payload['product_image_url'] = $rating->product?->image_url;
                $payload['farm_name'] = $rating->product?->establishment?->name
                    ?? $rating->product?->seller?->name;
                $payload['farm_barangay'] = $rating->product?->establishment?->barangay;
                $payload['farm_address'] = $rating->product?->establishment?->address;

                return $payload;
            });

        $ratings = $cafeRatings
            ->concat($farmProductRatings)
            ->sortByDesc(function (array $rating) {
                return strtotime((string) ($rating['created_at'] ?? '')) ?: 0;
            })
            ->values();

        return response()->json($ratings);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            "establishment_id" => [
                "required",
                "integer",
                Rule::exists("establishments", "id")->where(function ($query) {
                    $query->whereRaw("LOWER(type) = ?", ["cafe"]);
                }),
            ],
            "taste_rating" => ["required", "integer", "min:1", "max:5"],
            "environment_rating" => ["required", "integer", "min:1", "max:5"],
            "cleanliness_rating" => ["required", "integer", "min:1", "max:5"],
            "service_rating" => ["required", "integer", "min:1", "max:5"],
            "photo" => ["nullable", "image", "max:5120"],
        ]);

        $imagePath = null;
        if ($request->hasFile("photo")) {
            $imagePath = $request->file("photo")->store("ratings", "public");
        }

        $rating = Rating::create([
            "user_id" => $request->user()->id,
            "establishment_id" => $validated["establishment_id"],
            "taste_rating" => $validated["taste_rating"],
            "environment_rating" => $validated["environment_rating"],
            "cleanliness_rating" => $validated["cleanliness_rating"],
            "service_rating" => $validated["service_rating"],
            "image" => $imagePath,
        ])->load("user", "establishment");

        $payload = $rating->toArray();
        $payload["photo_url"] = $this->resolvePhotoUrl($rating->image);

        return response()->json([
            "message" => "Rating submitted successfully.",
            "data" => $payload,
        ], 201);
    }

    private function resolvePhotoUrl(?string $imagePath): ?string
    {
        if (! $imagePath) {
            return null;
        }

        return "/storage/".ltrim($imagePath, "/");
    }
}
