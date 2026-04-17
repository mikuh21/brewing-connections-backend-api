<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\TemporaryPasswordMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class PasswordResetController extends Controller
{
    public function sendTemporaryPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'generated_password' => ['nullable', 'string', 'min:8', 'max:64'],
            'temporary_password' => ['nullable', 'string', 'min:8', 'max:64'],
        ]);

        $email = strtolower(trim($validated['email']));
        $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();

        if (! $user) {
            return response()->json([
                'message' => 'If an account exists for this email, a temporary password has been sent.',
            ]);
        }

        $temporaryPassword = $this->resolveTemporaryPassword(
            $validated['generated_password'] ?? $validated['temporary_password'] ?? null,
            $user->name
        );

        $user->password = Hash::make($temporaryPassword);
        $user->save();

        Mail::to($user->email)->send(new TemporaryPasswordMail($user, $temporaryPassword));

        return response()->json([
            'message' => 'Temporary password emailed successfully.',
        ]);
    }

    private function resolveTemporaryPassword(?string $proposed, ?string $name): string
    {
        if ($proposed && preg_match('/^[A-Za-z]{2}[0-9]{4}[!@#$%&]{2}$/', $proposed)) {
            return $proposed;
        }

        $parts = preg_split('/\s+/', trim((string) $name)) ?: [];
        $firstInitial = strtoupper(substr($parts[0] ?? 'U', 0, 1));
        $surnameInitial = strtoupper(substr($parts[count($parts) - 1] ?? 'X', 0, 1));

        $numbers = (string) random_int(1000, 9999);
        $specials = ['!', '@', '#', '$', '%', '&'];
        $specialA = $specials[array_rand($specials)];
        $specialB = $specials[array_rand($specials)];

        return $firstInitial . $surnameInitial . $numbers . $specialA . $specialB;
    }
}
