<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetOtpMail;
use App\Mail\PasswordResetLinkMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function sendOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = strtolower(trim($validated['email']));
        $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();

        $message = 'If an account exists for this email, a reset code has been sent.';
        if (! $user) {
            return response()->json(['message' => $message]);
        }

        $otp = $this->generateOtp();

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($otp),
                'created_at' => now(),
            ]
        );

        Mail::to($user->email)->send(new PasswordResetOtpMail($user, $otp, 15));

        return response()->json(['message' => $message]);
    }

    public function resetWithOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:6'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $email = strtolower(trim($validated['email']));
        $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();

        if (! $user) {
            return response()->json([
                'message' => 'Invalid or expired reset code.',
            ], 422);
        }

        $row = DB::table('password_reset_tokens')->where('email', $user->email)->first();
        if (! $row || ! $row->created_at) {
            return response()->json([
                'message' => 'Invalid or expired reset code.',
            ], 422);
        }

        $createdAt = Carbon::parse($row->created_at);
        if ($createdAt->lt(now()->subMinutes(15))) {
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();

            return response()->json([
                'message' => 'Reset code has expired. Please request a new one.',
            ], 422);
        }

        if (! Hash::check($validated['otp'], $row->token)) {
            return response()->json([
                'message' => 'Invalid or expired reset code.',
            ], 422);
        }

        $user->forceFill([
            'password' => $validated['password'],
            'remember_token' => Str::random(60),
        ])->save();

        DB::table('password_reset_tokens')->where('email', $user->email)->delete();

        return response()->json([
            'message' => 'Password reset successfully.',
        ]);
    }

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

    private function generateOtp(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}
