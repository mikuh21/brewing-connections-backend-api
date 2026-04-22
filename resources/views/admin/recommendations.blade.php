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
                <span class="text-lg font-display font-bold">BrewHub</span>
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
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-l-[#D4AF37] p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[#9E8C78] text-sm font-medium">Highest Performing Category</p>
                        <p class="text-lg font-bold text-[#3A2E22] mt-1">{{ ucfirst(array_keys($overallAnalytics['averages'], max($overallAnalytics['averages']))[0]) }}</p>
                        <p class="text-sm text-[#9E8C78] mt-1">Avg {{ number_format(max($overallAnalytics['averages']), 1) }} ★</p>
                    </div>
                    <div class="w-12 h-12 bg-[#D4AF37]/15 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#D4AF37]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Needs Most Attention Card -->
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-l-[#800000] p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        @php
                            $needsAttentionKey = $overallAnalytics['needs_attention'] ?? null;
                            $needsAttentionAverage = filled($needsAttentionKey)
                                ? data_get($overallAnalytics, 'averages.' . $needsAttentionKey, 0)
                                : 0;
                        @endphp
                        <p class="text-[#9E8C78] text-sm font-medium">Needs Most Attention</p>
                        <p class="text-lg font-bold text-[#3A2E22] mt-1">{{ filled($needsAttentionKey) ? ucfirst($needsAttentionKey) : 'N/A' }}</p>
                        <p class="text-sm text-[#9E8C78] mt-1">Avg {{ number_format((float) $needsAttentionAverage, 1) }} ★</p>
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
            <p class="text-xs font-medium">Recommendation insights help café owners improve their customer experience and ratings.</p>
        </div>

        <!-- Recent Consumer Reviews -->
        <div class="mb-8">
            <h2 class="text-xl font-semibold text-[#3A2E22] mb-4 flex items-center gap-2">
                <span class="italic text-[#4A6741]">Recent</span> Consumer Ratings
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($recentReviews->take(2) as $review)
                <div class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow">
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
                            ★ {{ number_format(($review->taste_rating + $review->environment_rating + $review->cleanliness_rating + $review->service_rating) / 4, 1) }}
                        </div>
                    </div>
                    <div class="space-y-2 mb-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-[#9E8C78]">Taste</span>
                            <div class="flex gap-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <div class="w-2 h-2 rounded-full {{ $i <= $review->taste_rating ? 'bg-[#4A6741]' : 'bg-gray-200' }}"></div>
                                @endfor
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-[#9E8C78]">Environment</span>
                            <div class="flex gap-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <div class="w-2 h-2 rounded-full {{ $i <= $review->environment_rating ? 'bg-[#4A6741]' : 'bg-gray-200' }}"></div>
                                @endfor
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-[#9E8C78]">Cleanliness</span>
                            <div class="flex gap-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <div class="w-2 h-2 rounded-full {{ $i <= $review->cleanliness_rating ? 'bg-[#4A6741]' : 'bg-gray-200' }}"></div>
                                @endfor
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-[#9E8C78]">Service</span>
                            <div class="flex gap-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <div class="w-2 h-2 rounded-full {{ $i <= $review->service_rating ? 'bg-[#4A6741]' : 'bg-gray-200' }}"></div>
                                @endfor
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-[#EEE6DA] pt-3">
                        <p class="text-xs font-semibold text-[#6B5B4A] mb-1">Owner Response</p>
                        <p class="text-xs text-[#9E8C78] leading-relaxed">
                            {{ filled($review->owner_response ?? null) ? $review->owner_response : 'No owner response yet.' }}
                        </p>
                    </div>
                </div>
                @endforeach
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
                    $lowest = $overallAnalytics['needs_attention'];
                @endphp
                @foreach($categories as $key => $cat)
                <div class="recommendations-category-card {{ $key === 'overall' ? 'recommendations-category-card-overall' : '' }} bg-white rounded-xl shadow-sm p-4 flex-1 min-w-0 relative {{ $key === $lowest ? 'border-2 border-red-200' : '' }}">
                    @if($key === $lowest)
                    <div class="recommendations-attention-badge absolute -top-2 -right-2 bg-red-500 text-white px-2 py-1 rounded text-xs font-medium">
                        Needs Attention
                    </div>
                    @endif
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
            <div class="recommendations-opportunity bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="recommendations-opportunity-content flex items-center gap-3">
                    <svg class="w-6 h-6 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 15.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                    <div class="recommendations-opportunity-text">
                        <p class="text-sm font-medium text-red-800">Biggest Opportunity: {{ ucfirst($lowest) }}</p>
                        <p class="text-xs text-red-700">Improving {{ ucfirst($lowest) }} by 0.5 stars could boost overall ratings by ~{{ number_format((0.5 / 4) * 100, 1) }}%.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Prescriptive Analytics -->
        <div>
            <h2 class="text-xl font-semibold text-[#3A2E22] mb-4 flex items-center gap-2">
                <span class="italic text-[#4A6741]">Prescriptive</span> Insights
            </h2>
            <div class="bg-[#4A6741] text-white rounded-lg p-4 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium">Potential Rating Improvement</p>
                        <p class="text-2xl font-bold">+{{ number_format($recommendations->flatten()->sum('impact_score'), 1) }} stars</p>
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
            @endphp
            @foreach($priorityGroups as $p)
                @if(($priorityToShow === 'all' || $priorityToShow === $p) && isset($recommendations[$p]) && $recommendations[$p]->count() > 0)
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-[#3A2E22] mb-3 uppercase tracking-wide">
                        {{ ucfirst($p) }} Priority
                        <span class="inline-block ml-2 px-2 py-1 bg-{{ $p === 'high' ? 'red' : ($p === 'medium' ? 'yellow' : 'green') }}-100 text-{{ $p === 'high' ? 'red' : ($p === 'medium' ? 'yellow' : 'green') }}-800 text-xs font-medium rounded">
                            {{ $recommendations[$p]->count() }} Actions
                        </span>
                    </h3>
                    <div class="space-y-4">
                        @foreach($recommendations[$p] as $rec)
                        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 {{ $p === 'high' ? 'border-l-red-500' : ($p === 'medium' ? 'border-l-yellow-500' : 'border-l-green-500') }}">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs font-medium rounded">{{ ucfirst($rec->category) }}</span>
                                        <span class="text-sm text-[#9E8C78]">{{ $rec->establishment->name }}</span>
                                    </div>
                                    <h4 class="font-medium text-[#3A2E22] mb-1">{{ $rec->suggested_action }}</h4>
                                    <p class="text-sm text-[#9E8C78] mb-3">{{ $rec->insight }}</p>
                                    <div class="flex items-center gap-4 text-xs text-[#9E8C78]">
                                        <span class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            2-4 weeks
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Medium effort
                                        </span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-green-600 font-bold text-lg">+{{ number_format($rec->impact_score, 1) }} ★</div>
                                    <div class="text-xs text-[#9E8C78]">{{ $rec->based_on_reviews }} reviews</div>
                                </div>
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
    @media (max-width: 767px) {
        .recommendations-page main {
            padding: 4.75rem 0.9rem 1rem !important;
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