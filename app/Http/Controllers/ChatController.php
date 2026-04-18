<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index()
    {
        $authUser = User::query()->findOrFail(Auth::id());

        $conversations = $authUser->conversations()
            ->with(['users', 'latestMessage.sender'])
            ->orderByDesc(function ($query) {
                $query->select('created_at')
                    ->from('messages')
                    ->whereColumn('conversation_id', 'conversations.id')
                    ->latest()
                    ->limit(1);
            })
            ->get();

        $users = User::where('id', '!=', Auth::id())->get();

        return view('chat.index', compact('conversations', 'users'));
    }

    public function show(Conversation $conversation)
    {
        abort_unless($conversation->users->contains(Auth::id()), 403);

        $messages = $conversation->messages()
            ->with('sender')
            ->orderBy('created_at')
            ->get();

        // Mark as read
        $conversation->participants()
            ->where('user_id', Auth::id())
            ->update(['last_read_at' => now()]);

        $users = User::where('id', '!=', Auth::id())->get();
        $authUser = User::query()->findOrFail(Auth::id());
        $conversations = $authUser->conversations()
            ->with(['users', 'latestMessage.sender'])
            ->orderByDesc(function ($query) {
                $query->select('created_at')
                    ->from('messages')
                    ->whereColumn('conversation_id', 'conversations.id')
                    ->latest()
                    ->limit(1);
            })
            ->get();

        return view('chat.index', compact('conversation', 'conversations', 'messages', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate(['recipient_id' => 'required|exists:users,id']);

        $authUser = User::query()->findOrFail(Auth::id());

        $existingConversation = $authUser->conversations()
            ->whereHas('users', function ($q) use ($request) {
                $q->where('users.id', $request->recipient_id);
            })
            ->has('users', '=', 2)
            ->first();

        if ($existingConversation) {
            if ($request->wantsJson()) {
                return response()->json(['conversation_id' => $existingConversation->id]);
            }
            return redirect()->route('chat.show', $existingConversation);
        }

        $conversation = Conversation::create();
        $conversation->users()->attach([Auth::id(), $request->recipient_id]);

        if ($request->wantsJson()) {
            return response()->json(['conversation_id' => $conversation->id]);
        }
        return redirect()->route('chat.show', $conversation);
    }

    public function sendMessage(Request $request, Conversation $conversation)
    {
        abort_unless($conversation->users->contains(Auth::id()), 403);

        $request->validate(['body' => 'required|string|max:1000']);

        $message = $conversation->messages()->create([
            'sender_id' => Auth::id(),
            'body' => $request->body,
        ]);

        broadcast(new \App\Events\MessageSent($message))->toOthers();

        return response()->json([
            'id' => $message->id,
            'body' => $message->body,
            'sender_id' => $message->sender_id,
            'sender_name' => Auth::user()->name,
            'created_at' => $message->created_at->toIso8601String(),
        ]);
    }
}
