@extends('layouts.app')

@section('title', 'Rating Moderation - BrewHub')

@section('content')
<div class="rating-moderation-page min-h-screen bg-[#F5F0E8] flex" x-data="{
    deleteModalOpen: false,
    deleteId: null,
    deleteUser: '',
    deleteEstablishment: '',
    openDeleteModal(id, userName, establishmentName) {
        this.deleteId = id;
        this.deleteUser = userName;
        this.deleteEstablishment = establishmentName;
        this.deleteModalOpen = true;
    },
    closeDeleteModal() {
        this.deleteModalOpen = false;
        this.deleteId = null;
        this.deleteUser = '';
        this.deleteEstablishment = '';
    }
}">
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
                <a href="{{ route('admin.rating-moderation.index') }}" class="flex items-center {{ request()->routeIs('admin.rating-moderation.*') ? 'bg-[#4E3D2B]' : '' }} rounded-lg px-4 py-2 text-xs font-medium gap-2 transition-all duration-200 hover:bg-[#4E3D2B] hover:translate-x-1">
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
    <main class="ml-0 md:ml-64 flex-1 p-8 overflow-y-auto">
        <!-- Flash Message Alert -->
        @if(session('rating_deleted'))
            <div id="success-alert" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg flex items-start gap-3 animate-fade-in-up">
                <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <div class="flex-1">
                    <p class="text-sm font-medium text-green-800">Rating has been successfully deleted.</p>
                </div>
                <button onclick="document.getElementById('success-alert').remove()" class="text-green-600 hover:text-green-900 flex-shrink-0">
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
                    Rating Moderation
                </h1>
                <p class="text-[#9E8C78] text-sm font-medium">View and moderate café customer ratings</p>
            </div>
        </div>

        <!-- Overview Cards Section -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Total Ratings Card -->
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-l-green-500 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[#9E8C78] text-sm font-medium">Total Ratings</p>
                        <p class="text-3xl font-bold text-[#3A2E22] mt-1">{{ $totalRatings }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Most Positive Café Card -->
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-l-[#D4AF37] p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[#9E8C78] text-sm font-medium">Most Positive Rating</p>
                        <p class="text-lg font-bold text-[#3A2E22] mt-1">{{ $mostPositive?->establishment?->name ?? 'N/A' }}</p>
                        <p class="text-sm text-[#9E8C78] mt-1">Avg {{ $mostPositive ? number_format($mostPositive->avg_rating, 1) : '' }} ★</p>
                    </div>
                    <div class="w-12 h-12 bg-[#D4AF37]/15 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#D4AF37]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Least Overall Rating Card -->
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-l-[#800000] p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[#9E8C78] text-sm font-medium">Least Positive Rating</p>
                        <p class="text-lg font-bold text-[#3A2E22] mt-1">{{ $leastPositive?->establishment?->name ?? 'N/A' }}</p>
                        <p class="text-sm text-[#9E8C78] mt-1">Avg {{ $leastPositive ? number_format($leastPositive->avg_rating, 1) : '' }} ★</p>
                    </div>
                    <div class="w-12 h-12 bg-[#800000]/15 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#800000]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.737 3h4.017c.163 0 .326.02.485.06L17 4m-7 10v5a2 2 0 002 2h.095c.5 0 .905-.405.905-.905 0-.714.211-1.412.608-2.006L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Banner -->
        <div class="bg-[#4A6741]/80 text-white rounded-lg p-4 mb-8 flex items-center gap-3">
            <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
            </svg>
            <p class="text-xs font-medium">Ratings are automatically analyzed and fed into the Recommendations system to generate actionable insights for café owners.</p>
        </div>

        <!-- Filter Tabs -->
        <div class="mb-6 border-b border-gray-200 pb-4">
            <div class="flex gap-2">
                <button class="filter-tab {{ $filter === 'all' ? 'active' : '' }} px-4 py-2 text-sm font-medium transition-colors" onclick="window.location.href='{{ route('admin.rating-moderation.index', ['filter' => 'all']) }}'" style="color: {{ $filter === 'all' ? '#3B2F2F' : '#9E8C78' }}; border-bottom: 3px solid {{ $filter === 'all' ? '#3B2F2F' : 'transparent' }};">
                    All
                </button>
                <button class="filter-tab {{ $filter === 'this_week' ? 'active' : '' }} px-4 py-2 text-sm font-medium transition-colors" onclick="window.location.href='{{ route('admin.rating-moderation.index', ['filter' => 'this_week']) }}'" style="color: {{ $filter === 'this_week' ? '#3B2F2F' : '#9E8C78' }}; border-bottom: 3px solid {{ $filter === 'this_week' ? '#3B2F2F' : 'transparent' }};">
                    This Week
                </button>
                <button class="filter-tab {{ $filter === 'this_month' ? 'active' : '' }} px-4 py-2 text-sm font-medium transition-colors" onclick="window.location.href='{{ route('admin.rating-moderation.index', ['filter' => 'this_month']) }}'" style="color: {{ $filter === 'this_month' ? '#3B2F2F' : '#9E8C78' }}; border-bottom: 3px solid {{ $filter === 'this_month' ? '#3B2F2F' : 'transparent' }};">
                    This Month
                </button>
            </div>
        </div>

        <!-- Ratings Cards List -->
        <div class="filter-content bg-white rounded-xl shadow-sm p-4">
            <h2 class="text-2xl font-display font-bold text-[#3A2E22] mb-2">
                All <span class="italic text-[#4A6741]">Ratings</span>
            </h2>
            <p class="text-[#9E8C78] text-sm mb-4">Complete list of submitted café ratings</p>

            @forelse($ratings as $rating)
                <div class="rating-card bg-white border border-gray-200 rounded-xl p-4 mb-4 hover:shadow-md transition-shadow">
                    <!-- Top Row -->
                    <div class="rating-card-header flex items-center justify-between mb-4">
                        <div class="rating-card-user flex items-center gap-3">
                            <div class="w-10 h-10 bg-[#4A6741] rounded-full flex items-center justify-center text-white font-bold text-sm">
                                {{ substr($rating->user->name ?? 'U', 0, 1) }}
                            </div>
                            <div class="rating-card-title">
                                <span class="font-bold text-[#3A2E22]">{{ $rating->user->name ?? 'Unknown User' }}</span>
                                <span class="text-[#9E8C78]"> rated </span>
                                <span class="font-bold italic text-[#3A2E22]">{{ $rating->establishment->name ?? 'Unknown Establishment' }}</span>
                            </div>
                        </div>
                        <div class="rating-card-date text-[#9E8C78] text-sm">
                            {{ $rating->created_at->format('M d, Y') }}
                        </div>
                    </div>

                    <!-- Rating Categories -->
                    <div class="rating-breakdown border border-gray-200 rounded-xl p-4 mb-4 bg-[#F9F6F1]">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="rating-breakdown-item flex items-center gap-2">
                                <span class="text-sm font-medium text-[#3A2E22]">Taste:</span>
                                <div class="flex">
                                    @for($i = 1; $i <= 5; $i++)
                                        <span class="text-lg {{ $i <= $rating->taste_rating ? 'text-[#4A6741]' : 'text-gray-300' }}">★</span>
                                    @endfor
                                </div>
                                <span class="text-sm text-[#9E8C78]">({{ $rating->taste_rating }}/5)</span>
                            </div>
                            <div class="rating-breakdown-item flex items-center gap-2">
                                <span class="text-sm font-medium text-[#3A2E22]">Environment:</span>
                                <div class="flex">
                                    @for($i = 1; $i <= 5; $i++)
                                        <span class="text-lg {{ $i <= $rating->environment_rating ? 'text-[#4A6741]' : 'text-gray-300' }}">★</span>
                                    @endfor
                                </div>
                                <span class="text-sm text-[#9E8C78]">({{ $rating->environment_rating }}/5)</span>
                            </div>
                            <div class="rating-breakdown-item flex items-center gap-2">
                                <span class="text-sm font-medium text-[#3A2E22]">Cleanliness:</span>
                                <div class="flex">
                                    @for($i = 1; $i <= 5; $i++)
                                        <span class="text-lg {{ $i <= $rating->cleanliness_rating ? 'text-[#4A6741]' : 'text-gray-300' }}">★</span>
                                    @endfor
                                </div>
                                <span class="text-sm text-[#9E8C78]">({{ $rating->cleanliness_rating }}/5)</span>
                            </div>
                            <div class="rating-breakdown-item flex items-center gap-2">
                                <span class="text-sm font-medium text-[#3A2E22]">Service:</span>
                                <div class="flex">
                                    @for($i = 1; $i <= 5; $i++)
                                        <span class="text-lg {{ $i <= $rating->service_rating ? 'text-[#4A6741]' : 'text-gray-300' }}">★</span>
                                    @endfor
                                </div>
                                <span class="text-sm text-[#9E8C78]">({{ $rating->service_rating }}/5)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Overall Rating -->
                    @php
                        $overallValue = round((float) $rating->overall_rating, 1);
                        $overallBg = match(true) {
                            $overallValue < 3.0 => 'bg-red-50 border-red-200',
                            $overallValue < 4.0 => 'bg-yellow-50 border-yellow-200',
                            default => 'bg-green-50 border-green-200',
                        };
                        $overallStarColor = match(true) {
                            $overallValue < 3.0 => 'text-red-500',
                            $overallValue < 4.0 => 'text-yellow-500',
                            default => 'text-[#4A6741]',
                        };
                    @endphp
                    <div class="rating-overall border rounded-xl p-4 mb-4 {{ $overallBg }}">
                        <span class="text-sm font-semibold text-[#3A2E22]">Overall</span>
                        <div class="flex items-center gap-2 mt-1">
                            <div class="flex">
                                @for($i = 1; $i <= 5; $i++)
                                    <span class="text-2xl {{ $i <= $rating->overall_rating ? $overallStarColor : 'text-gray-300' }}">★</span>
                                @endfor
                            </div>
                            <span class="text-lg font-bold text-[#3A2E22]">({{ number_format($rating->overall_rating, 2) }}/5)</span>
                        </div>
                    </div>

                    <!-- Photo -->
                    @if($rating->image)
                        <div class="mb-4">
                            <p class="text-xs text-[#9E8C78] mb-2">Submitted Photo</p>
                                                <img src="{{ $rating->image }}" alt="Rating photo" class="rounded-lg max-h-40 object-cover w-full max-w-[220px]">
                        </div>
                    @endif

                    <!-- Owner Response -->
                    @if($rating->owner_response)
                        <div class="bg-[#F5EFE6] border-l-4 border-l-[#4A6741] p-4 rounded-lg mb-4">
                            <div class="flex items-center gap-2 mb-2">
                                <svg class="w-4 h-4 text-[#4A6741]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                                <span class="text-sm font-semibold text-[#4A6741]">Owner's Response</span>
                            </div>
                            <p class="text-sm italic text-[#3A2E22]">{{ $rating->owner_response }}</p>
                        </div>
                    @endif

                    <!-- Bottom Row -->
                    <div class="rating-actions flex justify-end">
                        <button type="button" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition-colors"
                            x-on:click="openDeleteModal({{ $rating->id }}, '{{ addslashes($rating->user->name ?? 'Unknown User') }}', '{{ addslashes($rating->establishment->name ?? 'Unknown Establishment') }}')">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Delete
                        </button>
                    </div>
                </div>
            @empty
                <div class="text-center py-8">
                    <p class="text-gray-400 text-lg">No ratings found for the selected period.</p>
                </div>
            @endforelse

            <!-- Pagination -->
            <div class="rating-pagination mt-6">
                {{ $ratings->links() }}
            </div>
        </div>
    </main>

    <!-- Hidden delete form -->
    <form id="deleteForm" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <!-- Delete Confirmation Modal (Coupon Promos exact style) -->
    <div x-show="deleteModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4" @keydown.escape="closeDeleteModal()" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" style="display: none;">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-40 backdrop-blur-sm" @click="closeDeleteModal()"></div>
        <!-- Modal Card -->
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full" @click.stop>
            <div class="p-8">
                <h2 class="text-2xl font-display font-bold text-[#3A2E22] mb-4">
                    Delete Rating?
                </h2>
                <p class="text-[#3A2E22] mb-6">
                    Are you sure you want to delete <span class="font-semibold" x-text="deleteUser + ' reviewed ' + deleteEstablishment"></span>? This action cannot be undone.
                </p>
                <div class="flex gap-3">
                    <button @click="closeDeleteModal()" class="flex-1 px-4 py-2 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="button" x-on:click="document.getElementById('deleteForm').action = '{{ url('admin/rating-moderation') }}/' + deleteId; document.getElementById('deleteForm').submit();" class="flex-1 px-4 py-2 rounded-lg bg-red-600 text-white font-medium hover:bg-red-700 transition-colors">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    @media (max-width: 767px) {
        .rating-moderation-page main {
            padding: 4.75rem 0.9rem 1rem !important;
        }

        .rating-moderation-page .filter-content {
            padding: 0.85rem !important;
            border-radius: 16px !important;
        }

        .rating-card {
            padding: 0.8rem !important;
            border-radius: 14px !important;
            margin-bottom: 0.8rem !important;
            overflow: hidden;
        }

        .rating-card-header {
            align-items: flex-start !important;
            gap: 0.6rem;
        }

        .rating-card-user {
            min-width: 0;
            flex: 1;
        }

        .rating-card-title {
            min-width: 0;
            font-size: 0.9rem;
            line-height: 1.25rem;
            overflow-wrap: anywhere;
        }

        .rating-card-date {
            flex-shrink: 0;
            font-size: 0.72rem !important;
            line-height: 1rem;
            text-align: right;
        }

        .rating-breakdown {
            padding: 0.7rem !important;
        }

        .rating-breakdown > .grid {
            grid-template-columns: 1fr !important;
            gap: 0.55rem !important;
        }

        .rating-breakdown-item {
            flex-wrap: wrap;
            align-items: center;
            row-gap: 0.2rem;
        }

        .rating-breakdown-item > span:first-child {
            min-width: 5.5rem;
            font-size: 0.78rem;
        }

        .rating-breakdown-item > .flex span {
            font-size: 1rem !important;
            line-height: 1;
        }

        .rating-breakdown-item > span:last-child {
            font-size: 0.72rem !important;
            line-height: 1;
        }

        .rating-overall {
            padding: 0.7rem !important;
        }

        .rating-overall .text-2xl {
            font-size: 1.1rem !important;
            line-height: 1;
        }

        .rating-overall .text-lg {
            font-size: 0.95rem !important;
            line-height: 1.25rem;
        }

        .rating-actions {
            justify-content: flex-end;
        }

        .rating-actions button {
            padding: 0.4rem 0.7rem !important;
            font-size: 0.72rem !important;
            border-radius: 10px;
        }

        .rating-pagination nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.75rem;
        }

        .rating-pagination nav > div {
            width: 100%;
        }

        .rating-pagination nav > div:first-child {
            display: flex;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .rating-pagination nav > div:first-child a,
        .rating-pagination nav > div:first-child span {
            flex: 1;
            display: inline-flex !important;
            justify-content: center;
            align-items: center;
            min-height: 2.35rem;
            border-radius: 0.65rem;
            border: 1px solid #D6CDC2 !important;
            background: #FFFFFF !important;
            color: #3A2E22 !important;
            font-weight: 600;
            font-size: 0.82rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            text-decoration: none;
        }

        .rating-pagination nav > div:first-child a:hover {
            background: #F6F1E8 !important;
        }

        .rating-pagination nav > div:last-child {
            display: none !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const content = document.querySelector('.filter-content');
        if (content) {
            content.classList.add('tab-panel');
        }
    });
</script>
@endpush

@endsection