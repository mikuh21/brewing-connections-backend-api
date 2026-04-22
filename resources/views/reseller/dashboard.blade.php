@extends('reseller.layouts.app')

@section('title', 'Reseller Dashboard - BrewHub')

@section('content')
@php
    $resellerName = $reseller->business_name ?? $reseller->name ?? auth()->user()->name ?? 'Reseller';
    $productsTotal = (int) data_get($productsListed ?? [], 'total', 0);
    $ordersThisWeek = (int) ($recentOrdersThisWeek ?? data_get($performanceOverview ?? [], 'this_week', 0));
    $ordersLastWeek = (int) data_get($performanceOverview ?? [], 'last_week', 0);

    $profileCompletenessChecks = [
        'Contact number' => $reseller->contact_number ?? null,
        'Address' => $reseller->address ?? null,
        'Barangay' => $reseller->barangay ?? null,
    ];

    $missingProfileFields = collect($profileCompletenessChecks)
        ->filter(function ($value) {
            return blank($value);
        })
        ->keys()
        ->values();

    $hasIncompleteProfile = $missingProfileFields->isNotEmpty();

    $activities = collect($recentActivity ?? collect())
        ->filter(function ($activity) {
            $type = (string) ($activity['type'] ?? '');

            return str_contains($type, 'order');
        })
        ->map(function ($activity) {
            $occurredAt = !empty($activity['occurred_at'])
                ? \Illuminate\Support\Carbon::parse($activity['occurred_at'])
                : now();

            return [
                'type' => $activity['type'] ?? 'activity',
                'title' => $activity['title'] ?? 'Activity update',
                'subtitle' => $activity['meta'] ?? null,
                'time' => $occurredAt->diffForHumans(),
            ];
        })
            ->take(5)
        ->values();
@endphp

@php
    $isResellerDeactivated = (($reseller->status ?? '') === 'deactivated');
@endphp

