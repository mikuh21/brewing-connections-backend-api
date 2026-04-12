<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserLocationController extends Controller
{
    public function update(Request $request)
    {
        $data = $request->validate([
            'latitude' => 'required|numeric|min:-90|max:90',
            'longitude' => 'required|numeric|min:-180|max:180',
        ]);

        // Optionally persist to user model or session.
        if ($request->user()) {
            $request->session()->put('user_location', $data);
            return response()->json(['message' => 'User location stored']);
        }

        return response()->json(['message' => 'Unauthenticated'], 401);
    }
}
