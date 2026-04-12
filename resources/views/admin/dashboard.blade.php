@extends('layouts.app')

@section('title', 'Admin Dashboard - BrewHub')

@section('content')
<div class="min-h-screen bg-[#F5F0E8] flex" x-data="{ logoutModalOpen: false }" @keydown.escape.window="logoutModalOpen = false">
    <!-- Sidebar -->
    <aside class="fixed left-0 top-0 h-screen w-64 bg-[#3A2E22] text-[#F5F0E8] flex flex-col justify-between py-6 px-4 rounded-r-xl shadow-lg overflow-hidden z-20">
        <div>
            <!-- Logo -->
            <div class="flex items-center mb-8">
                <svg class="w-6 h-6 mr-3 text-[#F5F0E8]" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
                <span class="text-lg font-display font-bold">BrewHub</span>
            </div>

            <!-- Navigation -->
            <nav class="space-y-1">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center bg-[#4E3D2B] rounded-lg px-4 py-2 text-xs font-medium gap-2 transition-all duration-200 hover:bg-[#5A4A3A] hover:translate-x-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Dashboard
                </a>
                <a href="{{ route('admin.map') }}" class="flex items-center rounded-lg px-4 py-2 text-xs font-medium gap-2 transition-all duration-200 hover:bg-[#4E3D2B] hover:translate-x-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                    Map
                </a>
                <a href="{{ route('admin.establishments.index') }}" class="flex items-center rounded-lg px-4 py-2 text-xs font-medium gap-2 transition-all duration-200 hover:bg-[#4E3D2B] hover:translate-x-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Establishments
                </a>
                <a href="{{ route('admin.registrations.index') }}" class="flex items-center rounded-lg px-4 py-2 text-xs font-medium gap-2 transition-all duration-200 hover:bg-[#4E3D2B] hover:translate-x-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                    Registrations
                </a>
                <a href="{{ route('admin.resellers.index') }}" class="flex items-center rounded-lg px-4 py-2 text-xs font-medium gap-2 transition-all duration-200 hover:bg-[#4E3D2B] hover:translate-x-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l9-4 9 4v8l-9 4-9-4V8z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l9 4 9-4" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16" />
                    </svg>
                    Resellers
                </a>
                <a href="{{ route('admin.coupon-promos.index') }}" class="flex items-center rounded-lg px-4 py-2 text-xs font-medium gap-2 transition-all duration-200 hover:bg-[#4E3D2B] hover:translate-x-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    Coupon Promos
                </a>
                <a href="{{ route('admin.rating-moderation.index') }}" class="flex items-center rounded-lg px-4 py-2 text-xs font-medium gap-2 transition-all duration-200 hover:bg-[#4E3D2B] hover:translate-x-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                    Rating Moderation
                </a>
                <a href="{{ route('admin.recommendations') }}" class="flex items-center rounded-lg px-4 py-2 text-xs font-medium gap-2 transition-all duration-200 hover:bg-[#4E3D2B] hover:translate-x-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Recommendations
                </a>
                <a href="{{ route('admin.marketplace.index') }}" class="flex items-center rounded-lg px-4 py-2 text-xs font-medium gap-2 transition-all duration-200 hover:bg-[#4E3D2B] hover:translate-x-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    Marketplace
                </a>
                <a href="{{ route('chat.index') }}" class="flex items-center rounded-lg px-4 py-2 text-xs font-medium gap-2 transition-all duration-200 hover:bg-[#4E3D2B] hover:translate-x-1">
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
                        <span class="ml-auto inline-flex items-center justify-center min-w-[20px] h-5 px-1 text-[10px] font-bold text-white bg-red-600 rounded-full">
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
                A
            </div>
            <div>
                <div class="font-medium text-sm">Admin User</div>
                <div class="text-xs text-[#9E8C78]">Administrator</div>
            </div>
            </div>
            <button
                type="button"
                @click="logoutModalOpen = true"
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

    <!-- Main Content -->
    <main class="ml-64 flex-1 p-8 overflow-y-auto">
        <!-- Top Navbar -->
        <div class="flex items-center justify-between mb-8 sticky top-0 z-10 bg-[#F5F0E8]">
            <div>
                <h1 class="text-3xl font-display font-bold text-[#3A2E22] mb-1">
                    Welcome back, <span class="italic text-[#4A6741]">Admin</span>
                </h1>
                <p class="text-[#9E8C78] text-sm font-medium">Manage your coffee ecosystem</p>
            </div>

            <div
                class="flex items-center space-x-4"
                x-data="{
                    open: false,
                    unreadCount: 0,
                    items: [],
                    seenIds: [],
                    showUnreadOnly: false,
                    poller: null,
                    isUnread(item) {
                        if (item.type === 'chat') {
                            return true;
                        }
                        return !this.seenIds.includes(item.id);
                    },
                    visibleItems() {
                        return this.showUnreadOnly
                            ? this.items.filter((item) => this.isUnread(item))
                            : this.items;
                    },
                    registrationItems() {
                        return this.visibleItems().filter((item) => item.type === 'registration');
                    },
                    resellerRegistrationItems() {
                        return this.visibleItems().filter((item) => item.type === 'reseller_registration');
                    },
                    chatItems() {
                        return this.visibleItems().filter((item) => item.type === 'chat');
                    },
                    storageKey() {
                        return 'adminSeenNotifications:{{ auth()->id() }}';
                    },
                    loadSeen() {
                        try {
                            const raw = localStorage.getItem(this.storageKey()) || '[]';
                            const parsed = JSON.parse(raw);
                            this.seenIds = Array.isArray(parsed) ? parsed : [];
                        } catch (_error) {
                            this.seenIds = [];
                        }
                    },
                    persistSeen() {
                        localStorage.setItem(this.storageKey(), JSON.stringify(this.seenIds));
                    },
                    recomputeUnread() {
                        this.unreadCount = this.items.filter((item) => this.isUnread(item)).length;
                    },
                    markItemAsRead(itemId) {
                        const item = this.items.find((entry) => entry.id === itemId);
                        if (item?.type === 'chat') {
                            return;
                        }
                        if (!this.seenIds.includes(itemId)) {
                            this.seenIds.push(itemId);
                            this.persistSeen();
                        }
                        this.recomputeUnread();
                    },
                    markAllAsRead() {
                        this.items.forEach((item) => {
                            if (item.type === 'chat') {
                                return;
                            }
                            if (!this.seenIds.includes(item.id)) {
                                this.seenIds.push(item.id);
                            }
                        });
                        this.persistSeen();
                        this.recomputeUnread();
                    },
                    async fetchNotifications() {
                        try {
                            const response = await fetch('{{ route('admin.notifications') }}', {
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });

                            if (!response.ok) {
                                return;
                            }

                            const payload = await response.json();
                            this.items = Array.isArray(payload?.items)
                                ? [...payload.items].sort((a, b) => Number(b?.timestamp || 0) - Number(a?.timestamp || 0))
                                : [];
                            this.recomputeUnread();

                            window.dispatchEvent(new CustomEvent('admin-notifications-updated', {
                                detail: {
                                    items: this.items
                                }
                            }));
                        } catch (_error) {
                            // Ignore transient polling errors.
                        }
                    },
                    init() {
                        this.loadSeen();
                        this.fetchNotifications();
                        this.poller = setInterval(() => this.fetchNotifications(), 10000);
                    }
                }"
                x-init="init()"
                @keydown.escape.window="open = false"
            >
                <!-- Notifications -->
                <div class="relative" @click.away="open = false">
                    <button type="button" @click="open = !open; if (open) fetchNotifications()" class="relative p-2 bg-white rounded-xl hover:bg-gray-50 transition-colors">
                        <svg class="w-6 h-6 text-[#3A2E22]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <span x-show="unreadCount > 0" x-cloak class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 bg-red-500 rounded-full text-white text-[10px] font-bold flex items-center justify-center" x-text="unreadCount > 99 ? '99+' : unreadCount"></span>
                    </button>

                    <div x-show="open" x-cloak class="absolute right-0 mt-2 w-96 bg-white rounded-xl border border-gray-200 shadow-lg z-50 overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-100 flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-semibold text-[#3A2E22]">Notifications</h3>
                                <p class="text-xs text-[#9E8C78]">Admin updates</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <button type="button" @click="showUnreadOnly = !showUnreadOnly" class="text-xs font-semibold whitespace-nowrap" :class="showUnreadOnly ? 'text-[#3A2E22]' : 'text-[#4A6741] hover:text-[#3A2E22]'" x-text="showUnreadOnly ? 'Show all' : 'Unread only'"></button>
                                <button type="button" x-show="unreadCount > 0" @click="markAllAsRead()" class="text-xs text-[#4A6741] font-semibold hover:text-[#3A2E22] whitespace-nowrap">Mark all read</button>
                            </div>
                        </div>

                        <div class="max-h-80 overflow-y-auto notif-scrollbar">
                            <template x-if="visibleItems().length === 0">
                                <div class="px-4 py-6 text-center">
                                    <p class="text-sm text-gray-500">No new notifications</p>
                                </div>
                            </template>

                            <template x-if="registrationItems().length > 0">
                                <div>
                                    <p class="px-4 pt-3 pb-1 text-[11px] uppercase tracking-wide font-semibold text-[#9E8C78]">Registrations</p>
                                    <template x-for="item in registrationItems()" :key="item.id">
                                        <a :href="item.url" @click="markItemAsRead(item.id); open = false" class="block px-4 py-3 border-b border-gray-100 hover:bg-[#FAF7F2] transition-colors">
                                            <div class="flex items-start gap-3">
                                                <div class="mt-0.5 inline-flex w-6 h-6 rounded-full bg-amber-100 items-center justify-center">
                                                    <svg class="w-3.5 h-3.5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4a4 4 0 100 8 4 4 0 000-8zm-7 16a7 7 0 1114 0H5z"/>
                                                    </svg>
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex items-center gap-2">
                                                        <p class="text-sm font-medium text-[#3A2E22]" x-text="item.title"></p>
                                                        <span x-show="isUnread(item)" class="inline-flex w-2 h-2 rounded-full bg-red-500"></span>
                                                    </div>
                                                    <p class="text-xs text-[#9E8C78] truncate" x-text="item.subtitle"></p>
                                                    <p class="text-[11px] text-gray-400 mt-1" x-text="item.time"></p>
                                                </div>
                                            </div>
                                        </a>
                                    </template>
                                </div>
                            </template>

                            <template x-if="resellerRegistrationItems().length > 0">
                                <div>
                                    <p class="px-4 pt-3 pb-1 text-[11px] uppercase tracking-wide font-semibold text-[#9E8C78]">Reseller Registrations</p>
                                    <template x-for="item in resellerRegistrationItems()" :key="item.id">
                                        <a :href="item.url" @click="markItemAsRead(item.id); open = false" class="block px-4 py-3 border-b border-gray-100 hover:bg-[#FAF7F2] transition-colors">
                                            <div class="flex items-start gap-3">
                                                <div class="mt-0.5 inline-flex w-6 h-6 rounded-full bg-cyan-100 items-center justify-center">
                                                    <svg class="w-3.5 h-3.5 text-cyan-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4a4 4 0 100 8 4 4 0 000-8zm-7 16a7 7 0 1114 0H5z"/>
                                                    </svg>
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex items-center gap-2">
                                                        <p class="text-sm font-medium text-[#3A2E22]" x-text="item.title"></p>
                                                        <span x-show="isUnread(item)" class="inline-flex w-2 h-2 rounded-full bg-red-500"></span>
                                                    </div>
                                                    <p class="text-xs text-[#9E8C78] truncate" x-text="item.subtitle"></p>
                                                    <p class="text-[11px] text-gray-400 mt-1" x-text="item.time"></p>
                                                </div>
                                            </div>
                                        </a>
                                    </template>
                                </div>
                            </template>

                            <template x-if="chatItems().length > 0">
                                <div>
                                    <p class="px-4 pt-3 pb-1 text-[11px] uppercase tracking-wide font-semibold text-[#9E8C78]">Messages</p>
                                    <template x-for="item in chatItems()" :key="item.id">
                                        <a :href="item.url" @click="markItemAsRead(item.id); open = false" class="block px-4 py-3 border-b border-gray-100 hover:bg-[#FAF7F2] transition-colors">
                                            <div class="flex items-start gap-3">
                                                <div class="mt-0.5 inline-flex w-6 h-6 rounded-full bg-purple-100 items-center justify-center">
                                                    <svg class="w-3.5 h-3.5 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                                    </svg>
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex items-center gap-2">
                                                        <p class="text-sm font-medium text-[#3A2E22]" x-text="item.title"></p>
                                                        <span x-show="isUnread(item)" class="inline-flex w-2 h-2 rounded-full bg-red-500"></span>
                                                    </div>
                                                    <p class="text-xs text-[#9E8C78] truncate" x-text="item.subtitle"></p>
                                                    <p class="text-[11px] text-gray-400 mt-1" x-text="item.time"></p>
                                                </div>
                                            </div>
                                        </a>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-l-green-500 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[#9E8C78] text-sm font-medium">Total Establishments</p>
                        <p class="text-3xl font-bold text-[#3A2E22] mt-1">{{ $totalEstablishments ?? 5 }}</p>
                        <p class="text-green-600 text-sm font-medium mt-1">+12% from last month</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border-l-4 border-l-amber-500 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[#9E8C78] text-sm font-medium">Total Registrations</p>
                        <p class="text-3xl font-bold text-[#3A2E22] mt-1">{{ $pendingRegistrations ?? 2 }}</p>
                        <p class="text-amber-600 text-sm font-medium mt-1">2 new this week</p>
                    </div>
                    <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-amber-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border-l-4 border-l-blue-500 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[#9E8C78] text-sm font-medium">Active Promos</p>
                        <p class="text-3xl font-bold text-[#3A2E22] mt-1">{{ $pendingReviews ?? 0 }}</p>
                        <p class="text-blue-600 text-sm font-medium mt-1">Requires attention</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border-l-4 border-l-rose-500 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[#9E8C78] text-sm font-medium">Active Listings</p>
                        <p class="text-3xl font-bold text-[#3A2E22] mt-1">{{ $activeListings ?? 0 }}</p>
                        <p class="text-rose-600 text-sm font-medium mt-1">+2 this week</p>
                    </div>
                    <div class="w-12 h-12 bg-rose-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Quick Actions -->
            <div class="bg-white rounded-xl shadow-sm p-8">
                <h2 class="text-2xl font-display font-bold text-[#3A2E22] mb-2">
                    Quick <span class="italic text-[#4A6741]">Actions</span>
                </h2>
                <p class="text-[#9E8C78] text-sm mb-6">Common tasks and shortcuts</p>

                <div class="grid grid-cols-2 gap-4">
                    <button class="group bg-[#FAF7F2] hover:bg-[#4A6741] rounded-xl p-4 transition-all duration-200 hover:shadow-lg hover:-translate-y-1 border border-gray-100">
                        <div class="w-12 h-12 bg-[#4A6741] rounded-lg flex items-center justify-center mb-3 group-hover:bg-white transition-colors">
                            <svg class="w-6 h-6 text-white group-hover:text-[#4A6741] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                            </svg>
                        </div>
                        <h3 class="font-semibold text-[#3A2E22] group-hover:text-white transition-colors">View Map</h3>
                        <p class="text-xs text-[#9E8C78] group-hover:text-white/80 transition-colors mt-1">Interactive GIS mapping</p>
                    </button>

                    <button class="group bg-[#FAF7F2] hover:bg-[#4A6741] rounded-xl p-4 transition-all duration-200 hover:shadow-lg hover:-translate-y-1 border border-gray-100">
                        <div class="w-12 h-12 bg-[#4A6741] rounded-lg flex items-center justify-center mb-3 group-hover:bg-white transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-white group-hover:text-[#4A6741] transition-colors">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-[#3A2E22] group-hover:text-white transition-colors">Review Registrations</h3>
                        <p class="text-xs text-[#9E8C78] group-hover:text-white/80 transition-colors mt-1">Registered users</p>
                    </button>

                    <button class="group bg-[#FAF7F2] hover:bg-[#4A6741] rounded-xl p-4 transition-all duration-200 hover:shadow-lg hover:-translate-y-1 border border-gray-100">
                        <div class="w-12 h-12 bg-[#4A6741] rounded-lg flex items-center justify-center mb-3 group-hover:bg-white transition-colors">
                            <svg class="w-6 h-6 text-white group-hover:text-[#4A6741] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                        </div>
                        <h3 class="font-semibold text-[#3A2E22] group-hover:text-white transition-colors">Moderate Ratings</h3>
                        <p class="text-xs text-[#9E8C78] group-hover:text-white/80 transition-colors mt-1">Review star ratings</p>
                    </button>

                    <button class="group bg-[#FAF7F2] hover:bg-[#4A6741] rounded-xl p-4 transition-all duration-200 hover:shadow-lg hover:-translate-y-1 border border-gray-100">
                        <div class="w-12 h-12 bg-[#4A6741] rounded-lg flex items-center justify-center mb-3 group-hover:bg-white transition-colors">
                            <svg class="w-6 h-6 text-white group-hover:text-[#4A6741] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <h3 class="font-semibold text-[#3A2E22] group-hover:text-white transition-colors">View Recommendations</h3>
                        <p class="text-xs text-[#9E8C78] group-hover:text-white/80 transition-colors mt-1">Prescriptive analytics</p>
                    </button>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white rounded-xl shadow-sm p-8"
                 x-data="{
                    activities: [],
                    toActivity(item) {
                        return {
                            id: item.id,
                            type: item.type,
                            title: item.title,
                            subtitle: item.subtitle || null,
                            time: item.time || 'Just now'
                        };
                    },
                    init() {
                        window.addEventListener('admin-notifications-updated', (event) => {
                            const liveItems = Array.isArray(event?.detail?.items) ? event.detail.items : [];
                            this.activities = liveItems.slice(0, 5).map((item) => this.toActivity(item));
                        });
                    }
                 }"
            >
                <h2 class="text-2xl font-display font-bold text-[#3A2E22] mb-2">
                    Recent <span class="italic text-[#4A6741]">Activity</span>
                </h2>
                <p class="text-[#9E8C78] text-sm mb-6">Latest updates and notifications</p>

                <div class="space-y-4">
                    <template x-if="activities.length === 0">
                        <div class="py-6 text-center">
                            <p class="text-[#9E8C78] text-sm">No recent activity yet.</p>
                        </div>
                    </template>

                    <template x-for="activity in activities" :key="activity.id">
                        <div class="flex items-start space-x-3">
                            <div
                                class="w-2 h-2 rounded-full mt-2 flex-shrink-0"
                                :class="{
                                    'bg-amber-500': activity.type === 'registration',
                                    'bg-cyan-500': activity.type === 'reseller_registration',
                                    'bg-blue-500': activity.type === 'reseller',
                                    'bg-emerald-500': activity.type === 'order',
                                    'bg-purple-500': activity.type === 'chat',
                                    'bg-gray-500': !['registration', 'reseller_registration', 'reseller', 'order', 'chat'].includes(activity.type)
                                }"
                            ></div>
                            <div class="flex-1">
                                <p class="text-[#3A2E22] text-sm font-medium" x-text="activity.title"></p>
                                <p class="text-[#9E8C78] text-xs" x-show="activity.subtitle" x-text="activity.subtitle"></p>
                                <p class="text-[#9E8C78] text-xs" x-text="activity.time"></p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Frequently Visited Establishments -->
        <div class="bg-white rounded-xl shadow-sm p-8">
            <h2 class="text-2xl font-display font-bold text-[#3A2E22] mb-2">
                Frequently Visited <span class="italic text-[#4A6741]">Establishments</span>
            </h2>
            <p class="text-[#9E8C78] text-sm mb-6">List of establishments frequently visited by consumers</p>

            <div class="py-12 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 text-gray-300 mx-auto mb-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z" />
                </svg>
                <h3 class="text-gray-400 font-medium mb-2">No data available yet</h3>
                <p class="text-gray-400 text-sm text-center">Frequently visited establishments will appear here based on consumer activity on the map.</p>
            </div>
        </div>
    </main>

    <div class="fixed inset-0 z-50 flex items-center justify-center px-4" x-show="logoutModalOpen" @click="logoutModalOpen = false" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" style="display: none;">
        <div class="fixed inset-0 bg-black bg-opacity-40 backdrop-blur-sm" @click.stop="logoutModalOpen = false"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full" @click.stop>
            <div class="p-6">
                <h2 class="text-2xl font-display font-bold text-[#3A2E22] mb-4">Log out?</h2>
                <p class="text-[#3A2E22] mb-6">Are you sure you want to log out of your account?</p>
                <div class="flex gap-3">
                    <button @click="logoutModalOpen = false" class="flex-1 inline-flex items-center justify-center h-10 px-4 rounded-lg border border-gray-300 text-gray-700 text-base font-semibold hover:bg-gray-50 transition-colors">Cancel</button>
                    <form method="POST" action="{{ route('logout') }}" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full inline-flex items-center justify-center h-10 px-4 rounded-lg bg-red-600 text-white text-base font-semibold hover:bg-red-700 transition-colors">Log out</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .notif-scrollbar {
        scrollbar-width: thin;
        scrollbar-color: rgba(107, 114, 128, 0.55) transparent;
    }

    .notif-scrollbar::-webkit-scrollbar {
        width: 6px;
    }

    .notif-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }

    .notif-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(107, 114, 128, 0.55);
        border-radius: 9999px;
    }

    .notif-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(107, 114, 128, 0.75);
    }
</style>
@endsection
