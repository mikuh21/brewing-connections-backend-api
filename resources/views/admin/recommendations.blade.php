@extends('layouts.app')

@section('title', 'Recommendations - BrewHub')

@section('content')
<div class="recommendations-page min-h-screen bg-[#F5F0E8] flex">
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
                {{ substr(auth()->user()->name, 0, 1) }}
            </div>
            <div>
                <div class="font-medium text-sm">{{ auth()->user()->name }}</div>
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

        <!-- Page Header -->
        <div class="flex items-center justify-between mb-8 sticky top-0 z-10 bg-[#F5F0E8]">
            <div>
                <h1 class="text-3xl font-display font-bold text-[#3A2E22] mb-1">
                    Recommendations
                </h1>
                <p class="text-[#9E8C78] text-sm font-medium">Experience-based journey and prescriptive insights</p>
            </div>
            <form method="POST" action="{{ route('admin.recommendations.refresh') }}">
                @csrf
                <button type="submit" class="bg-[#4A6741] text-white px-4 py-2 rounded-lg hover:bg-[#3A5A35] transition-colors text-sm font-medium">
                    Refresh Insights
                </button>
            </form>
        </div>

        @php
            $attentionThreshold = 3.0;
            $needsAttentionCandidates = collect($overallAnalytics['averages'] ?? [])
                ->map(fn ($avg) => round((float) $avg, 1))
                ->filter(fn ($avg) => $avg < $attentionThreshold);

            $needsAttentionKey = $needsAttentionCandidates->isNotEmpty()
                ? $needsAttentionCandidates->sort()->keys()->first()
                : null;

            $needsAttentionAverage = filled($needsAttentionKey)
                ? data_get($overallAnalytics, 'averages.' . $needsAttentionKey, 0)
                : null;
        @endphp

        <!-- Summary Cards Section -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Total Reviews Card -->
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-l-green-500 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[#9E8C78] text-sm font-medium">Total Ratings</p>
                        <p class="text-3xl font-bold text-[#3A2E22] mt-1">{{ $overallAnalytics['total_reviews'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Highest Performing Category Card -->
            @php
                $highestPerformingCafe = collect($establishments ?? [])
                    ->filter(function ($item) {
                        return is_numeric(data_get($item, 'analytics.overall_average'))
                            && (int) data_get($item, 'analytics.total_reviews', 0) > 0;
                    })
                    ->sortByDesc(fn ($item) => (float) data_get($item, 'analytics.overall_average'))
                    ->first();

                $highestCafeId = (int) data_get($highestPerformingCafe, 'establishment.id', 0);
                $highestCafeName = data_get($highestPerformingCafe, 'establishment.name', 'N/A');
                $highestCafeAverage = (float) data_get($highestPerformingCafe, 'analytics.overall_average', 0);
                $highestCafeTargetId = $highestCafeId > 0 ? 'cafe-insights-'.$highestCafeId : null;
                $highestCafeHref = $highestCafeTargetId
                    ? route('admin.recommendations', array_merge(request()->except('page'), ['priority' => 'all'])) . '#'.$highestCafeTargetId
                    : null;
            @endphp
            <a
                @if($highestCafeHref)
                    href="{{ $highestCafeHref }}"
                    data-scroll-target="{{ $highestCafeTargetId }}"
                @endif
                class="highest-performing-cafe-card block bg-white rounded-xl shadow-sm border-l-4 border-l-[#D4AF37] p-6 hover:shadow-md transition-shadow {{ $highestCafeTargetId ? 'cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#D4AF37]/40' : '' }}"
            >
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[#9E8C78] text-sm font-medium">Highest Performing Cafe</p>
                        <p class="text-lg font-bold text-[#3A2E22] mt-1">{{ $highestCafeName }}</p>
                        <p class="text-sm text-[#9E8C78] mt-1">Avg {{ number_format($highestCafeAverage, 1) }} ★</p>
                        @if($highestCafeTargetId)
                            <p class="text-[11px] text-[#9E8C78] mt-2">Tap to jump to this cafe's insights.</p>
                        @endif
                    </div>
                    <div class="w-12 h-12 bg-[#D4AF37]/15 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-[#D4AF37]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/>
                        </svg>
                    </div>
                </div>
            </a>

            <!-- Needs Most Attention Card -->
            <div class="bg-white rounded-xl shadow-sm border-l-4 {{ filled($needsAttentionKey) ? 'border-l-[#800000]' : 'border-l-[#9E8C78]' }} p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[#9E8C78] text-sm font-medium">Needs Most Attention</p>
                        <p class="text-lg font-bold text-[#3A2E22] mt-1">{{ filled($needsAttentionKey) ? ucfirst($needsAttentionKey) : 'No category needs attention' }}</p>
                        <p class="text-sm text-[#9E8C78] mt-1">
                            @if(filled($needsAttentionKey))
                                Avg {{ number_format((float) $needsAttentionAverage, 1) }} ★
                            @else
                                All category averages are at least {{ number_format($attentionThreshold, 1) }} ★
                            @endif
                        </p>
                    </div>
                    <div class="w-12 h-12 {{ filled($needsAttentionKey) ? 'bg-[#800000]/15' : 'bg-[#9E8C78]/15' }} rounded-lg flex items-center justify-center">
                        @if(filled($needsAttentionKey))
                            <svg class="w-6 h-6 text-[#800000]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.737 3h4.017c.163 0 .326.02.485.06L17 4m-7 10v5a2 2 0 002 2h.095c.5 0 .905-.405.905-.905 0-.714.211-1.412.608-2.006L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5"/>
                            </svg>
                        @else
                            <svg class="w-6 h-6 text-[#9E8C78]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 21a9 9 0 100-18 9 9 0 000 18z"/>
                            </svg>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Banner -->
        <div class="bg-[#4A6741]/80 text-white rounded-lg p-4 mb-8 flex items-center gap-3">
            <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
            </svg>
            <p class="text-xs font-medium">Recommendation insights help café owners improve their customer experience and ratings.</p>
        </div>

        <!-- Recent Consumer Reviews -->
        <div class="mb-8">
            <div class="recent-ratings-header flex items-center justify-between mb-4 gap-3">
                <h2 class="text-xl font-semibold text-[#3A2E22] flex items-center gap-2">
                    <span class="italic text-[#4A6741]">Recent</span> Consumer Ratings
                </h2>
                <div class="recent-ratings-controls flex items-center gap-2">
                    <button id="recent-ratings-prev" type="button" class="w-8 h-8 rounded-full border border-[#DCCFBD] text-[#6B5B4A] hover:bg-[#F8F4EE] disabled:opacity-40 disabled:cursor-not-allowed" aria-label="Previous rating">
                        <svg class="w-4 h-4 mx-auto" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m15 18-6-6 6-6"/>
                        </svg>
                    </button>
                    <span id="recent-ratings-counter" class="text-xs text-[#9E8C78] min-w-[56px] text-center">0 / 0</span>
                    <button id="recent-ratings-next" type="button" class="w-8 h-8 rounded-full border border-[#DCCFBD] text-[#6B5B4A] hover:bg-[#F8F4EE] disabled:opacity-40 disabled:cursor-not-allowed" aria-label="Next rating">
                        <svg class="w-4 h-4 mx-auto" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m9 18 6-6-6-6"/>
                        </svg>
                    </button>
                </div>
            </div>
            <div id="recent-ratings-slider" class="relative">
                @forelse($recentReviews as $review)
                <div class="recent-review-slide bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow {{ $loop->first ? '' : 'hidden' }}">
                    @php
                        $recentOverall = is_numeric($review->overall_rating ?? null)
                            ? (float) $review->overall_rating
                            : (float) (($review->taste_rating + $review->environment_rating + $review->cleanliness_rating + $review->service_rating) / 4);

                        $reviewImage = trim((string) ($review->image ?? ''));
                        $reviewImageUrl = null;
                        if ($reviewImage !== '') {
                            $reviewImageUrl = \Illuminate\Support\Str::startsWith($reviewImage, ['http://', 'https://', '/'])
                                ? $reviewImage
                                : asset('storage/' . ltrim($reviewImage, '/'));
                        }
                    @endphp
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-[#4A6741] rounded-full flex items-center justify-center text-white font-bold text-sm">
                                {{ substr($review->user_name, 0, 1) }}
                            </div>
                            <div>
                                <p class="font-medium text-[#3A2E22]">{{ $review->user_name }}</p>
                                <p class="text-sm text-[#9E8C78]">{{ $review->establishment_name }}</p>
                                <p class="text-xs text-[#9E8C78]">{{ \Carbon\Carbon::parse($review->created_at)->diffForHumans() }}</p>
                            </div>
                        </div>
                        <div class="bg-[#D4AF37] text-white px-2 py-1 rounded text-xs font-medium">
                            ★ {{ number_format($recentOverall, 1) }}
                        </div>
                    </div>
                    <div class="space-y-2 mb-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-[#9E8C78]">Taste</span>
                            <div class="flex gap-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-3.5 h-3.5 {{ $i <= (int) $review->taste_rating ? 'text-[#4A6741]' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    </svg>
                                @endfor
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-[#9E8C78]">Environment</span>
                            <div class="flex gap-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-3.5 h-3.5 {{ $i <= (int) $review->environment_rating ? 'text-[#4A6741]' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    </svg>
                                @endfor
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-[#9E8C78]">Cleanliness</span>
                            <div class="flex gap-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-3.5 h-3.5 {{ $i <= (int) $review->cleanliness_rating ? 'text-[#4A6741]' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    </svg>
                                @endfor
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-[#9E8C78]">Service</span>
                            <div class="flex gap-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-3.5 h-3.5 {{ $i <= (int) $review->service_rating ? 'text-[#4A6741]' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    </svg>
                                @endfor
                            </div>
                        </div>
                    </div>

                    @if($reviewImageUrl)
                        <div class="mb-4">
                            <p class="text-xs text-[#9E8C78] mb-2">Submitted Photo</p>
                            <a href="{{ $reviewImageUrl }}" target="_blank" rel="noopener noreferrer" class="block">
                                <img src="{{ $reviewImageUrl }}" alt="Submitted review photo" class="w-full max-w-[280px] h-40 object-cover rounded-lg border border-[#EDE4D8]">
                            </a>
                        </div>
                    @endif

                    <div class="border-t border-[#EEE6DA] pt-3">
                        <p class="text-xs font-semibold text-[#6B5B4A] mb-1">Owner Response</p>
                        <p class="text-xs text-[#9E8C78] leading-relaxed">
                            {{ filled($review->owner_response ?? null) ? $review->owner_response : 'No owner response yet.' }}
                        </p>
                    </div>
                </div>
                @empty
                <div class="bg-white rounded-xl shadow-sm p-6 text-sm text-[#9E8C78]">
                    No ratings yet.
                </div>
                @endforelse
            </div>
        </div>

        <!-- Experience Analytics by Category -->
        <div class="mb-8">
            <h2 class="text-xl font-semibold text-[#3A2E22] mb-4 flex items-center gap-2">
                <span class="italic text-[#4A6741]">Experience</span> Insights by Category
            </h2>
            <div class="recommendations-category-grid flex flex-wrap gap-4 mb-4">
                @php
                    $categories = [
                        'taste' => ['label' => 'Taste/Coffee Quality', 'avg' => $overallAnalytics['averages']['taste']],
                        'environment' => ['label' => 'Environment/Ambiance', 'avg' => $overallAnalytics['averages']['environment']],
                        'cleanliness' => ['label' => 'Cleanliness/Hygiene', 'avg' => $overallAnalytics['averages']['cleanliness']],
                        'service' => ['label' => 'Service/Staff', 'avg' => $overallAnalytics['averages']['service']],
                        'overall' => ['label' => 'Overall Rating', 'avg' => $overallAnalytics['overall_average']],
                    ];
                    $lowest = $needsAttentionKey;
                @endphp
                @foreach($categories as $key => $cat)
                @php
                    $categoryAvg = round((float) ($cat['avg'] ?? 0), 1);
                    $statusLabel = $categoryAvg < 3.0
                        ? 'Needs Attention'
                        : ($categoryAvg <= 4.0 ? 'Room for Improvement' : 'Performing Well');
                    $statusClass = $categoryAvg < 3.0
                        ? 'bg-red-500 text-white'
                        : ($categoryAvg <= 4.0 ? 'bg-amber-500 text-white' : 'bg-green-600 text-white');
                    $cardBorderClass = $categoryAvg < 3.0
                        ? 'border-2 border-red-200'
                        : ($categoryAvg <= 4.0 ? 'border-2 border-amber-200' : 'border border-[#EDE4D8]');
                @endphp
                <div class="recommendations-category-card {{ $key === 'overall' ? 'recommendations-category-card-overall' : '' }} bg-white rounded-xl shadow-sm p-4 flex-1 min-w-0 relative {{ $cardBorderClass }}">
                    <div class="recommendations-attention-badge absolute -top-2 -right-2 {{ $statusClass }} px-2 py-1 rounded text-xs font-medium">
                        {{ $statusLabel }}
                    </div>
                    <div class="text-center">
                        <p class="recommendations-category-label text-sm text-[#9E8C78] font-medium">{{ $cat['label'] }}</p>
                        <p class="recommendations-category-score text-2xl font-bold text-[#3A2E22] mt-1">{{ number_format($cat['avg'], 1) }}</p>
                        <div class="recommendations-category-stars flex justify-center mt-2">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-4 h-4 {{ $i <= round($cat['avg']) ? 'text-[#D4AF37]' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                            @endfor
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="recommendations-opportunity {{ filled($lowest) ? 'bg-red-50 border-red-200' : 'bg-green-50 border-green-200' }} border rounded-lg p-4">
                <div class="recommendations-opportunity-content flex items-center gap-3">
                    @if(filled($lowest))
                        <svg class="w-6 h-6 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 15.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    @else
                        <svg class="w-6 h-6 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    @endif
                    <div class="recommendations-opportunity-text">
                        @if(filled($lowest))
                            <p class="text-sm font-medium text-red-800">Biggest Opportunity: {{ ucfirst($lowest) }}</p>
                            <p class="text-xs text-red-700">Improving {{ ucfirst($lowest) }} by 0.5 stars could boost overall ratings by ~{{ number_format((0.5 / 4) * 100, 1) }}%.</p>
                        @else
                            <p class="text-sm font-medium text-green-800">All Core Rating Areas Are Performing Well</p>
                            <p class="text-xs text-green-700">No category currently needs attention based on current ratings.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Prescriptive Analytics -->
        <div>
            @php
                $allRecommendations = $recommendations->flatten();
                $totalImpact = (float) $allRecommendations->sum('impact_score');
                $totalActions = $allRecommendations->count();
                $averageImpact = $totalActions > 0 ? $totalImpact / $totalActions : 0;
                $highConfidenceActions = $allRecommendations->filter(fn ($rec) => (int) ($rec->based_on_reviews ?? 0) >= 20)->count();
            @endphp
            <h2 class="text-xl font-semibold text-[#3A2E22] mb-4 flex items-center gap-2">
                <span class="italic text-[#4A6741]">Prescriptive</span> Insights
            </h2>
            <div class="bg-[#4A6741] text-white rounded-lg p-4 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium">Potential Rating Improvement</p>
                        <p class="text-2xl font-bold">+{{ number_format($totalImpact, 1) }} stars</p>
                        <p class="text-xs text-white/80 mt-1">
                            {{ $totalActions }} actions • Avg +{{ number_format($averageImpact, 2) }} ★ per action • {{ $highConfidenceActions }} high-confidence actions
                        </p>
                        <p class="text-[11px] text-white/75 mt-2 leading-relaxed">
                            Priority rules: High is below 3.0, Medium is from 3.0 to 3.9, and Low is 4.0 and above.
                        </p>
                    </div>
                    <div class="flex gap-2">
                        @foreach(['high', 'medium', 'low'] as $p)
                            <span class="bg-white/20 px-3 py-1 rounded-full text-xs font-medium">
                                {{ $recommendations->get($p, collect())->count() }} {{ ucfirst($p) }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Filter Tabs -->
            <div class="mb-6 border-b border-gray-200 pb-4">
                <div class="flex gap-2">
                    @foreach(['all' => 'All', 'high' => 'High', 'medium' => 'Medium', 'low' => 'Low'] as $key => $label)
                        <a href="{{ route('admin.recommendations', array_merge(request()->except('page'), ['priority' => $key])) }}"
   class="filter-tab px-4 py-2 text-sm font-medium transition-colors {{ ($priority ?? 'all') === $key ? 'text-[#3B2F2F] border-b-4 border-[#3B2F2F] bg-[#F5F0E8]' : 'text-[#9E8C78]' }}"
   style="color: {{ ($priority ?? 'all') === $key ? '#3B2F2F' : '#9E8C78' }}; border-bottom: 3px solid {{ ($priority ?? 'all') === $key ? '#3B2F2F' : 'transparent' }};">
    {{ $label }}
</a>
                    @endforeach
                </div>
            </div>

            <div class="filter-content">
            @php
                $priorityToShow = $priority ?? 'all';
                $priorityGroups = ['high', 'medium', 'low'];
                $priorityRank = ['high' => 0, 'medium' => 1, 'low' => 2];
                $analyticsByEstablishment = collect($establishments ?? [])->mapWithKeys(function ($item) {
                    $establishmentId = data_get($item, 'establishment.id');
                    return [$establishmentId => data_get($item, 'analytics', [])];
                });

                $categoryDisplayName = [
                    'taste' => 'Taste',
                    'environment' => 'Environment',
                    'cleanliness' => 'Cleanliness',
                    'service' => 'Service',
                ];

                $dynamicCopyByCategory = [
                    'taste' => [
                        'high' => [
                            'action' => 'Upgrade bean quality and retrain baristas on grind, extraction, and consistency.',
                            'insight' => 'Taste ratings are currently low and are negatively affecting repeat visits.',
                        ],
                        'medium' => [
                            'action' => 'Standardize brew recipes and quality checks to improve taste consistency.',
                            'insight' => 'Taste ratings are moderate; consistency improvements can raise customer satisfaction.',
                        ],
                        'low' => [
                            'action' => 'Maintain current coffee quality and run periodic taste calibration sessions.',
                            'insight' => 'Taste ratings are strong; keep quality controls in place to sustain performance.',
                        ],
                    ],
                    'environment' => [
                        'high' => [
                            'action' => 'Prioritize ambiance fixes: seating comfort, lighting, and noise control.',
                            'insight' => 'Environment ratings are currently low and may reduce customer dwell time.',
                        ],
                        'medium' => [
                            'action' => 'Improve ambiance touchpoints such as cleanliness visuals, music level, and layout flow.',
                            'insight' => 'Environment ratings are average and can improve with targeted ambiance adjustments.',
                        ],
                        'low' => [
                            'action' => 'Preserve the current atmosphere and monitor peak-hour comfort and crowd flow.',
                            'insight' => 'Environment ratings are healthy; continue maintaining guest comfort standards.',
                        ],
                    ],
                    'cleanliness' => [
                        'high' => [
                            'action' => 'Strengthen cleaning SOPs and increase audit frequency across service and prep areas.',
                            'insight' => 'Cleanliness ratings are low and require immediate corrective action.',
                        ],
                        'medium' => [
                            'action' => 'Introduce shift-based hygiene checklists and visible cleaning checkpoints.',
                            'insight' => 'Cleanliness ratings are moderate; tighter routines can quickly improve confidence.',
                        ],
                        'low' => [
                            'action' => 'Maintain hygiene discipline and continue routine cleanliness verification.',
                            'insight' => 'Cleanliness ratings are strong; current standards are performing well.',
                        ],
                    ],
                    'service' => [
                        'high' => [
                            'action' => 'Run focused service recovery coaching and reduce queue/response times.',
                            'insight' => 'Service ratings are currently low and are impacting customer experience.',
                        ],
                        'medium' => [
                            'action' => 'Refine greeting, response-time, and escalation protocols for staff.',
                            'insight' => 'Service ratings are average; structured coaching can improve outcomes.',
                        ],
                        'low' => [
                            'action' => 'Keep service quality stable with ongoing feedback and recognition loops.',
                            'insight' => 'Service ratings are strong; maintain consistency through regular coaching.',
                        ],
                    ],
                ];

                $groupedRecommendations = $recommendations
                    ->flatten()
                    ->groupBy('establishment_id')
                    ->map(function ($establishmentRecommendations, $establishmentId) use ($priorityRank, $analyticsByEstablishment) {
                        $sorted = $establishmentRecommendations
                            ->sort(function ($left, $right) use ($priorityRank, $analyticsByEstablishment, $establishmentId) {
                                $leftPriority = $priorityRank[strtolower((string) ($left->priority ?? 'low'))] ?? 99;
                                $rightPriority = $priorityRank[strtolower((string) ($right->priority ?? 'low'))] ?? 99;

                                if ($leftPriority !== $rightPriority) {
                                    return $leftPriority <=> $rightPriority;
                                }

                                $leftCategoryKey = strtolower((string) ($left->category ?? ''));
                                $rightCategoryKey = strtolower((string) ($right->category ?? ''));
                                $leftAverage = round((float) data_get($analyticsByEstablishment, $establishmentId.'.averages.'.$leftCategoryKey, 0), 1);
                                $rightAverage = round((float) data_get($analyticsByEstablishment, $establishmentId.'.averages.'.$rightCategoryKey, 0), 1);

                                if ($leftAverage !== $rightAverage) {
                                    return $leftAverage <=> $rightAverage;
                                }

                                return $leftCategoryKey <=> $rightCategoryKey;
                            })
                            ->values();

                        $cardPriority = strtolower((string) data_get($sorted->first(), 'priority', 'low'));

                        return [
                            'priority' => in_array($cardPriority, ['high', 'medium', 'low'], true) ? $cardPriority : 'low',
                            'establishment' => $sorted->first()?->establishment,
                            'recommendations' => $sorted,
                        ];
                    })
                    ->groupBy('priority');
            @endphp
            @foreach($priorityGroups as $p)
                @php
                    $cafeCards = $groupedRecommendations->get($p, collect());
                @endphp
                @if(($priorityToShow === 'all' || $priorityToShow === $p) && $cafeCards->count() > 0)
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-[#3A2E22] mb-3 uppercase tracking-wide">
                        {{ ucfirst($p) }} Priority
                        <span class="inline-block ml-2 px-2 py-1 bg-{{ $p === 'high' ? 'red' : ($p === 'medium' ? 'yellow' : 'green') }}-100 text-{{ $p === 'high' ? 'red' : ($p === 'medium' ? 'yellow' : 'green') }}-800 text-xs font-medium rounded">
                            {{ $cafeCards->count() }} Cafes
                        </span>
                    </h3>
                    <div class="space-y-4">
                        @foreach($cafeCards as $card)
                        @php
                            $cardPriority = $card['priority'];
                            $priorityLabel = $cardPriority === 'high'
                                ? 'High Priority'
                                : ($cardPriority === 'medium' ? 'Medium Priority' : 'Low Priority');
                            $priorityBadgeClass = $cardPriority === 'high'
                                ? 'bg-red-100 text-red-700 border-red-300'
                                : ($cardPriority === 'medium' ? 'bg-amber-100 text-amber-700 border-amber-300' : 'bg-green-100 text-green-700 border-green-300');
                        @endphp
                        <div id="cafe-insights-{{ data_get($card, 'establishment.id') }}" class="recommendation-cafe-card bg-white rounded-2xl shadow-sm p-4 md:p-6 border border-[#EDE4D8]" data-cafe-insights-card>
                            <div class="recommendation-cafe-card__header flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="text-lg font-semibold text-[#3A2E22]">{{ data_get($card, 'establishment.name', 'Unknown Cafe') }}</p>
                                </div>
                                <span class="inline-flex w-fit items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $priorityBadgeClass }}">
                                    {{ $priorityLabel }}
                                </span>
                            </div>

                            <div class="recommendation-cafe-grid mt-4 grid grid-cols-1 lg:grid-cols-2 gap-4">
                                @foreach(data_get($card, 'recommendations', collect()) as $rec)
                                @php
                                    $categoryKey = strtolower((string) $rec->category);
                                    $categoryAverage = round((float) data_get($analyticsByEstablishment, $rec->establishment_id.'.averages.'.$categoryKey, 0), 1);
                                    $priorityKey = strtolower((string) ($rec->priority ?? $cardPriority));
                                    $categoryPriorityLabel = $priorityKey === 'high'
                                        ? 'High Priority'
                                        : ($priorityKey === 'medium' ? 'Medium Priority' : 'Low Priority');
                                    $categoryPriorityClass = $priorityKey === 'high'
                                        ? 'bg-red-100 text-red-700 border-red-300'
                                        : ($priorityKey === 'medium' ? 'bg-amber-100 text-amber-700 border-amber-300' : 'bg-green-100 text-green-700 border-green-300');

                                    $severityCopy = data_get($dynamicCopyByCategory, $categoryKey.'.'.$priorityKey, []);
                                    $dynamicAction = data_get($severityCopy, 'action', $rec->suggested_action);
                                    $dynamicInsight = data_get($severityCopy, 'insight', $rec->insight);

                                    $reviewsCount = (int) ($rec->based_on_reviews ?? 0);
                                    $timelineLabel = $priorityKey === 'high'
                                        ? '1-2 weeks target'
                                        : ($priorityKey === 'medium' ? '2-4 weeks target' : '4-8 weeks target');

                                    $baseEffort = match ($categoryKey) {
                                        'cleanliness' => 1,
                                        'taste', 'service' => 2,
                                        'environment' => 3,
                                        default => 2,
                                    };
                                    $priorityBoost = $priorityKey === 'high' ? 1 : 0;
                                    $effortScore = max(1, min(3, $baseEffort + $priorityBoost));
                                    $effortLabel = $effortScore === 1 ? 'Low effort' : ($effortScore === 2 ? 'Medium effort' : 'High effort');
                                    $confidenceLabel = $reviewsCount >= 20
                                        ? 'High confidence'
                                        : ($reviewsCount >= 8 ? 'Medium confidence' : 'Low confidence');
                                    $prescriptiveActions = [
                                        $dynamicAction,
                                        sprintf('Execution window: %s', $timelineLabel),
                                        sprintf('Estimated effort: %s', $effortLabel),
                                    ];
                                @endphp
                                <section class="recommendation-category-panel rounded-xl border border-[#EFE5D8] bg-[#FCFAF6] p-4">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                            <p class="text-sm font-semibold text-[#3A2E22]">{{ data_get($categoryDisplayName, $categoryKey, ucfirst($rec->category)) }}</p>
                                            <div class="flex flex-wrap items-center gap-2 mt-1">
                                                <div class="flex items-center gap-0.5">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <svg class="w-3.5 h-3.5 {{ $i <= round($categoryAverage) ? 'text-amber-400' : 'text-gray-300' }}" viewBox="0 0 24 24" fill="currentColor">
                                                            <path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                                        </svg>
                                                    @endfor
                                                </div>
                                                <span class="text-[11px] text-[#9E8C78]">{{ number_format($categoryAverage, 2) }}/5</span>
                                            </div>
                                        </div>
                                        <span class="inline-flex w-fit items-center rounded-full border px-2.5 py-1 text-[11px] font-semibold {{ $categoryPriorityClass }}">
                                            {{ $categoryPriorityLabel }}
                                        </span>
                                    </div>

                                    <div class="mt-3">
                                        <p class="text-[11px] font-semibold uppercase tracking-wide text-[#7D6B57]">Descriptive Recommendation</p>
                                        <p class="text-sm text-[#6B5B4A] mt-1 leading-relaxed">{{ $dynamicInsight }}</p>
                                    </div>

                                    <div class="mt-3">
                                        <p class="text-[11px] font-semibold uppercase tracking-wide text-[#7D6B57]">Prescriptive Insights</p>
                                        <ul class="mt-1 space-y-1.5">
                                            @foreach($prescriptiveActions as $action)
                                                <li class="text-sm text-[#6B5B4A] leading-relaxed flex items-start gap-2">
                                                    <span class="mt-1 inline-block w-1.5 h-1.5 rounded-full bg-[#4A6741]"></span>
                                                    <span>{{ $action }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>

                                    <p class="text-[11px] text-[#9E8C78] mt-3 leading-relaxed">
                                        Evidence: {{ $reviewsCount }} reviews • Current {{ data_get($categoryDisplayName, $categoryKey, ucfirst($categoryKey)) }} {{ number_format($categoryAverage, 1) }}/5 • {{ $confidenceLabel }} • Potential +{{ number_format((float) $rec->impact_score, 1) }} ★
                                    </p>
                                </section>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            @endforeach
            </div>
        </div>
    </main>
</div>

@push('styles')
<style>
    .recommendation-cafe-card.is-scroll-target {
        box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.28), 0 12px 32px rgba(58, 46, 34, 0.12);
        border-color: rgba(212, 175, 55, 0.7) !important;
        transition: box-shadow 0.3s ease, border-color 0.3s ease;
    }

    @media (max-width: 767px) {
        .recommendations-page main {
            padding: 4.75rem 0.9rem 1rem !important;
        }

        .recent-ratings-header {
            flex-wrap: wrap;
            align-items: flex-start;
            gap: 0.4rem;
        }

        .recent-ratings-header h2 {
            font-size: 1.05rem !important;
            line-height: 1.3rem !important;
        }

        .recent-ratings-controls {
            margin-left: auto;
        }

        #recent-ratings-counter {
            min-width: 52px !important;
            font-size: 0.68rem !important;
        }

        .recent-review-slide {
            padding: 0.9rem !important;
        }

        .recent-review-slide .w-10.h-10 {
            width: 2.1rem !important;
            height: 2.1rem !important;
            font-size: 0.75rem !important;
        }

        .recent-review-slide .text-sm {
            font-size: 0.78rem !important;
        }

        .recent-review-slide .text-xs {
            font-size: 0.67rem !important;
        }

        .recent-review-slide .space-y-2 {
            margin-top: 0.55rem;
            margin-bottom: 0.55rem;
        }

        .recent-review-slide svg.w-3\.5.h-3\.5 {
            width: 0.78rem !important;
            height: 0.78rem !important;
        }

        .recommendations-category-grid {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.6rem !important;
        }

        .recommendations-category-card {
            width: auto !important;
            min-width: 0 !important;
            padding: 0.65rem !important;
            border-radius: 12px !important;
        }

        .recommendations-category-card-overall {
            grid-column: 1 / -1;
        }

        .recommendations-category-card-overall > .text-center {
            text-align: center;
        }

        .recommendations-attention-badge {
            position: static !important;
            display: inline-flex;
            margin-bottom: 0.45rem;
            border-radius: 0.45rem;
            font-size: 0.62rem;
            line-height: 1;
            padding: 0.28rem 0.42rem !important;
        }

        .recommendations-category-label {
            font-size: 0.68rem !important;
            line-height: 1.05rem !important;
            min-height: 2.1rem;
            overflow-wrap: anywhere;
        }

        .recommendations-category-score {
            font-size: 1.35rem !important;
            line-height: 1.1 !important;
        }

        .recommendations-category-stars {
            margin-top: 0.35rem !important;
            gap: 0.08rem;
        }

        .recommendations-category-stars svg {
            width: 0.72rem !important;
            height: 0.72rem !important;
        }

        .recommendations-opportunity {
            padding: 0.7rem !important;
            border-radius: 12px;
        }

        .recommendations-opportunity-content {
            align-items: flex-start !important;
            gap: 0.55rem !important;
        }

        .recommendations-opportunity-content svg {
            width: 1rem !important;
            height: 1rem !important;
            margin-top: 0.1rem;
        }

        .recommendations-opportunity-text p:first-child {
            font-size: 0.76rem !important;
            line-height: 1.1rem;
        }

        .recommendations-opportunity-text p:last-child {
            font-size: 0.68rem !important;
            line-height: 1.05rem;
            overflow-wrap: anywhere;
        }

        .recommendation-cafe-card {
            padding: 0.9rem !important;
            border-radius: 14px !important;
        }

        .recommendation-cafe-card__header {
            gap: 0.55rem !important;
        }

        .recommendation-cafe-card__header p.text-lg {
            font-size: 0.95rem !important;
            line-height: 1.3rem !important;
        }

        .recommendation-cafe-card__header p.text-sm {
            font-size: 0.72rem !important;
            line-height: 1.1rem !important;
        }

        .recommendation-cafe-grid {
            grid-template-columns: 1fr !important;
            gap: 0.75rem !important;
            margin-top: 0.8rem !important;
        }

        .recommendation-category-panel {
            padding: 0.8rem !important;
            border-radius: 12px !important;
        }

        .recommendation-category-panel .text-sm {
            font-size: 0.76rem !important;
            line-height: 1.15rem !important;
        }

        .recommendation-category-panel ul {
            margin-top: 0.45rem !important;
        }

        .recommendation-category-panel li {
            gap: 0.42rem !important;
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

        const slides = Array.from(document.querySelectorAll('.recent-review-slide'));
        const prevButton = document.getElementById('recent-ratings-prev');
        const nextButton = document.getElementById('recent-ratings-next');
        const counter = document.getElementById('recent-ratings-counter');

        if (!prevButton || !nextButton || !counter) {
            return;
        }

        if (slides.length === 0) {
            counter.textContent = '0 / 0';
            prevButton.disabled = true;
            nextButton.disabled = true;
            return;
        }

        let activeIndex = slides.findIndex(slide => !slide.classList.contains('hidden'));
        if (activeIndex < 0) {
            activeIndex = 0;
        }

        function renderSlideState() {
            slides.forEach((slide, index) => {
                slide.classList.toggle('hidden', index !== activeIndex);
            });

            counter.textContent = `${activeIndex + 1} / ${slides.length}`;
            prevButton.disabled = activeIndex === 0;
            nextButton.disabled = activeIndex === slides.length - 1;
        }

        prevButton.addEventListener('click', function () {
            if (activeIndex <= 0) {
                return;
            }

            activeIndex -= 1;
            renderSlideState();
        });

        nextButton.addEventListener('click', function () {
            if (activeIndex >= slides.length - 1) {
                return;
            }

            activeIndex += 1;
            renderSlideState();
        });

        renderSlideState();

        function focusCafeInsightsCard(targetId) {
            if (!targetId) {
                return false;
            }

            const targetCard = document.getElementById(targetId);
            if (!targetCard) {
                return false;
            }

            targetCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
            targetCard.classList.add('is-scroll-target');
            window.setTimeout(function () {
                targetCard.classList.remove('is-scroll-target');
            }, 2200);

            if (window.location.hash !== '#' + targetId) {
                history.replaceState(null, '', '#' + targetId);
            }

            return true;
        }

        document.querySelectorAll('[data-scroll-target]').forEach(function (link) {
            link.addEventListener('click', function (event) {
                const targetId = link.getAttribute('data-scroll-target');
                if (!targetId) {
                    return;
                }

                if (focusCafeInsightsCard(targetId)) {
                    event.preventDefault();
                }
            });
        });

        const initialHash = window.location.hash.replace('#', '');
        if (initialHash) {
            window.setTimeout(function () {
                focusCafeInsightsCard(initialHash);
            }, 120);
        }
    });
</script>
@endpush

@endsection