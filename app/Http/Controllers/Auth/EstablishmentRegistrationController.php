<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use App\Mail\RegistrationWelcomeMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Throwable;

class EstablishmentRegistrationController extends Controller
{
    public function checkEmail(Request $request)
    {
        $email = strtolower(trim((string) $request->query('email', '')));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255) {
            return response()->json([
                'available' => false,
                'message' => 'Enter a valid email address.',
            ], 422);
        }

        $exists = User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->exists();

        return response()->json([
            'available' => !$exists,
            'message' => $exists ? 'This email is registered. Try another email.' : '',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'owner_name' => ['required', 'string', 'max:255'],
            'establishment_name' => ['required', 'string', 'max:255'],
            'establishment_type' => ['required', Rule::in(['farm', 'cafe', 'roaster'])],
            'email' => [
                'required',
                'email',
                'max:255',
                function ($attribute, $value, $fail) {
                    $normalizedEmail = strtolower(trim((string) $value));
                    if (User::query()->whereRaw('LOWER(email) = ?', [$normalizedEmail])->exists()) {
                        $fail('This email is registered. Try another email.');
                    }
                },
            ],
            'password' => ['required', 'string', 'min:8', 'max:16', 'regex:/[@$!%*#?&]/', 'confirmed'],
            'password_confirmation' => ['required', 'string'],
            'contact_number' => ['required', 'string', 'max:30', 'regex:/^[0-9+() -]{7,30}$/', 'regex:/^(?=(?:.*\\d){7,15}$)[0-9+() -]+$/'],
            'address' => ['required', 'string', 'max:255'],
            'barangay' => ['required', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'terms_agreed' => ['accepted'],
        ], [
            'password.regex' => 'Password must contain at least one special character.',
            'contact_number.regex' => 'Enter a valid contact number containing 7 to 15 digits, with optional spaces, +, hyphens, or parentheses.',
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

        $welcomeEmailSent = true;
        try {
            Mail::to($user->email)->send(new RegistrationWelcomeMail(
                $user,
                trim($validated['establishment_name']),
                $validated['establishment_type']
            ));
        } catch (Throwable $exception) {
            // Registration must remain successful even if the mail service is temporarily unavailable.
            report($exception);
            $welcomeEmailSent = false;
        }

        $workspaceRoute = match ($validated['establishment_type']) {
            'farm' => 'farm-owner.map',
            'roaster' => 'farm-owner.map',
            default => 'cafe-owner.map',
        };

        $redirect = redirect()
            ->route($workspaceRoute)
            ->with('success', 'Your account is ready. Pin your establishment on the map to publish its location.');

        if (!$welcomeEmailSent) {
            $redirect->with('warning', 'Your account was created, but the welcome email could not be sent right now.');
        }

        return $redirect;
    }
}
