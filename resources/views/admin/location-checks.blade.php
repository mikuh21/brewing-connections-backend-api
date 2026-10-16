@extends('layouts.app')

@section('title', 'Location Checks - BrewHub')

@section('content')
<style>
    #location-checks-page .location-filter-btn[aria-selected="true"],
    #location-checks-page .location-filter-btn[aria-selected="true"]:hover,
    #location-checks-page .location-filter-btn[aria-selected="true"]:focus-visible,
    #location-checks-page .location-filter-btn[aria-selected="true"]:active {
        background-color: #4A6741 !important;
        border-color: #4A6741 !important;
        color: #fff !important;
    }
    #location-checks-page .location-filter-btn[aria-selected="false"]:hover {
        background-color: #F8F2E8;
        border-color: #4A6741;
        color: #3A2E22;
    }
    #location-checks-page .location-filter-btn:focus-visible {
        outline: 2px solid #4A6741;
        outline-offset: 2px;
    }
</style>
<div class="min-h-screen bg-[#F5F0E8] flex" id="location-checks-page" data-csrf="{{ csrf_token() }}">
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
                <a href="{{ route('admin.location-checks.index') }}" class="flex items-center rounded-lg px-4 py-2 text-xs font-medium gap-2 transition-colors duration-200 hover:bg-[#4E3D2B] {{ request()->routeIs('admin.location-checks.*') ? 'bg-[#4E3D2B]' : '' }}">
                    <svg class="w-5 h-5 shrink-0 block" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="12" cy="12" r="8.5" stroke-width="2"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8.5 12.2 2.3 2.3 4.7-4.8"/>
                    </svg>
                    Location Checks
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
                    Store
                </a>
                    <a href="{{ route('chat.index') }}" class="flex items-center rounded-lg px-4 py-2 text-xs font-medium gap-2 transition-all duration-200 hover:bg-[#4E3D2B] hover:translate-x-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        Messages
                        @php
                            $authUser = Auth::user();
                            $totalUnread = $authUser->unreadMessagesCount();
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
    <main class="ml-0 md:ml-64 flex-1 p-4 md:p-8 overflow-y-auto">
        <div id="location-checks-feedback" class="hidden mb-5 rounded-lg border px-4 py-3 text-sm" role="status" aria-live="polite"></div>

        <div class="mb-8">
            <h1 class="text-3xl font-display font-bold text-[#3A2E22] mb-1">Location Checks</h1>
            <p class="text-[#9E8C78] text-sm font-medium">Review Coffee Trail feedback about establishment map pins</p>
        </div>

        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border border-[#E5DDD0] bg-white p-5 shadow-sm">
                <p class="text-sm text-[#9E8C78]">All Establishments</p>
                <p class="mt-2 text-2xl font-bold text-[#3A2E22]" id="location-count-all">{{ $counts['all'] }}</p>
            </div>
            <div class="rounded-xl border border-[#E5DDD0] bg-white p-5 shadow-sm">
                <p class="text-sm text-[#9E8C78]">Correct Location Reports</p>
                <p class="mt-2 text-2xl font-bold text-[#3A2E22]" id="location-count-correct">{{ $counts['correct'] }}</p>
            </div>
            <div class="rounded-xl border border-[#E5DDD0] bg-white p-5 shadow-sm">
                <p class="text-sm text-[#9E8C78]">Incorrect Location Reports</p>
                <p class="mt-2 text-2xl font-bold text-[#3A2E22]" id="location-count-incorrect">{{ $counts['incorrect'] }}</p>
            </div>
            <div class="rounded-xl border border-[#E5DDD0] bg-white p-5 shadow-sm">
                <p class="text-sm text-[#9E8C78]">Sent Notice Emails</p>
                <p class="mt-2 text-2xl font-bold text-[#3A2E22]" id="location-count-sent">{{ $counts['sent'] }}</p>
            </div>
        </div>

        <div class="mb-5 flex flex-wrap gap-2" role="tablist" aria-label="Filter establishments by location reports">
            @foreach([
                'all' => 'All Establishments',
                'needs-notice' => 'Needs Notice',
                'sent' => 'Notice Sent',
                'no-incorrect' => 'No Incorrect Reports',
            ] as $key => $label)
                <button type="button" role="tab" aria-selected="{{ $key === 'all' ? 'true' : 'false' }}" data-location-filter="{{ $key }}" class="location-filter-btn rounded-lg border px-4 py-2 text-sm font-semibold transition-colors duration-150 {{ $key === 'all' ? 'border-[#4A6741] bg-[#4A6741] text-white' : 'border-[#D8CCBC] bg-white text-[#5E5447]' }}">{{ $label }}</button>
            @endforeach
        </div>

        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <label for="establishment-type-filter" class="text-sm font-semibold text-[#5E5447]">Filter by establishment type</label>
            <select id="establishment-type-filter" class="w-full sm:w-64 max-w-[192px] rounded-lg border border-[#D8CCBC] bg-white px-3 py-1.5 text-xs text-[#3A2E22] shadow-sm outline-none transition focus:border-[#4A6741] focus:ring-2 focus:ring-[#4A6741]/20">
                <option value="all">All Types</option>
                <option value="farm">Farms</option>
                <option value="coffee-shop">Coffee Shops</option>
                <option value="reseller">Resellers</option>
                <option value="roaster">Roasters</option>
            </select>
        </div>

        <div class="overflow-hidden rounded-2xl border border-[#E5DDD0] bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[#E5DDD0]">
                    <thead class="bg-[#FAF7F2]">
                        <tr class="text-left text-xs uppercase tracking-wide text-[#8E7B66]">
                            <th class="px-5 py-4">Establishment</th>
                            <th class="px-5 py-4">Correct</th>
                            <th class="px-5 py-4">Incorrect</th>
                            <th class="px-5 py-4">Notice Email</th>
                            <th class="px-5 py-4">Action</th>
                        </tr>
                    </thead>
                    <tbody id="location-checks-table-body" class="divide-y divide-[#F0E8DD]">
                        @forelse($establishments as $establishment)
                            @php
                                $correctCount = (int) ($establishment->correct_count ?? 0);
                                $incorrectCount = (int) ($establishment->incorrect_count ?? 0);
                                $noticeSent = !empty($establishment->location_notice_email_sent_at);
                                $ownerEmail = $establishment->owner?->email ?: $establishment->email;
                                $canSendNotice = !$noticeSent && is_string($ownerEmail) && filter_var($ownerEmail, FILTER_VALIDATE_EMAIL);
                            @endphp
                            <tr class="location-check-row align-middle hover:bg-[#FCFAF7]"
                                data-correct="{{ $correctCount }}"
                                data-incorrect="{{ $incorrectCount }}"
                                data-notice-sent="{{ $noticeSent ? '1' : '0' }}"
                                data-needs-notice="{{ !$noticeSent ? '1' : '0' }}"
                                data-establishment-type="{{ strtolower(str_replace([' ', '_'], '-', trim((string) ($establishment->type ?? '')))) }}">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-[#3A2E22]">{{ $establishment->name }}</div>
                                    <div class="mt-1 max-w-xs text-xs text-[#9E8C78]">{{ $establishment->address ?: 'No address recorded' }}</div>
                                    <div class="mt-1 text-xs text-[#9E8C78]">{{ ucfirst($establishment->type ?: 'establishment') }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex min-w-12 justify-center rounded-full bg-green-100 px-3 py-1 text-xs font-bold text-green-800 location-correct-count">{{ $correctCount }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex min-w-12 justify-center rounded-full bg-red-100 px-3 py-1 text-xs font-bold text-red-800 location-incorrect-count">{{ $incorrectCount }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="location-notice-status inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $noticeSent ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">{{ $noticeSent ? 'Sent' : 'Not sent' }}</span>
                                    @if($noticeSent)
                                        <div class="location-notice-sent-date mt-1 text-xs text-[#9E8C78]">{{ optional($establishment->location_notice_email_sent_at)->format('M j, Y') }} | {{ optional($establishment->location_notice_email_sent_at)->format('g:i A') }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <button type="button"
                                        class="location-send-notice-btn inline-flex min-w-[150px] items-center justify-center gap-2 rounded-lg bg-[#4A6741] px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-[#3A2E22] disabled:cursor-not-allowed disabled:bg-gray-300 disabled:text-gray-600"
                                        data-url="{{ route('admin.location-checks.send-notice', $establishment) }}"
                                        data-establishment="{{ $establishment->name }}"
                                        data-can-send="{{ $canSendNotice ? '1' : '0' }}"
                                        title="{{ $noticeSent ? 'A notice email has already been sent' : (!filter_var((string) $ownerEmail, FILTER_VALIDATE_EMAIL) ? 'No valid owner email is available' : 'Send notice email') }}"
                                        {{ $canSendNotice ? '' : 'disabled' }}>
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 5.5A2.5 2.5 0 015.5 3h13A2.5 2.5 0 0121 5.5v13a2.5 2.5 0 01-2.5 2.5h-13A2.5 2.5 0 013 18.5v-13z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m4 5 8 7 8-7"/></svg>
                                        <span>Send Notice Email</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr id="location-checks-empty">
                                <td colspan="5" class="p-10 text-center">
                                    <div class="text-lg font-semibold text-[#3A2E22]">No registered establishments found</div>
                                    <p class="mt-2 text-sm text-[#9E8C78]">Registered establishments will appear here with their Coffee Trail location-check counts.</p>
                                </td>
                            </tr>
                        @endforelse
                        <tr id="location-checks-filter-empty" class="hidden">
                            <td colspan="5" class="p-10 text-center text-sm text-[#9E8C78]">No establishments match this filter.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const page = document.getElementById('location-checks-page');
    if (!page) return;

    const rows = Array.from(page.querySelectorAll('.location-check-row'));
    const filterButtons = Array.from(page.querySelectorAll('[data-location-filter]'));
    const feedback = document.getElementById('location-checks-feedback');
    const sentCount = document.getElementById('location-count-sent');
    const filterEmpty = document.getElementById('location-checks-filter-empty');
    const typeFilter = document.getElementById('establishment-type-filter');
    let activeFilter = 'all';

    const showFeedback = (message, success = false) => {
        feedback.textContent = message;
        feedback.className = 'mb-5 rounded-lg border px-4 py-3 text-sm ' + (success
            ? 'border-green-200 bg-green-50 text-green-800'
            : 'border-red-200 bg-red-50 text-red-800');
        feedback.classList.remove('hidden');
    };

    const applyFilter = (filter) => {
        activeFilter = filter;
        let visible = 0;
        rows.forEach((row) => {
            const correct = Number(row.dataset.correct || 0);
            const incorrect = Number(row.dataset.incorrect || 0);
            const sent = row.dataset.noticeSent === '1';
            const needsNotice = row.dataset.needsNotice === '1';
            const selectedType = typeFilter?.value || 'all';
            const rawType = (row.dataset.establishmentType || '').toLowerCase().replace(/[_\s]+/g, '-');
            const typeAliases = {
                farm: ['farm', 'farms', 'coffee-farm', 'coffee-farms'],
                'coffee-shop': ['coffee-shop', 'coffee-shops', 'coffeeshop', 'cafe', 'cafes'],
                reseller: ['reseller', 'resellers'],
                roaster: ['roaster', 'roasters', 'coffee-roaster', 'coffee-roasters']
            };
            const typeMatches = selectedType === 'all' || (typeAliases[selectedType] || [selectedType]).includes(rawType);
            const reportMatches = filter === 'all'
                || (filter === 'needs-notice' && needsNotice)
                || (filter === 'sent' && sent)
                || (filter === 'no-incorrect' && incorrect === 0);
            const matches = typeMatches && reportMatches;
            row.classList.toggle('hidden', !matches);
            if (matches) visible++;
        });
        filterButtons.forEach((button) => {
            const selected = button.dataset.locationFilter === filter;
            button.setAttribute('aria-selected', selected ? 'true' : 'false');
            button.classList.toggle('bg-[#4A6741]', selected);
            button.classList.toggle('text-white', selected);
            button.classList.toggle('border-[#4A6741]', selected);
            button.classList.toggle('bg-white', !selected);
            button.classList.toggle('text-[#5E5447]', !selected);
            button.classList.toggle('border-[#D8CCBC]', !selected);
        });
        filterEmpty?.classList.toggle('hidden', visible > 0 || rows.length === 0);
    };

    typeFilter?.addEventListener('change', () => applyFilter(activeFilter));
    filterButtons.forEach((button) => button.addEventListener('click', () => applyFilter(button.dataset.locationFilter)));

    page.querySelectorAll('.location-send-notice-btn').forEach((button) => {
        button.addEventListener('click', async () => {
            if (button.disabled || button.dataset.canSend !== '1') return;
            const originalHtml = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<span class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white border-r-transparent"></span><span>Sending…</span>';
            feedback.classList.add('hidden');

            try {
                const csrf = page.dataset.csrf || document.querySelector('meta[name="csrf-token"]')?.content || '';
                const response = await fetch(button.dataset.url, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({}),
                });
                const payload = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(payload.message || 'The notice email could not be sent.');

                const row = button.closest('.location-check-row');
                row.dataset.noticeSent = '1';
                row.dataset.needsNotice = '0';
                const status = row.querySelector('.location-notice-status');
                status.textContent = 'Sent';
                status.className = 'location-notice-status inline-flex rounded-full px-3 py-1 text-xs font-semibold bg-green-100 text-green-800';
                const date = row.querySelector('.location-notice-sent-date') || document.createElement('div');
                date.className = 'location-notice-sent-date mt-1 text-xs text-[#9E8C78]';
                date.textContent = payload.sent_at
                    ? new Date(payload.sent_at).toLocaleString('en-US', {
                        month: 'short',
                        day: 'numeric',
                        year: 'numeric',
                        hour: 'numeric',
                        minute: '2-digit',
                        hour12: true,
                    }).replace(',', ' |')
                    : 'Just sent';
                if (!date.parentElement) status.parentElement.appendChild(date);
                button.dataset.canSend = '0';
                button.title = 'A notice email has already been sent';
                button.innerHTML = originalHtml;
                button.disabled = true;
                if (payload.sent_count !== undefined && sentCount) sentCount.textContent = payload.sent_count;
                showFeedback(payload.message || 'Notice email sent successfully.', true);
                applyFilter(activeFilter);
            } catch (error) {
                button.innerHTML = originalHtml;
                button.disabled = false;
                showFeedback(error.message || 'The notice email could not be sent. Please try again.');
            }
        });
    });
});
</script>
@endpush
@endsection