@if($isResellerDeactivated)
<div class="fixed inset-0 z-[4100] flex items-center justify-center px-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-[2px]"></div>

    <div class="relative max-w-lg w-full bg-[#F5F0E8] border border-[#D9CDBA] rounded-2xl shadow-2xl p-6">
        <h2 class="text-2xl font-display font-bold text-[#3A2E22] mb-3">Account Locked</h2>
        <p class="text-sm text-[#5E5447] leading-relaxed mb-6">
            Your account is deactivated due to inactivity or violation of terms and conditions.
        </p>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full inline-flex items-center justify-center h-11 px-4 rounded-lg bg-[#2E5A3D] text-white text-sm font-semibold hover:bg-[#1E3A2A] transition-colors">
                Got it
            </button>
        </form>
    </div>
</div>
@elseif(!($isResellerVerified ?? false))
<div class="fixed inset-0 z-[4000] flex items-center justify-center px-4">
    <div class="absolute inset-0 bg-black/45 backdrop-blur-[2px]"></div>

    <div class="relative max-w-lg w-full bg-[#F5F0E8] border border-[#D9CDBA] rounded-2xl shadow-2xl p-6">
        <h2 class="text-2xl font-display font-bold text-[#3A2E22] mb-3">Verification in Progress</h2>
        <p class="text-sm text-[#5E5447] leading-relaxed mb-6">
            BrewHub admin is verifying your reseller account. It may take 1-2 business days. You may check your registered email for verification updates. Please come back and try again.
        </p>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full inline-flex items-center justify-center h-11 px-4 rounded-lg bg-[#2E5A3D] text-white text-sm font-semibold hover:bg-[#1E3A2A] transition-colors">
                Log out
            </button>
        </form>
    </div>
</div>
@endif

<div class="reseller-dashboard-page">
<div class="reseller-dashboard-header flex items-center justify-between mb-8 sticky top-0 z-10 bg-[#F5F0E8]">
    <div>
        <h1 class="text-3xl font-display font-bold text-[#3A2E22] mb-1">Welcome back, <span class="italic text-[#4A6741]">{{ $reseller->business_name ?? $resellerName }}</span></h1>
        <p class="text-[#9E8C78] text-sm font-medium">Manage your reseller profile and orders</p>
    </div>

    <div
        class="reseller-dashboard-notif flex items-center space-x-4"
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
            orderItems() {
                return this.visibleItems().filter((item) => item.type === 'order');
            },
            chatItems() {
                return this.visibleItems().filter((item) => item.type === 'chat');
            },
            storageKey() {
                return 'resellerSeenNotifications:{{ auth()->id() }}';
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
            playTone() {
                try {
                    const AudioCtx = window.AudioContext || window.webkitAudioContext;
                    if (!AudioCtx) {
                        return;
                    }
                    const ctx = new AudioCtx();
                    const oscillator = ctx.createOscillator();
                    const gain = ctx.createGain();

                    oscillator.type = 'sine';
                    oscillator.frequency.setValueAtTime(880, ctx.currentTime);
                    gain.gain.setValueAtTime(0.0001, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.06, ctx.currentTime + 0.01);
                    gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.18);

                    oscillator.connect(gain);
                    gain.connect(ctx.destination);
                    oscillator.start(ctx.currentTime);
                    oscillator.stop(ctx.currentTime + 0.2);
                } catch (_error) {
                    // Ignore audio failures (autoplay restrictions, etc.)
                }
            },
            async fetchNotifications() {
                try {
                    const response = await fetch('{{ route('reseller.notifications') }}', {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!response.ok) {
                        return;
                    }

                    const previousUnread = this.unreadCount;
                    const payload = await response.json();
                    this.items = Array.isArray(payload?.items)
                        ? [...payload.items].sort((a, b) => Number(b?.timestamp || 0) - Number(a?.timestamp || 0))
                        : [];
                    this.recomputeUnread();

                    if (this.unreadCount > previousUnread && previousUnread !== 0) {
                        this.playTone();
                    }
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
        <div class="relative" @click.away="open = false">
            <button type="button" @click="open = !open; if (open) fetchNotifications()" class="relative p-2 bg-white rounded-xl hover:bg-gray-50 transition-colors">
                <svg class="w-6 h-6 text-[#3A2E22]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <span x-show="unreadCount > 0" x-cloak class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 bg-red-500 rounded-full text-white text-[10px] font-bold flex items-center justify-center" x-text="unreadCount > 99 ? '99+' : unreadCount"></span>
            </button>

            <div x-show="open" x-cloak class="reseller-dashboard-notif-menu absolute right-0 mt-2 w-80 bg-white rounded-xl border border-gray-200 shadow-lg z-50 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-semibold text-[#3A2E22]">Notifications</h3>
                        <p class="text-xs text-[#9E8C78]">Updates for orders and chats</p>
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

                    <template x-if="orderItems().length > 0">
                        <div>
                            <p class="px-4 pt-3 pb-1 text-[11px] uppercase tracking-wide font-semibold text-[#9E8C78]">Orders</p>
                            <template x-for="item in orderItems()" :key="item.id">
                                <a :href="item.url" @click="markItemAsRead(item.id); open = false" class="block px-4 py-3 border-b border-gray-100 hover:bg-[#FAF7F2] transition-colors">
                                    <div class="flex items-start gap-3">
                                        <div class="mt-0.5 inline-flex w-6 h-6 rounded-full bg-amber-100 items-center justify-center">
                                            <svg class="w-3.5 h-3.5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14l-1 12H6L5 8zM9 8V6a3 3 0 116 0v2"/>
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
                                        <div class="mt-0.5 inline-flex w-6 h-6 rounded-full bg-blue-100 items-center justify-center">
                                            <svg class="w-3.5 h-3.5 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
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

@if($hasIncompleteProfile)
<div class="mb-8 bg-white rounded-2xl shadow-sm border border-[#E5DDD0] border-l-4 border-l-red-500 p-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-xl font-display font-bold text-[#3A2E22]">Incomplete Profile Information</h2>
            <p class="text-[#9E8C78] text-sm mt-1">
                Please complete your profile to improve visibility and account readiness.
                Missing: {{ $missingProfileFields->join(', ') }}.
            </p>
        </div>

        <a
            href="{{ route('reseller.profile') }}"
            class="inline-flex items-center justify-center h-10 px-5 rounded-lg bg-[#4A6741] text-white text-sm font-semibold hover:bg-[#3A2E22] transition-colors whitespace-nowrap"
        >
            My Profile
        </a>
    </div>
</div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-2xl shadow-sm border border-[#E5DDD0] border-l-4 border-l-green-500 p-6 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[#9E8C78] text-sm font-medium">Total Orders</p>
                <p class="text-3xl font-bold text-[#3A2E22] mt-1">{{ $totalOrders ?? 0 }}</p>
                <p class="text-green-600 text-sm font-medium mt-1">All-time reseller orders</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14l-1 12H6L5 8z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 8V6a3 3 0 116 0v2"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-[#E5DDD0] border-l-4 border-l-amber-500 p-6 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[#9E8C78] text-sm font-medium">Products Listed</p>
                <p class="text-3xl font-bold text-[#3A2E22] mt-1">{{ $productsTotal }}</p>
                <p class="text-amber-600 text-sm font-medium mt-1">Coffee Beans &amp; Ground Coffee</p>
            </div>
            <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-[#E5DDD0] border-l-4 border-l-blue-500 p-6 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[#9E8C78] text-sm font-medium">Recent Orders</p>
                <p class="text-3xl font-bold text-[#3A2E22] mt-1">{{ $ordersThisWeek }}</p>
                <p class="text-blue-600 text-sm font-medium mt-1">Orders placed this week</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12a9 9 0 1018 0 9 9 0 00-18 0z"/>
                </svg>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <div class="bg-white rounded-2xl shadow-sm border border-[#E5DDD0] p-6">
        <h2 class="text-2xl font-display font-bold text-[#3A2E22] mb-2">
            Quick <span class="italic text-[#4A6741]">Actions</span>
        </h2>
        <p class="text-[#9E8C78] text-sm mb-6">Common tasks and shortcuts</p>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <a href="{{ route('reseller.profile') }}" class="group bg-[#FAF7F2] hover:bg-[#4A6741] rounded-xl p-4 transition-all duration-200 hover:shadow-lg hover:-translate-y-1 border border-gray-100">
                <div class="w-12 h-12 bg-[#4A6741] rounded-lg flex items-center justify-center mb-3 group-hover:bg-white transition-colors">
                    <svg class="w-6 h-6 text-white group-hover:text-[#4A6741] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A9.003 9.003 0 0112 15a9.003 9.003 0 016.879 2.804M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21a9 9 0 100-18 9 9 0 000 18z"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-[#3A2E22] group-hover:text-white transition-colors">Manage My Profile</h3>
                <p class="text-xs text-[#9E8C78] group-hover:text-white/80 transition-colors mt-1">Update reseller profile</p>
            </a>

            <a href="{{ route('reseller.marketplace') }}#my-listings" class="group bg-[#FAF7F2] hover:bg-[#4A6741] rounded-xl p-4 transition-all duration-200 hover:shadow-lg hover:-translate-y-1 border border-gray-100">
                <div class="w-12 h-12 bg-[#4A6741] rounded-lg flex items-center justify-center mb-3 group-hover:bg-white transition-colors">
                    <svg class="w-6 h-6 text-white group-hover:text-[#4A6741] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-[#3A2E22] group-hover:text-white transition-colors">Manage Products</h3>
                <p class="text-xs text-[#9E8C78] group-hover:text-white/80 transition-colors mt-1">Edit your product listings</p>
            </a>

            <a href="{{ route('reseller.marketplace') }}#orders" class="group bg-[#FAF7F2] hover:bg-[#4A6741] rounded-xl p-4 transition-all duration-200 hover:shadow-lg hover:-translate-y-1 border border-gray-100">
                <div class="w-12 h-12 bg-[#4A6741] rounded-lg flex items-center justify-center mb-3 group-hover:bg-white transition-colors">
                    <svg class="w-6 h-6 text-white group-hover:text-[#4A6741] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14l-1 12H6L5 8z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 8V6a3 3 0 116 0v2"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-[#3A2E22] group-hover:text-white transition-colors">View Orders</h3>
                <p class="text-xs text-[#9E8C78] group-hover:text-white/80 transition-colors mt-1">Track order activity</p>
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-[#E5DDD0] p-6">
        <h2 class="text-2xl font-display font-bold text-[#3A2E22] mb-2">
            Recent <span class="italic text-[#4A6741]">Activity</span>
        </h2>
        <p class="text-[#9E8C78] text-sm mb-6">Latest updates and notifications</p>

        <div class="space-y-4">
            @forelse($activities as $activity)
                <div class="flex items-start space-x-3">
                    <div class="w-2 h-2 rounded-full mt-2 flex-shrink-0 {{ $activity['type'] === 'new_order' ? 'bg-blue-500' : 'bg-amber-500' }}"></div>
                    <div class="flex-1">
                        <p class="text-[#3A2E22] text-sm font-medium">{{ $activity['title'] }}</p>
                        @if(!empty($activity['subtitle']))
                            <p class="text-[#9E8C78] text-xs">{{ $activity['subtitle'] }}</p>
                        @endif
                        <p class="text-[#9E8C78] text-xs mt-1">{{ $activity['time'] }}</p>
                    </div>
                </div>
            @empty
                <div class="py-6 text-center">
                    <p class="text-[#9E8C78] text-sm">No recent activity yet.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-[#E5DDD0] p-6">
    <h2 class="text-2xl font-display font-bold text-[#3A2E22] mb-2">
        Performance <span class="italic text-[#4A6741]">Overview</span>
    </h2>
    <p class="text-[#9E8C78] text-sm mb-6">Weekly order trend and summary</p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-[#FAF7F2] rounded-xl border border-gray-100 p-6">
            <div class="flex items-start justify-between mb-2">
                <div>
                    <p class="text-[#9E8C78] text-sm font-medium">Orders This Week</p>
                    <p class="text-3xl font-bold text-[#3A2E22] mt-1">{{ $ordersThisWeek }}</p>
                    <p class="text-green-600 text-sm font-medium mt-1">Compared to {{ $ordersLastWeek }} last week</p>
                </div>
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17l3-3 2 2 5-5"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10v10"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-[#FAF7F2] rounded-xl border border-gray-100 p-6">
            <div class="flex items-start justify-between mb-2">
                <div>
                    <p class="text-[#9E8C78] text-sm font-medium">Weekly Change</p>
                    <p class="text-3xl font-bold text-[#3A2E22] mt-1">{{ $ordersThisWeek - $ordersLastWeek }}</p>
                    <p class="text-blue-600 text-sm font-medium mt-1">Net order delta vs last week</p>
                </div>
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<style>
    @media (max-width: 767px) {
        .reseller-dashboard-page .reseller-dashboard-header {
            position: static !important;
            flex-direction: column;
            align-items: flex-start;
            gap: 0.75rem;
            margin-bottom: 1rem !important;
        }

        .reseller-dashboard-page .reseller-dashboard-header h1 {
            font-size: 1.7rem !important;
            line-height: 2rem;
        }

        .reseller-dashboard-page .reseller-dashboard-notif {
            width: 100%;
            justify-content: flex-end;
        }

        .reseller-dashboard-page .reseller-dashboard-notif-menu {
            width: min(100vw - 2rem, 22rem) !important;
        }
    }
</style>
@endsection
