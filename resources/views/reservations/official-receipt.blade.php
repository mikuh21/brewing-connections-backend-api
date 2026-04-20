@extends('layouts.app')

@section('title', 'Official Reservation Receipt')

@section('content')
<div class="min-h-screen bg-[#F3E9D7] py-10 px-4">
    <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-xl overflow-hidden border border-[#E8DCC9]">
        <div class="bg-[#3A2E22] text-white px-6 py-5 sm:px-8 sm:py-6">
            <p class="text-xs tracking-[0.2em] uppercase text-[#F3E9D7]/80 font-body">BrewHub</p>
            <h1 class="text-2xl sm:text-3xl font-semibold font-poppins mt-1">Official Reservation Receipt</h1>
            <p class="text-sm text-[#F3E9D7]/80 font-body mt-1">
                Seller: {{ $order->product?->establishment?->name ?? 'Verified Farm Seller' }}
            </p>
        </div>

        <div class="px-6 py-6 sm:px-8 sm:py-7 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="rounded-lg border border-[#D7C9B1] bg-[#F3E9D7]/45 px-4 py-3">
                    <p class="text-xs uppercase tracking-[0.14em] text-[#946042] font-body">Reservation ID</p>
                    <p class="text-base text-[#3A2E22] font-poppins font-semibold mt-1">{{ $reservationCode }}</p>
                </div>
                <div class="rounded-lg border border-[#D7C9B1] bg-[#F3E9D7]/45 px-4 py-3">
                    <p class="text-xs uppercase tracking-[0.14em] text-[#946042] font-body">Order Status</p>
                    <p class="text-base text-[#3A2E22] font-poppins font-semibold mt-1">{{ ucfirst((string) $order->status) }}</p>
                </div>
            </div>

            <div class="rounded-lg border border-[#E2D5C1] divide-y divide-[#E2D5C1] overflow-hidden">
                <div class="grid grid-cols-[120px_1fr] gap-2.5 px-4 py-2.5">
                    <p class="text-sm text-[#946042] font-body">Product</p>
                    <p class="text-sm text-[#3A2E22] font-body">{{ $order->product?->name ?? 'N/A' }}</p>
                </div>
                <div class="grid grid-cols-[120px_1fr] gap-2.5 px-4 py-2.5">
                    <p class="text-sm text-[#946042] font-body">Quantity</p>
                    <p class="text-sm text-[#3A2E22] font-body">{{ (int) $order->quantity }}</p>
                </div>
                <div class="grid grid-cols-[120px_1fr] gap-2.5 px-4 py-2.5">
                    <p class="text-sm text-[#946042] font-body">Total</p>
                    <p class="text-sm text-[#3A2E22] font-body">PHP {{ number_format((float) $order->total_price, 2) }}</p>
                </div>
                <div class="grid grid-cols-[120px_1fr] gap-2.5 px-4 py-2.5">
                    <p class="text-sm text-[#946042] font-body">Customer</p>
                    <p class="text-sm text-[#3A2E22] font-body">{{ $receiptMeta['full_name'] ?? ($order->user?->name ?? 'N/A') }}</p>
                </div>
                <div class="grid grid-cols-[120px_1fr] gap-2.5 px-4 py-2.5">
                    <p class="text-sm text-[#946042] font-body">Address</p>
                    <p class="text-sm text-[#3A2E22] font-body break-words">{{ $receiptMeta['address'] ?? 'N/A' }}</p>
                </div>
                <div class="grid grid-cols-[120px_1fr] gap-2.5 px-4 py-2.5">
                    <p class="text-sm text-[#946042] font-body">Phone</p>
                    <p class="text-sm text-[#3A2E22] font-body">{{ $receiptMeta['phone'] ?? 'N/A' }}</p>
                </div>
                <div class="grid grid-cols-[120px_1fr] gap-2.5 px-4 py-2.5">
                    <p class="text-sm text-[#946042] font-body">Created</p>
                    <p class="text-sm text-[#3A2E22] font-body">{{ optional($order->created_at)->format('M d, Y h:i A') }}</p>
                </div>
            </div>

            <p class="text-xs sm:text-sm text-[#3A2E22]/80 font-body leading-relaxed">
                This is an official BrewHub reservation record. Sellers can view this order in their marketplace dashboard in real time.
            </p>
        </div>

        <div class="px-6 py-4 sm:px-8 bg-[#FAF7F1] border-t border-[#E6DDCF] flex flex-col sm:flex-row sm:justify-end gap-2">
            <a href="{{ url()->previous() }}" class="w-full sm:w-auto inline-flex items-center justify-center bg-white text-[#3A2E22] border border-[#C8B69A] px-4 py-2 rounded-lg text-sm font-body hover:bg-[#F3E9D7] transition-colors duration-200">
                Back
            </a>
            <button type="button" onclick="window.print()" class="w-full sm:w-auto inline-flex items-center justify-center bg-[#2E5A3D] text-white px-4 py-2 rounded-lg text-sm font-body font-medium hover:bg-[#1E3A2A] transition-colors duration-200">
                Print Receipt
            </button>
        </div>
    </div>
</div>
@endsection
