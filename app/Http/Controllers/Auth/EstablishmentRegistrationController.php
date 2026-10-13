<?php

namespace App\\Http\\Controllers\\Auth;

use App\\Http\\Controllers\\Controller;
use App\\Models\\Establishment;
use App\\Models\\User;
use Illuminate\\Http\\Request;
use Illuminate\\Support\\Facades\\Auth;
use Illuminate\\Support\\Facades\\DB;
use Illuminate\\Support\\Facades\\Hash;
use Illuminate\\Support\\Facades\\Schema;
use Illuminate\\Validation\\Rule;

class EstablishmentRegistrationController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'owner_name' => ['required', 'string', 'max:255'],
            'establishment_name' => ['required', 'string', 'max:255'],
            'establishment_type' => ['required', Rule::in(['farm', 'cafe'])],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:16', 'regex:/[@$!%*#?&]/', 'confirmed'],
            'password_confirmation' => ['required', 'string'],
            'contact_number' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:255'],
            'barangay' => ['required', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'terms_agreed' => ['accepted'],
        ], [
            'password.regex' => 'Password must contain at least one special character.',
            'terms_agreed.accepted' => 'You must agree to the Terms & Conditions to continue.',
        ]);

        $role = $validated['establishment_type'] === 'farm' ? 'farm_owner' : 'cafe_owner';

        $user = DB::transaction(function () use ($validated, $role) {
            $user = User::query()->create([
                'name' => trim($validated['owner_name']),
                'email' => strtolower(trim($validated['email'])),
                'password' => Hash::make($validated['password']),
                'contact_number' => trim($validated['contact_number']),
                'barangay' => trim($validated['barangay']),
                'role' => $role,
                'status' => 'active',
                'email_verified_at' => now(),
            ]);

            $payload = [
                'owner_id' => $user->id,
                'name' => trim($validated['establishment_name']),
                'type' => $validated['establishment_type'],
                'address' => trim($validated['address']),
                'barangay' => trim($validated['barangay']),
                'contact_number' => trim($validated['contact_number']),
                'email' => strtolower(trim($validated['email'])),
                'latitude' => null,
                'longitude' => null,
            ];

            if (Schema::hasColumn('establishments', 'website')) {
                $payload['website'] = $validated['website'] ?? null;
            }

            if (Schema::hasColumn('establishments', 'user_id')) {
                $payload['user_id'] = $user->id;
            }

            Establishment::query()->create($payload);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->route($role === 'farm_owner' ? 'farm-owner.map' : 'cafe-owner.map')
            ->with('success', 'Your account is ready. Pin your establishment on the map to publish its location.');
    }
}
