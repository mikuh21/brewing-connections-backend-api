@extends('layouts.app')

@section('title', 'Register Your Establishment - BrewHub')

@section('content')
<div class="min-h-screen bg-[#E8DDD0] px-4 py-10 sm:px-6 lg:px-8 flex items-center justify-center">
    <div class="w-full max-w-5xl overflow-hidden rounded-3xl bg-[#F3E9D7] shadow-2xl lg:grid lg:grid-cols-5">
        <aside class="hidden lg:flex lg:col-span-2 flex-col justify-between bg-gradient-to-b from-[#3A2E22] to-[#2E5A3D] p-8 text-white">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-3">
                <img src="{{ asset('images/brewhublogo2.png') }}" alt="BrewHub logo" class="h-8 w-8 object-contain">
                <span class="text-xl font-bold italic">BrewHub</span>
            </a>
            <div class="py-10">
                <div class="mb-5 text-5xl" aria-hidden="true">📍</div>
                <h1 class="text-3xl font-bold font-display leading-tight">Put your establishment on the map.</h1>
                <p class="mt-4 text-sm leading-6 text-[#F3E9D7]">Create your owner account, pin your location in Lipa, and help coffee lovers discover your establishment.</p>
                <ul class="mt-6 space-y-3 text-sm text-white/90">
                    <li class="flex gap-2"><span>✓</span><span>No admin approval queue</span></li>
                    <li class="flex gap-2"><span>✓</span><span>Your account manages only your establishment</span></li>
                    <li class="flex gap-2"><span>✓</span><span>Existing map markers remain visible</span></li>
                </ul>
            </div>
            <p class="text-xs text-white/60">BrewHub · Coffee connections in Lipa</p>
        </aside>

        <main class="lg:col-span-3 p-5 sm:p-8 lg:p-10">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-sm text-[#6B5B4A] hover:text-[#2E5A3D] lg:hidden mb-6">
                <img src="{{ asset('images/brewhublogo.png') }}" alt="" class="h-7 w-7 object-contain"> BrewHub
            </a>
            <h2 class="text-3xl font-bold italic font-display text-[#3A2E22]">Create your account</h2>
            <p class="mt-2 text-sm text-[#7B6A59]">Register your coffee farm or coffee shop, then pin its location on the map.</p>

            @if ($errors->any())
                <div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                    <p class="font-semibold">Please check the following:</p>
                    <ul class="mt-1 list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('establishment.register') }}" class="mt-6 space-y-5">
                @csrf
                <section>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-[#946042]">Establishment details</h3>
                    <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="establishment_name" class="mb-1 block text-sm font-medium text-[#3A2E22]">Establishment name *</label>
                            <input id="establishment_name" name="establishment_name" value="{{ old('establishment_name') }}" required maxlength="255" autocomplete="organization" class="w-full rounded-lg border border-[#D4C5B2] bg-white px-4 py-3 text-sm text-[#3A2E22] outline-none transition focus:border-[#2E5A3D] focus:ring-2 focus:ring-[#2E5A3D]/20" placeholder="e.g. Lipa Coffee Farm">
                        </div>
                        <div class="sm:col-span-2">
                            <label for="establishment_type" class="mb-1 block text-sm font-medium text-[#3A2E22]">Establishment type *</label>
                            <select id="establishment_type" name="establishment_type" required class="w-full rounded-lg border border-[#D4C5B2] bg-white px-4 py-3 text-sm text-[#3A2E22] outline-none transition focus:border-[#2E5A3D] focus:ring-2 focus:ring-[#2E5A3D]/20">
                                <option value="">Choose a type</option>
                                <option value="farm" @selected(old('establishment_type') === 'farm')>Coffee Farm</option>
                                <option value="cafe" @selected(old('establishment_type') === 'cafe')>Coffee Shop</option>
                            </select>
                        </div>
                        <div>
                            <label for="address" class="mb-1 block text-sm font-medium text-[#3A2E22]">Street / full address *</label>
                            <input id="address" name="address" value="{{ old('address') }}" required maxlength="255" autocomplete="street-address" class="w-full rounded-lg border border-[#D4C5B2] bg-white px-4 py-3 text-sm outline-none focus:border-[#2E5A3D] focus:ring-2 focus:ring-[#2E5A3D]/20" placeholder="Street and area">
                        </div>
                        <div>
                            <label for="barangay" class="mb-1 block text-sm font-medium text-[#3A2E22]">Barangay *</label>
                            <input id="barangay" name="barangay" value="{{ old('barangay') }}" required maxlength="255" class="w-full rounded-lg border border-[#D4C5B2] bg-white px-4 py-3 text-sm outline-none focus:border-[#2E5A3D] focus:ring-2 focus:ring-[#2E5A3D]/20" placeholder="Barangay, Lipa City">
                        </div>
                        <div>
                            <label for="contact_number" class="mb-1 block text-sm font-medium text-[#3A2E22]">Contact number *</label>
                            <input id="contact_number" name="contact_number" value="{{ old('contact_number') }}" required maxlength="30" autocomplete="tel" class="w-full rounded-lg border border-[#D4C5B2] bg-white px-4 py-3 text-sm outline-none focus:border-[#2E5A3D] focus:ring-2 focus:ring-[#2E5A3D]/20" placeholder="09XX XXX XXXX">
                        </div>
                        <div>
                            <label for="website" class="mb-1 block text-sm font-medium text-[#3A2E22]">Website <span class="font-normal text-[#8C7A68]">(optional)</span></label>
                            <input id="website" name="website" type="url" value="{{ old('website') }}" maxlength="255" class="w-full rounded-lg border border-[#D4C5B2] bg-white px-4 py-3 text-sm outline-none focus:border-[#2E5A3D] focus:ring-2 focus:ring-[#2E5A3D]/20" placeholder="https://">
                        </div>
                    </div>
                </section>

                <section class="border-t border-[#DED0BD] pt-5">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-[#946042]">Owner account</h3>
                    <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="owner_name" class="mb-1 block text-sm font-medium text-[#3A2E22]">Owner's full name *</label>
                            <input id="owner_name" name="owner_name" value="{{ old('owner_name') }}" required maxlength="255" autocomplete="name" class="w-full rounded-lg border border-[#D4C5B2] bg-white px-4 py-3 text-sm outline-none focus:border-[#2E5A3D] focus:ring-2 focus:ring-[#2E5A3D]/20" placeholder="Your full name">
                        </div>
                        <div class="sm:col-span-2">
                            <label for="email" class="mb-1 block text-sm font-medium text-[#3A2E22]">Email address *</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email" class="w-full rounded-lg border border-[#D4C5B2] bg-white px-4 py-3 text-sm outline-none focus:border-[#2E5A3D] focus:ring-2 focus:ring-[#2E5A3D]/20" placeholder="you@example.com">
                        </div>
                        <div>
                            <label for="password" class="mb-1 block text-sm font-medium text-[#3A2E22]">Password *</label>
                            <input id="password" name="password" type="password" required minlength="8" maxlength="16" autocomplete="new-password" class="w-full rounded-lg border border-[#D4C5B2] bg-white px-4 py-3 text-sm outline-none focus:border-[#2E5A3D] focus:ring-2 focus:ring-[#2E5A3D]/20" placeholder="8–16 characters">
                            <p class="mt-1 text-xs text-[#8C7A68]">Include at least one special character.</p>
                        </div>
                        <div>
                            <label for="password_confirmation" class="mb-1 block text-sm font-medium text-[#3A2E22]">Confirm password *</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" maxlength="16" autocomplete="new-password" class="w-full rounded-lg border border-[#D4C5B2] bg-white px-4 py-3 text-sm outline-none focus:border-[#2E5A3D] focus:ring-2 focus:ring-[#2E5A3D]/20" placeholder="Re-enter password">
                        </div>
                    </div>
                </section>

                <label class="flex items-start gap-3 text-sm leading-5 text-[#5D4B3A]">
                    <input type="checkbox" name="terms_agreed" value="1" required @checked(old('terms_agreed')) class="mt-1 h-4 w-4 rounded border-[#B9A58D] text-[#2E5A3D] focus:ring-[#2E5A3D]">
                    <span>I confirm that the details provided are accurate and agree to BrewHub's <a href="{{ url('/') }}#terms" class="font-semibold text-[#2E5A3D] underline underline-offset-2">Terms &amp; Conditions</a>.</span>
                </label>

                <button type="submit" class="w-full rounded-lg bg-[#2E5A3D] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1E3A2A] focus:outline-none focus:ring-2 focus:ring-[#2E5A3D]/40 focus:ring-offset-2">
                    Create Account &amp; Continue to Map
                </button>
            </form>

            <p class="mt-5 text-center text-sm text-[#3A2E22]">Already have an account? <a href="{{ route('login') }}" class="font-semibold text-[#2E5A3D] underline underline-offset-2 hover:text-[#1E3A2A]">Log in here</a></p>
            <p class="mt-3 text-center text-xs text-[#7B6A59]">Registering as a reseller instead? <a href="{{ route('login', ['register' => 'reseller']) }}" class="font-semibold text-[#2E5A3D] underline underline-offset-2 hover:text-[#1E3A2A]">Use reseller registration</a></p>
        </main>
    </div>
</div>
@endsection
