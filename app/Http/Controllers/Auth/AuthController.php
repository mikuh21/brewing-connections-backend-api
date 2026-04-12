<?php

namespace App\Http\Controllers\Auth;


use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


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

        if (!Auth::attempt(['email' => $validated['email'], 'password' => $validated['password']])) {
            return back()
                ->withErrors(['email' => 'Invalid credentials. Please try again.'])
                ->withInput($request->except('password'));
        }

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
}
