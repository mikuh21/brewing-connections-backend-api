<?php

namespace App\Http\Controllers\CafeOwner;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class CafeOwnerMarketplaceController extends Controller
{
    public function index()
    {
        $establishment = $this->resolveOwnedEstablishment((int) Auth::id());

        return view('cafe-owner.menu', compact('establishment'));
    }

    public function uploadMenu(Request $request)
    {
        $validated = $request->validate([
            'menu_file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
        ], [
            'menu_file.required' => 'Please choose a menu image or PDF to upload.',
            'menu_file.mimes' => 'Upload your menu as a JPG, PNG, WEBP image, or PDF.',
            'menu_file.max' => 'Your menu file must be 10 MB or smaller.',
        ]);

        $establishment = $this->resolveOwnedEstablishment((int) Auth::id());

        if (!$establishment) {
            return back()->withErrors([
                'menu_file' => 'Create your coffee shop profile before uploading a menu.',
            ]);
        }

        if (!Schema::hasColumn('establishments', 'menu_image')) {
            return back()->withErrors([
                'menu_file' => 'Menu uploads are not available until the latest database migration has run.',
            ]);
        }

        $path = $validated['menu_file']->store('establishment-menus', 'supabase');
        $establishment->menu_image = $path;
        if (Schema::hasColumn('establishments', 'menu_uploaded_at')) {
            $establishment->menu_uploaded_at = now();
        }
        $establishment->save();

        return redirect()
            ->route('cafe-owner.marketplace')
            ->with('status', 'Your cafe menu was uploaded successfully.');
    }

    public function deleteMenu()
    {
        $establishment = $this->resolveOwnedEstablishment((int) Auth::id());

        if (!$establishment) {
            return redirect()->route('cafe-owner.marketplace');
        }

        $storedPath = (string) ($establishment->getRawOriginal('menu_image') ?? '');

        if ($storedPath !== '' && !preg_match('/^https?:\/\//i', $storedPath)) {
            try {
                Storage::disk('supabase')->delete($storedPath);
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        $establishment->menu_image = null;
        if (Schema::hasColumn('establishments', 'menu_uploaded_at')) {
            $establishment->menu_uploaded_at = null;
        }
        $establishment->save();

        return redirect()
            ->route('cafe-owner.marketplace')
            ->with('status', 'Your cafe menu was removed.');
    }

    protected function resolveOwnedEstablishment(int $userId): ?Establishment
    {
        $query = Establishment::query();

        if (Schema::hasColumn('establishments', 'user_id')) {
            return $query->where('user_id', $userId)->first();
        }

        if (Schema::hasColumn('establishments', 'owner_id')) {
            return $query->where('owner_id', $userId)->first();
        }

        return null;
    }
}
