<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\EstablishmentLocationNoticeMail;
use App\Models\CoffeeTrailLocationCheck;
use App\Models\Establishment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Throwable;

class LocationCheckController extends Controller
{
    public function index(): View
    {
        $establishments = Establishment::query()
            ->with(['owner:id,name,email'])
            ->withCount([
                'locationChecks as correct_count' => function ($query) {
                    $query->where('response', 'yes');
                },
                'locationChecks as incorrect_count' => function ($query) {
                    $query->where('response', 'no');
                },
            ])
            ->orderBy('name')
            ->get();

        $counts = [
            'all' => $establishments->count(),
            'correct' => (int) CoffeeTrailLocationCheck::query()->where('response', 'yes')->count(),
            'incorrect' => (int) CoffeeTrailLocationCheck::query()->where('response', 'no')->count(),
            'sent' => $establishments->whereNotNull('location_notice_email_sent_at')->count(),
        ];

        return view('admin.location-checks', compact('establishments', 'counts'));
    }

    public function sendNoticeEmail(Request $request, Establishment $establishment): JsonResponse
    {
        if (!Schema::hasColumn('establishments', 'location_notice_email_sent_at')) {
            return response()->json([
                'message' => 'Location notice email tracking is not ready yet. Run the latest database migrations and try again.',
            ], 503);
        }

        $establishment->load('owner');

        $incorrectCount = CoffeeTrailLocationCheck::query()
            ->where('establishment_id', $establishment->id)
            ->where('response', 'no')
            ->count();

        if ($establishment->location_notice_email_sent_at) {
            return response()->json([
                'message' => 'A notice email has already been sent to this establishment owner.',
                'sent' => true,
                'sent_at' => optional($establishment->location_notice_email_sent_at)->toIso8601String(),
            ], 409);
        }

        $recipient = trim((string) ($establishment->owner?->email ?: $establishment->email));
        if ($recipient === '' || !filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'message' => 'No valid email address is available for this establishment owner.',
            ], 422);
        }

        $ownerName = (string) ($establishment->owner?->name ?: 'BrewHub Establishment Owner');

        try {
            Mail::to($recipient)->send(new EstablishmentLocationNoticeMail(
                $establishment,
                $ownerName,
                (int) $incorrectCount
            ));

            $establishment->forceFill([
                'location_notice_email_sent_at' => now(),
                'location_notice_email_sent_to' => $recipient,
            ])->save();
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'The notice email could not be sent. Please check the mail configuration and try again.',
            ], 500);
        }

        return response()->json([
            'message' => 'Notice email sent successfully.',
            'sent' => true,
            'sent_at' => optional($establishment->location_notice_email_sent_at)->toIso8601String(),
            'sent_to' => $recipient,
            'sent_count' => Establishment::query()->whereNotNull('location_notice_email_sent_at')->count(),
        ]);
    }
}
