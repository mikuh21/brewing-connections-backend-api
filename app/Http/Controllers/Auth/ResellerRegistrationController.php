<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ResellerRegistrationController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validateWithBag('resellerRegistration', [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'min:8', 'max:16', 'regex:/[@$!%*#?&]/', 'confirmed'],
            'password_confirmation' => ['required'],
            'contact_number' => ['required', 'string', 'max:30'],
            'barangay' => ['required', 'string', 'max:255'],
            'terms_agreed' => ['accepted'],
        ], [
            'password.regex' => 'Password must contain at least one special character.',
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

        return redirect()->route('reseller.dashboard');
    }
}
