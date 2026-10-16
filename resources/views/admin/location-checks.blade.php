@extends('layouts.app')

@section('title', 'Location Checks - BrewHub')

@section('content')
<div class="min-h-screen bg-[#F5F0E8] flex">
    <aside class="admin-sidebar fixed left-0 top-0 h-screen w-64 bg-[#3A2E22] text-[#F5F0E8] flex flex-col justify-between py-6 px-4 rounded-r-xl shadow-lg overflow-y-auto z-40 -translate-x-full md:translate-x-0 transition-transform duration-300 ease-out">
        <div>
            <div class="flex items-center mb-8">
                <img src="{{ asset('images/brewhublogo2.png') }}" alt="BrewHub logo" class="w-7 h-7 mr-2 object-contain shrink-0">
                <span class="brand-wordmark text-lg leading-none"><span class="brand-brew">Brew</span><span class="brand-hub">Hub</span></span>
            </div>
            <nav class="space-y-1">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center rounded-lg px-4 py-2 text-xs font-medium gap-2 hover:bg-[#4E3D2B]">Dashboard</a>
                <a href="{{ route('admin.map') }}" class="flex items-center rounded-lg px-4 py-2 text-xs font-medium gap-2 hover:bg-[#4E3D2B]">Map</a>
                <a href="{{ route('admin.establishments.index') }}" class="flex items-center rounded-lg px-4 py-2 text-xs font-medium gap-2 hover:bg-[#4E3D2B]">Establishments</a>
                <a href="{{ route('admin.registrations.index') }}" class="flex items-center rounded-lg px-4 py-2 text-xs font-medium gap-2 hover:bg-[#4E3D2B]">Registrations</a>
                <a href="{{ route('admin.resellers.index') }}" class="flex items-center rounded-lg px-4 py-2 text-xs font-medium gap-2 hover:bg-[#4E3D2B]">Resellers</a>
                <a href="{{ route('admin.coupon-promos.index') }}" class="flex items-center rounded-lg px-4 py-2 text-xs font-medium gap-2 hover:bg-[#4E3D2B]">Coupon Promos</a>
                <a href="{{ route('admin.rating-moderation.index') }}" class="flex items-center rounded-lg px-4 py-2 text-xs font-medium gap-2 hover:bg-[#4E3D2B]">Rating Moderation</a>
                <a href="{{ route('admin.location-checks.index') }}" class="flex items-center rounded-lg bg-[#4E3D2B] px-4 py-2 text-xs font-medium gap-2">Location Checks</a>
                <a href="{{ route('admin.recommendations') }}" class="flex items-center rounded-lg px-4 py-2 text-xs font-medium gap-2 hover:bg-[#4E3D2B]">Recommendations</a>
                <a href="{{ route('admin.marketplace.index') }}" class="flex items-center rounded-lg px-4 py-2 text-xs font-medium gap-2 hover:bg-[#4E3D2B]">Store</a>
                <a href="{{ route('chat.index') }}" class="flex items-center rounded-lg px-4 py-2 text-xs font-medium gap-2 hover:bg-[#4E3D2B]">Messages</a>
            </nav>
        </div>
        <div class="flex items-center justify-between gap-3 pt-6">
            <div class="min-w-0">
                <div class="font-medium text-sm">{{ auth()->user()->name ?? 'Admin User' }}</div>
                <div class="text-xs text-[#9E8C78]">Administrator</div>
            </div>
            <button type="button" @click="$dispatch('open-logout-modal')" class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-[#F5F0E8] hover:bg-[#4E3D2B]" title="Log out" aria-label="Log out">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H9m4 8H7a2 2 0 01-2-2V6a2 2 0 012-2h6"/></svg>
            </button>
        </div>
    </aside>

    <main class="ml-0 md:ml-64 flex-1 p-4 md:p-8 overflow-y-auto">
        @if(session('success'))
            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ $errors->first() }}</div>
        @endif

        <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-display font-bold text-[#3A2E22]">Location Checks</h1>
                <p class="mt-1 text-sm text-[#9E8C78]">Review Coffee Trail feedback about establishment map pins. A report never changes coordinates automatically.</p>
            </div>
        </div>

        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
            @foreach([
                ['label' => 'All Checks', 'value' => $counts['all']],
                ['label' => 'Pending Review', 'value' => $counts['pending']],
                ['label' => 'Incorrect Location Reports', 'value' => $counts['incorrect']],
                ['label' => 'Reviewed', 'value' => $counts['reviewed']],
                ['label' => 'Dismissed', 'value' => $counts['dismissed']],
            ] as $stat)
                <div class="rounded-xl border border-[#E5DDD0] bg-white p-5 shadow-sm">
                    <p class="text-sm text-[#9E8C78]">{{ $stat['label'] }}</p>
                    <p class="mt-2 text-2xl font-bold text-[#3A2E22]">{{ $stat['value'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="mb-5 flex flex-wrap gap-2">
            @foreach(['all' => 'All', 'pending' => 'Pending', 'reviewed' => 'Reviewed', 'dismissed' => 'Dismissed'] as $key => $label)
                <a href="{{ route('admin.location-checks.index', ['status' => $key]) }}" class="rounded-lg border px-4 py-2 text-sm font-semibold transition {{ $status === $key ? 'border-[#4A6741] bg-[#4A6741] text-white' : 'border-[#D8CCBC] bg-white text-[#5E5447] hover:bg-[#F8F2E8]' }}">{{ $label }}</a>
            @endforeach
        </div>

        <div class="overflow-hidden rounded-2xl border border-[#E5DDD0] bg-white shadow-sm">
            @if($checks->isEmpty())
                <div class="p-10 text-center">
                    <div class="text-lg font-semibold text-[#3A2E22]">No location checks found</div>
                    <p class="mt-2 text-sm text-[#9E8C78]">Location validation submissions from Coffee Trail users will appear here.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[#E5DDD0]">
                        <thead class="bg-[#FAF7F2]">
                            <tr class="text-left text-xs uppercase tracking-wide text-[#8E7B66]">
                                <th class="px-5 py-4">Establishment</th>
                                <th class="px-5 py-4">Response</th>
                                <th class="px-5 py-4">Submitted by</th>
                                <th class="px-5 py-4">Trail stop</th>
                                <th class="px-5 py-4">Submitted</th>
                                <th class="px-5 py-4">Review status</th>
                                <th class="px-5 py-4">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F0E8DD]">
                            @foreach($checks as $check)
                                @php
                                    $name = $check->establishment?->name ?? $check->establishment_name_snapshot ?? 'Deleted establishment';
                                    $address = $check->establishment?->address ?? $check->establishment_address_snapshot;
                                    $incorrect = $check->response === 'no';
                                @endphp
                                <tr class="align-top hover:bg-[#FCFAF7]">
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-[#3A2E22]">{{ $name }}</div>
                                        <div class="mt-1 max-w-xs text-xs text-[#9E8C78]">{{ $address ?: 'No address recorded' }}</div>
                                        @if($check->establishment && $check->establishment->latitude !== null && $check->establishment->longitude !== null)
                                            <a class="mt-2 inline-block text-xs font-semibold text-[#4A6741] underline" target="_blank" rel="noopener" href="https://www.google.com/maps?q={{ $check->establishment->latitude }},{{ $check->establishment->longitude }}">View current pin</a>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $incorrect ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">{{ $incorrect ? '👎 Incorrect location' : '👍 Correct location' }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="text-sm font-medium text-[#3A2E22]">{{ $check->user?->name ?? 'Deleted user' }}</div>
                                        <div class="text-xs text-[#9E8C78]">{{ $check->user?->email ?? '' }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-[#5E5447]">Trail #{{ $check->coffee_trail_id }}<br>Stop {{ $check->stop_sequence }}</td>
                                    <td class="px-5 py-4 text-sm text-[#5E5447]">{{ optional($check->created_at)->format('M j, Y g:i A') }}</td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $check->review_status === 'pending' ? 'bg-amber-100 text-amber-800' : ($check->review_status === 'reviewed' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700') }}">{{ ucfirst($check->review_status) }}</span>
                                        @if($check->reviewed_at)
                                            <div class="mt-1 text-xs text-[#9E8C78]">by {{ $check->reviewer?->name ?? 'Admin' }} · {{ $check->reviewed_at->format('M j, Y') }}</div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        <form method="POST" action="{{ route('admin.location-checks.status', $check) }}" class="flex min-w-[145px] flex-col gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <select name="review_status" class="rounded-lg border border-[#D8CCBC] px-2 py-2 text-xs" aria-label="Review status for {{ $name }}">
                                                <option value="pending" @selected($check->review_status === 'pending')>Pending</option>
                                                <option value="reviewed" @selected($check->review_status === 'reviewed')>Mark reviewed</option>
                                                <option value="dismissed" @selected($check->review_status === 'dismissed')>Dismiss report</option>
                                            </select>
                                            <button type="submit" class="rounded-lg bg-[#4A6741] px-3 py-2 text-xs font-semibold text-white hover:bg-[#3A2E22]">Save status</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-[#E5DDD0] px-5 py-4">{{ $checks->links() }}</div>
            @endif
        </div>
    </main>
</div>
@endsection
