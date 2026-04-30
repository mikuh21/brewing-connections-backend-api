<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RatingController extends Controller
{
    public function index(Request $request)
    {
        $perPage = max(1, min((int) $request->query("per_page", 20), 50));

        $ratings = Rating::query()
            ->whereNotNull('establishment_id')
            ->with("user", "establishment")
            ->latest()
            ->paginate($perPage);

        $ratings->getCollection()->transform(function (Rating $rating) {
            $payload = $rating->toArray();
            $payload["photo_url"] = $this->resolvePhotoUrl($rating->image);
            return $payload;
        });

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
