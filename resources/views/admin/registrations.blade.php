@extends('layouts.app')

@section('title', 'Registrations - BrewHub')

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
    <main class="ml-0 md:ml-64 flex-1 p-8 overflow-y-auto" x-data="{ deactivateModal: deactivateModalState() }" @open-deactivate="deactivateModal.openModal($event.detail.id, $event.detail.name)">
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
                    Registrations
                </h1>
                <p class="text-[#9E8C78] text-sm font-medium">View and manage consumer accounts</p>
            </div>
        </div>

        <!-- Overview Cards Section -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Total Consumers Card -->
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-l-green-500 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[#9E8C78] text-sm font-medium">Total Consumers</p>
                        <p class="text-3xl font-bold text-[#3A2E22] mt-1">{{ $totalConsumers ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-green-600">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Active Consumers Card -->
            <div class="bg-white rounded-xl shadow-sm border-l-4 p-6 hover:shadow-md transition-shadow" style="border-left-color: #D4AF37;">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[#9E8C78] text-sm font-medium">Active</p>
                        <p class="text-3xl font-bold text-[#3A2E22] mt-1">{{ $activeConsumers ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center" style="background-color: rgba(212, 175, 55, 0.15);">
                        <!-- Check badge icon (same as resellers) -->
                        <svg class="w-6 h-6" style="color: #D4AF37;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Deactivated Consumers Card -->
            <div class="bg-white rounded-xl shadow-sm border-l-4 p-6 hover:shadow-md transition-shadow" style="border-left-color: #800000;">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[#9E8C78] text-sm font-medium">Deactivated</p>
                        <p class="text-3xl font-bold text-[#3A2E22] mt-1">{{ $deactivatedConsumers ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center" style="background-color: rgba(128, 0, 0, 0.15);">
                        <!-- Proper X (cross) icon for Deactivated -->
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6" style="color: #800000;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Registrations Table Section -->
        <div class="registrations-panel filter-content bg-white rounded-xl shadow-sm p-8">
            <h2 class="text-2xl font-display font-bold text-[#3A2E22] mb-2">
                All <span class="italic text-[#4A6741]">Registrations</span>
            </h2>
            <p class="text-[#9E8C78] text-sm mb-6">Complete list of registered consumers</p>

            @if($consumers->count() > 0)
                <!-- Filter Tabs and Search Bar -->
                <div class="registrations-toolbar mb-6 flex items-center justify-between gap-4 border-b border-gray-200 pb-4">
                    <!-- Filter Tabs -->
                    <div class="registrations-filter-tabs flex gap-2">
                        <button class="filter-tab {{ $filter === 'all' ? 'active' : '' }} px-4 py-2 text-sm font-medium transition-colors" data-filter="all" style="color: {{ $filter === 'all' ? '#3B2F2F' : '#9E8C78' }}; border-bottom: 3px solid {{ $filter === 'all' ? '#3B2F2F' : 'transparent' }};">
                            All
                        </button>
                        <button class="filter-tab {{ $filter === 'active' ? 'active' : '' }} px-4 py-2 text-sm font-medium transition-colors" data-filter="active" style="color: {{ $filter === 'active' ? '#3B2F2F' : '#9E8C78' }}; border-bottom: 3px solid {{ $filter === 'active' ? '#3B2F2F' : 'transparent' }};">
                            Active
                        </button>
                        <button class="filter-tab {{ $filter === 'deactivated' ? 'active' : '' }} px-4 py-2 text-sm font-medium transition-colors" data-filter="deactivated" style="color: {{ $filter === 'deactivated' ? '#3B2F2F' : '#9E8C78' }}; border-bottom: 3px solid {{ $filter === 'deactivated' ? '#3B2F2F' : 'transparent' }};">
                            Deactivated
                        </button>
                        <button class="filter-tab {{ $filter === 'last_30_days' ? 'active' : '' }} px-4 py-2 text-sm font-medium transition-colors" data-filter="last_30_days" style="color: {{ $filter === 'last_30_days' ? '#3B2F2F' : '#9E8C78' }}; border-bottom: 3px solid {{ $filter === 'last_30_days' ? '#3B2F2F' : 'transparent' }};">
                            Last 30 Days
                        </button>
                    </div>

                    <!-- Search Input -->
                    <div class="registrations-search relative">
                        <svg class="w-4 h-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-[#9E8C78]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" id="search-input" placeholder="Search by name, email..." class="w-full pl-9 pr-3 py-1.5 text-xs bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#4A6741] focus:border-transparent md:w-64" />
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr style="background-color: #3B2F2F;">
                                <th class="px-6 py-3 text-left text-sm font-medium text-white">#</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-white">Name</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-white">Email</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-white">Phone</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-white">Status</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-white">Registered</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-white">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="consumers-tbody">
                            @foreach($consumers as $index => $consumer)
                                <tr class="consumer-row border-b border-gray-100 hover:bg-[#FAF7F2] transition-colors"
                                    data-name="{{ strtolower($consumer->name) }}"
                                    data-email="{{ strtolower($consumer->email) }}"
                                    data-status="{{ $consumer->status }}"
                                    style="background-color: {{ $index % 2 === 1 ? '#FAF7F2' : '#FFFFFF' }};">
                                    <td class="px-6 py-4 text-sm font-medium text-[#3A2E22]">{{ $consumers->firstItem() + $index }}</td>
                                    <td class="px-6 py-4 text-sm font-medium text-[#3A2E22]">{{ $consumer->name }}</td>
                                    <td class="px-6 py-4 text-sm text-[#9E8C78]">{{ $consumer->email }}</td>
                                    <td class="px-6 py-4 text-sm text-[#9E8C78]">{{ $consumer->phone ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        @if($consumer->status === 'active')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium" style="background-color: rgba(212, 175, 55, 0.15); color: #D4AF37;">Active</span>
                                        @elseif($consumer->status === 'deactivated')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium" style="background-color: rgba(128, 0, 0, 0.15); color: #800000;">Deactivated</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-[#9E8C78]">{{ $consumer->created_at->format('M j, Y') }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        @if($consumer->status === 'active')
                                            <button @click="$dispatch('open-deactivate', { id: {{ $consumer->id }}, name: '{{ $consumer->name }}' })" class="text-red-600 hover:text-red-800 font-medium transition-colors" title="Deactivate">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #800000;">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        @else
                                            <span class="text-gray-400 text-sm">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $consumers->appends(request()->query())->links() }}
                </div>
            @else
                <div class="py-12 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-12 h-12 text-gray-300 mx-auto mb-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                    <h3 class="text-gray-400 font-medium mb-2">No consumers yet</h3>
                    <p class="text-gray-400 text-sm text-center">Consumer registrations will appear here once users sign up through the mobile app.</p>
                </div>
            @endif
        </div>

        <!-- Deactivate Confirmation Modal -->
        <div class="fixed inset-0 z-50 flex items-center justify-center px-4" x-show="deactivateModal.isOpen" @keydown.escape="deactivateModal.closeModal()" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" style="display: none;">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black bg-opacity-40 backdrop-blur-sm" @click="deactivateModal.closeModal()"></div>
            
            <!-- Modal Card -->
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full" @click.stop>
                <div class="p-6">
                    <h2 class="text-2xl font-display font-bold text-[#3A2E22] mb-4">
                        Deactivate Consumer?
                    </h2>
                    <p class="text-[#3A2E22] mb-6">
                        Are you sure you want to deactivate <span class="font-semibold" x-text="deactivateModal.consumerName"></span>? They will lose access to the mobile app.
                    </p>
                    <div class="flex gap-3">
                        <button @click="deactivateModal.closeModal()" class="flex-1 px-4 py-2 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button @click="deactivateModal.confirmDeactivate()" class="flex-1 px-4 py-2 rounded-lg bg-red-600 text-white font-medium hover:bg-red-700 transition-colors">
                            Deactivate
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hidden Deactivate Form -->
        <form id="deactivate-form" method="POST" style="display: none;">
            @csrf
        </form>
    </main>
</div>

<script>
    function deactivateModalState() {
        return {
            isOpen: false,
            consumerId: null,
            consumerName: '',

            openModal(id, name) {
                this.consumerId = id;
                this.consumerName = name;
                this.isOpen = true;
            },

            closeModal() {
                this.isOpen = false;
                this.consumerId = null;
                this.consumerName = '';
            },

            confirmDeactivate() {
                if (!this.consumerId) return;

                const form = document.getElementById('deactivate-form');
                form.action = `/admin/registrations/${this.consumerId}/deactivate`;
                form.submit();
            }
        };
    }

    // Client-side filtering and searching for consumers
    document.addEventListener('DOMContentLoaded', function() {
        const filterTabs = document.querySelectorAll('.filter-tab');
        const searchInput = document.getElementById('search-input');
        const consumerRows = document.querySelectorAll('.consumer-row');
        const tbody = document.getElementById('consumers-tbody');
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

            consumerRows.forEach(row => {
                const rowName = row.getAttribute('data-name');
                const rowEmail = row.getAttribute('data-email');
                const rowStatus = row.getAttribute('data-status');

                // Check status filter
                const statusMatch = currentFilter === 'all' || rowStatus === currentFilter;

                // Check search query
                let searchMatch = true;
                if (currentSearch) {
                    searchMatch = rowName.includes(currentSearch) ||
                                 rowEmail.includes(currentSearch);
                }

                // Show or hide row
                if (statusMatch && searchMatch) {
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
                    noResultsRow.innerHTML = '<td colspan="7" class="px-6 py-8 text-center"><p class="text-gray-400">No consumers found matching your search.</p></td>';
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

@push('styles')
<style>
    @media (max-width: 767px) {
        .registrations-panel {
            padding: 1rem !important;
            border-radius: 18px !important;
        }

        .registrations-toolbar {
            display: flex !important;
            flex-direction: column;
            align-items: stretch !important;
            gap: 0.75rem !important;
            padding-bottom: 0.85rem !important;
        }

        .registrations-filter-tabs {
            display: flex;
            flex-wrap: nowrap;
            gap: 0.45rem;
            overflow-x: auto;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
        }

        .registrations-filter-tabs .filter-tab {
            flex: 0 0 auto;
            min-height: 2.25rem;
            padding: 0.55rem 0.85rem !important;
            border-radius: 999px;
            border-bottom-width: 0 !important;
            background: #F7F2EA;
        }

        .registrations-filter-tabs .filter-tab.active,
        .registrations-filter-tabs .filter-tab[style*='#3B2F2F'] {
            background: #3B2F2F;
            color: #FFFFFF !important;
        }

        .registrations-search {
            width: 100%;
        }

        .registrations-search input {
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
@endsection