<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Rating extends Model
{
    use SoftDeletes;

    protected $table = 'rating';

    protected $fillable = [
        'id',
        'user_id',
        'establishment_id',
        'product_id',
        'order_id',
        'taste_rating',
        'environment_rating',
        'cleanliness_rating',
        'service_rating',
        'overall_rating',
        'image',
        'owner_response',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'taste_rating' => 'integer',
        'environment_rating' => 'integer',
        'cleanliness_rating' => 'integer',
        'service_rating' => 'integer',
        'overall_rating' => 'decimal:2',
    ];

    protected $appends = [
        'image_url',
    ];

    /**
     * User relationship.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Establishment relationship.
     */
    public function establishment()
    {
        return $this->belongsTo(Establishment::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        return self::resolveImageUrl($this->attributes['image'] ?? null);
    }

    public static function resolveImageUrl(?string $image): ?string
    {
        $image = trim((string) $image);

        if ($image === '') {
            return null;
        }

        if (filter_var($image, FILTER_VALIDATE_URL)) {
            if (! Str::contains($image, 'supabase.co')) {
                return $image;
            }

            if (Str::contains($image, '/storage/v1/object/public/')) {
                return $image;
            }

            $parsed = parse_url($image);
            $path = ltrim((string) ($parsed['path'] ?? ''), '/');
            $origin = isset($parsed['scheme'], $parsed['host'])
                ? $parsed['scheme'] . '://' . $parsed['host'] . (isset($parsed['port']) ? ':' . $parsed['port'] : '')
                : null;

            return self::buildSupabasePublicUrl($path, $origin) ?? $image;
        }

        if (Str::startsWith($image, '/storage/')) {
            return $image;
        }

        return self::buildSupabasePublicUrl($image) ?? Storage::url($image);
    }

    private static function buildSupabasePublicUrl(string $path, ?string $originOverride = null): ?string
    {
        $normalizedPath = ltrim($path, '/');

        if ($normalizedPath === '') {
            return null;
        }

        $bucket = (string) config('filesystems.disks.supabase.bucket', '');
        if ($bucket === '') {
            $configuredUrl = (string) config('filesystems.disks.supabase.url', '');
            if (preg_match('#/storage/v1/object/public/([^/]+)#', $configuredUrl, $matches) === 1) {
                $bucket = (string) ($matches[1] ?? '');
            }
        }

        if ($bucket === '') {
            return null;
        }

        $origin = $originOverride ?: self::resolveSupabaseOrigin();
        if (! $origin) {
            return null;
        }

        return rtrim($origin, '/') . '/storage/v1/object/public/' . trim($bucket, '/') . '/' . $normalizedPath;
    }

    private static function resolveSupabaseOrigin(): ?string
    {
        $endpoint = (string) config('filesystems.disks.supabase.endpoint', '');
        if ($endpoint !== '') {
            $parsed = parse_url($endpoint);
            if (isset($parsed['scheme'], $parsed['host'])) {
                return $parsed['scheme'] . '://' . $parsed['host'] . (isset($parsed['port']) ? ':' . $parsed['port'] : '');
            }
        }

        $configuredUrl = (string) config('filesystems.disks.supabase.url', '');
        if ($configuredUrl !== '') {
            $parsed = parse_url($configuredUrl);
            if (isset($parsed['scheme'], $parsed['host'])) {
                return $parsed['scheme'] . '://' . $parsed['host'] . (isset($parsed['port']) ? ':' . $parsed['port'] : '');
            }
        }

        return null;
    }
}
