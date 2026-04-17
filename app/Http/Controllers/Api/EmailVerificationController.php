<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ConsumerEmailVerificationMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class EmailVerificationController extends Controller
{
    public function sendOtp(Request $request)
    {
        $user = auth('api')->user();
        if (! $user instanceof User) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 401);
        }

        $validated = $request->validate([
            'email' => ['nullable', 'email'],
        ]);

        if (isset($validated['email'])) {
            $requestedEmail = strtolower(trim($validated['email']));
            if ($requestedEmail !== strtolower($user->email)) {
                return response()->json([
                    'message' => 'Verification email must match your registered account email.',
                ], 422);
            }
        }

        if ($user->email_verified_at) {
            return response()->json([
                'message' => 'Email is already verified.',
            ]);
        }

        $otp = $this->generateOtp();

        DB::table('email_verification_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($otp),
                'created_at' => now(),
            ]
        );

        Mail::to($user->email)->send(new ConsumerEmailVerificationMail($user, $otp, 15));

        return response()->json([
            'message' => 'Verification code sent.',
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $user = auth('api')->user();
        if (! $user instanceof User) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 401);
        }

        $validated = $request->validate([
            'otp' => ['required', 'digits:6'],
            'email' => ['nullable', 'email'],
        ]);

        if (isset($validated['email'])) {
            $requestedEmail = strtolower(trim($validated['email']));
            if ($requestedEmail !== strtolower($user->email)) {
                return response()->json([
                    'message' => 'Verification email must match your registered account email.',
                ], 422);
            }
        }

        if ($user->email_verified_at) {
            return response()->json([
                'message' => 'Email is already verified.',
            ]);
        }

        $row = DB::table('email_verification_tokens')->where('email', $user->email)->first();
        if (! $row || ! $row->created_at) {
            return response()->json([
                'message' => 'Invalid or expired verification code.',
            ], 422);
        }

        $createdAt = Carbon::parse($row->created_at);
        if ($createdAt->lt(now()->subMinutes(15))) {
            DB::table('email_verification_tokens')->where('email', $user->email)->delete();

            return response()->json([
                'message' => 'Verification code has expired. Please request a new one.',
            ], 422);
        }

        if (! Hash::check($validated['otp'], $row->token)) {
            return response()->json([
                'message' => 'Invalid or expired verification code.',
            ], 422);
        }

        $user->email_verified_at = now();
        $user->save();

        DB::table('email_verification_tokens')->where('email', $user->email)->delete();

        return response()->json([
            'message' => 'Email verified successfully.',
        ]);
    }

    private function generateOtp(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}
