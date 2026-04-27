@extends('layouts.app')

@section('title', 'Messages - BrewHub')

@section('content')
<div class="chat-page min-h-screen bg-[#F5F0E8] flex" x-data="{ newChatOpen: false, selectedConversation: {{ $conversations->first()->id ?? 'null' }}, searchQuery: '' }">
    <!-- Sidebar Navigation -->
    <aside class="admin-sidebar fixed left-0 top-0 h-screen w-64 bg-[#3A2E22] text-[#F5F0E8] flex flex-col justify-between py-6 px-4 rounded-r-xl shadow-lg overflow-hidden z-40 -translate-x-full md:translate-x-0 transition-transform duration-300 ease-out">
        <div>
            <!-- Logo -->
            <div class="flex items-center mb-8">
                <img src="{{ asset('images/brewhublogo2.png') }}" alt="BrewHub logo" class="w-7 h-7 mr-2 object-contain shrink-0">
                <span class="brand-wordmark text-lg leading-none"><span class="brand-brew">Brew</span><span class="brand-hub">Hub</span></span>
            </div>

            <!-- Navigation -->
            <nav class="space-y-1">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center rounded-lg px-4 py-2 text-xs font-medium gap-2 transition-all duration-200 hover:bg-[#4E3D2B] hover:translate-x-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Dashboard
                </a>
                <a href="{{ route('admin.map') }}" class="flex items-center {{ request()->routeIs('admin.map') ? 'bg-[#4E3D2B]' : '' }} rounded-lg px-4 py-2 text-xs font-medium gap-2 transition-all duration-200 hover:bg-[#4E3D2B] hover:translate-x-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                    Map
                </a>
                <a href="{{ route('admin.establishments.index') }}" class="flex items-center {{ request()->routeIs('admin.establishments.*') ? 'bg-[#4E3D2B]' : '' }} rounded-lg px-4 py-2 text-xs font-medium gap-2 transition-all duration-200 hover:bg-[#4E3D2B] hover:translate-x-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Establishments
                </a>
                <a href="{{ route('admin.registrations.index') }}" class="flex items-center {{ request()->routeIs('admin.registrations.*') ? 'bg-[#4E3D2B]' : '' }} rounded-lg px-4 py-2 text-xs font-medium gap-2 transition-all duration-200 hover:bg-[#4E3D2B] hover:translate-x-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                    Registrations
                </a>
                <a href="{{ route('admin.resellers.index') }}" class="flex items-center {{ request()->routeIs('admin.resellers.*') ? 'bg-[#4E3D2B]' : '' }} rounded-lg px-4 py-2 text-xs font-medium gap-2 transition-all duration-200 hover:bg-[#4E3D2B] hover:translate-x-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l9-4 9 4v8l-9 4-9-4V8z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l9 4 9-4" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16" />
                    </svg>
                    Resellers
                </a>
                <a href="{{ route('admin.coupon-promos.index') }}" class="flex items-center {{ request()->routeIs('admin.coupon-promos.*') ? 'bg-[#4E3D2B]' : '' }} rounded-lg px-4 py-2 text-xs font-medium gap-2 transition-all duration-200 hover:bg-[#4E3D2B] hover:translate-x-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    Coupon Promos
                </a>
                <a href="{{ route('admin.rating-moderation.index') }}" class="flex items-center {{ request()->routeIs('admin.rating-moderation.*') ? 'bg-[#4E3D2B]' : '' }} rounded-lg px-4 py-2 text-xs font-medium gap-2 transition-all duration-200 hover:bg-[#4E3D2B] hover:translate-x-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                    Rating Moderation
                </a>
                <a href="{{ route('admin.recommendations') }}" class="flex items-center {{ request()->routeIs('admin.recommendations') ? 'bg-[#4E3D2B]' : '' }} rounded-lg px-4 py-2 text-xs font-medium gap-2 transition-all duration-200 hover:bg-[#4E3D2B] hover:translate-x-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Recommendations
                </a>
                <a href="{{ route('admin.marketplace.index') }}" class="flex items-center {{ request()->routeIs('admin.marketplace.*') ? 'bg-[#4E3D2B]' : '' }} rounded-lg px-4 py-2 text-xs font-medium gap-2 transition-all duration-200 hover:bg-[#4E3D2B] hover:translate-x-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    Marketplace
                </a>
                <a href="{{ route('chat.index') }}" class="flex items-center bg-[#4E3D2B] rounded-lg px-4 py-2 text-xs font-medium gap-2 transition-all duration-200 hover:bg-[#5A4A3A] hover:translate-x-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    Messages
                        @php
                            $authUser = Auth::user();
                            $totalUnread = $authUser->conversations()
                                ->get()
                                ->sum(function($conv) use ($authUser) {
                                    return $conv->unreadCount($authUser->id);
                                });
                        @endphp
                        @if($totalUnread > 0)
                            <span class="ml-auto inline-flex items-center justify-center min-w-[20px] h-5 px-1 text-[10px] font-bold text-white bg-red-600 rounded-full leading-none">
                                {{ $totalUnread }}
                            </span>
                        @endif
                    </a>
            </nav>
        </div>
        <!-- User Profile -->
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center min-w-0">
                <div class="w-10 h-10 bg-[#4A6741] rounded-full flex items-center justify-center text-white font-bold text-sm mr-3">
                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                </div>
                <div>
                    <div class="font-medium text-sm">{{ auth()->user()->name ?? 'Admin User' }}</div>
                    <div class="text-xs text-[#9E8C78]">Administrator</div>
                </div>
            </div>
            <button
                type="button"
                @click="$dispatch('open-logout-modal')"
                class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-[#F5F0E8] hover:bg-[#4E3D2B] transition-colors"
                title="Log out"
                aria-label="Log out"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H9m4 8H7a2 2 0 01-2-2V6a2 2 0 012-2h6"/>
                </svg>
            </button>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="chat-main ml-0 md:ml-64 flex-1 p-8 overflow-y-auto">
        <div class="chat-layout flex gap-6 h-[calc(100vh-120px)]">
            <!-- LEFT PANEL: Conversations List -->
            <div class="chat-conversations-panel w-80 bg-white rounded-xl shadow-sm flex flex-col overflow-hidden">
                <!-- Header -->
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

                    <!-- Search Bar -->
                    <div class="relative">
                        <svg class="w-4 h-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-[#9E8C78]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input
                            type="text"
                            x-model="searchQuery"
                            placeholder="Search conversations..."
                            class="chat-search-input pl-9 pr-3 py-1.5 text-xs bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#4A6741] focus:border-transparent w-64"
                        />
                    </div>
                </div>

                <!-- Conversations List -->
                <div class="flex-1 overflow-y-auto">
                    @forelse($conversations as $conv)
                        @php
                            $otherUser = $conv->users->where('id', '!=', Auth::id())->first();
                            $latestMessage = $conv->latestMessage;
                            $unreadCount = $conv->unreadCount(Auth::id());
                            $isActive = request()->route('conversation') && request()->route('conversation')->id === $conv->id;
                        @endphp

                        <a 
                            href="{{ route('chat.show', $conv) }}" 
                            @click="selectedConversation = {{ $conv->id }}"
                            x-show="!searchQuery || '{{ strtolower($otherUser->name) }}'.includes(searchQuery.toLowerCase())"
                            class="flex items-start gap-3 p-4 border-b transition-colors cursor-pointer {{ $isActive ? 'bg-[#F5F0E8] border-[#D8CCBA]' : ($unreadCount > 0 ? 'bg-[#ECF4ED] border-[#C8DEC9] hover:bg-[#E4F0E6]' : 'border-gray-50 hover:bg-gray-50') }}"
                        >
                                <!-- Avatar -->
                                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-[#2C4A2E] text-white flex items-center justify-center text-sm font-bold">
                                    {{ strtoupper(substr($otherUser->name, 0, 1)) }}
                                </div>

                                <!-- Conversation Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-1">
                                        <h3 class="text-sm font-semibold text-[#3A2E22] {{ $unreadCount > 0 ? 'font-bold' : '' }}">
                                            {{ $otherUser->name }}
                                        </h3>
                                        <span class="text-xs text-gray-500 ml-2">
                                            {{ $latestMessage ? $latestMessage->created_at->format('M d') : '' }}
                                        </span>
                                    </div>

                                    <!-- Latest Message Preview -->
                                    <p class="text-xs truncate {{ $unreadCount > 0 ? 'text-[#2F5C38] font-semibold' : 'text-gray-500' }}">
                                        {{ $latestMessage ? substr($latestMessage->body, 0, 50) : 'No messages yet' }}
                                    </p>
                                </div>

                                <!-- Unread Badge -->
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

            <!-- RIGHT PANEL: Messages or Empty State -->
            <div class="chat-messages-panel flex-1 bg-white rounded-xl shadow-sm flex flex-col overflow-hidden">
                @if($conversations->isEmpty() || !isset($conversation))
                    <!-- Empty State -->
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

                    <!-- Conversation Header -->
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

                    <!-- Messages Area -->
                    <div class="flex-1 overflow-y-auto p-4 space-y-4" id="messages-container">
                        @php
                            $lastMessageDate = null;
                        @endphp
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
                                @php
                                    $lastMessageDate = $messageDateKey;
                                @endphp
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

                    <!-- Message Input -->
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

    <!-- NEW CHAT MODAL -->
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
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-40 backdrop-blur-sm" @click.stop="newChatOpen = false"></div>

        <!-- Modal Card -->
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

                <!-- User Search -->
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

                <!-- Users List -->
                <div class="space-y-2">
                    @forelse($users as $user)
                        <div class="user-item" x-show="!userSearch || '{{ strtolower($user->name) }}'.includes(userSearch.toLowerCase())">
                            <button 
                                type="button"
                                @click="
                                    fetch('{{ route('chat.store') }}', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                        },
                                        body: JSON.stringify({ recipient_id: {{ $user->id }} })
                                    })
                                    .then(r => r.json())
                                    .then(data => {
                                        window.location.href = '{{ url('chat') }}/' + data.conversation_id;
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

    @media (max-width: 767px) {
        .chat-page .chat-main {
            padding-top: 4.75rem !important;
            padding-left: 0.85rem !important;
            padding-right: 0.85rem !important;
            padding-bottom: 0.85rem !important;
        }

        .chat-page .chat-layout {
            flex-direction: column;
            gap: 0.75rem !important;
            height: auto !important;
            min-height: 0;
        }

        .chat-page .chat-conversations-panel,
        .chat-page .chat-messages-panel {
            width: 100% !important;
            border-radius: 14px;
        }

        .chat-page .chat-conversations-panel {
            max-height: 42vh;
        }

        .chat-page .chat-messages-panel {
            min-height: 52vh;
        }

        .chat-page .chat-search-input {
            width: 100% !important;
        }

        .chat-page #messages-container .max-w-xs {
            max-width: min(82vw, 21rem) !important;
        }
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

    // Auto-scroll to bottom on page load
    document.addEventListener('DOMContentLoaded', function() {
        const messagesContainer = document.getElementById('messages-container');
        if (messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    });

    // Handle message form submission
    document.getElementById('message-form')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const input = document.getElementById('message-input');
        const container = document.getElementById('messages-container');
        const body = input.value.trim();

        if (!body) return;

        try {
            const response = await fetch('{{ isset($conversation) ? route('chat.messages.store', $conversation) : '#' }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
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

    // Real-time messaging with Laravel Echo
    @if(isset($conversation))
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
