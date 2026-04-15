<?php

namespace App\Http\Controllers\Auth;


use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class AuthController extends Controller
{
    // Show login form
    public function showLogin()
    {
        return view('auth.login');
    }

    // Handle login POST
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $normalizedEmail = Str::lower(trim((string) $validated['email']));
        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
            ->first();

        if (!$user || !$this->passwordMatchesAndUpgradeIfNeeded($user, (string) $validated['password'])) {
            return back()
                ->withErrors(['email' => 'Invalid credentials. Please try again.'])
                ->withInput($request->except('password'));
        }

        Auth::login($user);

        // Regenerate session
        $request->session()->regenerate();

        $user = Auth::user();
        if (!$user) {
            Auth::logout();
            return back()
                ->withErrors(['email' => 'Unauthorized access.'])
                ->withInput($request->except('password'));
        }

        if ($user->role === 'reseller' && $user->status === 'deactivated') {
            if (!empty($user->deactivation_notice_seen_at)) {
                Auth::logout();

                return back()
                    ->withErrors(['email' => 'Invalid credentials. Please try again.'])
                    ->withInput($request->except('password'));
            }

            User::query()
                ->whereKey($user->id)
                ->update(['deactivation_notice_seen_at' => now()]);

            return redirect()->route('reseller.dashboard');
        }

        switch ($user->role) {
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'farm_owner':
                return redirect()->route('farm-owner.dashboard');
            case 'cafe_owner':
                return redirect()->route('cafe-owner.dashboard');
            case 'reseller':
                return redirect()->route('reseller.dashboard');
            case 'consumer':
                return redirect()->route('home');
            default:
                Auth::logout();
                return back()
                    ->withErrors(['email' => 'Unauthorized access.'])
                    ->withInput($request->except('password'));
        }
    }

    // Handle logout POST
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    private function passwordMatchesAndUpgradeIfNeeded(User $user, string $inputPassword): bool
    {
        $storedPassword = (string) ($user->password ?? '');

        if (Hash::check($inputPassword, $storedPassword)) {
            return true;
        }

        // Compatibility path for manually inserted plain-text passwords.
        if ($storedPassword !== '' && hash_equals(trim($storedPassword), trim($inputPassword))) {
            $user->forceFill([
                'password' => Hash::make($inputPassword),
            ])->save();

            return true;
        }

        return false;
    }
}
