<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ChatController extends Controller
{
    public function conversations(): JsonResponse
    {
        $authUser = User::query()->findOrFail(Auth::id());

        $conversations = $authUser->conversations()
            ->with([
                'users:id,name,role,image_url',
                'latestMessage.sender:id,name,role',
            ])
            ->orderByDesc(function ($query) {
                $query->select('created_at')
                    ->from('messages')
                    ->whereColumn('conversation_id', 'conversations.id')
                    ->latest()
                    ->limit(1);
            })
            ->get();

        $payload = $conversations
            ->map(fn (Conversation $conversation) => $this->serializeConversation($conversation, $authUser->id))
            ->values();

        return response()->json([
            'data' => $payload,
            'meta' => [
                'total_unread' => $payload->sum('unread_count'),
            ],
        ]);
    }

    public function recipients(): JsonResponse
    {
        $users = User::query()
            ->where('id', '!=', Auth::id())
            ->whereIn('role', ['admin', 'farm_owner', 'cafe_owner', 'reseller'])
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role', 'image_url']);

        return response()->json([
            'data' => $users->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'avatar_url' => $user->image_url,
            ])->values(),
        ]);
    }

    public function storeConversation(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'recipient_id' => ['required', 'integer', 'exists:users,id', 'different:' . Auth::id()],
        ]);

        $conversation = $this->findOrCreateConversation((int) $payload['recipient_id']);

        $conversation->load([
            'users:id,name,role,image_url',
            'latestMessage.sender:id,name,role',
        ]);

        return response()->json([
            'data' => $this->serializeConversation($conversation, (int) Auth::id()),
        ]);
    }

    public function messages(Conversation $conversation): JsonResponse
    {
        abort_unless($this->isParticipant($conversation, (int) Auth::id()), 403);

        $conversation->load([
            'users:id,name,role,image_url',
            'latestMessage.sender:id,name,role',
        ]);

        $messages = $conversation->messages()
            ->with('sender:id,name,role,image_url')
            ->orderBy('created_at')
            ->get();

        $conversation->participants()
            ->where('user_id', Auth::id())
            ->update(['last_read_at' => now()]);

        return response()->json([
            'conversation' => $this->serializeConversation($conversation, (int) Auth::id()),
            'data' => $messages->map(fn (Message $message) => $this->serializeMessage($message))->values(),
        ]);
    }

    public function sendMessage(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'conversation_id' => ['nullable', 'integer', 'exists:conversations,id'],
            'recipient_id' => ['nullable', 'integer', 'exists:users,id', 'different:' . Auth::id()],
            'body' => ['required', 'string', 'max:1000'],
        ]);

        if (empty($payload['conversation_id']) && empty($payload['recipient_id'])) {
            throw ValidationException::withMessages([
                'conversation_id' => ['conversation_id or recipient_id is required.'],
            ]);
        }

        if (! empty($payload['conversation_id'])) {
            $conversation = Conversation::query()->findOrFail($payload['conversation_id']);

            abort_unless($this->isParticipant($conversation, (int) Auth::id()), 403);
        } else {
            $conversation = $this->findOrCreateConversation((int) $payload['recipient_id']);
        }

        $message = $conversation->messages()->create([
            'sender_id' => Auth::id(),
            'body' => $payload['body'],
        ]);

        $conversation->participants()
            ->where('user_id', Auth::id())
            ->update(['last_read_at' => now()]);

        broadcast(new MessageSent($message))->toOthers();

        $conversation->load([
            'users:id,name,role,image_url',
            'latestMessage.sender:id,name,role',
        ]);

        return response()->json([
            'data' => $this->serializeMessage($message->load('sender:id,name,role,image_url')),
            'conversation' => $this->serializeConversation($conversation, (int) Auth::id()),
        ]);
    }

    public function markAsRead(Conversation $conversation): JsonResponse
    {
        abort_unless($this->isParticipant($conversation, (int) Auth::id()), 403);

        $conversation->participants()
            ->where('user_id', Auth::id())
            ->update(['last_read_at' => now()]);

        return response()->json([
            'message' => 'Conversation marked as read.',
        ]);
    }

    private function findOrCreateConversation(int $recipientId): Conversation
    {
        $authUser = User::query()->findOrFail(Auth::id());

        $existingConversation = $authUser->conversations()
            ->whereHas('users', function ($query) use ($recipientId) {
                $query->where('users.id', $recipientId);
            })
            ->has('users', '=', 2)
            ->first();

        if ($existingConversation) {
            return $existingConversation;
        }

        $conversation = Conversation::create();
        $conversation->users()->attach([Auth::id(), $recipientId]);

        return $conversation;
    }

    private function isParticipant(Conversation $conversation, int $userId): bool
    {
        return $conversation->users()->where('users.id', $userId)->exists();
    }

    private function serializeConversation(Conversation $conversation, int $authUserId): array
    {
        $otherParticipant = $conversation->users->firstWhere('id', '!=', $authUserId);
        $latestMessage = $conversation->latestMessage;

        return [
            'id' => $conversation->id,
            'title' => $conversation->title,
            'other_participant' => $otherParticipant ? [
                'id' => $otherParticipant->id,
                'name' => $otherParticipant->name,
                'role' => $otherParticipant->role,
                'avatar_url' => $otherParticipant->image_url,
            ] : null,
            'latest_message' => $latestMessage ? [
                'id' => $latestMessage->id,
                'body' => $latestMessage->body,
                'sender_id' => $latestMessage->sender_id,
                'created_at' => optional($latestMessage->created_at)->toIso8601String(),
            ] : null,
            'latest_message_at' => optional($latestMessage?->created_at)->toIso8601String(),
            'unread_count' => $conversation->unreadCount($authUserId),
        ];
    }

    private function serializeMessage(Message $message): array
    {
        return [
            'id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'body' => $message->body,
            'sender_id' => $message->sender_id,
            'sender_name' => $message->sender?->name,
            'sender_role' => $message->sender?->role,
            'created_at' => optional($message->created_at)->toIso8601String(),
        ];
    }
}
