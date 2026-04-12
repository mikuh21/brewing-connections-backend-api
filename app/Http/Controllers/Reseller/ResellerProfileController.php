<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\CoffeeVariety;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ResellerProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $resellerColumns = Schema::hasTable('resellers') ? Schema::getColumnListing('resellers') : [];
        $userLocationColumns = $this->resolveUserLocationColumns();
        $canAdjustProfilePhoto = $this->hasUserProfileFocusColumns();
        $profileFocusX = $canAdjustProfilePhoto ? (int) ($user->profile_focus_x ?? 50) : 50;
        $profileFocusY = $canAdjustProfilePhoto ? (int) ($user->profile_focus_y ?? 50) : 50;
        $allVarieties = CoffeeVariety::query()->orderBy('name')->get();
        $selectedVarietyIds = $this->resolveResellerVarietyIds((int) $user->id);
        $primaryVarietyId = $this->resolvePrimaryResellerVarietyId((int) $user->id);
        $resellerRecord = $this->resolveResellerRecord((int) $user->id, $resellerColumns);

        $resellerFieldMeta = collect($resellerColumns)
            ->mapWithKeys(function (string $column) {
                return [
                    $column => [
                        'label' => str($column)->replace('_', ' ')->title()->toString(),
                        'type' => Schema::getColumnType('resellers', $column),
                        'readonly' => in_array($column, ['id', 'user_id', 'reseller_id', 'created_at', 'updated_at', 'deleted_at'], true),
                    ],
                ];
            })
            ->all();

        $userPhotoColumn = $this->resolveUserPhotoColumn();
        $currentProfilePhoto = $userPhotoColumn ? data_get($user, $userPhotoColumn) : null;

        return view('reseller.profile', compact(
            'user',
            'resellerRecord',
            'resellerColumns',
            'userLocationColumns',
            'canAdjustProfilePhoto',
            'profileFocusX',
            'profileFocusY',
            'allVarieties',
            'selectedVarietyIds',
            'primaryVarietyId',
            'resellerFieldMeta',
            'userPhotoColumn',
            'currentProfilePhoto'
        ));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $userId = (int) $user->id;
        $resellerColumns = Schema::hasTable('resellers') ? Schema::getColumnListing('resellers') : [];
        $userLocationColumns = $this->resolveUserLocationColumns();
        $userPhotoColumn = $this->resolveUserPhotoColumn();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'profile_photo' => ['nullable', 'image', 'max:5120'],
        ];

        if (in_array('address', $userLocationColumns, true)) {
            $rules['address'] = ['nullable', 'string', 'max:255'];
        }

        if (in_array('barangay', $userLocationColumns, true)) {
            $rules['barangay'] = ['nullable', 'string', 'max:255'];
        }

        if (in_array('contact_number', $userLocationColumns, true)) {
            $rules['contact_number'] = ['nullable', 'string', 'max:50'];
        }

        if (in_array('latitude', $userLocationColumns, true)) {
            $rules['latitude'] = ['nullable', 'numeric', 'between:-90,90'];
        }

        if (in_array('longitude', $userLocationColumns, true)) {
            $rules['longitude'] = ['nullable', 'numeric', 'between:-180,180'];
        }

        if ($this->hasUserProfileFocusColumns()) {
            $rules['profile_focus_x'] = ['nullable', 'integer', 'between:0,100'];
            $rules['profile_focus_y'] = ['nullable', 'integer', 'between:0,100'];
        }

        $rules['varieties'] = ['nullable', 'array'];
        $rules['varieties.*'] = ['integer', 'exists:coffee_varieties,id'];
        $rules['primary_variety'] = ['nullable', 'integer', 'exists:coffee_varieties,id'];

        foreach ($resellerColumns as $column) {
            if (in_array($column, ['id', 'user_id', 'reseller_id', 'created_at', 'updated_at', 'deleted_at'], true)) {
                continue;
            }

            $columnType = Schema::getColumnType('resellers', $column);
            $rules[$column] = $this->rulesForColumnType($columnType);
        }

        $validated = $request->validate($rules);

        DB::transaction(function () use ($validated, $request, $userId, $resellerColumns, $userPhotoColumn, $userLocationColumns) {
            $userPayload = [
                'name' => $validated['name'],
                'email' => $validated['email'],
            ];

            foreach ($userLocationColumns as $column) {
                if (array_key_exists($column, $validated)) {
                    $userPayload[$column] = $validated[$column];
                }
            }

            if ($this->hasUserProfileFocusColumns()) {
                $userPayload['profile_focus_x'] = (int) ($validated['profile_focus_x'] ?? 50);
                $userPayload['profile_focus_y'] = (int) ($validated['profile_focus_y'] ?? 50);
            }

            if ($userPhotoColumn && $request->hasFile('profile_photo')) {
                $path = $request->file('profile_photo')->store('profile-photos', 'public');
                $userPayload[$userPhotoColumn] = Storage::url($path);
            }

            if (Schema::hasColumn('users', 'updated_at')) {
                $userPayload['updated_at'] = now();
            }

            DB::table('users')
                ->where('id', $userId)
                ->update($userPayload);

            $this->syncResellerVarieties($userId, $validated);

            if (!Schema::hasTable('resellers')) {
                return;
            }

            $query = $this->resellerQueryForUser($userId, $resellerColumns);
            $existing = $query->first();
            $resellerPayload = [];

            foreach ($resellerColumns as $column) {
                if (in_array($column, ['id', 'user_id', 'reseller_id', 'created_at', 'updated_at', 'deleted_at'], true)) {
                    continue;
                }

                if (array_key_exists($column, $validated)) {
                    $resellerPayload[$column] = $validated[$column];
                }
            }

            if (in_array('updated_at', $resellerColumns, true)) {
                $resellerPayload['updated_at'] = now();
            }

            if ($existing) {
                $query->update($resellerPayload);
                return;
            }

            if (in_array('user_id', $resellerColumns, true)) {
                $resellerPayload['user_id'] = $userId;
            }

            if (in_array('reseller_id', $resellerColumns, true)) {
                $resellerPayload['reseller_id'] = $userId;
            }

            if (in_array('created_at', $resellerColumns, true)) {
                $resellerPayload['created_at'] = now();
            }

            DB::table('resellers')->insert($resellerPayload);
        });

        return redirect()
            ->route('reseller.profile')
            ->with('status', 'Reseller profile updated successfully.');
    }

    protected function resolveResellerRecord(int $userId, array $resellerColumns): object
    {
        if (!Schema::hasTable('resellers')) {
            return (object) [];
        }

        $record = $this->resellerQueryForUser($userId, $resellerColumns)->first();

        if ($record) {
            return $record;
        }

        $fallback = [];
        foreach ($resellerColumns as $column) {
            $fallback[$column] = null;
        }

        if (array_key_exists('user_id', $fallback)) {
            $fallback['user_id'] = $userId;
        }

        if (array_key_exists('reseller_id', $fallback)) {
            $fallback['reseller_id'] = $userId;
        }

        return (object) $fallback;
    }

    protected function resellerQueryForUser(int $userId, array $resellerColumns)
    {
        $query = DB::table('resellers');

        if (in_array('user_id', $resellerColumns, true)) {
            return $query->where('user_id', $userId);
        }

        if (in_array('reseller_id', $resellerColumns, true)) {
            return $query->where('reseller_id', $userId);
        }

        if (in_array('id', $resellerColumns, true)) {
            return $query->where('id', $userId);
        }

        return $query->whereRaw('1 = 0');
    }

    protected function rulesForColumnType(string $columnType): array
    {
        return match ($columnType) {
            'integer', 'bigint', 'smallint', 'mediumint', 'tinyint' => ['nullable', 'integer'],
            'decimal', 'double', 'float' => ['nullable', 'numeric'],
            'date' => ['nullable', 'date'],
            'datetime', 'timestamp' => ['nullable', 'date'],
            'boolean' => ['nullable', 'boolean'],
            'json' => ['nullable', 'json'],
            'text', 'mediumtext', 'longtext' => ['nullable', 'string'],
            default => ['nullable', 'string', 'max:255'],
        };
    }

    protected function resolveUserPhotoColumn(): ?string
    {
        if (!Schema::hasTable('users')) {
            return null;
        }

        $candidates = ['image_url', 'profile_photo', 'profile_photo_path', 'avatar', 'photo', 'image'];

        foreach ($candidates as $column) {
            if (Schema::hasColumn('users', $column)) {
                return $column;
            }
        }

        return null;
    }

    protected function resolveUserLocationColumns(): array
    {
        if (!Schema::hasTable('users')) {
            return [];
        }

        return collect(['address', 'barangay', 'contact_number', 'latitude', 'longitude'])
            ->filter(fn (string $column) => Schema::hasColumn('users', $column))
            ->values()
            ->all();
    }

    protected function hasUserProfileFocusColumns(): bool
    {
        return Schema::hasTable('users')
            && Schema::hasColumn('users', 'profile_focus_x')
            && Schema::hasColumn('users', 'profile_focus_y');
    }

    protected function resolveResellerVarietyIds(int $userId): array
    {
        if (!Schema::hasTable('reseller_varieties')) {
            return [];
        }

        return DB::table('reseller_varieties')
            ->where('reseller_id', $userId)
            ->pluck('coffee_variety_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    protected function resolvePrimaryResellerVarietyId(int $userId): ?int
    {
        if (!Schema::hasTable('reseller_varieties')) {
            return null;
        }

        $primaryId = DB::table('reseller_varieties')
            ->where('reseller_id', $userId)
            ->where('is_primary', true)
            ->value('coffee_variety_id');

        return $primaryId ? (int) $primaryId : null;
    }

    protected function syncResellerVarieties(int $userId, array $validated): void
    {
        if (!Schema::hasTable('reseller_varieties')) {
            return;
        }

        $varietyIds = collect($validated['varieties'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $primaryVarietyId = isset($validated['primary_variety'])
            ? (int) $validated['primary_variety']
            : null;

        DB::table('reseller_varieties')
            ->where('reseller_id', $userId)
            ->delete();

        if ($varietyIds->isEmpty()) {
            return;
        }

        $now = now();
        $rows = $varietyIds->map(function (int $varietyId) use ($userId, $primaryVarietyId, $now) {
            return [
                'reseller_id' => $userId,
                'coffee_variety_id' => $varietyId,
                'is_primary' => $primaryVarietyId === $varietyId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->all();

        DB::table('reseller_varieties')->insert($rows);
    }
}
