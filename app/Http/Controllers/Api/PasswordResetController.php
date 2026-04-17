<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetLinkMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class PasswordResetController extends Controller
{
    public function sendResetLink(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = strtolower(trim($validated['email']));
        $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();

        if (! $user) {
            return response()->json([
                'message' => 'If an account exists for this email, a password reset link has been sent.',
            ]);
        }

        $token = Password::createToken($user);
        $resetUrl = route('password.reset.form', [
            'token' => $token,
            'email' => $user->email,
        ]);

        Mail::to($user->email)->send(new PasswordResetLinkMail($user, $resetUrl));

        return response()->json([
            'message' => 'Password reset link emailed successfully.',
        ]);
    }
}
