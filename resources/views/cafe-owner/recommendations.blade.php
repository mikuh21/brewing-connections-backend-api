@extends('cafe-owner.layouts.app')

@php
    $title = 'Recommendations';

    $avgValue = (float) ($avgRating ?? $averageRating ?? 0);
    $avgFormatted = number_format($avgValue, 2);

    $distribution = $ratingDistribution ?? [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
    $scoreCounts = [
        1 => (int) ($distribution[1] ?? 0),
        2 => (int) ($distribution[2] ?? 0),
        3 => (int) ($distribution[3] ?? 0),
        4 => (int) ($distribution[4] ?? 0),
        5 => (int) ($distribution[5] ?? 0),
    ];
    $ratingSeries = [
        $scoreCounts[1],
        $scoreCounts[2],
        $scoreCounts[3],
        $scoreCounts[4],
        $scoreCounts[5],
    ];
    $ratingDistributionData = [
        'labels' => ['1 Star', '2 Stars', '3 Stars', '4 Stars', '5 Stars'],
        'series' => $ratingSeries,
    ];

    $monthlyLabels = data_get($monthlyRecommendationCount ?? [], 'labels', []);
    $monthlySeries = data_get($monthlyRecommendationCount ?? [], 'series', []);
    $monthlyRecommendationData = [
        'labels' => $monthlyLabels,
        'series' => $monthlySeries,
    ];
    $thisMonthRecos = (int) ($currentWeekRecommendationCount ?? 0);
    $weeklyHasRatings = (bool) ($weeklyHasRatings ?? false);
    $insightsDateLabel = (string) ($insightsDateLabel ?? (now()->startOfWeek(\Carbon\Carbon::MONDAY)->format('M d, Y').' - '.now()->endOfWeek(\Carbon\Carbon::SUNDAY)->format('M d, Y')));

    $total = max(1, (int) ($totalReviews ?? 0));
    $high = (int) ($highCount ?? 0);
    $medium = (int) ($mediumCount ?? 0);
    $low = (int) ($lowCount ?? 0);

    $highPct = round(($high / $total) * 100);
    $mediumPct = round(($medium / $total) * 100);
    $lowPct = round(($low / $total) * 100);

    $priorityRaw = strtolower((string) ($priorityLevel ?? ''));
    $priorityKey = str_contains($priorityRaw, 'high')
        ? 'high'
        : (str_contains($priorityRaw, 'medium') ? 'medium' : 'low');

    $priorityCardClass = $priorityKey === 'high'
        ? 'bg-red-100 text-red-700 border-red-300'
        : ($priorityKey === 'medium' ? 'bg-amber-100 text-amber-700 border-amber-300' : 'bg-green-100 text-green-700 border-green-300');

    $priorityLabel = $priorityKey === 'high'
        ? 'High Priority'
        : ($priorityKey === 'medium' ? 'Medium Priority' : 'Low Priority');

    $priorityDesc = $priorityKey === 'high'
        ? 'Needs immediate attention'
        : ($priorityKey === 'medium' ? 'Room for improvement' : 'Performing well');

    $categoryLabels = [
        'taste' => 'Taste',
        'environment' => 'Environment',
        'cleanliness' => 'Cleanliness',
        'service' => 'Service',
    ];

    $categoryAveragesMap = collect($categoryLabels)
        ->mapWithKeys(function ($label, $key) use ($categoryAverages) {
            return [$key => (float) data_get($categoryAverages ?? [], $key, 0)];
        })
        ->toArray();

    $priorityCategoryKeys = collect($priorityCategories ?? [$priorityCategory ?? 'taste'])
        ->map(fn ($key) => strtolower((string) $key))
        ->filter(fn ($key) => array_key_exists($key, $categoryLabels))
        ->values();

    if ($priorityCategoryKeys->isEmpty()) {
        $priorityCategoryKeys = collect($categoryAveragesMap)
            ->sort()
            ->keys()
            ->take(1)
            ->values();
    }

    $priorityCategoryLabel = $priorityCategoryKeys
        ->map(fn ($key) => $categoryLabels[$key] ?? ucfirst($key))
        ->join(', ');

    $priorityCategorySummary = $priorityCategoryKeys->count() > 1
        ? 'Priority Categories'
        : 'Priority Category';

    $priorityCategoryFieldMap = [
        'taste' => 'taste_rating',
        'environment' => 'environment_rating',
        'cleanliness' => 'cleanliness_rating',
        'service' => 'service_rating',
    ];

    $priorityTargetReview = collect($latestReviews ?? collect())
        ->sortBy(function ($review) use ($priorityCategoryKeys, $priorityCategoryFieldMap) {
            return $priorityCategoryKeys->map(function ($key) use ($review, $priorityCategoryFieldMap) {
                $field = $priorityCategoryFieldMap[$key] ?? 'taste_rating';
                return (int) data_get($review, $field, 0);
            })->min() ?? 0;
        })
        ->first();
    $priorityTargetReviewId = (int) data_get($priorityTargetReview, 'review_id', 0);

    $reviewsForPdf = collect($latestReviews ?? collect())->map(function ($r) {
        return [
            'name' => data_get($r, 'user_name', 'Anonymous'),
            'date' => data_get($r, 'created_at') ? \Illuminate\Support\Carbon::parse(data_get($r, 'created_at'))->format('M d, Y') : '-',
            'score' => round((float) data_get($r, 'score', 0), 1),
            'taste' => (int) data_get($r, 'taste_rating', 0),
            'environment' => (int) data_get($r, 'environment_rating', 0),
            'cleanliness' => (int) data_get($r, 'cleanliness_rating', 0),
            'service' => (int) data_get($r, 'service_rating', 0),
        ];
    })->values();
@endphp

@section('title', 'Recommendations - BrewHub')

@section('content')
<div class="cafe-recommendations-page">
<div class="mb-8">
    <div class="cafe-reco-header flex items-start justify-between gap-4">
        <div class="cafe-reco-heading">
            <h1 class="text-3xl font-display font-bold text-[#3A2E22] mb-1">
                Recommendation <span class="italic text-[#4A6741]">Insights</span>
            </h1>
            <p class="text-[#9E8C78] text-sm font-medium">Ratings and actionable quality signals for your cafe</p>
        </div>
        <button type="button" onclick="downloadRecommendationsReport()" title="Download Analytics Report" class="cafe-reco-download-btn inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#4A6741] text-white hover:bg-[#3f5b38] transition-colors shadow-sm text-sm font-medium">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17v3a2 2 0 002 2h14a2 2 0 002-2v-3"/></svg>
            <span class="hidden sm:inline">Download Report</span>
        </button>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-2xl shadow-sm border border-[#E5DDD0] p-6">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-[#9E8C78] text-sm font-medium">Average Rating</p>
                <p class="text-3xl font-bold text-[#3A2E22] mt-2">{{ $avgFormatted }}</p>
            </div>
            <div class="w-11 h-11 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-[#E5DDD0] p-6">
        <p class="text-[#9E8C78] text-sm font-medium">Total Ratings</p>
        <p class="text-3xl font-bold text-[#3A2E22] mt-2">{{ (int) ($totalReviews ?? 0) }}</p>
        <p class="text-xs text-[#9E8C78] mt-2">All-time customer feedback entries</p>
    </div>

    <div id="thisMonthInsightsCard" class="bg-white rounded-2xl shadow-sm border border-[#E5DDD0] p-6 cursor-pointer transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-[#4A6741]/50" role="button" tabindex="0" aria-label="Jump to descriptive and prescriptive insights">
        <div class="flex items-start justify-between gap-2">
            <p id="insightsOverviewTitle" class="text-[#9E8C78] text-sm font-medium">This Week's Insights</p>
            <select id="insightsPeriodFilterCard" class="h-8 rounded-lg border border-[#DCCFBD] bg-white px-2.5 text-[11px] text-[#6B5B4A] focus:outline-none focus:ring-2 focus:ring-[#4A6741]/30" aria-label="Filter insights period">
                <option value="all">All Time</option>
                <option value="month">This Month</option>
                <option value="week" selected>This Week</option>
            </select>
        </div>
        @if($weeklyHasRatings)
            <p id="insightsOverviewCount" class="text-3xl font-bold text-[#3A2E22] mt-2">{{ $thisMonthRecos }}</p>
            <p id="insightsOverviewSubtext" class="text-xs text-[#9E8C78] mt-2">Generated prescriptive insights this week</p>
        @else
            <p id="insightsOverviewCount" class="text-3xl font-bold text-[#C6B8A6] mt-2">--</p>
            <p id="insightsOverviewSubtext" class="text-xs text-[#9E8C78] mt-2">No ratings received in this week yet.</p>
        @endif
        <p id="insightsOverviewDate" class="text-[11px] text-[#9E8C78] mt-1">{{ $insightsDateLabel }}</p>
        <p class="text-[11px] text-[#9E8C78] mt-2">Click to view descriptive and prescriptive insights</p>
    </div>

    <div id="priorityStatusCard" data-target-review-id="{{ $weeklyHasRatings ? $priorityTargetReviewId : 0 }}" data-has-ratings="{{ $weeklyHasRatings ? '1' : '0' }}" class="bg-white rounded-2xl shadow-sm border border-[#E5DDD0] p-6 {{ $weeklyHasRatings ? 'cursor-pointer transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-[#4A6741]/50' : '' }}" role="{{ $weeklyHasRatings ? 'button' : 'region' }}" tabindex="{{ $weeklyHasRatings ? '0' : '-1' }}" aria-label="{{ $weeklyHasRatings ? 'Jump to priority review' : 'Priority status summary' }}">
        <p class="text-[#9E8C78] text-sm font-medium mb-3">Priority Status</p>
        @if($weeklyHasRatings)
            <p class="text-sm text-[#6B5B4A] mb-2">{{ $priorityCategorySummary }}: <span class="font-semibold text-[#3A2E22]">{{ $priorityCategoryLabel }}</span></p>
            <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $priorityCardClass }}">
                {{ $priorityLabel }}
            </span>
            <p class="text-sm text-[#6B5B4A] mt-3">{{ $priorityDesc }}</p>
            @if($priorityCategoryKeys->count() > 1)
                <p class="text-[11px] text-[#9E8C78] mt-2 leading-relaxed">These categories are tied at the same weakest score, so all of them are highlighted in the overview.</p>
            @endif
            <p class="text-[11px] text-[#9E8C78] mt-2">Click to jump to a matching recent review</p>
        @else
            <p class="text-sm text-[#6B5B4A]">No ratings received in this week yet.</p>
            <p class="text-[11px] text-[#9E8C78] mt-2">Priority category and level appear once the week has at least one rating.</p>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-2xl shadow-sm border border-[#E5DDD0] p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-display font-bold text-[#3A2E22]">Rating Distribution</h2>
            <p class="text-xs text-[#9E8C78]">1 to 5 stars</p>
        </div>
        <div class="h-72">
            <canvas id="ratingDistributionChart"></canvas>
        </div>
    </div>

    <div id="monthlyRecommendationsSection" class="bg-white rounded-2xl shadow-sm border border-[#E5DDD0] p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-xl font-display font-bold text-[#3A2E22]">Monthly Recommendation Sets</h2>
                <p class="text-[11px] text-[#9E8C78] mt-1">Each point counts stored recommendation generations.</p>
            </div>
            <p class="text-xs text-[#9E8C78]">Last 6 months</p>
        </div>
        <div class="h-72">
            <canvas id="monthlyRecommendationsChart"></canvas>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6 items-stretch">
    <div class="bg-white rounded-2xl shadow-sm border border-[#E5DDD0] p-6 h-full">
        <h2 class="text-xl font-display font-bold text-[#3A2E22] mb-5">Priority Breakdown</h2>

        <div class="space-y-5">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2 text-sm font-medium text-[#3A2E22]">
                        <span class="inline-block w-2.5 h-2.5 rounded-full bg-red-500"></span>
                        <span>High Priority</span>
                    </div>
                    <p class="text-sm text-[#6B5B4A]">{{ $high }} reviews (score 1-2)</p>
                </div>
                <div class="w-full h-2 rounded-full bg-red-100 overflow-hidden">
                    <div class="h-full bg-red-500 rounded-full" style="width: {{ $highPct }}%"></div>
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2 text-sm font-medium text-[#3A2E22]">
                        <span class="inline-block w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                        <span>Medium Priority</span>
                    </div>
                    <p class="text-sm text-[#6B5B4A]">{{ $medium }} reviews (score 3)</p>
                </div>
                <div class="w-full h-2 rounded-full bg-amber-100 overflow-hidden">
                    <div class="h-full bg-amber-500 rounded-full" style="width: {{ $mediumPct }}%"></div>
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2 text-sm font-medium text-[#3A2E22]">
                        <span class="inline-block w-2.5 h-2.5 rounded-full bg-[#4A6741]"></span>
                        <span>Low Priority</span>
                    </div>
                    <p class="text-sm text-[#6B5B4A]">{{ $low }} reviews (score 4-5)</p>
                </div>
                <div class="w-full h-2 rounded-full bg-green-100 overflow-hidden">
                    <div class="h-full bg-[#4A6741] rounded-full" style="width: {{ $lowPct }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-[#E5DDD0] p-6 h-full">
        <div class="flex items-center justify-between gap-3 mb-5">
            <h2 class="text-xl font-display font-bold text-[#3A2E22]">Recent Ratings</h2>
            <div class="flex items-center gap-2">
                <button id="recentRatingsPrev" type="button" class="w-8 h-8 rounded-full border border-[#DCCFBD] text-[#6B5B4A] hover:bg-[#F8F4EE] disabled:opacity-40 disabled:cursor-not-allowed" aria-label="Previous rating">
                    <svg class="w-4 h-4 mx-auto" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m15 18-6-6 6-6"/>
                    </svg>
                </button>
                <span id="recentRatingsCounter" class="text-xs text-[#9E8C78] min-w-[60px] text-center">1 / 1</span>
                <button id="recentRatingsNext" type="button" class="w-8 h-8 rounded-full border border-[#DCCFBD] text-[#6B5B4A] hover:bg-[#F8F4EE] disabled:opacity-40 disabled:cursor-not-allowed" aria-label="Next rating">
                    <svg class="w-4 h-4 mx-auto" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m9 18 6-6-6-6"/>
                    </svg>
                </button>
            </div>
        </div>

        <div id="recentRatingsList" class="space-y-4">
            @forelse(($latestReviews ?? collect()) as $review)
                @php
                    $reviewScore = (float) data_get($review, 'score', 0);
                    $reviewBucket = max(1, min(5, (int) round($reviewScore)));
                    $reviewPriority = $reviewBucket <= 2 ? 'high' : ($reviewBucket === 3 ? 'medium' : 'low');
                    $reviewBadgeClass = $reviewPriority === 'high'
                        ? 'bg-red-100 text-red-700 border-red-300'
                        : ($reviewPriority === 'medium' ? 'bg-amber-100 text-amber-700 border-amber-300' : 'bg-green-100 text-green-700 border-green-300');
                    $reviewBadgeLabel = $reviewPriority === 'high' ? 'High' : ($reviewPriority === 'medium' ? 'Medium' : 'Low');
                    $reviewName = (string) data_get($review, 'user_name', 'Anonymous');
                    $initial = strtoupper(substr($reviewName, 0, 1));
                    $reviewDate = data_get($review, 'created_at')
                        ? \Illuminate\Support\Carbon::parse(data_get($review, 'created_at'))->format('M d, Y')
                        : '-';

                    $reviewCategoryScores = [
                        'Taste' => max(0, min(5, (int) data_get($review, 'taste_rating', 0))),
                        'Environment' => max(0, min(5, (int) data_get($review, 'environment_rating', 0))),
                        'Cleanliness' => max(0, min(5, (int) data_get($review, 'cleanliness_rating', 0))),
                        'Service' => max(0, min(5, (int) data_get($review, 'service_rating', 0))),
                    ];
                    $reviewPhoto = data_get($review, 'image_url');
                    $ownerResponse = trim((string) data_get($review, 'owner_response', ''));
                @endphp

                @php
                    $reviewId = (int) data_get($review, 'review_id', 0);
                    $reviewElementId = $reviewId > 0 ? 'priority-review-'.$reviewId : 'priority-review-'.$loop->index;
                    $reviewResponseEndpoint = $reviewId > 0
                        ? route('cafe-owner.recommendations.reviews.owner-response', ['rating' => $reviewId])
                        : null;
                @endphp

                <div id="{{ $reviewElementId }}" class="priority-review-card js-review-item relative border border-[#EDE4D8] rounded-xl p-4 transition-all duration-300">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-full bg-[#4A6741] text-white text-sm font-semibold flex items-center justify-center">
                                {{ $initial }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-[#3A2E22] truncate">{{ $reviewName }}</p>
                                <p class="text-xs text-[#9E8C78]">{{ $reviewDate }}</p>
                            </div>
                        </div>

                        @if($reviewResponseEndpoint)
                            @php
                                $reviewModalPayload = [
                                    'reviewId' => $reviewId,
                                    'reviewer' => $reviewName,
                                    'date' => $reviewDate,
                                    'overall' => round($reviewScore, 1),
                                    'priorityLabel' => $reviewBadgeLabel,
                                    'priorityClass' => $reviewBadgeClass,
                                    'categories' => [
                                        ['label' => 'Taste', 'score' => (int) data_get($review, 'taste_rating', 0)],
                                        ['label' => 'Environment', 'score' => (int) data_get($review, 'environment_rating', 0)],
                                        ['label' => 'Cleanliness', 'score' => (int) data_get($review, 'cleanliness_rating', 0)],
                                        ['label' => 'Service', 'score' => (int) data_get($review, 'service_rating', 0)],
                                    ],
                                    'photo' => $reviewPhoto,
                                    'ownerResponse' => $ownerResponse,
                                    'endpoint' => $reviewResponseEndpoint,
                                ];
                            @endphp
                            <button
                                type="button"
                                class="review-response-trigger inline-flex items-center justify-center text-[#4A6741] hover:text-[#3f5b38] transition-colors"
                                data-review='@json($reviewModalPayload)'
                                aria-label="Respond to this review"
                            >
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M8 10h8M8 14h5"/>
                                    <path d="M19 5H5a2 2 0 00-2 2v8a2 2 0 002 2h3v4l4.5-4H19a2 2 0 002-2V7a2 2 0 00-2-2z"/>
                                </svg>
                            </button>
                        @endif
                    </div>

                    <div class="flex items-center gap-2 mt-3">
                        <div class="flex items-center gap-0.5">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-4 h-4 {{ $i <= $reviewBucket ? 'text-amber-400' : 'text-gray-300' }}" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                </svg>
                            @endfor
                        </div>

                        <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold {{ $reviewBadgeClass }}">
                            {{ $reviewBadgeLabel }}
                        </span>
                    </div>

                    <div class="mt-3 space-y-2">
                        @foreach($reviewCategoryScores as $categoryLabel => $categoryScore)
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-xs text-[#6B5B4A]">{{ $categoryLabel }}</p>
                                <div class="flex items-center gap-1">
                                    <div class="flex items-center gap-0.5">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg class="w-3.5 h-3.5 {{ $i <= $categoryScore ? 'text-amber-400' : 'text-gray-300' }}" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                            </svg>
                                        @endfor
                                    </div>
                                    <span class="text-[11px] text-[#9E8C78]">{{ $categoryScore }}/5</span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($reviewPhoto)
                        <div class="mt-3">
                            <p class="text-xs font-medium text-[#6B5B4A] mb-2">Submitted Photo</p>
                            <a href="{{ $reviewPhoto }}" target="_blank" rel="noopener noreferrer" class="block">
                                <img src="{{ $reviewPhoto }}" alt="Review photo" class="w-full h-44 object-cover rounded-lg border border-[#EDE4D8]">
                            </a>
                        </div>
                    @endif

                    <div class="mt-3">
                        <p class="text-xs font-medium text-[#6B5B4A] mb-1">Owner Response</p>
                        <p data-owner-response-text data-review-id="{{ $reviewId }}" class="text-xs text-[#9E8C78] leading-relaxed">{{ $ownerResponse !== '' ? $ownerResponse : 'No owner response yet.' }}</p>
                    </div>
                </div>
            @empty
                <p class="text-sm text-[#9E8C78]">No ratings yet.</p>
            @endforelse
        </div>
    </div>
</div>

<div id="descriptivePrescriptiveInsightsSection" class="mt-8 bg-white rounded-2xl shadow-sm border border-[#E5DDD0] p-6">
    <div class="flex items-start justify-between gap-3 mb-5">
        <div>
            <h2 class="text-xl font-display font-bold text-[#3A2E22]">Descriptive & Prescriptive Insights</h2>
            <p id="insightsContainerSubtitle" class="text-xs text-[#9E8C78] mt-1">Weekly star-rating based recommendations from experience-journey signals and rule-based generated actions.</p>
            <p id="insightsCoverageLabel" class="text-[11px] text-[#9E8C78] mt-1">Coverage: {{ $insightsDateLabel }}</p>
        </div>
        <select id="insightsPeriodFilterContainer" class="h-9 rounded-lg border border-[#DCCFBD] bg-white px-3 text-xs text-[#6B5B4A] focus:outline-none focus:ring-2 focus:ring-[#4A6741]/30" aria-label="Filter descriptive and prescriptive insights period">
            <option value="all">All Time</option>
            <option value="month">This Month</option>
            <option value="week" selected>This Week</option>
        </select>
    </div>

    <div id="journeyInsightsGrid" class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        @forelse(($journeyInsights ?? []) as $insight)
            @php
                $insightPriorityKey = data_get($insight, 'priority_key', 'low');
                $insightBadgeClass = $insightPriorityKey === 'high'
                    ? 'bg-red-100 text-red-700 border-red-300'
                    : ($insightPriorityKey === 'medium' ? 'bg-amber-100 text-amber-700 border-amber-300' : 'bg-green-100 text-green-700 border-green-300');
                $insightScoreBucket = max(0, min(5, (int) data_get($insight, 'score_bucket', 0)));
            @endphp

            <div class="border border-[#EDE4D8] rounded-xl p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-[#3A2E22]">{{ data_get($insight, 'category_label', 'Category') }}</p>
                        <div class="flex items-center gap-2 mt-1">
                            <div class="flex items-center gap-0.5">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-3.5 h-3.5 {{ $i <= $insightScoreBucket ? 'text-amber-400' : 'text-gray-300' }}" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                    </svg>
                                @endfor
                            </div>
                            <span class="text-[11px] text-[#9E8C78]">{{ number_format((float) data_get($insight, 'score', 0), 2) }}/5</span>
                        </div>
                    </div>
                    <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold {{ $insightBadgeClass }}">
                        {{ data_get($insight, 'priority_label', 'Low Priority') }}
                    </span>
                </div>

                <div class="mt-3">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-[#7D6B57]">Descriptive Recommendation</p>
                    <p class="text-sm text-[#6B5B4A] mt-1 leading-relaxed">{{ data_get($insight, 'descriptive') }}</p>
                </div>

                <div class="mt-3">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-[#7D6B57]">Prescriptive Insights</p>
                    <ul class="mt-1 space-y-1.5">
                        @foreach((array) data_get($insight, 'prescriptive', []) as $action)
                            <li class="text-sm text-[#6B5B4A] leading-relaxed flex items-start gap-2">
                                <span class="mt-1 inline-block w-1.5 h-1.5 rounded-full bg-[#4A6741]"></span>
                                <span>{{ $action }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <p class="text-[11px] text-[#9E8C78] mt-3">{{ data_get($insight, 'evidence') }}</p>
            </div>
        @empty
            <div class="lg:col-span-2 border border-dashed border-[#DCCFBE] rounded-xl p-6 bg-[#F9F6F1]">
                <p class="text-sm font-semibold text-[#6B5B4A]">No weekly insights available yet</p>
                <p class="text-sm text-[#9E8C78] mt-1">We need at least one rating in the selected week to generate descriptive and prescriptive insights.</p>
                <p class="text-[11px] text-[#9E8C78] mt-2">Coverage: {{ $insightsDateLabel }}</p>
            </div>
        @endforelse
    </div>
</div>

<div class="mt-8 bg-white rounded-2xl shadow-sm border border-[#E5DDD0] p-6">
    <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
        <div>
            <h2 class="text-xl font-display font-bold text-[#3A2E22]">Recommendation History</h2>
            <p class="text-xs text-[#9E8C78] mt-1">Each set below is a stored recommendation generation.</p>
            <p id="historyCoverageLabel" class="text-[11px] text-[#9E8C78] mt-1">Coverage: {{ $insightsDateLabel }}</p>
        </div>
        <div class="rounded-xl border border-[#E5DDD0] bg-[#FCFAF7] px-4 py-3 min-w-[160px]">
            <p class="text-[11px] uppercase tracking-wide text-[#9E8C78] font-semibold">Sets</p>
            <p id="historyOverviewCount" class="text-3xl font-bold text-[#3A2E22] mt-1">{{ count($historyEntries ?? []) }}</p>
            <p id="historyOverviewSubtext" class="text-xs text-[#9E8C78] mt-1">Historical recommendation sets in this period</p>
        </div>
    </div>

    <div id="recommendationHistoryList" class="mt-5 space-y-4">
        @forelse(($historyEntries ?? []) as $entry)
            @php
                $entryPriorityKey = data_get($entry, 'priority_key', 'low');
                $entryPriorityClass = $entryPriorityKey === 'high'
                    ? 'bg-red-100 text-red-700 border-red-300'
                    : ($entryPriorityKey === 'medium' ? 'bg-amber-100 text-amber-700 border-amber-300' : 'bg-green-100 text-green-700 border-green-300');
            @endphp
            <div class="rounded-2xl border border-[#E5DDD0] bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-[#3A2E22]">Generated {{ data_get($entry, 'generated_label', '-') }}</p>
                        <p class="text-xs text-[#9E8C78] mt-1">{{ (int) data_get($entry, 'review_count', 0) }} cumulative ratings at generation time</p>
                    </div>
                    <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $entryPriorityClass }}">
                        {{ data_get($entry, 'priority_label', 'Low Priority') }}
                    </span>
                </div>

                <div class="mt-4 grid grid-cols-1 lg:grid-cols-2 gap-4">
                    @foreach((array) data_get($entry, 'items', []) as $item)
                        @php
                            $itemPriorityKey = data_get($item, 'priority_key', 'low');
                            $itemPriorityClass = $itemPriorityKey === 'high'
                                ? 'bg-red-100 text-red-700 border-red-300'
                                : ($itemPriorityKey === 'medium' ? 'bg-amber-100 text-amber-700 border-amber-300' : 'bg-green-100 text-green-700 border-green-300');
                        @endphp
                        <div class="rounded-xl border border-[#EDE4D8] bg-[#FCFAF7] p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-[#3A2E22]">{{ data_get($item, 'category_label', 'Category') }}</p>
                                    <p class="text-[11px] text-[#9E8C78] mt-1">Average score: {{ number_format((float) data_get($item, 'average_score', 0), 2) }}/5</p>
                                </div>
                                <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold {{ $itemPriorityClass }}">
                                    {{ data_get($item, 'priority_label', 'Low Priority') }}
                                </span>
                            </div>
                            <p class="text-sm text-[#6B5B4A] mt-3 leading-relaxed">{{ data_get($item, 'insight') }}</p>
                            <p class="text-[11px] text-[#7D6B57] mt-3 uppercase tracking-wide font-semibold">Suggested Action</p>
                            <p class="text-sm text-[#6B5B4A] mt-1 leading-relaxed">{{ data_get($item, 'suggested_action') }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="border border-dashed border-[#DCCFBE] rounded-xl p-6 bg-[#F9F6F1]">
                <p class="text-sm font-semibold text-[#6B5B4A]">No recommendation sets for this week yet</p>
                <p class="text-sm text-[#9E8C78] mt-1">Historical recommendation generations will appear here each time ratings trigger a new snapshot.</p>
                <p class="text-[11px] text-[#9E8C78] mt-2">Coverage: {{ $insightsDateLabel }}</p>
            </div>
        @endforelse
    </div>
</div>

<div id="ownerResponseModal" class="fixed inset-0 z-50 hidden opacity-0 transition-opacity duration-200 ease-out" aria-hidden="true">
    <div class="absolute inset-0 bg-black/40" data-modal-close></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-xl bg-white rounded-2xl shadow-2xl border border-[#E5DDD0] overflow-hidden transform scale-95 opacity-0 transition duration-200 ease-out" data-modal-dialog>
            <div class="px-5 py-4 border-b border-[#EFE7DA] flex items-center justify-between">
                <h3 class="text-lg font-display font-bold text-[#3A2E22]">Review Details & Owner Response</h3>
                <button type="button" class="w-8 h-8 rounded-full hover:bg-[#F1EFEA] text-[#7D6B57]" data-modal-close aria-label="Close">
                    <svg class="w-4 h-4 mx-auto" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
                </button>
            </div>

            <div class="p-5 space-y-4 max-h-[75vh] overflow-y-auto">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p id="modalReviewer" class="text-sm font-semibold text-[#3A2E22]"></p>
                        <p id="modalReviewDate" class="text-xs text-[#9E8C78]"></p>
                    </div>
                    <div id="modalPriorityBadge" class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold"></div>
                </div>

                <div class="flex items-center gap-2">
                    <div id="modalOverallStars" class="flex items-center gap-0.5"></div>
                    <span id="modalOverallText" class="text-xs text-[#6B5B4A]"></span>
                </div>

                <div id="modalCategoryRows" class="space-y-2"></div>

                <div id="modalPhotoWrap" class="hidden">
                    <p class="text-xs font-medium text-[#6B5B4A] mb-2">Submitted Photo</p>
                    <a id="modalPhotoLink" target="_blank" rel="noopener noreferrer" class="block">
                        <img id="modalPhotoImg" alt="Review photo" class="w-full h-48 object-cover rounded-lg border border-[#EDE4D8]">
                    </a>
                </div>

                <div>
                    <label for="modalOwnerResponse" class="block text-xs font-medium text-[#6B5B4A] mb-2">Owner Response</label>
                    <textarea id="modalOwnerResponse" rows="4" maxlength="1500" class="w-full rounded-lg border border-[#DCCFBD] text-sm text-[#3A2E22] p-3 focus:outline-none focus:ring-2 focus:ring-[#4A6741]/30" placeholder="Write your response to this review..."></textarea>
                    <p class="text-[11px] text-[#9E8C78] mt-1">Max 1500 characters</p>
                </div>
            </div>

            <div class="px-5 py-4 border-t border-[#EFE7DA] flex items-center justify-end gap-2">
                <button type="button" class="px-4 py-2 text-sm rounded-lg border border-[#DCCFBD] text-[#6B5B4A] hover:bg-[#F8F4EE]" data-modal-close>Cancel</button>
                <button type="button" id="modalSaveOwnerResponse" class="px-4 py-2 text-sm rounded-lg bg-[#4A6741] text-white hover:bg-[#3f5b38] disabled:opacity-60">Save Response</button>
            </div>
        </div>
    </div>
</div>

<button
    id="scrollToTopButton"
    type="button"
    class="fixed bottom-6 right-6 z-40 w-12 h-12 rounded-full bg-[#4A6741] text-white shadow-lg hover:bg-[#3f5b38] focus:outline-none focus:ring-2 focus:ring-[#4A6741]/50 opacity-0 pointer-events-none transition-opacity duration-300"
    aria-label="Scroll to top"
>
    <svg class="w-5 h-5 mx-auto" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 19V5"/>
        <path d="m5 12 7-7 7 7"/>
    </svg>
</button>
</div>

<style>
    @media (max-width: 767px) {
        .cafe-recommendations-page .cafe-reco-header {
            flex-direction: row;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .cafe-recommendations-page .cafe-reco-heading {
            flex: 1 1 auto;
            min-width: 0;
        }

        .cafe-recommendations-page .cafe-reco-download-btn {
            margin-left: auto;
            flex: 0 0 auto;
        }

        .cafe-recommendations-page .mb-8 h1 {
            font-size: 1.7rem !important;
            line-height: 2rem;
        }

        .cafe-recommendations-page .grid.grid-cols-1.md\:grid-cols-2.xl\:grid-cols-4,
        .cafe-recommendations-page .grid.grid-cols-1.xl\:grid-cols-2 {
            gap: 0.9rem !important;
        }

        .cafe-recommendations-page .bg-white.rounded-2xl.shadow-sm.border.border-\[\#E5DDD0\].p-6 {
            padding: 1rem !important;
        }
    }
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
    const ratingDistributionData = @json($ratingDistributionData);

    const monthlyRecommendationData = @json($monthlyRecommendationData);
    const insightsFilterPayload = @json($insightsFilterPayload ?? []);
    const historyFilterPayload = @json($historyFilterPayload ?? []);

    const sharedScales = {
        x: {
            grid: {
                color: '#E5DDD0'
            },
            ticks: {
                color: '#9E8C78'
            }
        },
        y: {
            beginAtZero: true,
            grid: {
                color: '#E5DDD0'
            },
            ticks: {
                color: '#9E8C78',
                precision: 0
            }
        }
    };

    const ratingCtx = document.getElementById('ratingDistributionChart');
    if (ratingCtx) {
        new Chart(ratingCtx, {
            type: 'bar',
            data: {
                labels: ratingDistributionData.labels,
                datasets: [{
                    label: 'Reviews',
                    data: ratingDistributionData.series,
                    backgroundColor: ['#EF4444', '#EF4444', '#F59E0B', '#4A6741', '#4A6741'],
                    borderRadius: 8,
                    maxBarThickness: 42
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: sharedScales
            }
        });
    }

    const monthlyCtx = document.getElementById('monthlyRecommendationsChart');
    if (monthlyCtx) {
        new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: monthlyRecommendationData.labels,
                datasets: [{
                    label: 'Recommendation Sets',
                    data: monthlyRecommendationData.series,
                    borderColor: '#4A6741',
                    backgroundColor: 'rgba(74, 103, 65, 0.10)',
                    pointBackgroundColor: '#4A6741',
                    pointBorderColor: '#4A6741',
                    pointRadius: 4,
                    pointHoverRadius: 5,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: sharedScales
            }
        });
    }

    const thisMonthInsightsCard = document.getElementById('thisMonthInsightsCard');
    const descriptivePrescriptiveInsightsSection = document.getElementById('descriptivePrescriptiveInsightsSection');
    const insightsOverviewTitle = document.getElementById('insightsOverviewTitle');
    const insightsOverviewCount = document.getElementById('insightsOverviewCount');
    const insightsOverviewSubtext = document.getElementById('insightsOverviewSubtext');
    const insightsOverviewDate = document.getElementById('insightsOverviewDate');
    const insightsContainerSubtitle = document.getElementById('insightsContainerSubtitle');
    const insightsCoverageLabel = document.getElementById('insightsCoverageLabel');
    const journeyInsightsGrid = document.getElementById('journeyInsightsGrid');
    const historyOverviewCount = document.getElementById('historyOverviewCount');
    const historyOverviewSubtext = document.getElementById('historyOverviewSubtext');
    const historyCoverageLabel = document.getElementById('historyCoverageLabel');
    const recommendationHistoryList = document.getElementById('recommendationHistoryList');
    const insightsPeriodFilterCard = document.getElementById('insightsPeriodFilterCard');
    const insightsPeriodFilterContainer = document.getElementById('insightsPeriodFilterContainer');
    let currentInsightsPeriod = 'week';
    const priorityCard = document.getElementById('priorityStatusCard');
    const recentRatingsList = document.getElementById('recentRatingsList');
    const recentRatingsPrev = document.getElementById('recentRatingsPrev');
    const recentRatingsNext = document.getElementById('recentRatingsNext');
    const recentRatingsCounter = document.getElementById('recentRatingsCounter');
    const reviewCards = recentRatingsList
        ? Array.from(recentRatingsList.querySelectorAll('.js-review-item'))
        : [];
    let activeReviewCardIndex = 0;

    const setActiveReviewIndex = (nextIndex) => {
        if (reviewCards.length === 0) {
            if (recentRatingsCounter) {
                recentRatingsCounter.textContent = '0 / 0';
            }
            if (recentRatingsPrev) {
                recentRatingsPrev.disabled = true;
            }
            if (recentRatingsNext) {
                recentRatingsNext.disabled = true;
            }
            return;
        }

        const maxIndex = reviewCards.length - 1;
        activeReviewCardIndex = Math.max(0, Math.min(maxIndex, nextIndex));

        reviewCards.forEach((card, index) => {
            card.classList.toggle('hidden', index !== activeReviewCardIndex);
        });

        if (recentRatingsCounter) {
            recentRatingsCounter.textContent = `${activeReviewCardIndex + 1} / ${reviewCards.length}`;
        }
        if (recentRatingsPrev) {
            recentRatingsPrev.disabled = activeReviewCardIndex === 0;
        }
        if (recentRatingsNext) {
            recentRatingsNext.disabled = activeReviewCardIndex === maxIndex;
        }
    };

    if (reviewCards.length > 0) {
        setActiveReviewIndex(0);
        recentRatingsPrev?.addEventListener('click', () => setActiveReviewIndex(activeReviewCardIndex - 1));
        recentRatingsNext?.addEventListener('click', () => setActiveReviewIndex(activeReviewCardIndex + 1));
    } else {
        setActiveReviewIndex(0);
    }

    const priorityCardHasRatings = priorityCard ? priorityCard.dataset.hasRatings === '1' : false;

    if (priorityCard && priorityCardHasRatings) {
        const showPriorityReviewFocus = () => {
            const targetReviewId = Number(priorityCard.dataset.targetReviewId || 0);
            if (!targetReviewId) {
                return;
            }

            const targetCardId = `priority-review-${targetReviewId}`;
            const targetCardIndex = reviewCards.findIndex((card) => card.id === targetCardId);
            if (targetCardIndex >= 0) {
                setActiveReviewIndex(targetCardIndex);
            }

            const targetCard = document.getElementById(targetCardId);
            if (!targetCard) {
                return;
            }

            targetCard.scrollIntoView({ behavior: 'smooth', block: 'center' });

            targetCard.classList.add('ring-2', 'ring-[#4A6741]', 'shadow-lg', 'shadow-[#4A6741]/20', 'scale-[1.01]');

            const existingIndicator = targetCard.querySelector('.priority-focus-indicator');
            if (existingIndicator) {
                existingIndicator.remove();
            }

            const indicator = document.createElement('div');
            indicator.className = 'priority-focus-indicator absolute -top-2 right-3 bg-[#4A6741] text-white text-[10px] font-semibold px-2 py-1 rounded-full shadow animate-pulse';
            indicator.textContent = 'Priority Focus';
            targetCard.appendChild(indicator);

            window.setTimeout(() => {
                targetCard.classList.remove('ring-2', 'ring-[#4A6741]', 'shadow-lg', 'shadow-[#4A6741]/20', 'scale-[1.01]');
                indicator.remove();
            }, 4200);
        };

        priorityCard.addEventListener('click', showPriorityReviewFocus);
        priorityCard.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                showPriorityReviewFocus();
            }
        });
    }

    if (thisMonthInsightsCard && descriptivePrescriptiveInsightsSection) {
        const showMonthlyInsightsFocus = () => {
            descriptivePrescriptiveInsightsSection.scrollIntoView({ behavior: 'smooth', block: 'center' });

            descriptivePrescriptiveInsightsSection.classList.add(
                'ring-2',
                'ring-[#4A6741]',
                'shadow-lg',
                'shadow-[#4A6741]/20',
                'scale-[1.01]',
                'transition-all',
                'duration-200'
            );

            window.setTimeout(() => {
                descriptivePrescriptiveInsightsSection.classList.remove(
                    'ring-2',
                    'ring-[#4A6741]',
                    'shadow-lg',
                    'shadow-[#4A6741]/20',
                    'scale-[1.01]',
                    'transition-all',
                    'duration-200'
                );
            }, 1800);
        };

        thisMonthInsightsCard.addEventListener('click', showMonthlyInsightsFocus);
        thisMonthInsightsCard.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                showMonthlyInsightsFocus();
            }
        });
    }

    const ownerResponseModal = document.getElementById('ownerResponseModal');
    const ownerResponseDialog = ownerResponseModal ? ownerResponseModal.querySelector('[data-modal-dialog]') : null;
    const responseTriggerButtons = document.querySelectorAll('.review-response-trigger');
    const closeModalButtons = ownerResponseModal ? ownerResponseModal.querySelectorAll('[data-modal-close]') : [];
    const saveOwnerResponseButton = document.getElementById('modalSaveOwnerResponse');

    const modalReviewer = document.getElementById('modalReviewer');
    const modalReviewDate = document.getElementById('modalReviewDate');
    const modalPriorityBadge = document.getElementById('modalPriorityBadge');
    const modalOverallStars = document.getElementById('modalOverallStars');
    const modalOverallText = document.getElementById('modalOverallText');
    const modalCategoryRows = document.getElementById('modalCategoryRows');
    const modalPhotoWrap = document.getElementById('modalPhotoWrap');
    const modalPhotoLink = document.getElementById('modalPhotoLink');
    const modalPhotoImg = document.getElementById('modalPhotoImg');
    const modalOwnerResponse = document.getElementById('modalOwnerResponse');

    let activeReview = null;
    let ownerResponseModalClosingTimer = null;

    const escapeHtml = (value) => String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/\"/g, '&quot;')
        .replace(/'/g, '&#039;');

    const renderStars = (score, sizeClass = 'w-4 h-4') => {
        const safeScore = Math.max(0, Math.min(5, Number(score) || 0));
        const full = Math.round(safeScore);
        let html = '';

        for (let i = 1; i <= 5; i += 1) {
            const colorClass = i <= full ? 'text-amber-400' : 'text-gray-300';
            html += `<svg class="${sizeClass} ${colorClass}" viewBox="0 0 24 24" fill="currentColor"><path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>`;
        }

        return html;
    };

    const insightsPeriodMeta = {
        all: {
            title: 'All-Time Summary',
            countText: 'Current descriptive and prescriptive insight summary across all time',
            emptyText: 'No ratings available to generate all-time insights yet.',
            subtitle: 'All-time summary based on ratings, with historical generations listed separately below.',
        },
        month: {
            title: "This Month's Insights",
            countText: 'Generated prescriptive insights this month',
            emptyText: 'No ratings received in this month yet.',
            subtitle: 'Monthly star-rating based recommendations from experience-journey signals and rule-based generated actions.',
        },
        week: {
            title: "This Week's Insights",
            countText: 'Generated prescriptive insights this week',
            emptyText: 'No ratings received in this week yet.',
            subtitle: 'Weekly star-rating based recommendations from experience-journey signals and rule-based generated actions.',
        },
    };

    const getPriorityBadgeClass = (priorityKey) => {
        if (priorityKey === 'high') {
            return 'bg-red-100 text-red-700 border-red-300';
        }
        if (priorityKey === 'medium') {
            return 'bg-amber-100 text-amber-700 border-amber-300';
        }
        return 'bg-green-100 text-green-700 border-green-300';
    };

    const renderRecommendationHistory = (entries, periodKey, dateLabel) => {
        if (!recommendationHistoryList) {
            return;
        }

        if (!Array.isArray(entries) || entries.length === 0) {
            const emptyPeriodLabel = periodKey === 'all' ? 'all time' : (periodKey === 'month' ? 'this month' : 'this week');
            recommendationHistoryList.innerHTML = `
                <div class="border border-dashed border-[#DCCFBE] rounded-xl p-6 bg-[#F9F6F1]">
                    <p class="text-sm font-semibold text-[#6B5B4A]">No recommendation sets for ${escapeHtml(emptyPeriodLabel)} yet</p>
                    <p class="text-sm text-[#9E8C78] mt-1">Historical recommendation generations will appear here each time ratings trigger a new snapshot.</p>
                    <p class="text-[11px] text-[#9E8C78] mt-2">Coverage: ${escapeHtml(dateLabel || '-')}</p>
                </div>
            `;
            return;
        }

        recommendationHistoryList.innerHTML = entries.map((entry) => {
            const entryPriorityClass = getPriorityBadgeClass(String(entry.priority_key || 'low').toLowerCase());
            const items = Array.isArray(entry.items) ? entry.items : [];
            const itemsHtml = items.map((item) => {
                const itemPriorityClass = getPriorityBadgeClass(String(item.priority_key || 'low').toLowerCase());

                return `
                    <div class="rounded-xl border border-[#EDE4D8] bg-[#FCFAF7] p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-[#3A2E22]">${escapeHtml(item.category_label || 'Category')}</p>
                                <p class="text-[11px] text-[#9E8C78] mt-1">Average score: ${escapeHtml(Number(item.average_score || 0).toFixed(2))}/5</p>
                            </div>
                            <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold ${itemPriorityClass}">
                                ${escapeHtml(item.priority_label || 'Low Priority')}
                            </span>
                        </div>
                        <p class="text-sm text-[#6B5B4A] mt-3 leading-relaxed">${escapeHtml(item.insight || '')}</p>
                        <p class="text-[11px] text-[#7D6B57] mt-3 uppercase tracking-wide font-semibold">Suggested Action</p>
                        <p class="text-sm text-[#6B5B4A] mt-1 leading-relaxed">${escapeHtml(item.suggested_action || '')}</p>
                    </div>
                `;
            }).join('');

            return `
                <div class="rounded-2xl border border-[#E5DDD0] bg-white p-5 shadow-sm">
                    <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-[#3A2E22]">Generated ${escapeHtml(entry.generated_label || '-')}</p>
                            <p class="text-xs text-[#9E8C78] mt-1">${escapeHtml(String(entry.review_count || 0))} cumulative ratings at generation time</p>
                        </div>
                        <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold ${entryPriorityClass}">
                            ${escapeHtml(entry.priority_label || 'Low Priority')}
                        </span>
                    </div>
                    <div class="mt-4 grid grid-cols-1 lg:grid-cols-2 gap-4">${itemsHtml}</div>
                </div>
            `;
        }).join('');
    };

    const renderJourneyInsightsGrid = (insights, periodKey, dateLabel) => {
        if (!journeyInsightsGrid) {
            return;
        }

        if (!Array.isArray(insights) || insights.length === 0) {
            const emptyPeriodLabel = periodKey === 'all' ? 'all time' : (periodKey === 'month' ? 'this month' : 'this week');
            journeyInsightsGrid.innerHTML = `
                <div class="lg:col-span-2 border border-dashed border-[#DCCFBE] rounded-xl p-6 bg-[#F9F6F1]">
                    <p class="text-sm font-semibold text-[#6B5B4A]">No ${escapeHtml(emptyPeriodLabel)} insights available yet</p>
                    <p class="text-sm text-[#9E8C78] mt-1">We need at least one rating in the selected period to generate descriptive and prescriptive insights.</p>
                    <p class="text-[11px] text-[#9E8C78] mt-2">Coverage: ${escapeHtml(dateLabel || '-')}</p>
                </div>
            `;
            return;
        }

        journeyInsightsGrid.innerHTML = insights.map((insight) => {
            const priorityKey = String(insight.priority_key || 'low').toLowerCase();
            const badgeClass = getPriorityBadgeClass(priorityKey);
            const score = Number(insight.score || 0);
            const priorityLabel = insight.priority_label || 'Low Priority';
            const categoryLabel = insight.category_label || 'Category';
            const descriptive = insight.descriptive || '';
            const evidence = insight.evidence || '';
            const actions = Array.isArray(insight.prescriptive) ? insight.prescriptive : [];

            const actionItemsHtml = actions.map((action) => `
                <li class="text-sm text-[#6B5B4A] leading-relaxed flex items-start gap-2">
                    <span class="mt-1 inline-block w-1.5 h-1.5 rounded-full bg-[#4A6741]"></span>
                    <span>${escapeHtml(action)}</span>
                </li>
            `).join('');

            return `
                <div class="border border-[#EDE4D8] rounded-xl p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-[#3A2E22]">${escapeHtml(categoryLabel)}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <div class="flex items-center gap-0.5">${renderStars(score, 'w-3.5 h-3.5')}</div>
                                <span class="text-[11px] text-[#9E8C78]">${escapeHtml(score.toFixed(2))}/5</span>
                            </div>
                        </div>
                        <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold ${badgeClass}">
                            ${escapeHtml(priorityLabel)}
                        </span>
                    </div>

                    <div class="mt-3">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-[#7D6B57]">Descriptive Recommendation</p>
                        <p class="text-sm text-[#6B5B4A] mt-1 leading-relaxed">${escapeHtml(descriptive)}</p>
                    </div>

                    <div class="mt-3">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-[#7D6B57]">Prescriptive Insights</p>
                        <ul class="mt-1 space-y-1.5">${actionItemsHtml}</ul>
                    </div>

                    <p class="text-[11px] text-[#9E8C78] mt-3">${escapeHtml(evidence)}</p>
                </div>
            `;
        }).join('');
    };

    const applyInsightsPeriod = (periodKey) => {
        const normalizedPeriod = ['all', 'month', 'week'].includes(periodKey) ? periodKey : 'week';
        currentInsightsPeriod = normalizedPeriod;
        const isAllPeriod = normalizedPeriod === 'all';
        const periodData = insightsFilterPayload?.[normalizedPeriod] || insightsFilterPayload?.week || null;
        const periodMeta = insightsPeriodMeta[normalizedPeriod] || insightsPeriodMeta.week;

        if (!periodData) {
            return;
        }

        const hasRatings = Boolean(periodData.has_ratings);
        const dateLabel = String(periodData.date_label || '-');
        const journeyInsights = Array.isArray(periodData.journey_insights) ? periodData.journey_insights : [];
        const historyEntries = Array.isArray(historyFilterPayload?.[normalizedPeriod]) ? historyFilterPayload[normalizedPeriod] : [];
        const insightCount = journeyInsights.length;
        const count = isAllPeriod ? insightCount : Number(periodData.count || 0);
        const hasVisibleInsights = isAllPeriod ? insightCount > 0 : hasRatings;

        if (insightsOverviewTitle) {
            insightsOverviewTitle.textContent = periodMeta.title;
        }
        if (insightsOverviewCount) {
            insightsOverviewCount.textContent = hasVisibleInsights ? String(count) : '--';
            insightsOverviewCount.classList.toggle('text-[#3A2E22]', hasVisibleInsights);
            insightsOverviewCount.classList.toggle('text-[#C6B8A6]', !hasVisibleInsights);
        }
        if (insightsOverviewSubtext) {
            insightsOverviewSubtext.textContent = hasVisibleInsights ? periodMeta.countText : periodMeta.emptyText;
        }
        if (insightsOverviewDate) {
            insightsOverviewDate.textContent = dateLabel;
        }
        if (insightsContainerSubtitle) {
            insightsContainerSubtitle.textContent = periodMeta.subtitle;
        }
        if (insightsCoverageLabel) {
            insightsCoverageLabel.textContent = `Coverage: ${dateLabel}`;
        }
        if (historyOverviewCount) {
            historyOverviewCount.textContent = String(historyEntries.length);
        }
        if (historyOverviewSubtext) {
            historyOverviewSubtext.textContent = historyEntries.length === 1
                ? 'Historical recommendation set in this period'
                : 'Historical recommendation sets in this period';
        }
        if (historyCoverageLabel) {
            historyCoverageLabel.textContent = `Coverage: ${dateLabel}`;
        }

        renderJourneyInsightsGrid(journeyInsights, normalizedPeriod, dateLabel);
        renderRecommendationHistory(historyEntries, normalizedPeriod, dateLabel);

        if (insightsPeriodFilterCard && insightsPeriodFilterCard.value !== normalizedPeriod) {
            insightsPeriodFilterCard.value = normalizedPeriod;
        }
        if (insightsPeriodFilterContainer && insightsPeriodFilterContainer.value !== normalizedPeriod) {
            insightsPeriodFilterContainer.value = normalizedPeriod;
        }
    };

    [insightsPeriodFilterCard, insightsPeriodFilterContainer].forEach((select) => {
        if (!select) {
            return;
        }

        ['click', 'mousedown', 'touchstart', 'keydown', 'change'].forEach((eventName) => {
            select.addEventListener(eventName, (event) => {
                event.stopPropagation();
            });
        });
    });

    insightsPeriodFilterCard?.addEventListener('change', (event) => {
        applyInsightsPeriod(event.target.value);
    });

    insightsPeriodFilterContainer?.addEventListener('change', (event) => {
        applyInsightsPeriod(event.target.value);
    });

    applyInsightsPeriod('week');

    const openOwnerResponseModal = (reviewData) => {
        if (!ownerResponseModal || !ownerResponseDialog || !reviewData) {
            return;
        }

        if (ownerResponseModalClosingTimer) {
            window.clearTimeout(ownerResponseModalClosingTimer);
            ownerResponseModalClosingTimer = null;
        }

        activeReview = reviewData;

        modalReviewer.textContent = reviewData.reviewer || 'Anonymous';
        modalReviewDate.textContent = reviewData.date || '-';
        modalPriorityBadge.className = `inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold ${reviewData.priorityClass || ''}`;
        modalPriorityBadge.textContent = reviewData.priorityLabel || 'Low';

        modalOverallStars.innerHTML = renderStars(reviewData.overall || 0, 'w-4 h-4');
        modalOverallText.textContent = `${Number(reviewData.overall || 0).toFixed(1)}/5 overall`;

        const categories = Array.isArray(reviewData.categories) ? reviewData.categories : [];
        modalCategoryRows.innerHTML = categories.map((item) => {
            const label = escapeHtml(item.label || '-');
            const score = Math.max(0, Math.min(5, Number(item.score) || 0));
            return `
                <div class="flex items-center justify-between gap-2">
                    <p class="text-xs text-[#6B5B4A]">${label}</p>
                    <div class="flex items-center gap-1">
                        <div class="flex items-center gap-0.5">${renderStars(score, 'w-3.5 h-3.5')}</div>
                        <span class="text-[11px] text-[#9E8C78]">${score}/5</span>
                    </div>
                </div>
            `;
        }).join('');

        if (reviewData.photo) {
            modalPhotoWrap.classList.remove('hidden');
            modalPhotoLink.href = reviewData.photo;
            modalPhotoImg.src = reviewData.photo;
        } else {
            modalPhotoWrap.classList.add('hidden');
            modalPhotoLink.removeAttribute('href');
            modalPhotoImg.removeAttribute('src');
        }

        modalOwnerResponse.value = reviewData.ownerResponse || '';

        ownerResponseModal.classList.remove('hidden');
        ownerResponseModal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');

        window.requestAnimationFrame(() => {
            ownerResponseModal.classList.remove('opacity-0');
            ownerResponseModal.classList.add('opacity-100');
            ownerResponseDialog.classList.remove('opacity-0', 'scale-95');
            ownerResponseDialog.classList.add('opacity-100', 'scale-100');
        });
    };

    const closeOwnerResponseModal = () => {
        if (!ownerResponseModal || !ownerResponseDialog) {
            return;
        }

        ownerResponseModal.classList.remove('opacity-100');
        ownerResponseModal.classList.add('opacity-0');
        ownerResponseDialog.classList.remove('opacity-100', 'scale-100');
        ownerResponseDialog.classList.add('opacity-0', 'scale-95');

        if (ownerResponseModalClosingTimer) {
            window.clearTimeout(ownerResponseModalClosingTimer);
        }

        ownerResponseModalClosingTimer = window.setTimeout(() => {
            ownerResponseModal.classList.add('hidden');
            ownerResponseModalClosingTimer = null;
        }, 180);

        ownerResponseModal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
        activeReview = null;
    };

    if (responseTriggerButtons.length > 0 && ownerResponseModal) {
        responseTriggerButtons.forEach((button) => {
            button.addEventListener('click', () => {
                try {
                    const reviewData = JSON.parse(button.dataset.review || '{}');
                    openOwnerResponseModal(reviewData);
                } catch {
                    // Do nothing when malformed payload is encountered.
                }
            });
        });

        closeModalButtons.forEach((button) => {
            button.addEventListener('click', closeOwnerResponseModal);
        });

        ownerResponseModal.addEventListener('click', (event) => {
            const clickedCloseControl = event.target.matches('[data-modal-close]');
            const clickedOutsideDialog = !event.target.closest('[data-modal-dialog]');

            if (clickedCloseControl || clickedOutsideDialog) {
                closeOwnerResponseModal();
            }
        });

        ownerResponseModal.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeOwnerResponseModal();
            }
        });

        saveOwnerResponseButton?.addEventListener('click', async () => {
            if (!activeReview?.endpoint) {
                return;
            }

            const responseText = modalOwnerResponse.value.trim();

            saveOwnerResponseButton.disabled = true;
            saveOwnerResponseButton.textContent = 'Saving...';

            try {
                const response = await fetch(activeReview.endpoint, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({ owner_response: responseText }),
                });

                if (!response.ok) {
                    throw new Error('Failed to save owner response.');
                }

                const payload = await response.json();
                const nextResponse = (payload?.owner_response || '').trim();

                const reviewTextNode = document.querySelector(`[data-owner-response-text][data-review-id="${activeReview.reviewId}"]`);
                if (reviewTextNode) {
                    reviewTextNode.textContent = nextResponse !== '' ? nextResponse : 'No owner response yet.';
                }

                activeReview.ownerResponse = nextResponse;
                closeOwnerResponseModal();
            } catch {
                alert('Unable to save owner response right now. Please try again.');
            } finally {
                saveOwnerResponseButton.disabled = false;
                saveOwnerResponseButton.textContent = 'Save Response';
            }
        });
    }

    const scrollToTopButton = document.getElementById('scrollToTopButton');

    function downloadRecommendationsReport() {
        if (typeof jspdf === 'undefined') return;

        const { jsPDF } = jspdf;
        const doc = new jsPDF('p', 'mm', 'a4');
        const pageW = doc.internal.pageSize.getWidth();
        const pageH = doc.internal.pageSize.getHeight();
        const margin = 18;
        const contentW = pageW - margin * 2;
        let y = 18;

        const selectedPeriod = ['all', 'month', 'week'].includes(currentInsightsPeriod)
            ? currentInsightsPeriod
            : (insightsPeriodFilterCard?.value || insightsPeriodFilterContainer?.value || 'week');
        const periodData = insightsFilterPayload?.[selectedPeriod] || insightsFilterPayload?.week || {};
        const periodMeta = insightsPeriodMeta?.[selectedPeriod] || insightsPeriodMeta.week;
        const periodHistoryEntries = Array.isArray(historyFilterPayload?.[selectedPeriod]) ? historyFilterPayload[selectedPeriod] : [];
        const periodJourneyInsights = Array.isArray(periodData?.journey_insights) ? periodData.journey_insights : [];
        const periodCategoryAverages = periodData?.category_averages || {};
        const periodDateLabel = String(periodData?.date_label || '-');
        const periodPriorityLabel = String(periodData?.priority_level || 'No Priority Yet');
        const periodInsightCount = selectedPeriod === 'all'
            ? periodJourneyInsights.length
            : Number(periodData?.count || 0);
        const periodHasRatings = Boolean(periodData?.has_ratings);

        const establishment = @js($establishment->name ?? 'BrewHub Cafe');
        const city = @js($establishment->city ?? 'Lipa');

        const checkPage = (needed) => {
            if (y + needed > pageH - 20) {
                doc.addPage();
                y = 18;
            }
        };

        // ── Header ──
        doc.setFontSize(20);
        doc.setFont('helvetica', 'bold');
        doc.setTextColor(58, 46, 34);
        doc.text('Recommendation Insights Report', margin, y);
        y += 7;

        doc.setFontSize(10);
        doc.setFont('helvetica', 'normal');
        doc.setTextColor(122, 105, 87);
        doc.text('Generated: ' + new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }), margin, y);
        y += 4;
        doc.text(establishment + ' \u2022 BrewHub \u2022 ' + city, margin, y);
        y += 4;
        doc.text('Report Filter: ' + periodMeta.title + ' \u2022 Coverage: ' + periodDateLabel, margin, y);
        y += 8;

        doc.setDrawColor(229, 221, 208);
        doc.setLineWidth(0.5);
        doc.line(margin, y, pageW - margin, y);
        y += 10;

        // ── Summary Cards ──
        const avgRating = @js($avgFormatted);
        const totalReviews = @js((int) ($totalReviews ?? 0));

        const summaryCards = [
            { label: 'Average Rating', value: String(avgRating) },
            { label: 'Total Ratings', value: String(totalReviews) },
            { label: periodMeta.title, value: periodHasRatings || selectedPeriod === 'all' ? String(periodInsightCount) : '--' },
            { label: 'Priority Status', value: periodHasRatings ? periodPriorityLabel : 'No Ratings Yet' },
        ];

        const cardW = (contentW - 9) / 4;
        const cardH = 22;

        summaryCards.forEach((card, i) => {
            const cx = margin + i * (cardW + 3);
            doc.setFillColor(250, 247, 241);
            doc.setDrawColor(229, 221, 208);
            doc.roundedRect(cx, y, cardW, cardH, 2, 2, 'FD');

            doc.setFontSize(7);
            doc.setFont('helvetica', 'normal');
            doc.setTextColor(122, 105, 87);
            doc.text(card.label, cx + 3, y + 7);

            doc.setFontSize(14);
            doc.setFont('helvetica', 'bold');
            doc.setTextColor(58, 46, 34);
            doc.text(card.value, cx + 3, y + 17);
        });
        y += cardH + 12;

        // ── Charts ──
        const ratingCanvas = document.getElementById('ratingDistributionChart');
        const monthlyCanvas = document.getElementById('monthlyRecommendationsChart');
        const chartW = (contentW - 6) / 2;
        const chartH = 50;

        if (ratingCanvas) {
            doc.setFontSize(10);
            doc.setFont('helvetica', 'bold');
            doc.setTextColor(58, 46, 34);
            doc.text('Rating Distribution', margin, y);
            const img1 = ratingCanvas.toDataURL('image/png');
            doc.addImage(img1, 'PNG', margin, y + 3, chartW, chartH);
        }

        if (monthlyCanvas) {
            doc.setFontSize(10);
            doc.setFont('helvetica', 'bold');
            doc.setTextColor(58, 46, 34);
            doc.text('Monthly Recommendation Sets', margin + chartW + 6, y);
            const img2 = monthlyCanvas.toDataURL('image/png');
            doc.addImage(img2, 'PNG', margin + chartW + 6, y + 3, chartW, chartH);
        }
        y += chartH + 14;

        // ── Priority Breakdown ──
        checkPage(50);
        doc.setFontSize(12);
        doc.setFont('helvetica', 'bold');
        doc.setTextColor(58, 46, 34);
        doc.text('Priority Breakdown', margin, y);
        y += 8;

        const priorities = [
            { label: 'High Priority (score 1-2)', count: @js($high), pct: @js($highPct), color: [239, 68, 68] },
            { label: 'Medium Priority (score 3)', count: @js($medium), pct: @js($mediumPct), color: [245, 158, 11] },
            { label: 'Low Priority (score 4-5)', count: @js($low), pct: @js($lowPct), color: [74, 103, 65] },
        ];

        priorities.forEach((p) => {
            doc.setFontSize(9);
            doc.setFont('helvetica', 'normal');
            doc.setTextColor(58, 46, 34);
            doc.text(p.label + ' — ' + p.count + ' reviews', margin, y);

            // bar background
            const barY = y + 2;
            const barW = contentW * 0.6;
            const barH = 3;
            doc.setFillColor(240, 233, 222);
            doc.roundedRect(margin, barY, barW, barH, 1.5, 1.5, 'F');

            // bar fill
            if (p.pct > 0) {
                doc.setFillColor(p.color[0], p.color[1], p.color[2]);
                doc.roundedRect(margin, barY, Math.max(2, barW * (p.pct / 100)), barH, 1.5, 1.5, 'F');
            }

            doc.setFontSize(8);
            doc.setTextColor(122, 105, 87);
            doc.text(p.pct + '%', margin + barW + 3, barY + 2.5);
            y += 10;
        });
        y += 6;

        // ── Category Averages ──
        checkPage(40);
        doc.setFontSize(12);
        doc.setFont('helvetica', 'bold');
        doc.setTextColor(58, 46, 34);
        doc.text('Category Averages (' + periodMeta.title + ')', margin, y);
        y += 8;

        const catLabels = { taste: 'Taste', environment: 'Environment', cleanliness: 'Cleanliness', service: 'Service' };

        Object.entries(catLabels).forEach(([key, label]) => {
            const score = Number(periodCategoryAverages[key] || 0).toFixed(2);
            doc.setFontSize(9);
            doc.setFont('helvetica', 'normal');
            doc.setTextColor(58, 46, 34);
            doc.text(label + ': ' + score + '/5', margin, y);

            const barY2 = y + 2;
            const barW2 = 60;
            const barH2 = 3;
            doc.setFillColor(240, 233, 222);
            doc.roundedRect(margin, barY2, barW2, barH2, 1.5, 1.5, 'F');
            doc.setFillColor(74, 103, 65);
            doc.roundedRect(margin, barY2, Math.max(1, barW2 * (Number(periodCategoryAverages[key] || 0) / 5)), barH2, 1.5, 1.5, 'F');
            y += 10;
        });
        y += 6;

        // ── Descriptive & Prescriptive Insights ──
        const insights = periodJourneyInsights;

        if (insights.length > 0) {
            checkPage(20);
            doc.setFontSize(12);
            doc.setFont('helvetica', 'bold');
            doc.setTextColor(58, 46, 34);
            doc.text('Descriptive & Prescriptive Insights (' + periodMeta.title + ')', margin, y);
            y += 8;

            insights.forEach((insight) => {
                checkPage(40);

                // Category header
                doc.setFontSize(10);
                doc.setFont('helvetica', 'bold');
                doc.setTextColor(58, 46, 34);
                const catLabel = insight.category_label || 'Category';
                const catScore = Number(insight.score || 0).toFixed(2);
                const pLabel = insight.priority_label || 'Low Priority';
                doc.text(catLabel + ' (' + catScore + '/5) — ' + pLabel, margin, y);
                y += 6;

                // Descriptive
                doc.setFontSize(8);
                doc.setFont('helvetica', 'bold');
                doc.setTextColor(125, 107, 87);
                doc.text('DESCRIPTIVE RECOMMENDATION', margin, y);
                y += 4;

                doc.setFontSize(9);
                doc.setFont('helvetica', 'normal');
                doc.setTextColor(107, 91, 74);
                const descLines = doc.splitTextToSize(insight.descriptive || '', contentW);
                descLines.forEach((line) => {
                    checkPage(6);
                    doc.text(line, margin, y);
                    y += 4;
                });
                y += 2;

                // Prescriptive
                const prescriptive = insight.prescriptive || [];
                if (prescriptive.length > 0) {
                    doc.setFontSize(8);
                    doc.setFont('helvetica', 'bold');
                    doc.setTextColor(125, 107, 87);
                    doc.text('PRESCRIPTIVE INSIGHTS', margin, y);
                    y += 4;

                    doc.setFontSize(9);
                    doc.setFont('helvetica', 'normal');
                    doc.setTextColor(107, 91, 74);
                    prescriptive.forEach((action) => {
                        const actionLines = doc.splitTextToSize('\u2022 ' + action, contentW - 4);
                        actionLines.forEach((line) => {
                            checkPage(6);
                            doc.text(line, margin + 2, y);
                            y += 4;
                        });
                    });
                }

                // Evidence
                if (insight.evidence) {
                    y += 1;
                    doc.setFontSize(7);
                    doc.setTextColor(158, 140, 120);
                    const evLines = doc.splitTextToSize(insight.evidence, contentW);
                    evLines.forEach((line) => {
                        checkPage(5);
                        doc.text(line, margin, y);
                        y += 3.5;
                    });
                }

                y += 6;
            });
        } else {
            checkPage(14);
            doc.setFontSize(12);
            doc.setFont('helvetica', 'bold');
            doc.setTextColor(58, 46, 34);
            doc.text('Descriptive & Prescriptive Insights (' + periodMeta.title + ')', margin, y);
            y += 7;
            doc.setFontSize(9);
            doc.setFont('helvetica', 'normal');
            doc.setTextColor(107, 91, 74);
            doc.text('No insights available for the selected filter.', margin, y);
            y += 8;
        }

        // ── Recommendation History ──
        checkPage(20);
        doc.setFontSize(12);
        doc.setFont('helvetica', 'bold');
        doc.setTextColor(58, 46, 34);
        doc.text('Recommendation History (' + periodMeta.title + ')', margin, y);
        y += 8;

        if (periodHistoryEntries.length === 0) {
            doc.setFontSize(9);
            doc.setFont('helvetica', 'normal');
            doc.setTextColor(107, 91, 74);
            doc.text('No historical recommendation sets for the selected filter.', margin, y);
            y += 8;
        } else {
            periodHistoryEntries.forEach((entry, entryIndex) => {
                const entryItems = Array.isArray(entry.items) ? entry.items : [];
                checkPage(18 + (entryItems.length * 16));

                doc.setFontSize(10);
                doc.setFont('helvetica', 'bold');
                doc.setTextColor(58, 46, 34);
                doc.text('Set ' + String(entryIndex + 1) + ': ' + String(entry.generated_label || '-'), margin, y);
                y += 5;

                doc.setFontSize(8);
                doc.setFont('helvetica', 'normal');
                doc.setTextColor(122, 105, 87);
                doc.text('Priority: ' + String(entry.priority_label || 'Low Priority') + ' • Ratings at generation: ' + String(entry.review_count || 0), margin, y);
                y += 6;

                entryItems.forEach((item) => {
                    checkPage(14);
                    doc.setFontSize(9);
                    doc.setFont('helvetica', 'bold');
                    doc.setTextColor(58, 46, 34);
                    doc.text(String(item.category_label || 'Category') + ' (' + Number(item.average_score || 0).toFixed(2) + '/5)', margin, y);
                    y += 4;

                    doc.setFontSize(8);
                    doc.setFont('helvetica', 'normal');
                    doc.setTextColor(107, 91, 74);
                    const historyInsightLines = doc.splitTextToSize(String(item.insight || ''), contentW);
                    historyInsightLines.forEach((line) => {
                        checkPage(5);
                        doc.text(line, margin + 2, y);
                        y += 3.5;
                    });

                    if (item.suggested_action) {
                        const historyActionLines = doc.splitTextToSize('Suggested action: ' + String(item.suggested_action), contentW - 2);
                        historyActionLines.forEach((line) => {
                            checkPage(5);
                            doc.text(line, margin + 2, y);
                            y += 3.5;
                        });
                    }

                    y += 3;
                });

                y += 4;
            });
        }

        // ── Recent Ratings ──
        const rawReviews = @json($latestReviews ?? []);
        const nowDate = new Date();
        const currentWeekStart = new Date(nowDate);
        const currentDay = currentWeekStart.getDay();
        const mondayOffset = currentDay === 0 ? -6 : 1 - currentDay;
        currentWeekStart.setDate(currentWeekStart.getDate() + mondayOffset);
        currentWeekStart.setHours(0, 0, 0, 0);
        const currentWeekEnd = new Date(currentWeekStart);
        currentWeekEnd.setDate(currentWeekEnd.getDate() + 6);
        currentWeekEnd.setHours(23, 59, 59, 999);

        const reviews = rawReviews
            .filter((review) => {
                if (!review?.created_at || selectedPeriod === 'all') {
                    return true;
                }

                const reviewDate = new Date(review.created_at);
                if (Number.isNaN(reviewDate.getTime())) {
                    return false;
                }

                if (selectedPeriod === 'month') {
                    return reviewDate.getFullYear() === nowDate.getFullYear()
                        && reviewDate.getMonth() === nowDate.getMonth();
                }

                return reviewDate >= currentWeekStart && reviewDate <= currentWeekEnd;
            })
            .map((review) => ({
                name: review.user_name || 'Anonymous',
                date: review.created_at
                    ? new Date(review.created_at).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' })
                    : '-',
                score: Number(review.score || 0).toFixed(1),
                taste: Number(review.taste_rating || 0),
                environment: Number(review.environment_rating || 0),
                cleanliness: Number(review.cleanliness_rating || 0),
                service: Number(review.service_rating || 0),
            }));

        if (reviews.length > 0) {
            checkPage(20);
            doc.setFontSize(12);
            doc.setFont('helvetica', 'bold');
            doc.setTextColor(58, 46, 34);
            doc.text('Recent Ratings (' + periodMeta.title + ')', margin, y);
            y += 6;

            // Table header
            const cols = ['Customer', 'Date', 'Overall', 'Taste', 'Environ.', 'Clean.', 'Service'];
            const colW = [36, 26, 18, 18, 20, 18, 18];
            doc.setFillColor(250, 247, 241);
            doc.rect(margin, y, contentW, 7, 'F');
            doc.setFontSize(7);
            doc.setFont('helvetica', 'bold');
            doc.setTextColor(158, 140, 120);
            let tx = margin + 2;
            cols.forEach((col, i) => {
                doc.text(col.toUpperCase(), tx, y + 5);
                tx += colW[i];
            });
            y += 8;

            doc.setFont('helvetica', 'normal');
            doc.setTextColor(58, 46, 34);
            doc.setFontSize(7);

            reviews.forEach((r) => {
                checkPage(8);
                tx = margin + 2;
                const vals = [r.name, r.date, r.score + '/5', r.taste + '/5', r.environment + '/5', r.cleanliness + '/5', r.service + '/5'];
                vals.forEach((val, i) => {
                    doc.text(String(val), tx, y + 4);
                    tx += colW[i];
                });
                doc.setDrawColor(240, 233, 222);
                doc.line(margin, y + 6, pageW - margin, y + 6);
                y += 7;
            });
        } else {
            checkPage(12);
            doc.setFontSize(12);
            doc.setFont('helvetica', 'bold');
            doc.setTextColor(58, 46, 34);
            doc.text('Recent Ratings (' + periodMeta.title + ')', margin, y);
            y += 6;
            doc.setFontSize(9);
            doc.setFont('helvetica', 'normal');
            doc.setTextColor(107, 91, 74);
            doc.text('No recent ratings are available for the selected filter.', margin, y);
            y += 8;
        }

        // ── Footer ──
        const footerY = pageH - 10;
        doc.setFontSize(8);
        doc.setFont('helvetica', 'italic');
        doc.setTextColor(158, 140, 120);
        doc.text('BrewHub \u2022 ' + city + ' \u2014 ' + establishment, pageW / 2, footerY, { align: 'center' });

        doc.save('Recommendation-Insights-Report.pdf');
    }

    if (scrollToTopButton) {
        const toggleScrollToTopButton = () => {
            const shouldShow = window.scrollY > 300;
            scrollToTopButton.classList.toggle('opacity-0', !shouldShow);
            scrollToTopButton.classList.toggle('pointer-events-none', !shouldShow);
            scrollToTopButton.classList.toggle('opacity-100', shouldShow);
        };

        window.addEventListener('scroll', toggleScrollToTopButton, { passive: true });
        toggleScrollToTopButton();

        scrollToTopButton.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
</script>
@endpush
