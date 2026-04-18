@extends('farm-owner.layouts.app')

@section('title', 'Messages - BrewHub')

@section('content')
<div class="min-h-screen bg-[#F5F0E8] flex" x-data="{ newChatOpen: false, selectedConversation: {{ $conversations->first()->id ?? 'null' }}, searchQuery: '' }">
    <main class="flex-1 overflow-y-auto">
        <div class="flex gap-6 h-[calc(100vh-160px)]">
            <div class="w-80 bg-white rounded-xl shadow-sm flex flex-col overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <h1 class="text-2xl font-display font-bold text-[#3A2E22]">Messages</h1>
                        <button @click="newChatOpen = true" class="inline-flex items-center gap-2 px-4 py-2 bg-[#2C4A2E] text-white text-xs font-semibold rounded-lg hover:bg-[#1F3620] transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            New Chat
                        </button>
                    </div>

                    <div class="relative">
                        <svg class="w-4 h-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-[#9E8C78]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input
                            type="text"
                            x-model="searchQuery"
                            placeholder="Search conversations..."
                            class="pl-9 pr-3 py-1.5 text-xs bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#4A6741] focus:border-transparent w-64"
                        />
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto">
                    @forelse($conversations as $conv)
                        @php
                            $otherUser = $conv->users->where('id', '!=', Auth::id())->first();
                            $latestMessage = $conv->latestMessage;
                            $unreadCount = $conv->unreadCount(Auth::id());
                            $isActive = isset($conversation) && $conversation && $conversation->id === $conv->id;
                        @endphp

                        <a
                            href="{{ route('farm-owner.messages.show', $conv) }}"
                            @click="selectedConversation = {{ $conv->id }}"
                            x-show="!searchQuery || '{{ strtolower($otherUser->name) }}'.includes(searchQuery.toLowerCase())"
                            class="flex items-start gap-3 p-4 border-b transition-colors cursor-pointer {{ $isActive ? 'bg-[#F5F0E8] border-[#D8CCBA]' : ($unreadCount > 0 ? 'bg-[#ECF4ED] border-[#C8DEC9] hover:bg-[#E4F0E6]' : 'border-gray-50 hover:bg-gray-50') }}"
                        >
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-[#2C4A2E] text-white flex items-center justify-center text-sm font-bold">
                                {{ strtoupper(substr($otherUser->name, 0, 1)) }}
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <h3 class="text-sm font-semibold text-[#3A2E22] {{ $unreadCount > 0 ? 'font-bold' : '' }}">
                                        {{ $otherUser->name }}
                                    </h3>
                                    <span class="text-xs text-gray-500 ml-2">
                                        {{ $latestMessage ? $latestMessage->created_at->format('M d') : '' }}
                                    </span>
                                </div>

                                <p class="text-xs truncate {{ $unreadCount > 0 ? 'text-[#2F5C38] font-semibold' : 'text-gray-500' }}">
                                    {{ $latestMessage ? substr($latestMessage->body, 0, 50) : 'No messages yet' }}
                                </p>
                            </div>

                            @if($unreadCount > 0)
                                <div class="flex-shrink-0 w-5 h-5 rounded-full bg-[#2C4A2E] text-white flex items-center justify-center text-[10px] font-bold">
                                    {{ $unreadCount }}
                                </div>
                            @endif
                        </a>
                    @empty
                        <div class="flex items-center justify-center h-full text-center px-4">
                            <div>
                                <p class="text-gray-500 text-sm">No conversations yet</p>
                                <button @click="newChatOpen = true" class="mt-2 text-[#2C4A2E] text-sm font-semibold hover:text-[#1F3620] transition-colors">
                                    Start a new chat
                                </button>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="flex-1 bg-white rounded-xl shadow-sm flex flex-col overflow-hidden">
                @if($conversations->isEmpty() || !isset($conversation) || !$conversation)
                    <div class="flex-1 flex items-center justify-center">
                        <div class="text-center">
                            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            <p class="text-gray-500 text-lg">Select a conversation to start messaging</p>
                        </div>
                    </div>
                @else
                    @php
                        $otherUser = $conversation->users->where('id', '!=', Auth::id())->first();
                    @endphp

                    <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-[#2C4A2E] text-white flex items-center justify-center text-sm font-bold">
                                {{ strtoupper(substr($otherUser->name, 0, 1)) }}
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-[#3A2E22]">{{ $otherUser->name }}</h2>
                                <span class="inline-block text-xs font-medium px-2 py-1 rounded bg-[#F5F0E8] text-[#3A2E22] mt-1">
                                    {{ ucfirst($otherUser->role) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto p-4 space-y-4" id="messages-container">
                        @php($lastMessageDate = null)
                        @foreach($messages as $message)
                            @php
                                $isOwn = $message->sender_id === Auth::id();
                                $messageDateKey = $message->created_at->toDateString();
                            @endphp

                            @if($lastMessageDate !== $messageDateKey)
                                <div class="flex items-center gap-3 py-2">
                                    <div class="h-px flex-1 bg-gray-200"></div>
                                    <span class="text-[11px] font-medium text-gray-500">{{ $message->created_at->format('M j, Y') }}</span>
                                    <div class="h-px flex-1 bg-gray-200"></div>
                                </div>
                                @php($lastMessageDate = $messageDateKey)
                            @endif

                            <div class="space-y-1" data-message-group="1" data-message-date="{{ $messageDateKey }}">
                                <div class="flex {{ $isOwn ? 'justify-end' : 'justify-start' }}">
                                    <div class="max-w-xs {{ $isOwn ? 'bg-[#2C4A2E] text-white' : 'bg-gray-100 text-gray-900' }} rounded-2xl px-4 py-2">
                                        <p class="text-sm">{{ $message->body }}</p>
                                    </div>
                                </div>

                                <div class="flex {{ $isOwn ? 'justify-end' : 'justify-start' }}">
                                    <p class="text-xs text-gray-500">
                                        <span class="font-semibold">{{ $isOwn ? 'You' : $message->sender->name }}</span> • {{ $message->created_at->format('M j') }} • {{ $message->created_at->format('g:i A') }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="p-4 border-t border-gray-100">
                        <form id="message-form" class="flex items-center gap-3">
                            @csrf
                            <input
                                id="message-input"
                                type="text"
                                name="body"
                                placeholder="Type a message..."
                                autocomplete="off"
                                class="flex-1 px-4 py-2 text-sm rounded-full border border-gray-200 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#2C4A2E] transition-all"
                            >
                            <button
                                type="submit"
                                class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-[#2C4A2E] text-white hover:bg-[#1F3620] transition-colors"
                            >
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M16.6915026,12.4744748 L3.50612381,13.2599618 C3.19218622,13.2599618 3.03521743,13.4170592 3.03521743,13.5741566 L1.15159189,20.0151496 C0.8376543,20.8006365 0.99,21.89 1.77946707,22.52 C2.41,22.99 3.50612381,23.1 4.13399899,22.99 L21.714504,14.0454487 C22.6563168,13.5741566 23.1272231,12.6315722 22.6563168,11.6889879 L4.13399899,2.74423314 C3.34915502,2.5871357 2.40734225,2.69821616 1.77946707,3.16346272 C0.994623095,3.78218711 0.837654326,4.8715722 1.15159189,5.65702918 L3.03521743,12.0980222 C3.03521743,12.2551196 3.19218622,12.4122171 3.50612381,12.4122171 L16.6915026,13.1977039 C16.6915026,13.1977039 17.1624089,13.1977039 17.1624089,12.8743169 L17.1624089,12.0980222 C17.1624089,11.89 17.1624089,11.4136991 16.6915026,11.4136991 C16.0636274,11.4136991 16.6915026,12.0980222 16.6915026,12.4744748 Z"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </main>

    <div
        class="fixed inset-0 z-50 flex items-center justify-center px-4"
        x-show="newChatOpen"
        @keydown.escape="newChatOpen = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click="newChatOpen = false"
        style="display: none;"
    >
        <div class="fixed inset-0 bg-black bg-opacity-40 backdrop-blur-sm" @click.stop="newChatOpen = false"></div>

        <div
            class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto chat-modal-scrollbar"
            @click.stop
            x-data="{ userSearch: '' }"
        >
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-display font-bold text-[#3A2E22]">Start New Chat</h2>
                    <button @click="newChatOpen = false" class="text-gray-500 hover:text-gray-800 text-2xl">&times;</button>
                </div>

                <div class="relative mb-4">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-[#9E8C78]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        type="text"
                        x-model="userSearch"
                        placeholder="Search users..."
                        class="w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-200 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#2C4A2E] transition-all"
                    >
                </div>

                <div class="space-y-2">
                    @forelse($users as $user)
                        <div class="user-item" x-show="!userSearch || '{{ strtolower($user->name) }}'.includes(userSearch.toLowerCase())">
                            <button
                                type="button"
                                @click="
                                    fetch('{{ route('farm-owner.messages.store') }}', {
                                        method: 'POST',
                                        credentials: 'same-origin',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'Accept': 'application/json',
                                            'X-Requested-With': 'XMLHttpRequest',
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                        },
                                        body: JSON.stringify({ recipient_id: {{ $user->id }} })
                                    })
                                    .then(async (r) => {
                                        if (!r.ok) {
                                            throw new Error('Unable to start chat.');
                                        }
                                        return r.json();
                                    })
                                    .then(data => {
                                        if (!data || !data.conversation_id) {
                                            throw new Error('Invalid chat response.');
                                        }
                                        window.location.href = '{{ url('farm-owner/messages') }}/' + data.conversation_id;
                                    })
                                    .catch(() => {
                                        alert('Unable to start chat right now. Please try again.');
                                    })
                                "
                                class="w-full flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition-colors border border-transparent hover:border-gray-200"
                            >
                                <div class="flex items-center gap-3 flex-1">
                                    <div class="w-8 h-8 rounded-full bg-[#2C4A2E] text-white flex items-center justify-center text-xs font-bold">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div class="text-left">
                                        <p class="text-sm font-semibold text-[#3A2E22]">{{ $user->name }}</p>
                                        <span class="text-xs text-gray-500">{{ ucwords(str_replace('_', ' ', $user->role)) }}</span>
                                    </div>
                                </div>
                                <span class="text-xs font-medium px-2 py-1 rounded bg-[#F5F0E8] text-[#3A2E22]">
                                    {{ ucwords(str_replace('_', ' ', $user->role)) }}
                                </span>
                            </button>
                        </div>
                    @empty
                        <p class="text-center text-gray-500 py-4">No users available</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .chat-modal-scrollbar {
        scrollbar-width: thin;
        scrollbar-color: rgba(107, 114, 128, 0.55) transparent;
    }

    .chat-modal-scrollbar::-webkit-scrollbar {
        width: 6px;
    }

    .chat-modal-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }

    .chat-modal-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(107, 114, 128, 0.55);
        border-radius: 9999px;
    }

    .chat-modal-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(107, 114, 128, 0.75);
    }
</style>

<script>
    const escapeHtml = (value = '') => String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

    const formatChatTimestamp = (dateInput) => {
        const date = new Date(dateInput);
        if (Number.isNaN(date.getTime())) return '';

        const day = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        const time = date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
        return `${day} • ${time}`;
    };

    const formatChatDateLabel = (dateInput) => {
        const date = new Date(dateInput);
        if (Number.isNaN(date.getTime())) return '';
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    };

    const formatDateKey = (dateInput) => {
        const date = new Date(dateInput);
        if (Number.isNaN(date.getTime())) return '';
        return date.toISOString().slice(0, 10);
    };

    const appendDateDividerIfNeeded = (container, dateInput) => {
        if (!container) return;

        const nextDateKey = formatDateKey(dateInput);
        const groups = container.querySelectorAll('[data-message-group]');
        const lastGroup = groups.length ? groups[groups.length - 1] : null;
        const lastDateKey = lastGroup?.getAttribute('data-message-date') || '';

        if (nextDateKey && nextDateKey !== lastDateKey) {
            const dividerHtml = `
                <div class="flex items-center gap-3 py-2">
                    <div class="h-px flex-1 bg-gray-200"></div>
                    <span class="text-[11px] font-medium text-gray-500">${formatChatDateLabel(dateInput)}</span>
                    <div class="h-px flex-1 bg-gray-200"></div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', dividerHtml);
        }
    };

    const buildMessageGroupHtml = ({ isOwnMessage, senderName, body, createdAt }) => {
        const safeBody = escapeHtml(body || '');
        const safeSender = escapeHtml(senderName || 'User');
        const safeDateKey = formatDateKey(createdAt);
        const timestamp = formatChatTimestamp(createdAt);

        return `
            <div class="space-y-1" data-message-group="1" data-message-date="${safeDateKey}">
                <div class="flex ${isOwnMessage ? 'justify-end' : 'justify-start'}">
                    <div class="max-w-xs ${isOwnMessage ? 'bg-[#2C4A2E] text-white' : 'bg-gray-100 text-gray-900'} rounded-2xl px-4 py-2">
                        <p class="text-sm">${safeBody}</p>
                    </div>
                </div>
                <div class="flex ${isOwnMessage ? 'justify-end' : 'justify-start'}">
                    <p class="text-xs text-gray-500">
                        <span class="font-semibold">${isOwnMessage ? 'You' : safeSender}</span> • ${timestamp}
                    </p>
                </div>
            </div>
        `;
    };

    document.addEventListener('DOMContentLoaded', function() {
        const messagesContainer = document.getElementById('messages-container');
        if (messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    });

    document.getElementById('message-form')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const input = document.getElementById('message-input');
        const container = document.getElementById('messages-container');
        const body = input.value.trim();

        if (!body) return;

        try {
            const response = await fetch('{{ isset($conversation) && $conversation ? route('farm-owner.messages.send', $conversation) : '#' }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ body })
            });

            if (response.ok) {
                const data = await response.json();

                if (container) {
                    const createdAt = data?.created_at || new Date().toISOString();
                    appendDateDividerIfNeeded(container, createdAt);
                    const messageHtml = buildMessageGroupHtml({
                        isOwnMessage: true,
                        senderName: 'You',
                        body: data?.body ?? body,
                        createdAt,
                    });
                    container.insertAdjacentHTML('beforeend', messageHtml);
                    container.scrollTop = container.scrollHeight;
                }

                input.value = '';
                input.focus();
            } else {
                alert('Message was not sent. Please try again.');
            }
        } catch (error) {
            console.error('Failed to send message:', error);
            alert('Message was not sent. Please try again.');
        }
    });

    @if(isset($conversation) && $conversation)
        Echo.join('conversation.{{ $conversation->id }}')
            .listen('MessageSent', (e) => {
                const container = document.getElementById('messages-container');
                if (!container) return;

                const isOwnMessage = e.sender_id === {{ Auth::id() }};
                const createdAt = e.created_at || new Date().toISOString();
                appendDateDividerIfNeeded(container, createdAt);
                const messageHtml = buildMessageGroupHtml({
                    isOwnMessage,
                    senderName: e.sender_name,
                    body: e.body,
                    createdAt,
                });
                container.insertAdjacentHTML('beforeend', messageHtml);
                container.scrollTop = container.scrollHeight;
            });
    @endif
</script>
@endsection
