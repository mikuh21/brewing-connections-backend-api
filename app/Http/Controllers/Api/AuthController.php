<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Throwable;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'string', 'min:8'],
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'consumer',
                'status' => 'active',
            ]);

            $token = JWTAuth::fromUser($user);

            return response()->json([
                'token' => $token,
                'user' => $user,
            ], 201);
        } catch (QueryException $exception) {
            Log::error('API register database failure', [
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'Authentication service is temporarily unavailable. Please try again shortly.',
            ], 503);
        } catch (Throwable $exception) {
            Log::error('API register unexpected failure', [
                'type' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'Authentication service is temporarily unavailable. Please try again shortly.',
            ], 503);
        }
    }

    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required', 'string'],
            ]);

            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'message' => 'Invalid credentials.',
                ], 401);
            }

            $user = auth('api')->user();

            return response()->json([
                'token' => $token,
                'user' => $user,
            ]);
        } catch (QueryException $exception) {
            Log::error('API login database failure', [
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'Authentication service is temporarily unavailable. Please try again shortly.',
            ], 503);
        } catch (Throwable $exception) {
            Log::error('API login unexpected failure', [
                'type' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'Authentication service is temporarily unavailable. Please try again shortly.',
            ], 503);
        }
    }

    public function logout()
    {
        $token = JWTAuth::getToken();

        if ($token) {
            JWTAuth::invalidate($token);
        }

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    public function me()
    {
        return response()->json([
            'user' => auth('api')->user(),
        ]);
    }
}
