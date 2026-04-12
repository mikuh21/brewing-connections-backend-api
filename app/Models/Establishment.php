<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Establishment extends Model
{
    use SoftDeletes;
    /**
     * Coffee varieties relationship (many-to-many).
     */
    public function varieties()
    {
        return $this->belongsToMany(
            \App\Models\CoffeeVariety::class, 
            'establishment_varieties', 
            'establishment_id', 
            'coffee_variety_id'
        )->withPivot('is_primary')->withTimestamps();
    }

    /**
     * Reviews relationship (one-to-many).
     */
    public function reviews()
    {
        return $this->hasMany(\App\Models\Rating::class, 'establishment_id');
    }

    /**
     * Owner relationship (belongs-to).
     */
    public function owner()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Coupon promos relationship (one-to-many).
     */
    public function couponPromos()
    {
        return $this->hasMany(\App\Models\CouponPromo::class, 'establishment_id');
    }

    protected $fillable = [
        'owner_id',
        'name',
        'type',
        'description',
        'address',
        'barangay',
        'latitude',
        'longitude',
        'contact_number',
        'email',
        'website',
        'visit_hours',
        'activities',
        'image',
        'banner_focus_x',
        'banner_focus_y',
        'profile_focus_x',
        'profile_focus_y',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Get coordinates as ['lat' => ..., 'lng' => ...]
     */
    public function getCoordinatesAttribute()
    {
        return [
            'lat' => $this->latitude,
            'lng' => $this->longitude,
        ];
    }

    /**
     * Scope: filter establishments within Lipa, Batangas bounding box.
     */
    public function scopeWithinLipa($query)
    {
        // Lipa bounding box: 13.85–14.05 lat, 121.05–121.30 lng
        $minLat = 13.85;
        $maxLat = 14.05;
        $minLng = 121.05;
        $maxLng = 121.30;
        // Use ST_MakeEnvelope for bbox, geography type
        return $query->whereRaw(
            "ST_DWithin(geom::geography, ST_MakeEnvelope(?, ?, ?, ?, 4326)::geography, 0)",
            [$minLng, $minLat, $maxLng, $maxLat]
        );
    }

    /**
     * Scope: filter establishments within a radius from a point.
     */
    public function scopeNearby($query, float $lat, float $lng, float $radiusKm = 20)
    {
        $radiusMeters = $radiusKm * 1000;
        return $query->whereRaw(
            "ST_DWithin(geom::geography, ST_MakePoint(?, ?)::geography, ?)",
            [$lng, $lat, $radiusMeters]
        );
    }

    /**
     * Scope: filter by coffee variety names.
     */
    public function scopeByVarietyNames($query, array $varietyNames)
    {
        return $query->join('establishment_varieties as ev', 'ev.establishment_id', '=', 'establishments.id')
                     ->join('coffee_varieties as cv', 'cv.id', '=', 'ev.coffee_variety_id')
                     ->whereIn('cv.name', $varietyNames);
    }

    /**
     * Scope: filter active establishments (not soft deleted).
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Static method to get nearby establishments for trail generation.
     */
    public static function getNearbyForTrail(float $lat, float $lng, array $varietyNames, array $types = [], int $limit = 6)
    {
        $query = self::active()
            ->byVarietyNames($varietyNames)
            ->nearby($lat, $lng);

        if (!empty($types)) {
            $query->whereIn('establishments.type', $types);
        }

        return $query
            ->select('establishments.*', DB::raw("ST_Distance(geom::geography, ST_MakePoint($lng, $lat)::geography) as distance_meters"))
            ->orderBy('distance_meters', 'asc')
            ->distinct()
            ->limit($limit)
            ->get();
    }

    /**
     * Get waypoint string for Mapbox.
     */
    public function toWaypoint()
    {
        return sprintf('%F,%F', $this->longitude, $this->latitude);
    }
}