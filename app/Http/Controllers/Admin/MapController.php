<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Establishment;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use App\Services\MapDetailsService;
use App\Services\EstablishmentDeletionService;

class MapController extends Controller
{
    private const DEFAULT_FARM_OWNER_EMAIL = 'abm.arnoldbm@gmail.com';

    protected function resolveOwnerIdForMappedEstablishment(Request $request): ?int
    {
        if ($request->input('type') !== 'farm') {
            $normalizedEmail = strtolower(trim((string) $request->input('email', '')));

            if ($normalizedEmail === '') {
                $messages = [
                    'email' => 'Email is required for cafe and roaster establishments so BrewHub can link a dedicated owner account.',
                ];

                $ownerPassword = trim((string) $request->input('owner_password', ''));
                if ($ownerPassword === '') {
                    $messages['owner_password'] = 'Owner account password is required when creating a new cafe owner account.';
                }

                throw ValidationException::withMessages($messages);
            }

            $owner = User::query()
                ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
                ->first();

            if ($owner) {
                if ($owner->role !== 'cafe_owner') {
                    throw ValidationException::withMessages([
                        'email' => 'This email is already assigned to a non cafe-owner account. Use a dedicated owner email instead.',
                    ]);
                }

                return (int) $owner->id;
            }

            $ownerPassword = trim((string) $request->input('owner_password', ''));

            if ($ownerPassword === '') {
                throw ValidationException::withMessages([
                    'owner_password' => 'Owner account password is required when creating a new cafe owner account.',
                ]);
            }

            $ownerPayload = [
                'name' => trim((string) $request->input('name', '')),
                'email' => $normalizedEmail,
                'password' => Hash::make($ownerPassword),
                'role' => 'cafe_owner',
                'email_verified_at' => now(),
            ];

            if (Schema::hasColumn('users', 'status')) {
                $ownerPayload['status'] = 'active';
            }

            if (Schema::hasColumn('users', 'deactivated_at')) {
                $ownerPayload['deactivated_at'] = null;
            }

            if (Schema::hasColumn('users', 'is_verified_reseller')) {
                $ownerPayload['is_verified_reseller'] = false;
            }

            return (int) User::query()->create($ownerPayload)->id;
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
            ->with(['resellerProducts.product'])
            ->get(['id', 'name', 'barangay', 'latitude', 'longitude', 'updated_at'])
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'barangay' => $user->barangay,
                    'latitude' => $user->latitude,
                    'longitude' => $user->longitude,
                    'verified_at' => optional($user->updated_at)?->toIso8601String(),
                    'associated_products' => MapDetailsService::resellerProducts($user->resellerProducts),
                ];
            })
            ->values();
    }

    /**
     * Display the map view.
     */
    public function index()
    {
        $mapboxToken = config('services.mapbox.api_key');
        $googleMapsKey = config('services.google_maps.key');

        $verifiedResellers = $this->getVerifiedResellersForMapping();

        $establishments = Establishment::query()
            ->whereNull('deleted_at')
            ->with([
                'varieties:id,name',
                'products:id,establishment_id,name,description,category,price_per_unit,unit,image_url,is_active',
                'couponPromos' => function ($query) {
                    $query->where('status', 'active')
                        ->where('valid_until', '>=', now()->toDateString());
                },
            ])
            ->withCount('reviews')
            ->withAvg('reviews', 'overall_rating')
            ->withAvg('reviews', 'taste_rating')
            ->withAvg('reviews', 'environment_rating')
            ->withAvg('reviews', 'cleanliness_rating')
            ->withAvg('reviews', 'service_rating')
            ->get();

        $establishments = $establishments->map(function($e) {
            $reviewCount = (int) ($e->reviews_count ?? 0);
            $overallAverage = $reviewCount > 0 ? round((float) ($e->reviews_avg_overall_rating ?? 0), 1) : null;
            $tasteAverage = $reviewCount > 0 ? round((float) ($e->reviews_avg_taste_rating ?? 0), 1) : null;
            $environmentAverage = $reviewCount > 0 ? round((float) ($e->reviews_avg_environment_rating ?? 0), 1) : null;
            $cleanlinessAverage = $reviewCount > 0 ? round((float) ($e->reviews_avg_cleanliness_rating ?? 0), 1) : null;
            $serviceAverage = $reviewCount > 0 ? round((float) ($e->reviews_avg_service_rating ?? 0), 1) : null;

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
                'rating_average' => $overallAverage,
                'review_count' => $reviewCount,
                'taste_avg' => $tasteAverage,
                'environment_avg' => $environmentAverage,
                'cleanliness_avg' => $cleanlinessAverage,
                'service_avg' => $serviceAverage,
                'associated_products' => MapDetailsService::products($e->products),
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
        $rules = [
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::in(['farm', 'cafe', 'roaster'])],
            'description' => 'nullable|string',
            'address' => 'required|string|max:255',
            'barangay' => 'required|string|max:255',
            'contact_number' => ['nullable', 'string', 'max:50', 'regex:/^(?:\+63|0)?9\d{9}$/'],
            'website' => 'nullable|url|max:255',
            'visit_hours' => 'nullable|string|max:255',
            'activities' => 'nullable|string|max:255',
            'latitude' => 'required|numeric|min:13.85|max:14.05',
            'longitude' => 'required|numeric|min:121.05|max:121.30',
            'varieties' => 'nullable|array',
            'varieties.*' => 'integer|exists:coffee_varieties,id',
            'primary_variety' => 'nullable|integer|exists:coffee_varieties,id',
            'image' => 'nullable|image|max:2048',
        ];

        if ($request->input('type') !== 'farm') {
            $rules['email'] = ['required', 'email', 'max:255'];
            $rules['owner_password'] = ['nullable', 'string', 'min:8', 'max:255', Rule::requiredIf(fn () => trim((string) $request->input('email', '')) === '')];
        } else {
            $rules['email'] = ['nullable', 'email', 'max:255'];
            $rules['owner_password'] = ['nullable', 'string', 'min:8', 'max:255'];
        }

        $request->validate($rules, [
            'type.in' => 'The selected type is invalid. Choose Farm, Cafe, or Roaster.',
            'contact_number.regex' => 'Contact number must be a valid Philippine mobile number, e.g. 0917XXXXXXX.',
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
            $path = $request->file('image')->store('establishments', 'supabase');
            $establishment->image = $path;
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
        ], 200);
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
     * Permanently delete an establishment and all dependent records.
     */
    public function destroy($id, EstablishmentDeletionService $deletionService)
    {
        $establishment = Establishment::findOrFail($id);
        $deletionService->delete($establishment);

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