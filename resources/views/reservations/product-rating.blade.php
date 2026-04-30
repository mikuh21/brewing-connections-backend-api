@extends('layouts.app')

@section('title', 'Rate Product')

@section('content')
@php
    $existingRating = $order->productRating;
    $currentScore = old('overall_rating', $existingRating?->overall_rating ? (int) round((float) $existingRating->overall_rating) : null);
    $productImage = $order->product?->image_url ?? null;
    $existingRatingScore = $existingRating?->overall_rating ? (int) round((float) $existingRating->overall_rating) : 0;
    $existingRatingImageUrl = $existingRating?->image ? asset('storage/' . ltrim($existingRating->image, '/')) : null;
@endphp
<div class="min-h-screen bg-[#F3E9D7] px-4 py-6 sm:px-6 sm:py-10">
    <div class="mx-auto flex max-w-5xl flex-col gap-5 lg:flex-row lg:items-start lg:gap-6">
        <div class="w-full lg:max-w-sm">
            <div class="overflow-hidden rounded-[28px] border border-[#E6DDCF] bg-[#3A2E22] text-white shadow-[0_24px_60px_rgba(58,46,34,0.18)]">
                <div class="px-5 py-5 sm:px-6 sm:py-6">
                    <p class="text-xs uppercase tracking-[0.22em] text-[#F3E9D7]/80 font-body">BrewHub</p>
                    <h1 class="mt-2 text-3xl font-semibold leading-tight font-poppins">Product Rating</h1>
                    <p class="mt-2 text-sm leading-6 text-[#F3E9D7]/80 font-body">
                        Share one overall rating for your reservation and optionally upload a photo.
                    </p>
                </div>

                <div class="border-t border-white/10 bg-white/5 px-5 py-5 sm:px-6">
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs uppercase tracking-[0.16em] text-[#F3E9D7]/70 font-body">Reservation</p>
                            <p class="mt-1 text-base font-semibold font-poppins">{{ $reservationCode }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-[0.16em] text-[#F3E9D7]/70 font-body">Product</p>
                            <p class="mt-1 text-base font-semibold font-poppins">{{ $order->product?->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-[0.16em] text-[#F3E9D7]/70 font-body">Seller</p>
                            <p class="mt-1 text-sm font-body text-[#F3E9D7]/90">{{ $order->product?->establishment?->name ?? 'Verified Farm Seller' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-[0.16em] text-[#F3E9D7]/70 font-body">Status</p>
                            <p class="mt-1 text-sm font-body text-[#F3E9D7]/90">{{ ucfirst((string) $order->status) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full flex-1">
            <div class="overflow-hidden rounded-[28px] border border-[#E6DDCF] bg-white shadow-[0_24px_60px_rgba(58,46,34,0.12)]">
                <div class="grid gap-0 lg:grid-cols-[1.15fr_0.85fr]">
                    <div class="px-4 py-5 sm:px-7 sm:py-8">
                        @if (session('status'))
                            <div class="mb-5 rounded-2xl border border-[#B7D2BF] bg-[#EDF7F0] px-4 py-3 text-sm text-[#2E5A3D] font-body">
                                {{ session('status') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="mb-5 rounded-2xl border border-[#E4B8A8] bg-[#FFF2EE] px-4 py-3 text-sm text-[#8A3A20] font-body">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        @if ($existingRating)
                            <div class="rounded-2xl border border-[#D7C9B1] bg-[#FAF6EE] px-4 py-4 sm:px-5">
                                <p class="text-sm font-semibold text-[#3A2E22] font-poppins">Thanks for rating this product.</p>
                                <p class="mt-1 text-sm text-[#6B5B4A] font-body">
                                    You rated this order {{ (int) round((float) $existingRating->overall_rating) }}/5 on {{ optional($existingRating->created_at)->format('M d, Y') }}.
                                </p>
                                <div class="mt-4 flex flex-wrap gap-1.5" aria-label="{{ $existingRatingScore }} out of 5 stars">
                                    @for ($star = 1; $star <= 5; $star++)
                                        <span class="text-2xl {{ $star <= $existingRatingScore ? 'text-[#D18A2F]' : 'text-[#D8CFC2]' }}">&#9733;</span>
                                    @endfor
                                </div>

                                @if ($existingRatingImageUrl)
                                    <div class="mt-4 overflow-hidden rounded-2xl border border-[#E6DDCF] bg-white">
                                        <img src="{{ $existingRatingImageUrl }}" alt="Uploaded rating image for {{ $order->product?->name ?? 'product' }}" class="h-52 w-full object-cover sm:h-64">
                                    </div>
                                @endif

                                <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center">
                                    <a href="{{ $receiptUrl }}" class="inline-flex items-center justify-center rounded-lg border border-[#C8B69A] px-4 py-2.5 text-sm font-medium text-[#3A2E22] transition-colors duration-200 hover:bg-[#F3E9D7]">
                                        Back to Receipt
                                    </a>
                                    @if ($existingRatingImageUrl)
                                        <a href="{{ $existingRatingImageUrl }}" target="_blank" rel="noreferrer" class="inline-flex items-center justify-center rounded-lg bg-[#2E5A3D] px-4 py-2.5 text-sm font-medium text-white transition-colors duration-200 hover:bg-[#1E3A2A]">
                                            View Uploaded Photo
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="mb-6">
                                <p class="text-sm uppercase tracking-[0.16em] text-[#946042] font-body">Overall rating</p>
                                <p class="mt-2 text-sm leading-6 text-[#6B5B4A] font-body">
                                    Pick one score for the full product experience. Lower scores help flag issues quickly, and higher scores confirm strong product quality.
                                </p>
                            </div>

                            <form action="{{ $formAction }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                                @csrf

                                <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
                                    @for ($score = 1; $score <= 5; $score++)
                                        <label class="group cursor-pointer">
                                            <input type="radio" name="overall_rating" value="{{ $score }}" class="peer sr-only" {{ (int) $currentScore === $score ? 'checked' : '' }}>
                                            <span class="flex h-full min-h-[80px] flex-col items-center justify-center rounded-2xl border border-[#D7C9B1] bg-[#FAF6EE] px-2.5 py-3 text-center transition-all duration-150 peer-checked:border-[#2E5A3D] peer-checked:bg-[#EAF2EC] peer-checked:shadow-[0_10px_24px_rgba(46,90,61,0.14)] group-hover:border-[#B69574] sm:min-h-[88px] sm:px-3 sm:py-4">
                                                <span class="text-2xl font-semibold text-[#3A2E22] font-poppins">{{ $score }}</span>
                                                <span class="mt-1 text-xs uppercase tracking-[0.14em] text-[#946042] font-body">
                                                    {{ $score === 1 ? 'Poor' : ($score === 2 ? 'Fair' : ($score === 3 ? 'Good' : ($score === 4 ? 'Great' : 'Excellent'))) }}
                                                </span>
                                            </span>
                                        </label>
                                    @endfor
                                </div>

                                <div class="rounded-2xl border border-dashed border-[#D7C9B1] bg-[#FCF9F4] px-4 py-4 sm:px-5">
                                    <label for="photo" class="block text-sm font-semibold text-[#3A2E22] font-poppins">Optional photo</label>
                                    <p class="mt-1 text-sm leading-6 text-[#6B5B4A] font-body">
                                        Upload a product photo from your gallery or camera. This is optional.
                                    </p>
                                    <input id="photo" name="photo" type="file" accept="image/*" capture="environment" class="mt-4 block w-full rounded-xl border border-[#D7C9B1] bg-white px-3 py-3 text-sm text-[#3A2E22] file:mr-3 file:rounded-lg file:border-0 file:bg-[#2E5A3D] file:px-3 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-[#1E3A2A]">
                                </div>

                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <a href="{{ $receiptUrl }}" class="inline-flex items-center justify-center rounded-lg border border-[#C8B69A] px-4 py-2.5 text-sm font-medium text-[#3A2E22] transition-colors duration-200 hover:bg-[#F3E9D7]">
                                        Back to Receipt
                                    </a>
                                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg bg-[#2E5A3D] px-5 py-3 text-sm font-medium text-white transition-colors duration-200 hover:bg-[#1E3A2A] sm:w-auto">
                                        Submit Product Rating
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>

                    <div class="border-t border-[#EEE3D3] bg-[#FBF7F0] px-4 py-5 sm:px-7 sm:py-8 lg:border-l lg:border-t-0">
                        <div class="overflow-hidden rounded-[24px] border border-[#E6DDCF] bg-white shadow-[0_14px_30px_rgba(58,46,34,0.08)]">
                            <div class="aspect-[4/3] bg-[#F0E2CC]">
                                @if ($productImage)
                                    <img src="{{ $productImage }}" alt="{{ $order->product?->name ?? 'Product' }}" class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full items-center justify-center text-sm text-[#6B5B4A] font-body">
                                        No product image available
                                    </div>
                                @endif
                            </div>
                            <div class="px-4 py-4 sm:px-5">
                                <p class="text-lg font-semibold text-[#3A2E22] font-poppins">{{ $order->product?->name ?? 'Product' }}</p>
                                <p class="mt-1 text-sm text-[#6B5B4A] font-body">{{ $order->product?->establishment?->name ?? 'Verified Farm Seller' }}</p>
                                <div class="mt-4 space-y-2 text-sm text-[#3A2E22] font-body">
                                    <div class="flex items-start justify-between gap-3">
                                        <span class="text-[#946042]">Quantity</span>
                                        <span class="text-right">{{ (int) $order->quantity }}</span>
                                    </div>
                                    <div class="flex items-start justify-between gap-3">
                                        <span class="text-[#946042]">Total</span>
                                        <span class="text-right">PHP {{ number_format((float) $order->total_price, 2) }}</span>
                                    </div>
                                    <div class="flex items-start justify-between gap-3">
                                        <span class="text-[#946042]">Customer</span>
                                        <span class="text-right">{{ $receiptMeta['full_name'] ?? ($order->user?->name ?? 'N/A') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection