<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Establishment;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;

class MapController extends Controller
{
    private const DEFAULT_FARM_OWNER_EMAIL = 'abm.arnoldbm@gmail.com';

    protected function resolveOwnerIdForMappedEstablishment(Request $request): ?int
    {
        if ($request->input('type') !== 'farm') {
            return optional($request->user())->id;
        }

        $farmOwner = User::query()
            ->where('role', 'farm_owner')
            ->whereRaw('LOWER(email) = ?', [strtolower(self::DEFAULT_FARM_OWNER_EMAIL)])
            ->first();

        return $farmOwner?->id ?? optional($request->user())->id;
    }

    protected function getVerifiedResellersForMapping()
    {
        return User::query()
            ->where('role', 'reseller')
            ->where('is_verified_reseller', true)
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', '!=', 'deactivated');
            })
            ->whereNull('deactivated_at')
            ->orderBy('name')
            ->get(['id', 'name', 'barangay', 'latitude', 'longitude', 'updated_at'])
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'barangay' => $user->barangay,
                    'latitude' => $user->latitude,
                    'longitude' => $user->longitude,
                    'verified_at' => optional($user->updated_at)?->toIso8601String(),
                ];
            })
            ->values();
    }

    /**
     * Display the map view.
     */
    public function index()
    {
        $mapboxToken = env('MAPBOX_API_KEY');
        $googleMapsKey = env('GOOGLE_MAPS_KEY');

        $verifiedResellers = $this->getVerifiedResellersForMapping();

        $establishments = Establishment::with([
            'varieties',
            'reviews',
            'couponPromos' => function($query) {
                $query->where('status', 'active')
                      ->where('valid_until', '>=', now()->toDateString());
            }
        ])->whereNull('deleted_at')->get();

        $establishments = $establishments->map(function($e) {
            return [
                'id' => $e->id,
                'name' => $e->name,
                'type' => $e->type,
                'description' => $e->description,
                'address' => $e->address,
                'barangay' => $e->barangay,
                'contact_number' => $e->contact_number,
                'email' => $e->email,
                'website' => $e->website,
                'visit_hours' => $e->visit_hours,
                'activities' => $e->activities,
                'latitude' => $e->latitude,
                'longitude' => $e->longitude,
                'image' => $e->image,
                'coffee_varieties' => $e->varieties->pluck('name')->toArray(),
                'rating_average' => $e->reviews_avg_overall_rating ? round($e->reviews_avg_overall_rating, 1) : null,
                'review_count' => $e->reviews_count,
                'taste_avg' => $e->reviews_avg_taste_rating ? round($e->reviews_avg_taste_rating, 1) : null,
                'environment_avg' => $e->reviews_avg_environment_rating ? round($e->reviews_avg_environment_rating, 1) : null,
                'cleanliness_avg' => $e->reviews_avg_cleanliness_rating ? round($e->reviews_avg_cleanliness_rating, 1) : null,
                'service_avg' => $e->reviews_avg_service_rating ? round($e->reviews_avg_service_rating, 1) : null,
                'active_promos' => $e->couponPromos->map(function($p) {
                    return [
                        'title' => $p->title,
                        'discount_type' => $p->discount_type,
                        'discount_value' => $p->discount_value,
                        'qr_code_token' => $p->qr_code_token,
                        'valid_from' => $p->valid_from,
                        'valid_until' => $p->valid_until,
                        'description' => $p->description,
                    ];
                }),
            ];
        });

        return view('admin.map', compact('mapboxToken', 'googleMapsKey', 'establishments', 'verifiedResellers'));
    }

    /**
     * Return verified resellers used by mapping modal and live badge count.
     */
    public function verifiedResellers()
    {
        return response()->json([
            'resellers' => $this->getVerifiedResellersForMapping(),
        ]);
    }

    /**
     * Store a new establishment.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::in(['farm', 'cafe', 'roaster'])],
            'description' => 'nullable|string',
            'address' => 'required|string|max:255',
            'barangay' => 'required|string|max:255',
            'contact_number' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'visit_hours' => 'nullable|string|max:255',
            'activities' => 'nullable|string|max:255',
            'latitude' => 'required|numeric|min:13.85|max:14.05',
            'longitude' => 'required|numeric|min:121.05|max:121.30',
            'varieties' => 'nullable|array',
            'varieties.*' => 'integer|exists:coffee_varieties,id',
            'primary_variety' => 'nullable|integer|exists:coffee_varieties,id',
            'image' => 'nullable|image|max:2048',
        ], [
            'type.in' => 'The selected type is invalid. Choose Farm, Cafe, or Roaster.',
        ]);

        $resolvedOwnerId = $this->resolveOwnerIdForMappedEstablishment($request);

        $establishment = new Establishment();
        $establishment->owner_id = $resolvedOwnerId;
        if (Schema::hasColumn('establishments', 'user_id')) {
            $establishment->user_id = $resolvedOwnerId;
        }
        $establishment->name = $request->name;
        $establishment->type = $request->type;
        $establishment->description = $request->description;
        $establishment->address = $request->address;
        $establishment->barangay = $request->barangay;
        $establishment->contact_number = $request->contact_number;
        $establishment->email = $request->email;
        $establishment->website = $request->website;
        $establishment->visit_hours = $request->visit_hours;
        $establishment->activities = $request->activities;
        $establishment->latitude = $request->latitude;
        $establishment->longitude = $request->longitude;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('establishments', 'public');
            $establishment->image = '/storage/' . $path;
        }

        $establishment->save();

        $varieties = $request->input('varieties', []);
        $primaryVariety = $request->input('primary_variety');

        if (is_array($varieties) && count($varieties) > 0) {
            $syncData = [];
            foreach ($varieties as $varietyId) {
                $syncData[$varietyId] = [
                    'is_primary' => ((int)$primaryVariety === (int)$varietyId),
                ];
            }
            $establishment->varieties()->sync($syncData);
        }

        return response()->json([
            'success' => true,
            'message' => 'Establishment created successfully',
            'establishment' => $establishment
        ], 201);
    }

    /**
     * Update establishment position (latitude, longitude, geom).
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'latitude' => 'required|numeric|min:13.85|max:14.05',
            'longitude' => 'required|numeric|min:121.05|max:121.30',
        ]);

        $establishment = Establishment::findOrFail($id);
        $establishment->latitude = $request->latitude;
        $establishment->longitude = $request->longitude;
        $establishment->save();

        return response()->json(['message' => 'Establishment updated successfully', 'establishment' => $establishment]);
    }

    /**
     * Soft delete establishment.
     */
    public function destroy($id)
    {
        $establishment = Establishment::findOrFail($id);
        $establishment->delete(); // Soft delete

        return response()->json(['message' => 'Establishment deleted successfully']);
    }

    /**
     * Update verified reseller coordinates.
     */
    public function updateResellerLocation(Request $request, $id)
    {
        $request->validate([
            'latitude' => 'required|numeric|min:13.50|max:14.40',
            'longitude' => 'required|numeric|min:120.70|max:121.80',
        ]);

        $reseller = User::query()
            ->where('role', 'reseller')
            ->where('is_verified_reseller', true)
            ->findOrFail($id);

        $reseller->latitude = $request->latitude;
        $reseller->longitude = $request->longitude;
        $reseller->save();

        return response()->json([
            'message' => 'Reseller location updated successfully',
            'reseller' => [
                'id' => $reseller->id,
                'name' => $reseller->name,
                'latitude' => $reseller->latitude,
                'longitude' => $reseller->longitude,
            ],
        ]);
    }
}