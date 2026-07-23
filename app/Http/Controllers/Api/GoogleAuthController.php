<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Google\Client as GoogleClient;

class GoogleAuthController extends Controller
{
    public function handleToken(Request $request)
    {
        $validated = $request->validate([
            'id_token' => ['required', 'string'],
        ]);

        $primaryClientId = env('GOOGLE_CLIENT_ID');
        $androidClientId = env('GOOGLE_ANDROID_CLIENT_ID');

        $client = new GoogleClient([
            'client_id' => $primaryClientId,
        ]);

        $payload = $client->verifyIdToken($validated['id_token'], [$primaryClientId, $androidClientId]);

        if (! $payload) {
            return response()->json([
                'message' => 'Invalid Google token',
            ], 401);
        }

        $user = User::where('email', $payload['email'] ?? null)->first();

        if (! $user) {
            $user = User::create([
                'name' => $payload['name'] ?? $payload['email'] ?? 'Google User',
                'email' => $payload['email'],
                'password' => bcrypt(Str::random(32)),
                'email_verified_at' => now(),
                'role' => 'consumer',
                'status' => 'active',
                'image_url' => $payload['picture'] ?? null,
            ]);
        }

        $token = $user->createToken('mobile-google')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'image_url' => $user->image_url,
            ],
        ]);
    }
}
