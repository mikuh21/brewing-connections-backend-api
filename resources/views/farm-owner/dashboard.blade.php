@extends('farm-owner.layouts.app')

@php
    $title = 'Dashboard';
@endphp

@section('title', 'Farm Owner Dashboard - BrewHub')

@section('content')
@php
    $farmName = auth()->user()->farm_name ?? auth()->user()->name ?? 'Farm Owner';
@endphp

<div class="farm-dashboard-header flex items-center justify-between mb-8 sticky top-0 z-10 bg-[#F5F0E8]">
    <div>
        <h1 class="text-3xl font-display font-bold text-[#3A2E22] mb-1">
            Welcome back, <span class="italic text-[#4A6741]">{{ $farmName }}</span>
        </h1>
        <p class="text-[#9E8C78] text-sm font-medium">Manage your coffee farm</p>
    </div>

    <div
        class="farm-dashboard-notif flex items-center space-x-4"
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
                return 'farmOwnerSeenNotifications:{{ auth()->id() }}';
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
                    const response = await fetch('{{ route('farm-owner.notifications') }}', {
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

                    window.dispatchEvent(new CustomEvent('farm-owner-notifications-updated', {
                        detail: {
                            items: this.items
                        }
                    }));

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

            <div x-show="open" x-cloak class="farm-dashboard-notif-menu absolute right-0 mt-2 w-80 bg-white rounded-xl border border-gray-200 shadow-lg z-50 overflow-hidden">
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

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border-l-4 border-l-green-500 p-6 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[#9E8C78] text-sm font-medium">Total Visits</p>
                <p class="text-3xl font-bold text-[#3A2E22] mt-1">{{ $totalVisits ?? 128 }}</p>
                <p class="text-green-600 text-sm font-medium mt-1">AI Coffee Trail generation</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7S3.732 16.057 2.458 12z"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border-l-4 border-l-amber-500 p-6 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[#9E8C78] text-sm font-medium">Products Listed</p>
                <p class="text-3xl font-bold text-[#3A2E22] mt-1">{{ $productsListed ?? 24 }}</p>
                <p class="text-amber-600 text-sm font-medium mt-1">Coffee Beans & Ground Coffee</p>
            </div>
            <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border-l-4 border-l-blue-500 p-6 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[#9E8C78] text-sm font-medium">Orders This Week</p>
                <p class="text-3xl font-bold text-[#3A2E22] mt-1">{{ $ordersThisWeek ?? 11 }}</p>
                <p class="text-blue-600 text-sm font-medium mt-1">+3 from last week</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14l-1 12H6L5 8z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 8V6a3 3 0 116 0v2"/>
                </svg>
            </div>
        </div>
    </div>
</div>

@php
    $initialRecentActivities = collect($recentActivity ?? collect())
        ->map(function ($activity) {
            $occurredAt = !empty($activity['occurred_at'])
                ? \Illuminate\Support\Carbon::parse($activity['occurred_at'])
                : now();

            return [
                'id' => ($activity['type'] ?? 'activity') . '-' . md5(($activity['title'] ?? '') . ($activity['meta'] ?? '') . (string) ($activity['occurred_at'] ?? now())),
                'type' => $activity['type'] ?? 'activity',
                'title' => $activity['title'] ?? 'Activity update',
                'subtitle' => $activity['meta'] ?? null,
                'time' => $occurredAt->diffForHumans(),
                'timestamp' => $occurredAt->timestamp,
            ];
        })
        ->values();
@endphp

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8"
     x-data="{
        baseActivities: @js($initialRecentActivities),
        liveActivities: [],
        toActivity(item) {
            return {
                id: item.id,
                type: item.type,
                title: item.title,
                subtitle: item.subtitle || null,
                time: item.time || 'Just now',
                timestamp: Number(item.timestamp || 0)
            };
        },
        combinedActivities() {
            const merged = [...this.liveActivities, ...this.baseActivities];
            const byId = new Map();

            merged.forEach((item) => {
                if (!byId.has(item.id)) {
                    byId.set(item.id, item);
                }
            });

            return [...byId.values()]
                .sort((a, b) => Number(b.timestamp || 0) - Number(a.timestamp || 0))
                .slice(0, 5);
        },
        init() {
            window.addEventListener('farm-owner-notifications-updated', (event) => {
                const liveItems = Array.isArray(event?.detail?.items) ? event.detail.items : [];
                this.liveActivities = liveItems.map((item) => this.toActivity(item));
            });
        }
     }"
>
    @php
        $dashboardQuickActionFarmId = (int) request('farm_id', 0);
        $dashboardQuickActionRouteParams = $dashboardQuickActionFarmId > 0
            ? ['farm_id' => $dashboardQuickActionFarmId]
            : [];
    @endphp

    <div class="bg-white rounded-xl shadow-sm p-8">
        <h2 class="text-2xl font-display font-bold text-[#3A2E22] mb-2">
            Quick <span class="italic text-[#4A6741]">Actions</span>
        </h2>
        <p class="text-[#9E8C78] text-sm mb-6">Common tasks and shortcuts</p>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <a href="{{ route('farm-owner.my-farm', $dashboardQuickActionRouteParams) }}" class="group bg-[#FAF7F2] hover:bg-[#4A6741] rounded-xl p-4 transition-all duration-200 hover:shadow-lg hover:-translate-y-1 border border-gray-100">
                <div class="w-12 h-12 bg-[#4A6741] rounded-lg flex items-center justify-center mb-3 group-hover:bg-white transition-colors">
                    <svg class="w-6 h-6 text-white group-hover:text-[#4A6741] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-[#3A2E22] group-hover:text-white transition-colors">Manage Farm</h3>
                <p class="text-xs text-[#9E8C78] group-hover:text-white/80 transition-colors mt-1">Update farm profile</p>
            </a>

            <a href="{{ route('farm-owner.marketplace', $dashboardQuickActionRouteParams) }}#orders" class="group bg-[#FAF7F2] hover:bg-[#4A6741] rounded-xl p-4 transition-all duration-200 hover:shadow-lg hover:-translate-y-1 border border-gray-100">
                <div class="w-12 h-12 bg-[#4A6741] rounded-lg flex items-center justify-center mb-3 group-hover:bg-white transition-colors">
                    <svg class="w-6 h-6 text-white group-hover:text-[#4A6741] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14l-1 12H6L5 8z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 8V6a3 3 0 116 0v2"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-[#3A2E22] group-hover:text-white transition-colors">View Orders</h3>
                <p class="text-xs text-[#9E8C78] group-hover:text-white/80 transition-colors mt-1">Marketplace orders</p>
            </a>

            <a href="{{ route('farm-owner.messages', $dashboardQuickActionRouteParams) }}" class="group bg-[#FAF7F2] hover:bg-[#4A6741] rounded-xl p-4 transition-all duration-200 hover:shadow-lg hover:-translate-y-1 border border-gray-100">
                <div class="w-12 h-12 bg-[#4A6741] rounded-lg flex items-center justify-center mb-3 group-hover:bg-white transition-colors">
                    <svg class="w-6 h-6 text-white group-hover:text-[#4A6741] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-[#3A2E22] group-hover:text-white transition-colors">View Chats</h3>
                <p class="text-xs text-[#9E8C78] group-hover:text-white/80 transition-colors mt-1">Reply to messages</p>
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-8">
        <h2 class="text-2xl font-display font-bold text-[#3A2E22] mb-2">
            Recent <span class="italic text-[#4A6741]">Activity</span>
        </h2>
        <p class="text-[#9E8C78] text-sm mb-6">Latest updates and notifications</p>

        <div class="space-y-4">
            <template x-if="combinedActivities().length === 0">
                <div class="py-6 text-center">
                    <p class="text-[#9E8C78] text-sm">No recent activity yet.</p>
                </div>
            </template>

            <template x-for="activity in combinedActivities()" :key="activity.id">
                <div class="flex items-start space-x-3">
                    <div class="w-2 h-2 rounded-full mt-2 flex-shrink-0" :class="activity.type === 'order' ? 'bg-blue-500' : 'bg-amber-500'"></div>
                    <div class="flex-1">
                        <p class="text-[#3A2E22] text-sm font-medium" x-text="activity.title"></p>
                        <p class="text-[#9E8C78] text-xs" x-show="activity.subtitle" x-text="activity.subtitle"></p>
                        <p class="text-[#9E8C78] text-xs mt-1" x-text="activity.time"></p>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm p-8">
    <h2 class="text-2xl font-display font-bold text-[#3A2E22] mb-2">
        Performance <span class="italic text-[#4A6741]">Overview</span>
    </h2>
    <p class="text-[#9E8C78] text-sm mb-6">Engagement signals from map and trail interactions</p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-[#FAF7F2] rounded-xl border border-gray-100 p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <p class="text-[#9E8C78] text-sm font-medium">Farm Clicks</p>
                    <p class="text-3xl font-bold text-[#3A2E22] mt-1">{{ $farmClicks ?? 86 }}</p>
                    <p class="text-green-600 text-sm font-medium mt-1">Total clicks from consumer map</p>
                </div>
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17l3-3 2 2 5-5"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10v10"/>
                    </svg>
                </div>
            </div>
            <div class="h-16 rounded-lg bg-white border border-dashed border-[#D9C9B2] flex items-center justify-center">
                <span class="text-xs text-[#9E8C78]">Chart placeholder</span>
            </div>
        </div>

        <div class="bg-[#FAF7F2] rounded-xl border border-gray-100 p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <p class="text-[#9E8C78] text-sm font-medium">Coffee Trail Visits</p>
                    <p class="text-3xl font-bold text-[#3A2E22] mt-1">{{ $totalVisits ?? 0 }}</p>
                    <p class="text-blue-600 text-sm font-medium mt-1">From coffee trail generation</p>
                </div>
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </div>
            </div>
            <div class="flex items-center gap-2 text-xs font-medium text-blue-600">
                <span class="inline-block w-2 h-2 rounded-full bg-blue-500"></span>
                Positive weekly trend
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

    @media (max-width: 767px) {
        .farm-dashboard-header {
            position: static !important;
            flex-direction: column;
            align-items: flex-start;
            gap: 0.75rem;
            margin-bottom: 1rem !important;
        }

        .farm-dashboard-header h1 {
            font-size: 1.7rem !important;
            line-height: 2rem;
        }

        .farm-dashboard-notif {
            width: 100%;
            justify-content: flex-end;
        }

        .farm-dashboard-notif-menu {
            width: min(100vw - 2rem, 22rem) !important;
        }
    }
</style>
@endsection
