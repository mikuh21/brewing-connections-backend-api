<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ResellerPendingMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ResellerRegistrationController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validateWithBag('resellerRegistration', [
            'name' => ['required', 'string', 'max:255'],
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
            'password' => ['required', 'min:8', 'max:16', 'regex:/[@$!%*#?&]/', 'confirmed'],
            'password_confirmation' => ['required'],
            'contact_number' => ['required', 'string', 'max:30', 'regex:/^[0-9+() -]{7,30}$/', 'regex:/^(?=(?:.*\\d){7,15}$)[0-9+() -]+$/'],
            'barangay' => ['required', 'string', 'max:255'],
            'terms_agreed' => ['accepted'],
        ], [
            'password.regex' => 'Password must contain at least one special character.',
            'contact_number.regex' => 'Enter a valid contact number containing 7 to 15 digits, with optional spaces, +, hyphens, or parentheses.',
            'terms_agreed.accepted' => 'You must agree to the Terms & Conditions to continue.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'contact_number' => $validated['contact_number'],
            'barangay' => $validated['barangay'],
            'role' => 'reseller',
            'status' => 'active',
            'is_verified_reseller' => false,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        try {
            Mail::to($user->email)->send(new ResellerPendingMail($user));
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('reseller.dashboard')
                ->with('warning', 'Your account was created, but we could not send the pending review email right now.');
        }

        return redirect()->route('reseller.dashboard');
    }
}
