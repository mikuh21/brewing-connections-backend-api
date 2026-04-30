<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait NormalizesSupabaseMediaUrls
{
    public static function normalizeMediaUrl(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            if (! Str::contains($value, 'supabase.co')) {
                return $value;
            }

            if (Str::contains($value, '/storage/v1/object/public/')) {
                return $value;
            }

            $parsed = parse_url($value);
            $path = ltrim((string) ($parsed['path'] ?? ''), '/');
            $origin = isset($parsed['scheme'], $parsed['host'])
                ? $parsed['scheme'] . '://' . $parsed['host'] . (isset($parsed['port']) ? ':' . $parsed['port'] : '')
                : null;

            return static::buildSupabasePublicUrl($path, $origin) ?? $value;
        }

        if (Str::startsWith($value, '/storage/')) {
            return $value;
        }

        return static::buildSupabasePublicUrl($value) ?? Storage::url($value);
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

        $origin = $originOverride ?: static::resolveSupabaseOrigin();
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