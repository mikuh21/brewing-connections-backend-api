<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard');
    }

    public function notifications(): JsonResponse
    {
        $user = User::query()->findOrFail(Auth::id());

        $registrationItems = User::query()
            ->where('role', 'consumer')
            ->where('created_at', '>=', now()->subDays(7))
            ->latest()
            ->limit(5)
            ->get()
            ->map(function (User $consumer) {
                return [
                    'id' => 'registration-' . $consumer->id,
                    'type' => 'registration',
                    'title' => 'New consumer registration: ' . $consumer->name,
                    'subtitle' => $consumer->email,
                    'time' => optional($consumer->created_at)->diffForHumans(),
                    'timestamp' => optional($consumer->created_at)?->timestamp ?? 0,
                    'url' => route('admin.registrations.index'),
                ];
            });

        $resellerRegistrationItems = User::query()
            ->where('role', 'reseller')
            ->where('created_at', '>=', now()->subDays(7))
            ->latest()
            ->limit(5)
            ->get()
            ->map(function (User $reseller) {
                return [
                    'id' => 'reseller-registration-' . $reseller->id,
                    'type' => 'reseller_registration',
                    'title' => 'New reseller registration: ' . $reseller->name,
                    'subtitle' => $reseller->email,
                    'time' => optional($reseller->created_at)->diffForHumans(),
                    'timestamp' => optional($reseller->created_at)?->timestamp ?? 0,
                    'url' => route('admin.resellers.index'),
                ];
            });

        $chatItems = collect();
        $unreadChatCount = 0;

        $conversations = $user->conversations()->get();

        foreach ($conversations as $conversation) {
            $unread = $conversation->unreadCount($user->id);
            if ($unread <= 0) {
                continue;
            }

            $unreadChatCount += $unread;

            $participant = $conversation->participants()
                ->where('user_id', $user->id)
                ->first();

            $latestUnreadIncoming = $conversation->messages()
                ->with('sender:id,name')
                ->where('sender_id', '!=', $user->id)
                ->when(!empty($participant?->last_read_at), function ($query) use ($participant) {
                    $query->where('created_at', '>', $participant->last_read_at);
                }, function ($query) {
                    $query->whereNull('read_at');
                })
                ->latest()
                ->first();

            if (!$latestUnreadIncoming) {
                continue;
            }

            $chatItems->push([
                'id' => 'chat-' . $conversation->id . '-' . $latestUnreadIncoming->id,
                'type' => 'chat',
                'title' => 'New message from ' . ($latestUnreadIncoming->sender->name ?? 'User'),
                'subtitle' => Str::limit((string) $latestUnreadIncoming->body, 60),
                'time' => optional($latestUnreadIncoming->created_at)->diffForHumans(),
                'timestamp' => optional($latestUnreadIncoming->created_at)?->timestamp ?? 0,
                'url' => route('chat.show', $conversation),
            ]);
        }

        $items = $registrationItems
            ->merge($resellerRegistrationItems)
            ->merge($chatItems)
            ->sortByDesc('timestamp')
            ->take(12)
            ->values();

        return response()->json([
            'counts' => [
                'registrations' => $registrationItems->count(),
                'reseller_registrations' => $resellerRegistrationItems->count(),
                'resellers_pending' => User::query()
                    ->where('role', 'reseller')
                    ->where(function ($query) {
                        $query->where('is_verified_reseller', false)
                            ->orWhereNull('is_verified_reseller');
                    })
                    ->count(),
                'unread_chats' => $unreadChatCount,
                'total' => $registrationItems->count() + $resellerRegistrationItems->count() + $unreadChatCount,
            ],
            'items' => $items,
            'updated_at' => now()->toIso8601String(),
        ]);
    }
}
