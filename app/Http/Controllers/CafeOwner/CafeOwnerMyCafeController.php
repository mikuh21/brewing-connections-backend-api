<?php

namespace App\Http\Controllers\CafeOwner;

use App\Http\Controllers\Controller;
use App\Models\CoffeeVariety;
use App\Models\Establishment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class CafeOwnerMyCafeController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $baseQuery = Establishment::query()->with('varieties');

        if (Schema::hasColumn('establishments', 'user_id')) {
            $baseQuery->where('user_id', $userId);
        } else {
            $baseQuery->where('owner_id', $userId);
        }

        $activeEstablishmentId = (int) session('cafe_owner_active_establishment_id', 0);
        $establishment = null;

        if ($activeEstablishmentId > 0) {
            $establishment = (clone $baseQuery)
                ->whereKey($activeEstablishmentId)
                ->first();
        }

        if (!$establishment) {
            $establishment = (clone $baseQuery)
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->first();
        }

        if ($establishment) {
            session(['cafe_owner_active_establishment_id' => $establishment->id]);
        }

        $allVarieties = CoffeeVariety::query()->orderBy('name')->get();
        $selectedVarietyIds = $establishment?->varieties?->pluck('id')->all() ?? [];
        $primaryVarietyId = optional($establishment?->varieties?->firstWhere('pivot.is_primary', true))->id;

        return view('cafe-owner.my-cafe', compact(
            'establishment',
            'allVarieties',
            'selectedVarietyIds',
            'primaryVarietyId'
        ));
    }

    public function update(Request $request)
    {
        $userId = Auth::id();

        $validated = $request->validate([
            'establishment_id' => 'nullable|integer|exists:establishments,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'barangay' => 'nullable|string|max:255',
            'operating_hours' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'image' => 'nullable|image|max:5120',
            'banner_focus_x' => 'nullable|integer|between:0,100',
            'banner_focus_y' => 'nullable|integer|between:0,100',
            'profile_focus_x' => 'nullable|integer|between:0,100',
            'profile_focus_y' => 'nullable|integer|between:0,100',
            'varieties' => 'nullable|array',
            'varieties.*' => 'integer|exists:coffee_varieties,id',
            'primary_variety' => 'nullable|integer|exists:coffee_varieties,id',
        ]);

        $query = Establishment::query();

        if (Schema::hasColumn('establishments', 'user_id')) {
            $query->where('user_id', $userId);
        } else {
            $query->where('owner_id', $userId);
        }

        if (!empty($validated['establishment_id'])) {
            $query->whereKey((int) $validated['establishment_id']);
        }

        $establishment = $query
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->firstOrFail();

        $payload = [];

        foreach (['name', 'description', 'address', 'barangay', 'contact_number', 'website', 'latitude', 'longitude'] as $field) {
            if (Schema::hasColumn('establishments', $field) && array_key_exists($field, $validated)) {
                $payload[$field] = $validated[$field] ?? null;
            }
        }

        foreach (['banner_focus_x', 'banner_focus_y', 'profile_focus_x', 'profile_focus_y'] as $field) {
            if (Schema::hasColumn('establishments', $field) && array_key_exists($field, $validated)) {
                $payload[$field] = (int) ($validated[$field] ?? 50);
            }
        }

        if (Schema::hasColumn('establishments', 'operating_hours')) {
            $payload['operating_hours'] = $validated['operating_hours'] ?? null;
        }

        if (Schema::hasColumn('establishments', 'visit_hours')) {
            $payload['visit_hours'] = $validated['operating_hours'] ?? null;
        }

        if ($request->hasFile('image')) {
            $storedPath = $request->file('image')->store('establishments', 'public');

            if (Schema::hasColumn('establishments', 'image')) {
                $payload['image'] = Storage::url($storedPath);
            }
        }

        if (!empty($payload)) {
            $establishment->update($payload);
        }

        $varietyIds = collect($validated['varieties'] ?? [])->map(fn ($id) => (int) $id)->unique()->values();
        $primaryVarietyId = isset($validated['primary_variety']) ? (int) $validated['primary_variety'] : null;

        $syncPayload = $varietyIds
            ->mapWithKeys(function (int $varietyId) use ($primaryVarietyId) {
                return [$varietyId => ['is_primary' => $primaryVarietyId === $varietyId]];
            })
            ->all();

        $establishment->varieties()->sync($syncPayload);

        // Always bump profile timestamp after a successful profile update flow.
        $establishment->touch();
        session(['cafe_owner_active_establishment_id' => $establishment->id]);

        return redirect()
            ->route('cafe-owner.my-cafe')
            ->with('success', 'Cafe details updated successfully.');
    }
}
