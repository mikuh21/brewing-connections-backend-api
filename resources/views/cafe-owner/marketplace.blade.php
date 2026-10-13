@extends('cafe-owner.layouts.app')

@section('title', 'Cafe Menu - BrewHub')

@section('content')
@php
    $menuUrl = $establishment?->menu_image;
    $menuExtension = strtolower(pathinfo(parse_url((string) $menuUrl, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION));
    $isPdfMenu = $menuExtension === 'pdf';
@endphp

<div class="mx-auto max-w-5xl space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-3xl font-display font-bold text-[#3A2E22] mb-1">My <span class="italic text-[#4A6741]">Cafe Menu</span></h1>
            <p class="text-sm font-medium text-[#9E8C78]">Upload and maintain the menu customers can view when discovering your coffee shop.</p>
        </div>
        @if($menuUrl)
            <span class="inline-flex items-center gap-2 rounded-full bg-green-50 px-3 py-1.5 text-xs font-semibold text-green-700 border border-green-200">
                <span class="h-2 w-2 rounded-full bg-green-500"></span> Menu uploaded
            </span>
        @else
            <span class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 border border-amber-200">
                <span class="h-2 w-2 rounded-full bg-amber-500"></span> No menu uploaded
            </span>
        @endif
    </div>

    @if(session('status'))
        <div class="flex items-start gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">
            <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/></svg>
            <p>{{ session('status') }}</p>
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(!$establishment)
        <div class="rounded-2xl border border-[#E5DDD0] bg-white p-8 text-center shadow-sm">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-[#EEF4EA] text-[#4A6741]">
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M5 10v10h14V10M4 10l2-6h12l2 6M9 20v-6h6v6"/></svg>
            </div>
            <h2 class="text-xl font-display font-bold text-[#3A2E22]">Set up your coffee shop first</h2>
            <p class="mx-auto mt-2 max-w-lg text-sm text-[#9E8C78]">Complete your coffee shop profile before uploading a menu for customers to view.</p>
            <a href="{{ route('cafe-owner.my-cafe') }}" class="mt-5 inline-flex min-h-10 items-center justify-center rounded-lg bg-[#4A6741] px-5 py-2 text-sm font-semibold text-white transition hover:bg-[#3A2E22]">Set Up My Cafe</a>
        </div>
    @else
        <section class="overflow-hidden rounded-2xl border border-[#E5DDD0] bg-white shadow-sm">
            <div class="border-b border-[#EDE5DA] bg-[#FAF7F2] px-5 py-4 sm:px-6">
                <h2 class="text-lg font-display font-bold text-[#3A2E22]">{{ $establishment->name }}</h2>
                <p class="mt-1 text-xs text-[#9E8C78]">Menu file · JPG, PNG, WEBP, or PDF · Maximum 10 MB</p>
            </div>

            @if($menuUrl)
                <div class="p-4 sm:p-6">
                    <div class="overflow-hidden rounded-xl border border-[#E5DDD0] bg-[#FAF7F2]">
                        @if($isPdfMenu)
                            <iframe src="{{ $menuUrl }}" title="{{ $establishment->name }} menu PDF" class="h-[65vh] min-h-[420px] w-full" loading="lazy"></iframe>
                        @else
                            <a href="{{ $menuUrl }}" target="_blank" rel="noopener noreferrer" class="block">
                                <img src="{{ $menuUrl }}" alt="{{ $establishment->name }} menu" class="mx-auto max-h-[70vh] w-full object-contain" />
                            </a>
                        @endif
                    </div>
                    <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                        <p class="text-xs text-[#9E8C78]">This is the menu currently visible to visitors on the BrewHub website.</p>
                        <a href="{{ $menuUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-9 items-center gap-2 rounded-lg border border-[#D8CCBA] bg-white px-3 py-2 text-xs font-semibold text-[#3A2E22] hover:bg-[#FAF7F2]">
                            View Full Menu
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14 3h7v7m-1-6L10 14M19 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h6"/></svg>
                        </a>
                    </div>
                </div>
            @else
                <div class="px-5 py-12 text-center sm:px-8">
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-[#EEF4EA] text-[#4A6741]">
                        <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7 3h8l5 5v13H7z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 3v5h5M10 13h7M10 17h7"/></svg>
                    </div>
                    <h3 class="text-lg font-display font-bold text-[#3A2E22]">Upload your cafe menu</h3>
                    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-[#9E8C78]">Share a clear image or PDF of your menu. Customers can view it from your coffee shop profile. You do not need to add individual products, prices, stock, or orders.</p>
                </div>
            @endif

            <div class="border-t border-[#EDE5DA] p-5 sm:p-6">
                <form method="POST" action="{{ route('cafe-owner.marketplace.menu.store') }}" enctype="multipart/form-data" class="flex flex-col gap-4 sm:flex-row sm:items-end">
                    @csrf
                    <div class="min-w-0 flex-1">
                        <label for="menu_file" class="mb-2 block text-sm font-semibold text-[#3A2E22]">{{ $menuUrl ? 'Replace menu file' : 'Choose menu file' }}</label>
                        <input id="menu_file" name="menu_file" type="file" accept=".jpg,.jpeg,.png,.webp,.pdf,image/jpeg,image/png,image/webp,application/pdf" required class="block w-full rounded-lg border border-[#D8CCBA] bg-white text-sm text-[#6B5B4A] file:mr-4 file:border-0 file:bg-[#F5F0E8] file:px-4 file:py-3 file:text-sm file:font-semibold file:text-[#3A2E22] hover:file:bg-[#EDE3D5] focus:outline-none focus:ring-2 focus:ring-[#4A6741]" />
                        <p class="mt-2 text-xs text-[#9E8C78]">Use a readable, up-to-date menu. Existing menu stays published until the replacement upload succeeds.</p>
                    </div>
                    <button type="submit" class="inline-flex min-h-11 shrink-0 items-center justify-center gap-2 rounded-lg bg-[#4A6741] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#3A2E22]">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16.5V20h16v-3.5M12 4v11m-4-7 4-4 4 4"/></svg>
                        {{ $menuUrl ? 'Replace Menu' : 'Upload Menu' }}
                    </button>
                </form>

                @if($menuUrl)
                    <form method="POST" action="{{ route('cafe-owner.marketplace.menu.destroy') }}" class="mt-4" onsubmit="return confirm('Remove the published menu from your coffee shop profile?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs font-semibold text-red-600 underline decoration-red-200 underline-offset-4 hover:text-red-700">Remove current menu</button>
                    </form>
                @endif
            </div>
        </section>
    @endif
</div>
@endsection
