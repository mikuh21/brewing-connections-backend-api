@extends('layouts.app')

@section('title', 'Coupon Promos - BrewHub')

@section('content')
<div class="min-h-screen bg-[#F5F0E8] flex">
    <!-- Sidebar -->
    <aside class="admin-sidebar fixed left-0 top-0 h-screen w-64 bg-[#3A2E22] text-[#F5F0E8] flex flex-col justify-between py-6 px-4 rounded-r-xl shadow-lg overflow-hidden z-40 -translate-x-full md:translate-x-0 transition-transform duration-300 ease-out">
        <div>
            <!-- Logo -->
            <div class="flex items-center mb-8">
                <img src="{{ asset('images/brewhublogo2.png') }}" alt="BrewHub logo" class="w-7 h-7 mr-2 object-contain shrink-0">
                <span class="brand-wordmark text-lg leading-none"><span class="brand-brew">Brew</span><span class="brand-hub">Hub</span></span>
            </div>

            <!-- Navigation -->
            <nav class="space-y-1">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center {{ request()->routeIs('admin.dashboard') ? 'bg-[#4E3D2B]' : '' }} rounded-lg px-4 py-2 text-xs font-medium gap-2 transition-all duration-200 hover:bg-[#4E3D2B] hover:translate-x-1">
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

    <!-- Main Content -->
    <main class="ml-0 md:ml-64 flex-1 p-8 overflow-y-auto" x-data="{ deleteModal: deleteModalState() }" @open-delete="deleteModal.openModal($event.detail.id, $event.detail.title)">
        <!-- Flash Message Alert -->
        @if(session('success'))
            <div id="success-alert" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg flex items-start gap-3 animate-fade-in-up">
                <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <div class="flex-1">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
                <button onclick="document.getElementById('success-alert').remove()" class="text-green-600 hover:text-green-900 flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div id="error-alert" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg flex items-start gap-3 animate-fade-in-up">
                <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
                <div class="flex-1">
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
                <button onclick="document.getElementById('error-alert').remove()" class="text-red-600 hover:text-red-900 flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        @endif

        <!-- Page Header -->
        <div class="flex items-center justify-between mb-8 sticky top-0 z-10 bg-[#F5F0E8]">
            <div>
                <h1 class="text-3xl font-display font-bold text-[#3A2E22] mb-1">
                    Coupon Promos
                </h1>
                <p class="text-[#9E8C78] text-sm font-medium">View and manage café promotional coupons</p>
            </div>
        </div>

        <!-- Overview Cards Section -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Total Coupons Card -->
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-l-green-500 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[#9E8C78] text-sm font-medium">Total Coupons</p>
                        <p class="text-3xl font-bold text-[#3A2E22] mt-1">{{ $totalCoupons }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Active Coupons Card -->
            <div class="bg-white rounded-xl shadow-sm border-l-4 p-6 hover:shadow-md transition-shadow" style="border-left-color: #D4AF37;">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[#9E8C78] text-sm font-medium">Active</p>
                        <p class="text-3xl font-bold text-[#3A2E22] mt-1">{{ $activeCoupons }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center" style="background-color: rgba(212, 175, 55, 0.15);">
                        <svg class="w-6 h-6" style="color: #D4AF37;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Expired Coupons Card -->
            <div class="bg-white rounded-xl shadow-sm border-l-4 p-6 hover:shadow-md transition-shadow" style="border-left-color: #800000;">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[#9E8C78] text-sm font-medium">Expired</p>
                        <p class="text-3xl font-bold text-[#3A2E22] mt-1">{{ $expiredCoupons }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center" style="background-color: rgba(128, 0, 0, 0.15);">
                        <svg class="w-6 h-6" style="color: #800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="filter-content bg-white rounded-xl shadow-sm p-8">
            <h2 class="text-2xl font-display font-bold text-[#3A2E22] mb-2">
                All <span class="italic text-[#4A6741]">Coupon Promos</span>
            </h2>
            <p class="text-[#9E8C78] text-sm mb-6">Complete list of café promotional coupons</p>

            <!-- Filter Tabs -->
            <div class="mb-6 border-b border-gray-200 pb-4">
                <div class="flex gap-2">
                    <button class="filter-tab {{ $currentFilter === 'all' ? 'active' : '' }} px-4 py-2 text-sm font-medium transition-colors" data-filter="all" onclick="window.location.href='{{ route('admin.coupon-promos.index', ['filter' => 'all']) }}'" style="color: {{ $currentFilter === 'all' ? '#3B2F2F' : '#9E8C78' }}; border-bottom: 3px solid {{ $currentFilter === 'all' ? '#3B2F2F' : 'transparent' }};">
                        All
                    </button>
                    <button class="filter-tab {{ $currentFilter === 'active' ? 'active' : '' }} px-4 py-2 text-sm font-medium transition-colors" data-filter="active" onclick="window.location.href='{{ route('admin.coupon-promos.index', ['filter' => 'active']) }}'" style="color: {{ $currentFilter === 'active' ? '#3B2F2F' : '#9E8C78' }}; border-bottom: 3px solid {{ $currentFilter === 'active' ? '#3B2F2F' : 'transparent' }};">
                        Active
                    </button>
                    <button class="filter-tab {{ $currentFilter === 'expired' ? 'active' : '' }} px-4 py-2 text-sm font-medium transition-colors" data-filter="expired" onclick="window.location.href='{{ route('admin.coupon-promos.index', ['filter' => 'expired']) }}'" style="color: {{ $currentFilter === 'expired' ? '#3B2F2F' : '#9E8C78' }}; border-bottom: 3px solid {{ $currentFilter === 'expired' ? '#3B2F2F' : 'transparent' }};">
                        Expired
                    </button>
                </div>
            </div>

                <div class="overflow-x-auto">
                    <table id="coupon-promos-table" class="w-full">
                        <thead>
                            <tr class="bg-[#3A2E22] text-white rounded-t-xl">
                                <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider">#</th>
                                <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider">Promo Title</th>
                                <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider">Description</th>
                                <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider">Café Name</th>
                                <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider">Discount</th>
                                <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider">Valid Period</th>
                                <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider">Usage</th>
                                <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($coupons as $index => $coupon)
                                <tr class="hover:bg-gray-50 coupon-row">
                                    <td class="px-3 py-2 whitespace-nowrap text-xs text-[#3A2E22]">{{ $index + 1 }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap text-xs text-[#3A2E22]">{{ $coupon->title }}</td>
                                    <td class="px-3 py-2 text-xs text-[#9E8C78]">{{ $coupon->description }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap text-xs text-[#3A2E22]">{{ $coupon->establishment->name ?? 'N/A' }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap text-xs text-[#3A2E22]">
                                        @if($coupon->discount_type === 'percentage')
                                            {{ $coupon->discount_value }}%
                                        @else
                                            ₱{{ $coupon->discount_value }}
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap text-xs text-[#3A2E22]">
                                        {{ \Carbon\Carbon::parse($coupon->valid_from)->format('M j, Y') }} – {{ \Carbon\Carbon::parse($coupon->valid_until)->format('M j, Y') }}
                                    </td>
                                    <td class="px-3 py-2">
                                        <div class="text-xs text-[#3A2E22]">{{ $coupon->used_count }} / {{ $coupon->max_usage }}</div>
                                        <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                            <div class="bg-green-500 h-2 rounded-full" style="width: {{ ($coupon->used_count / max($coupon->max_usage, 1)) * 100 }}%"></div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $coupon->display_status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ ucfirst($coupon->display_status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button class="view-qr-btn" data-id="{{ $coupon->id }}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </button>
                                        <button @click="$dispatch('open-delete', { id: {{ $coupon->id }}, title: '{{ $coupon->title }}' })" class="text-red-600 hover:text-red-800 font-medium transition-colors" title="Delete">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr id="no-results-row">
                                    <td colspan="9" class="px-6 py-8 text-center text-gray-400">
                                        No coupon promos found matching your search.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div class="fixed inset-0 z-50 flex items-center justify-center px-4" x-show="deleteModal.isOpen" @keydown.escape="deleteModal.closeModal()" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" @click="deleteModal.closeModal()" style="display: none;">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black bg-opacity-40 backdrop-blur-sm" @click.stop="deleteModal.closeModal()"></div>
            
            <!-- Modal Card -->
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full" @click.stop>
                <div class="p-6">
                    <h2 class="text-2xl font-display font-bold text-[#3A2E22] mb-4">
                        Delete Coupon?
                    </h2>
                    <p class="text-[#3A2E22] mb-6">
                        Are you sure you want to delete <span class="font-semibold" x-text="deleteModal.couponTitle"></span>? This action cannot be undone.
                    </p>
                    <div class="flex gap-3">
                        <button @click="deleteModal.closeModal()" class="flex-1 px-4 py-2 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button @click="deleteModal.confirmDelete()" class="flex-1 px-4 py-2 rounded-lg bg-red-600 text-white font-medium hover:bg-red-700 transition-colors">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hidden Delete Form -->
        <form id="delete-form" method="POST" style="display: none;">
            @csrf
            @method('DELETE')
        </form>

        <!-- QR Code Modal -->
        <div id="qr-modal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center px-2 py-4 z-[9999]" x-show="$store.qrModal.open" @keydown.escape="$store.qrModal.hide()" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" @click="$store.qrModal.hide()" style="display: none;">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black bg-opacity-40 backdrop-blur-sm" @click.stop="$store.qrModal.hide()"></div>

            <!-- Modal Card -->
            <div class="relative mx-auto p-4 border w-full max-w-3xl shadow-xl rounded-2xl bg-white ring-1 ring-gray-200" @click.stop>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-2xl font-display font-bold" id="modal-cafe-header" style="color: #3A2E22;"></h3>
                    <button id="qr-close" class="text-gray-400 hover:text-gray-600" title="Close" @click="$store.qrModal.hide()">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="border border-[#F3E9D7] bg-[#F3E9D7] rounded-xl p-2">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Left side - QR Code -->
                        <div class="p-3 flex flex-col items-center justify-center rounded-xl bg-white">
                            <div id="qrCanvas" class="w-[220px] h-[220px] bg-white border p-2 rounded-lg flex items-center justify-center text-sm text-gray-500">QR preview</div>
                        </div>

                        <!-- Right side - Details -->
                    <div class="p-3">
                        <div class="mb-4">
                            <h4 class="text-xl font-bold text-gray-900 mb-1" id="modal-title"></h4>
                            <p class="text-sm text-gray-700" id="modal-description"></p>
                        </div>

                        <div class="space-y-2 text-gray-700">
                            <p class="text-base"><span class="font-semibold">Discount:</span> <span id="modal-discount" class="text-green-600 font-semibold"></span></p>
                            <p class="text-base"><span class="font-semibold">Valid Period:</span> <span id="modal-valid"></span></p>
                            <p class="text-base"><span class="font-semibold">Available Claims:</span> <span id="modal-usage"></span></p>
                        </div>

                        <div class="mt-6 text-center">
                            <div class="text-lg tracking-tight font-display" style="color: #3A2E22;">
                                <span class="font-normal">Brewing</span> <span class="italic">Connections</span>
                            </div>
                            <p id="modal-cafe-footer" class="text-sm font-medium" style="color: #3A2E22;"> <span id="modal-cafe-footer-text"></span></p>
                        </div>

                        <div class="mt-4 text-center">
                            <button id="download-qr" class="px-6 py-2 bg-[#2E5A3D] text-white font-semibold rounded-lg hover:bg-[#285133] transition-colors text-sm">
                                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Download QR
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toast Notification -->
        <div id="toast" class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-md hidden z-50">
            <span id="toast-message"></span>
        </div>
    </main>
</div>

<style>
    /* Custom scrollbar for main content */
    main::-webkit-scrollbar {
        width: 6px;
    }

    main::-webkit-scrollbar-track {
        background: transparent;
    }

    main::-webkit-scrollbar-thumb {
        background: rgba(158, 140, 120, 0.4);
        border-radius: 3px;
    }

    main::-webkit-scrollbar-thumb:hover {
        background: rgba(158, 140, 120, 0.6);
    }

    /* Custom scrollbar for table horizontal scroll */
    .overflow-x-auto::-webkit-scrollbar {
        height: 6px;
    }

    .overflow-x-auto::-webkit-scrollbar-track {
        background: transparent;
    }

    .overflow-x-auto::-webkit-scrollbar-thumb {
        background: rgba(158, 140, 120, 0.4);
        border-radius: 3px;
    }

    .overflow-x-auto::-webkit-scrollbar-thumb:hover {
        background: rgba(158, 140, 120, 0.6);
    }

    /* Firefox scrollbar support */
    main {
        scrollbar-width: thin;
        scrollbar-color: rgba(158, 140, 120, 0.4) transparent;
    }

    .overflow-x-auto {
        scrollbar-width: thin;
        scrollbar-color: rgba(158, 140, 120, 0.4) transparent;
    }
</style>

<script>
    // Load QRCode library with fallback sources to avoid blank QR on blocked CDN.
    let qrCodeReadyPromise = null;

    function loadScript(source) {
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = source;
            script.async = true;
            script.onload = () => resolve(true);
            script.onerror = () => reject(new Error(`Failed to load script: ${source}`));
            document.head.appendChild(script);
        });
    }

    async function ensureQRCodeLibrary() {
        if (typeof window.QRCode === 'function') {
            return true;
        }

        if (qrCodeReadyPromise) {
            return qrCodeReadyPromise;
        }

        const sources = [
            'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js',
            'https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js',
            'https://unpkg.com/qrcodejs@1.0.0/qrcode.min.js',
        ];

        qrCodeReadyPromise = (async () => {
            for (const source of sources) {
                try {
                    await loadScript(source);
                    if (typeof window.QRCode === 'function') {
                        console.log('QRCode library loaded from:', source);
                        return true;
                    }
                } catch (error) {
                    console.error(error.message);
                }
            }

            return false;
        })();

        return qrCodeReadyPromise;
    }

    // Initialize Alpine store for QR modal
    document.addEventListener('alpine:init', () => {
        Alpine.store('qrModal', {
            open: false,
            toggle() {
                this.open = !this.open;
            },
            show() {
                this.open = true;
                console.log('QR modal shown via store');
            },
            hide() {
                this.open = false;
                console.log('QR modal hidden via store');
            }
        });
        console.log('Alpine QR modal store initialized');
    });

    function deleteModalState() {
        return {
            isOpen: false,
            couponId: null,
            couponTitle: '',

            openModal(id, title) {
                this.couponId = id;
                this.couponTitle = title;
                this.isOpen = true;
            },

            closeModal() {
                this.isOpen = false;
                this.couponId = null;
                this.couponTitle = '';
            },

            confirmDelete() {
                if (!this.couponId) return;
                
                const form = document.getElementById('delete-form');
                form.action = `/admin/coupon-promos/${this.couponId}`;
                form.submit();
            }
        };
    }

    function refreshNoResultsRow() {
        const tbody = document.querySelector('#coupon-promos-table tbody');
        const noResultsRow = document.getElementById('no-results-row');
        if (!tbody || !noResultsRow) {
            return;
        }

        const visibleRows = Array.from(tbody.querySelectorAll('tr.coupon-row')).filter(row => row.style.display !== 'none');
        noResultsRow.style.display = visibleRows.length === 0 ? '' : 'none';
    }

document.addEventListener('DOMContentLoaded', function() {
    const qrModal = document.getElementById('qr-modal');
    const toast = document.getElementById('toast');
    const qrCanvas = document.getElementById('qrCanvas');
    const downloadBtn = document.getElementById('download-qr');
    let selectedCouponForDownload = null;

    function formatDate(dateStr) {
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }

    function formatDateShort(dateStr) {
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }

    function roundedRect(ctx, x, y, width, height, radius) {
        const r = Math.min(radius, width / 2, height / 2);
        ctx.beginPath();
        ctx.moveTo(x + r, y);
        ctx.lineTo(x + width - r, y);
        ctx.quadraticCurveTo(x + width, y, x + width, y + r);
        ctx.lineTo(x + width, y + height - r);
        ctx.quadraticCurveTo(x + width, y + height, x + width - r, y + height);
        ctx.lineTo(x + r, y + height);
        ctx.quadraticCurveTo(x, y + height, x, y + height - r);
        ctx.lineTo(x, y + r);
        ctx.quadraticCurveTo(x, y, x + r, y);
        ctx.closePath();
    }

    function drawWrappedText(ctx, text, x, y, maxWidth, lineHeight, maxLines = 2) {
        const words = String(text || '').split(' ');
        const lines = [];
        let line = '';

        for (let i = 0; i < words.length; i++) {
            const testLine = line ? `${line} ${words[i]}` : words[i];
            const metrics = ctx.measureText(testLine);

            if (metrics.width > maxWidth && line) {
                lines.push(line);
                line = words[i];
                if (lines.length >= maxLines - 1) {
                    break;
                }
            } else {
                line = testLine;
            }
        }

        if (line && lines.length < maxLines) {
            lines.push(line);
        }

        lines.forEach((content, index) => {
            let rendered = content;
            if (index === maxLines - 1 && words.length > 0) {
                const originalText = String(text || '');
                const combined = lines.join(' ');
                if (combined.length < originalText.length) {
                    while (ctx.measureText(`${rendered}...`).width > maxWidth && rendered.length > 0) {
                        rendered = rendered.slice(0, -1);
                    }
                    rendered = `${rendered}...`;
                }
            }

            ctx.fillText(rendered, x, y + (index * lineHeight));
        });

        return lines.length;
    }

    function dataUrlToFile(dataUrl, filename) {
        const parts = String(dataUrl).split(',');
        const meta = parts[0] || '';
        const base64 = parts[1] || '';
        const mimeMatch = meta.match(/data:(.*?);base64/);
        const mimeType = mimeMatch ? mimeMatch[1] : 'image/png';

        const binary = atob(base64);
        const bytes = new Uint8Array(binary.length);
        for (let index = 0; index < binary.length; index++) {
            bytes[index] = binary.charCodeAt(index);
        }

        return new File([bytes], filename, { type: mimeType });
    }

    async function tryShareQrImage(imageUrl, filename) {
        if (!navigator.share || !navigator.canShare) {
            return false;
        }

        try {
            const file = dataUrlToFile(imageUrl, filename);
            if (!navigator.canShare({ files: [file] })) {
                return false;
            }

            await navigator.share({
                title: 'Coupon QR',
                text: 'Coupon promo QR code',
                files: [file],
            });

            return true;
        } catch (error) {
            // User cancelled share panel; this is not an app error.
            if (error && error.name === 'AbortError') {
                return true;
            }

            return false;
        }
    }

    function triggerDirectDownload(imageUrl, filename) {
        const file = dataUrlToFile(imageUrl, filename);
        const blobUrl = URL.createObjectURL(file);
        const link = document.createElement('a');
        link.href = blobUrl;
        link.download = filename;
        link.rel = 'noopener';
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        link.remove();

        setTimeout(() => {
            URL.revokeObjectURL(blobUrl);
        }, 1000);
    }

    async function buildModalStyledDownloadImage(qrNode, couponData) {
        const width = 1200;
        const height = 780;
        const canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;
        const ctx = canvas.getContext('2d');

        // Backdrop
        ctx.fillStyle = '#F3EFE7';
        ctx.fillRect(0, 0, width, height);

        // Modal card
        const cardX = 70;
        const cardY = 55;
        const cardW = width - 140;
        const cardH = height - 110;
        ctx.fillStyle = '#FFFFFF';
        roundedRect(ctx, cardX, cardY, cardW, cardH, 28);
        ctx.fill();

        // Header
        ctx.fillStyle = '#3A2E22';
        ctx.font = 'bold 54px Playfair Display, serif';
        ctx.textAlign = 'left';
        ctx.fillText(String(couponData.establishment || ''), cardX + 34, cardY + 78);

        // Inner panel
        const panelX = cardX + 34;
        const panelY = cardY + 115;
        const panelW = cardW - 68;
        const panelH = cardH - 145;
        ctx.fillStyle = '#F3E9D7';
        roundedRect(ctx, panelX, panelY, panelW, panelH, 20);
        ctx.fill();

        // Left QR container
        const leftX = panelX + 20;
        const leftY = panelY + 18;
        const leftW = 430;
        const leftH = panelH - 36;
        ctx.fillStyle = '#FFFFFF';
        roundedRect(ctx, leftX, leftY, leftW, leftH, 20);
        ctx.fill();

        // Draw QR code in left container
        const qrSize = 280;
        const qrX = leftX + Math.floor((leftW - qrSize) / 2);
        const qrY = leftY + Math.floor((leftH - qrSize) / 2);
        const qrImage = await new Promise((resolve, reject) => {
            const image = new Image();
            image.onload = () => resolve(image);
            image.onerror = () => reject(new Error('Unable to render QR image for download.'));

            image.src = qrNode.tagName.toLowerCase() === 'canvas'
                ? qrNode.toDataURL('image/png')
                : qrNode.src;
        });

        ctx.drawImage(qrImage, qrX, qrY, qrSize, qrSize);

        // Right details column
        const rightX = leftX + leftW + 44;
        const rightY = leftY + 38;
        const rightW = panelX + panelW - rightX - 28;

        ctx.fillStyle = '#1F2937';
        ctx.font = 'bold 46px Playfair Display, serif';
        const titleLines = drawWrappedText(ctx, couponData.title || '', rightX, rightY, rightW, 50, 2);

        const titleBottomY = rightY + (Math.max(titleLines, 1) * 50);
        ctx.fillStyle = '#4B5563';
        ctx.font = '400 24px Poppins, sans-serif';
        const descStartY = titleBottomY + 14;
        const descLines = drawWrappedText(ctx, couponData.description || '', rightX, descStartY, rightW, 30, 2);

        const descBottomY = descStartY + (Math.max(descLines, 1) * 30);
        const detailsStartY = descBottomY + 24;

        ctx.fillStyle = '#374151';
        ctx.font = '600 30px Poppins, sans-serif';
        ctx.fillText('Discount:', rightX, detailsStartY);
        ctx.fillStyle = '#16A34A';
        ctx.font = '700 30px Poppins, sans-serif';
        const discountLines = drawWrappedText(ctx, String(couponData.discountLabel || ''), rightX, detailsStartY + 36, rightW, 34, 1);

        const validLabelY = detailsStartY + 36 + (Math.max(discountLines, 1) * 34) + 12;
        ctx.fillStyle = '#374151';
        ctx.font = '600 30px Poppins, sans-serif';
        ctx.fillText('Valid Period:', rightX, validLabelY);
        ctx.font = '400 24px Poppins, sans-serif';
        ctx.fillStyle = '#4B5563';
        const validText = `${couponData.validFromLabel || ''} - ${couponData.validUntilLabel || ''}`;
        const validLines = drawWrappedText(ctx, validText, rightX, validLabelY + 34, rightW, 30, 2);

        // Branding footer in panel
        const brandCenterX = rightX + Math.floor(rightW / 2);
        const brandSecondLineY = panelY + panelH - 22;
        const brandFirstLineY = brandSecondLineY - 34;
        const detailsBottomY = validLabelY + 34 + (Math.max(validLines, 1) * 30);

        if (detailsBottomY > brandFirstLineY - 12) {
            // Compact fallback for tight layouts: shrink detail copy once more.
            ctx.fillStyle = '#F3E9D7';
            ctx.fillRect(rightX, detailsStartY - 8, rightW + 8, brandFirstLineY - detailsStartY - 4);

            ctx.fillStyle = '#374151';
            ctx.font = '600 26px Poppins, sans-serif';
            ctx.fillText('Discount:', rightX, detailsStartY);
            ctx.fillStyle = '#16A34A';
            ctx.font = '700 26px Poppins, sans-serif';
            drawWrappedText(ctx, String(couponData.discountLabel || ''), rightX, detailsStartY + 30, rightW, 30, 1);

            const fallbackValidLabelY = detailsStartY + 68;
            ctx.fillStyle = '#374151';
            ctx.font = '600 26px Poppins, sans-serif';
            ctx.fillText('Valid Period:', rightX, fallbackValidLabelY);
            ctx.fillStyle = '#4B5563';
            ctx.font = '400 22px Poppins, sans-serif';
            drawWrappedText(ctx, validText, rightX, fallbackValidLabelY + 28, rightW, 26, 2);
        }

        ctx.textAlign = 'center';
        ctx.fillStyle = '#4B3A2A';
        ctx.font = '400 22px Playfair Display, serif';
        ctx.fillText('Brewing Connections', brandCenterX, brandFirstLineY);

        ctx.fillStyle = '#3A2E22';
        ctx.font = '600 22px Poppins, sans-serif';
        ctx.fillText(String(couponData.establishment || ''), brandCenterX, brandSecondLineY);

        return canvas.toDataURL('image/png');
    }

    function drawQrPlaceholder(message) {
        if (!qrCanvas) {
            return;
        }

        qrCanvas.innerHTML = `<span class="text-sm text-gray-500">${message}</span>`;
        qrCanvas.dataset.qrReady = 'false';
    }

    function setDownloadAvailability(isEnabled) {
        if (!downloadBtn) {
            return;
        }

        downloadBtn.disabled = !isEnabled;
        downloadBtn.classList.toggle('opacity-50', !isEnabled);
        downloadBtn.classList.toggle('cursor-not-allowed', !isEnabled);
    }

    function openQrModal(id) {
        console.log('openQrModal called with ID:', id);
        fetch(`/admin/coupon-promos/${id}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(async data => {
            console.log(data);

            document.getElementById('modal-cafe-header').textContent = data.establishment;
            document.getElementById('modal-cafe-footer-text').textContent = data.establishment;
            document.getElementById('modal-title').textContent = data.title;
            document.getElementById('modal-description').textContent = data.description;
            const discountLabel = data.discount_type === 'percentage' ? `${data.discount_value}% OFF` : `₱${data.discount_value} OFF`;
            document.getElementById('modal-discount').textContent = discountLabel;
            document.getElementById('modal-valid').textContent = `${formatDate(data.valid_from)} - ${formatDate(data.valid_until)}`;
            document.getElementById('modal-usage').textContent = `${data.used_count} / ${data.max_usage}`;

            selectedCouponForDownload = {
                title: data.title,
                description: data.description,
                establishment: data.establishment,
                discountLabel,
                validFromLabel: formatDateShort(data.valid_from),
                validUntilLabel: formatDateShort(data.valid_until),
            };

            drawQrPlaceholder('Loading QR...');
            setDownloadAvailability(false);

            // Show modal using Alpine.js store
            console.log('Showing QR modal via Alpine store');
            if (typeof Alpine !== 'undefined' && Alpine.store('qrModal')) {
                Alpine.store('qrModal').show();
            } else {
                console.error('Alpine.js store not available');
            }

            const qrToken = (data.qr_code_token || '').trim();
            if (!qrToken) {
                drawQrPlaceholder('QR unavailable');
                showToast('QR token is missing for this promo.', 'error');
                return;
            }

            const qrLibraryReady = await ensureQRCodeLibrary();
            if (!qrLibraryReady) {
                drawQrPlaceholder('QR unavailable');
                showToast('Unable to load QR generator. Please try again.', 'error');
                return;
            }

            const qrPayload = `{{ url('/redeem') }}/${qrToken}`;
            qrCanvas.innerHTML = '';

            try {
                new window.QRCode(qrCanvas, {
                    text: qrPayload,
                    width: 220,
                    height: 220,
                    colorDark: '#3A2E22',
                    colorLight: '#FFFFFF',
                    correctLevel: window.QRCode.CorrectLevel.H
                });

                setTimeout(() => {
                    const renderedNode = qrCanvas.querySelector('canvas, img');
                    if (!renderedNode) {
                        drawQrPlaceholder('QR unavailable');
                        showToast('Failed to generate QR code.', 'error');
                        return;
                    }

                    qrCanvas.dataset.qrReady = 'true';
                    setDownloadAvailability(true);
                }, 100);
            } catch (error) {
                console.error('QR Code generation failed:', error);
                drawQrPlaceholder('QR unavailable');
                showToast('Failed to generate QR code.', 'error');
            }
        })
        .catch(error => {
            console.error('Error fetching coupon data:', error);
            showToast('Failed to load promo details.', 'error');
        });
    }

    // QR buttons - use event delegation on the table
    const table = document.getElementById('coupon-promos-table');
    if (table) {
        table.addEventListener('click', function(e) {
            console.log('Table clicked, target:', e.target);
            const btn = e.target.closest('.view-qr-btn');
            if (!btn) {
                console.log('No .view-qr-btn found');
                return;
            }
            e.stopPropagation(); // Prevent event bubbling
            console.log('QR button clicked, ID:', btn.dataset.id);
            const id = btn.dataset.id;
            openQrModal(id);
        });
        console.log('Event listener attached to table');
    } else {
        console.error('Table not found');
    }

    ensureQRCodeLibrary().then((loaded) => {
        console.log('QRCode library available:', loaded);
    });

    // Download QR
    document.getElementById('download-qr').addEventListener('click', async function() {
        const qrHolder = document.getElementById('qrCanvas');
        const title = document.getElementById('modal-title').textContent;
        const normalizedTitle = String(title || 'coupon').trim().toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
        const filename = `coupon-qr-${normalizedTitle || 'download'}.png`;
        const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);

        if (qrHolder.dataset.qrReady !== 'true') {
            showToast('QR code is not ready yet.', 'error');
            return;
        }

        const qrNode = qrHolder.querySelector('canvas, img');
        if (!qrNode) {
            showToast('QR code is not ready yet.', 'error');
            return;
        }

        if (!selectedCouponForDownload) {
            showToast('Promo details are not available yet.', 'error');
            return;
        }

        try {
            const imageUrl = await buildModalStyledDownloadImage(qrNode, selectedCouponForDownload);

            if (isMobile) {
                const shared = await tryShareQrImage(imageUrl, filename);
                if (shared) {
                    return;
                }
            }

            triggerDirectDownload(imageUrl, filename);
        } catch (error) {
            console.error(error);
            showToast('Failed to create QR download image.', 'error');
        }
    });

    function showToast(message, type) {
        const toastMessage = document.getElementById('toast-message');
        toastMessage.textContent = message;
        toast.className = `fixed bottom-4 right-4 px-4 py-2 rounded-md text-white ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
        toast.classList.remove('hidden');
        setTimeout(() => {
            toast.classList.add('hidden');
        }, 3000);
    }

    refreshNoResultsRow();
});

document.addEventListener('DOMContentLoaded', function () {
    const content = document.querySelector('.filter-content');
    if (content) {
        content.classList.add('tab-panel');
    }
});
</script>
@endsection