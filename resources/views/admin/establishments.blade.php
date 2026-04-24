@extends('layouts.app')

@section('title', 'Establishments - BrewHub')

@section('content')
<div class="min-h-screen bg-[#F5F0E8] flex">
    <!-- Sidebar -->
    <aside class="admin-sidebar fixed left-0 top-0 h-screen w-64 bg-[#3A2E22] text-[#F5F0E8] flex flex-col justify-between py-6 px-4 rounded-r-xl shadow-lg overflow-hidden z-40 -translate-x-full md:translate-x-0 transition-transform duration-300 ease-out">
        <div>
            <!-- Logo -->
            <div class="flex items-center mb-8">
                <svg class="w-6 h-6 mr-3 text-[#F5F0E8]" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
                <span class="brand-wordmark text-lg"><span class="brand-brew">Brew</span><span class="brand-hub">Hub</span></span>
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
    <main class="ml-0 md:ml-64 flex-1 p-8 overflow-y-auto" x-data="{ deleteModal: deleteModalState() }" @open-delete="deleteModal.openModal($event.detail.id, $event.detail.name)">
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

        <!-- Top Navbar -->
        <div class="flex items-center justify-between mb-8 sticky top-0 z-10 bg-[#F5F0E8]">
            <div>
                <h1 class="text-3xl font-display font-bold text-[#3A2E22] mb-1">
                    Establishments
                </h1>
                <p class="text-[#9E8C78] text-sm font-medium">View and manage all farms, cafés, and roasters</p>
            </div>
        </div>

        <!-- Overview Cards Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Establishments Card -->
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-l-green-500 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[#9E8C78] text-sm font-medium">Total Establishments</p>
                        <p class="text-3xl font-bold text-[#3A2E22] mt-1">{{ $totalCount ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Farms Card -->
            <div class="bg-white rounded-xl shadow-sm border-l-4 p-6 hover:shadow-md transition-shadow" style="border-left-color: #4A6741;">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[#9E8C78] text-sm font-medium">Total Farms</p>
                        <p class="text-3xl font-bold text-[#3A2E22] mt-1">{{ $farmCount ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center" style="background-color: rgba(74, 103, 65, 0.15);">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="color: #4A6741;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Cafés Card -->
            <div class="bg-white rounded-xl shadow-sm border-l-4 p-6 hover:shadow-md transition-shadow" style="border-left-color: #8B4513;">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[#9E8C78] text-sm font-medium">Total Cafés</p>
                        <p class="text-3xl font-bold text-[#3A2E22] mt-1">{{ $cafeCount ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center" style="background-color: rgba(139, 69, 19, 0.15);">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="color: #8B4513;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 3h10a2 2 0 012 2v8a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 13h12" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 7v6" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 7v6" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Roasters Card -->
            <div class="bg-white rounded-xl shadow-sm border-l-4 p-6 hover:shadow-md transition-shadow" style="border-left-color: #6B3A2A;">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[#9E8C78] text-sm font-medium">Total Roasters</p>
                        <p class="text-3xl font-bold text-[#3A2E22] mt-1">{{ $roasterCount ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center" style="background-color: rgba(107, 58, 42, 0.15);">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="color: #6B3A2A;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Establishments Table Section -->
        <div class="establishments-panel filter-content bg-white rounded-xl shadow-sm p-8">
            <h2 class="text-2xl font-display font-bold text-[#3A2E22] mb-2">
                All <span class="italic text-[#4A6741]">Establishments</span>
            </h2>
            <p class="text-[#9E8C78] text-sm mb-6">Complete list of registered establishments</p>

            @if($establishments->count() > 0)
                <!-- Filter Tabs and Search Bar -->
                <div class="establishments-toolbar mb-6 flex items-center justify-between gap-4 border-b border-gray-200 pb-4">
                    <!-- Filter Tabs -->
                    <div class="establishments-filter-tabs flex gap-2">
                        <button class="filter-tab active px-4 py-2 text-sm font-medium transition-colors" data-filter="all" style="color: #3B2F2F; border-bottom: 3px solid #3B2F2F;">
                            All
                        </button>
                        <button class="filter-tab px-4 py-2 text-sm font-medium transition-colors" data-filter="farm" style="color: #9E8C78; border-bottom: 3px solid transparent;">
                            Farms
                        </button>
                        <button class="filter-tab px-4 py-2 text-sm font-medium transition-colors" data-filter="cafe" style="color: #9E8C78; border-bottom: 3px solid transparent;">
                            Cafés
                        </button>
                        <button class="filter-tab px-4 py-2 text-sm font-medium transition-colors" data-filter="roaster" style="color: #9E8C78; border-bottom: 3px solid transparent;">
                            Roasters
                        </button>
                    </div>

                    <!-- Search Input -->
                    <div class="establishments-search relative">
                        <svg class="w-4 h-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-[#9E8C78]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" id="search-input" placeholder="Search by name, type, barangay..." class="w-full pl-9 pr-3 py-1.5 text-xs bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#4A6741] focus:border-transparent md:w-64" />
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr style="background-color: #3B2F2F;">
                                <th class="px-6 py-3 text-left text-sm font-medium uppercase text-white">#</th>
                                <th class="px-6 py-3 text-left text-sm font-medium uppercase text-white">Name</th>
                                <th class="px-6 py-3 text-left text-sm font-medium uppercase text-white">Type</th>
                                <th class="px-6 py-3 text-left text-sm font-medium uppercase text-white">Barangay</th>
                                <th class="px-6 py-3 text-left text-sm font-medium uppercase text-white">Contact</th>
                                <th class="px-6 py-3 text-left text-sm font-medium uppercase text-white">Coffee Varieties</th>
                                <th class="px-6 py-3 text-left text-sm font-medium uppercase text-white">Rating</th>
                                <th class="px-6 py-3 text-left text-sm font-medium uppercase text-white">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="establishments-tbody">
                            @foreach($establishments as $index => $establishment)
                                <tr class="establishment-row border-b border-gray-100 hover:bg-[#FAF7F2] transition-colors" 
                                    data-type="{{ strtolower($establishment->type) }}"
                                    data-name="{{ strtolower($establishment->name) }}"
                                    data-barangay="{{ strtolower($establishment->barangay ?? '') }}"
                                    data-varieties="{{ strtolower($establishment->varieties->pluck('name')->join(' ')) }}"
                                    style="background-color: {{ $index % 2 === 1 ? '#FAF7F2' : '#FFFFFF' }};">
                                    <td class="px-6 py-4 text-sm font-medium text-[#3A2E22]">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4 text-sm font-medium text-[#3A2E22]">{{ $establishment->name }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        @if($establishment->type === 'farm')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium" style="background-color: rgba(74, 103, 65, 0.15); color: #4A6741;">Farm</span>
                                        @elseif($establishment->type === 'cafe')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium" style="background-color: rgba(139, 69, 19, 0.15); color: #8B4513;">Café</span>
                                        @elseif($establishment->type === 'roaster')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium" style="background-color: rgba(107, 58, 42, 0.15); color: #6B3A2A;">Roaster</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-[#9E8C78]">{{ $establishment->barangay ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-sm text-[#9E8C78]">{{ $establishment->contact_number ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-sm text-[#9E8C78]">
                                        @if($establishment->varieties->count() > 0)
                                            <span class="text-xs">{{ $establishment->varieties->pluck('name')->join(', ') }}</span>
                                        @else
                                            <span class="text-xs text-gray-400">None</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        @if($establishment->reviews_avg_overall_rating)
                                            <div class="flex items-center gap-1">
                                                <span class="text-[#3A2E22] font-medium">{{ round($establishment->reviews_avg_overall_rating, 1) }}</span>
                                                <span class="text-yellow-400">★</span>
                                            </div>
                                        @else
                                            <span class="text-gray-400">No ratings</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <button @click="$dispatch('open-delete', { id: {{ $establishment->id }}, name: '{{ addslashes($establishment->name) }}' })" class="text-red-600 hover:text-red-800 font-medium transition-colors" title="Delete">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="py-12 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 text-gray-300 mx-auto mb-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <h3 class="text-gray-400 font-medium mb-2">No establishments yet</h3>
                    <p class="text-gray-400 text-sm text-center">Start by adding your first farm, café, or roaster establishment via the map or admin panel.</p>
                </div>
            @endif
        </div>

        <!-- Delete Confirmation Modal -->
        <div class="fixed inset-0 z-50 flex items-center justify-center px-4" x-show="deleteModal.isOpen" @keydown.escape="deleteModal.closeModal()" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" style="display: none;">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black bg-opacity-40 backdrop-blur-sm" @click="deleteModal.closeModal()"></div>
            
            <!-- Modal Card -->
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full" @click.stop>
                <div class="p-6">
                    <h2 class="text-2xl font-display font-bold text-[#3A2E22] mb-4">
                        Delete Establishment?
                    </h2>
                    <p class="text-[#3A2E22] mb-6">
                        Are you sure you want to delete <span class="font-semibold" x-text="deleteModal.establishmentName"></span>? This action cannot be undone.
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
    </main>
</div>

<script>
    function deleteModalState() {
        return {
            isOpen: false,
            establishmentId: null,
            establishmentName: '',

            openModal(id, name) {
                this.establishmentId = id;
                this.establishmentName = name;
                this.isOpen = true;
            },

            closeModal() {
                this.isOpen = false;
                this.establishmentId = null;
                this.establishmentName = '';
            },

            confirmDelete() {
                if (!this.establishmentId) return;
                
                const form = document.getElementById('delete-form');
                form.action = `/admin/establishments/${this.establishmentId}`;
                form.submit();
            }
        };
    }

    // Client-side filtering and searching for establishments
    document.addEventListener('DOMContentLoaded', function() {
        const filterTabs = document.querySelectorAll('.filter-tab');
        const searchInput = document.getElementById('search-input');
        const establishmentRows = document.querySelectorAll('.establishment-row');
        const tbody = document.getElementById('establishments-tbody');
        let currentFilter = 'all';
        let currentSearch = '';

        // Handle filter tab clicks
        filterTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                currentFilter = this.getAttribute('data-filter');

                // Update active tab styling
                filterTabs.forEach(btn => {
                    if (btn === this) {
                        btn.style.color = '#3B2F2F';
                        btn.style.borderBottomColor = '#3B2F2F';
                    } else {
                        btn.style.color = '#9E8C78';
                        btn.style.borderBottomColor = 'transparent';
                    }
                });

                updateTableDisplay();
            });
        });

        // Handle search input
        searchInput.addEventListener('input', function() {
            currentSearch = this.value.toLowerCase();
            updateTableDisplay();
        });

        // Update table display based on filter and search
        function updateTableDisplay() {
            let visibleCount = 0;

            establishmentRows.forEach(row => {
                const rowType = row.getAttribute('data-type');
                const rowName = row.getAttribute('data-name');
                const rowBarangay = row.getAttribute('data-barangay');
                const rowVarieties = row.getAttribute('data-varieties');

                // Check type filter
                const typeMatch = currentFilter === 'all' || rowType === currentFilter;

                // Check search query
                let searchMatch = true;
                if (currentSearch) {
                    searchMatch = rowName.includes(currentSearch) ||
                                 rowType.includes(currentSearch) ||
                                 rowBarangay.includes(currentSearch) ||
                                 rowVarieties.includes(currentSearch);
                }

                // Show or hide row
                if (typeMatch && searchMatch) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Show or hide "No results found" message
            let noResultsRow = document.getElementById('no-results-row');
            if (visibleCount === 0) {
                if (!noResultsRow) {
                    noResultsRow = document.createElement('tr');
                    noResultsRow.id = 'no-results-row';
                    noResultsRow.innerHTML = '<td colspan="8" class="px-6 py-8 text-center"><p class="text-gray-400">No establishments found matching your search.</p></td>';
                    tbody.appendChild(noResultsRow);
                }
                noResultsRow.style.display = '';
            } else if (noResultsRow) {
                noResultsRow.style.display = 'none';
            }
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        const content = document.querySelector('.filter-content');
        if (content) {
            content.classList.add('tab-panel');
        }
    });
</script>
@endsection

@push('styles')
<style>
    @media (max-width: 767px) {
        .establishments-panel {
            padding: 1rem !important;
            border-radius: 18px !important;
        }

        .establishments-toolbar {
            display: flex !important;
            flex-direction: column;
            align-items: stretch !important;
            gap: 0.75rem !important;
            padding-bottom: 0.85rem !important;
        }

        .establishments-filter-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 0.45rem;
        }

        .establishments-filter-tabs .filter-tab {
            flex: 0 1 auto;
            min-height: 2.25rem;
            padding: 0.55rem 0.85rem !important;
            border-radius: 999px;
            border-bottom-width: 0 !important;
            background: #F7F2EA;
        }

        .establishments-filter-tabs .filter-tab.active,
        .establishments-filter-tabs .filter-tab[style*='#3B2F2F'] {
            background: #3B2F2F;
            color: #FFFFFF !important;
        }

        .establishments-search {
            width: 100%;
        }

        .establishments-search input {
            width: 100% !important;
            min-width: 0;
            height: 2.7rem;
            font-size: 0.85rem;
            border-radius: 14px;
            background: #FCFAF7;
        }
    }
</style>
@endpush
