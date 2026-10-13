@extends('layouts.app')

@section('title', 'Register - BrewHub')

@section('content')
@php
    $registrationType = old('registration_type', old('establishment_type', request('type', '')));
    $isResellerRegistration = $registrationType === 'reseller';
    $resellerErrors = $errors->resellerRegistration ?? collect();
    $registrationErrorMessages = collect($errors->getMessages())->except('email')->flatten()->all();
    $resellerErrorMessages = collect($resellerErrors->getMessages())->except('email')->flatten()->all();
    $hasRegistrationErrors = count($registrationErrorMessages) > 0;
    $hasResellerErrors = count($resellerErrorMessages) > 0;
    $emailRegistrationError = $isResellerRegistration
        ? $resellerErrors->first('email')
        : $errors->first('email');
    $stepTwoErrorKeys = ['establishment_name', 'contact_number', 'address', 'barangay', 'website', 'terms_agreed'];
    $registrationStep = $errors->hasAny($stepTwoErrorKeys)
        || ($hasResellerErrors && $resellerErrors->hasAny(['contact_number', 'barangay', 'terms_agreed']))
        ? 2 : 1;
@endphp

@push('styles')
<style>
    @keyframes authPageEnter {
        from { opacity: 0; transform: translateY(5px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes authPageLeave {
        to { opacity: 0; transform: translateY(5px); }
    }
    .auth-layout { animation: authPageEnter 180ms ease-out both; }
    .auth-layout.auth-page-leaving { animation: authPageLeave 140ms ease-in both; }
    @media (prefers-reduced-motion: reduce) {
        .auth-layout { animation: none !important; }
    }
    .auth-navigation-panel {
        transition: opacity 420ms cubic-bezier(0.22, 1, 0.36, 1),
                    transform 420ms cubic-bezier(0.22, 1, 0.36, 1);
    }
    .auth-navigation-panel.registration-leaving {
        opacity: 0;
        transform: translateY(10px) scale(0.985);
        pointer-events: none;
    }
    @media (prefers-reduced-motion: reduce) {
        .auth-navigation-panel { transition: none !important; }
    }
    .registration-shell {
        box-sizing: border-box;
        min-height: 100svh;
        overflow-y: auto;
        overflow-x: hidden;
    }
    .registration-layout {
        height: 540px;
        min-height: 0;
    }
    .slide {
        position: absolute;
        inset: 0;
        opacity: 0;
        transition: opacity 0.8s ease-in-out;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        text-align: center;
    }
    .slide.active { opacity: 1; }
    .dot.active { background: white; transform: scale(1.2); }
    .registration-enter {
        animation: registrationFadeIn 420ms cubic-bezier(0.22, 1, 0.36, 1) both;
    }
    .registration-step-leaving {
        transition: opacity 420ms cubic-bezier(0.22, 1, 0.36, 1),
                    transform 420ms cubic-bezier(0.22, 1, 0.36, 1);
    }
    #registration-type-picker, #registration-form-panel {
        transition: opacity 420ms cubic-bezier(0.22, 1, 0.36, 1),
                    transform 420ms cubic-bezier(0.22, 1, 0.36, 1),
                    visibility 420ms linear;
    }
    #registration-type-picker.registration-leaving, #registration-form-panel.registration-leaving,
    .registration-step-leaving {
        opacity: 0;
        transform: translateY(10px) scale(.985);
    }
    @keyframes registrationFadeIn {
        from { opacity: 0; transform: translateY(10px) scale(.985); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    @media (prefers-reduced-motion: reduce) {
        .slide, .registration-enter, .registration-step-leaving, #registration-type-picker, #registration-form-panel {
            animation: none !important;
            transition: none !important;
        }
    }
    .registration-form-scroll {
        scrollbar-width: thin;
        scrollbar-color: #C9B69F transparent;
    }
    .registration-form-scroll::-webkit-scrollbar { width: 6px; }
    .registration-form-scroll::-webkit-scrollbar-thumb { background: #C9B69F; border-radius: 999px; }
    .registration-type-card {
        min-height: 132px;
        border: 1px solid #D9CBB9;
        background: rgba(255,255,255,.64);
        transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease, background-color .18s ease;
    }
    .registration-type-card:hover,
    .registration-type-card:focus-visible {
        transform: translateY(-2px);
        border-color: #2E5A3D;
        background: #FFFFFF;
        box-shadow: 0 10px 24px rgba(46,90,61,.12);
        outline: none;
    }
    .registration-type-card:focus-visible { outline: 2px solid #2E5A3D; outline-offset: 3px; }
    .registration-step-dot {
        display: flex; width: 2rem; height: 2rem; flex: 0 0 2rem;
        align-items: center; justify-content: center; border-radius: 9999px;
        border: 1px solid #CDBCA7; background: #FFFFFF; color: #8C7A68;
        font-size: .8rem; font-weight: 700;
    }
    .registration-step-dot.is-active,
    .registration-step-dot.is-complete { background: #2E5A3D; border-color: #2E5A3D; color: #FFFFFF; }
    .registration-step-line { height: 2px; flex: 1 1 auto; background: #D9CBB9; }
    .registration-step-line.is-complete { background: #2E5A3D; }
    .registration-field {
        width: 100%; min-height: 44px; border: 1px solid #D4C5B2; border-radius: .5rem;
        background: #FFFFFF; padding: .7rem .85rem; color: #3A2E22; font-size: .875rem;
        outline: none; transition: border-color .18s ease, box-shadow .18s ease;
    }
    .registration-field:focus { border-color: #2E5A3D; box-shadow: 0 0 0 3px rgba(46,90,61,.12); }
    @media (max-width: 767px) {
        html,
        body {
            overflow-x: hidden !important;
            overflow-y: auto !important;
            height: auto !important;
            min-height: 100vh !important;
        }

        .registration-shell {
            min-height: 100svh;
            height: auto !important;
            overflow: visible !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 4.5rem 0 1.5rem !important;
            background: linear-gradient(180deg, #e8ddd0 0%, #efe6d8 45%, #e8ddd0 100%) !important;
        }

        .registration-shell > a.mobile-auth-logo {
            top: 1.25rem;
            left: 1.25rem;
            letter-spacing: -0.02em;
        }

        .registration-layout {
            max-width: none !important;
            width: 100% !important;
            height: auto !important;
            min-height: calc(100svh - 6rem);
            border-radius: 0 !important;
            box-shadow: none !important;
            overflow: visible !important;
            background: transparent !important;
        }

        .registration-layout > main {
            min-height: calc(100svh - 6rem);
            background: transparent !important;
        }

        .registration-form-scroll {
            display: flex;
            flex: 1 1 auto;
            flex-direction: column;
            justify-content: center;
            min-height: calc(100svh - 6rem);
            overflow: visible !important;
            padding: 0 1.05rem 1rem !important;
        }

        .registration-form-scroll > div.mb-5 {
            display: none !important;
        }

        #registration-type-picker {
            width: 100%;
            margin-block: auto;
        }

        .registration-form-scroll:has(#registration-form-panel:not(.hidden)) {
            justify-content: flex-start;
            padding-top: 0.8rem !important;
        }

        #registration-form-panel {
            width: 100%;
            padding-bottom: 0.9rem;
        }

        #registration-type-picker > h2 {
            font-size: 1.85rem !important;
            line-height: 1.05 !important;
        }

        #registration-type-picker > p {
            font-size: 0.95rem;
            line-height: 1.4rem;
        }

        .registration-type-card {
            min-height: 112px;
            padding: 0.9rem !important;
        }

        .registration-type-card > span:first-child {
            width: 2.5rem;
            height: 2.5rem;
            margin-bottom: 0.65rem;
        }

        .registration-type-card > span:nth-child(2) {
            font-size: 0.95rem;
            line-height: 1.25rem;
        }

        .registration-type-card > span:nth-child(3) {
            font-size: 0.72rem;
            line-height: 1rem;
        }

        #registration-form-panel #registration-heading {
            font-size: 1.85rem !important;
            line-height: 1.05 !important;
        }

        #registration-form-panel #registration-subheading {
            font-size: 0.85rem;
            line-height: 1.25rem;
        }

        #registration-form-panel [aria-label="Registration progress"] {
            gap: 0.5rem;
        }

        #registration-form-panel [aria-label="Registration progress"] span:not(.registration-step-dot) {
            font-size: 0.72rem;
            line-height: 1rem;
        }

        #guided-registration-form {
            margin-top: 1rem !important;
        }

        #guided-registration-form .registration-field {
            min-height: 44px;
            font-size: 0.875rem;
        }
    }
    </style>
@endpush

<div class="registration-shell flex w-screen items-center justify-center bg-[#E8DDD0] px-4 py-6 sm:px-6">
    <a href="{{ url('/') }}" class="mobile-auth-logo md:hidden fixed top-5 left-5 z-30 inline-flex items-center text-[#3A2E22] hover:text-[#2E5A3D] transition-colors" aria-label="Go to landing page">
        <span class="inline-flex items-center gap-2">
            <img src="{{ asset('images/brewhublogo.png') }}" alt="BrewHub logo" class="w-7 h-7 object-contain shrink-0">
            <span class="brand-wordmark text-xl"><span class="brand-brew">Brew</span><span class="brand-hub">Hub</span></span>
        </span>
    </a>

    <div class="registration-layout auth-layout flex w-full max-w-5xl overflow-hidden rounded-3xl bg-[#F3E9D7] shadow-2xl">
        <!-- Left Panel: Onboarding Slider -->
        <div class="w-full md:w-2/5 bg-gradient-to-b from-[#3A2E22] to-[#2E5A3D] hidden md:flex flex-col relative overflow-hidden rounded-l-3xl">
            <!-- Logo -->
            <div class="absolute top-6 left-6 z-20">
                <a href="{{ url('/') }}" class="inline-flex items-center text-white hover:text-[#F3E9D7] transition-colors" aria-label="Go to landing page">
                    <span class="inline-flex items-center gap-2">
                        <img src="{{ asset('images/brewhublogo2.png') }}" alt="BrewHub logo" class="w-7 h-7 object-contain shrink-0">
                        <span class="brand-wordmark text-xl"><span class="brand-brew">Brew</span><span class="brand-hub">Hub</span></span>
                    </span>
                </a>
            </div>

            <!-- Carousel -->
            <div class="flex-1 relative overflow-hidden">
                <!-- Slides -->
                <div class="slide active">
                    <div class="text-6xl mb-4">🗺️</div>
                    <h3 class="text-2xl font-bold text-white font-display mb-2">GIS Mapping</h3>
                    <p class="text-sm text-[#F3E9D7] max-w-xs mx-auto">Interactive maps to view, register and manage coffee establishments with precise location data</p>
                </div>
                <div class="slide">
                    <div class="text-6xl mb-4">☕</div>
                    <h3 class="text-2xl font-bold text-white font-display mb-2">Coffee Trail</h3>
                    <p class="text-sm text-[#F3E9D7] max-w-xs mx-auto">Generate personalized coffee routes based on your location and taste preferences</p>
                </div>
                <div class="slide">
                    <div class="text-6xl mb-4">🎟️</div>
                    <h3 class="text-2xl font-bold text-white font-display mb-2">Smart Coupon Promo Generator</h3>
                    <p class="text-sm text-[#F3E9D7] max-w-xs mx-auto">Create and distribute smart promotional coupons to attract more customers to your establishment</p>
                </div>
                <div class="slide">
                    <div class="text-6xl mb-4">📊</div>
                    <h3 class="text-2xl font-bold text-white font-display mb-2">Recommendation Insights</h3>
                    <p class="text-sm text-[#F3E9D7] max-w-xs mx-auto">Prescriptive insights for cafe owners</p>
                </div>
                <div class="slide">
                    <div class="text-6xl mb-4">🛒</div>
                    <h3 class="text-2xl font-bold text-white font-display mb-2">Store</h3>
                    <p class="text-sm text-[#F3E9D7] max-w-xs mx-auto">Order premium coffee directly from local farms to your doorstep</p>
                </div>

                <!-- Navigation Arrows -->
                <button id="prevBtn" class="absolute left-3 top-1/2 -translate-y-1/2 z-10 w-7 h-7 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center transition-all duration-200 border border-white/30">
                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <button id="nextBtn" class="absolute right-3 top-1/2 -translate-y-1/2 z-10 w-7 h-7 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center transition-all duration-200 border border-white/30">
                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>

                <!-- Dot Indicators -->
                <div class="flex gap-2 justify-center mt-6 absolute bottom-12 left-0 right-0">
                    <button class="dot w-2.5 h-2.5 rounded-full bg-white/40 transition-all duration-300" data-index="0"></button>
                    <button class="dot w-2.5 h-2.5 rounded-full bg-white/40 transition-all duration-300" data-index="1"></button>
                    <button class="dot w-2.5 h-2.5 rounded-full bg-white/40 transition-all duration-300" data-index="2"></button>
                    <button class="dot w-2.5 h-2.5 rounded-full bg-white/40 transition-all duration-300" data-index="3"></button>
                    <button class="dot w-2.5 h-2.5 rounded-full bg-white/40 transition-all duration-300" data-index="4"></button>
                </div>
            </div>
        </div>

        <main class="flex w-full min-w-0 flex-col bg-[#F3E9D7] md:w-3/5">
            <div class="registration-form-scroll min-h-0 flex-1 overflow-y-auto px-5 py-6 sm:px-8 sm:py-7 lg:px-10">
                <div class="mb-5 md:hidden">
                    <h1 class="font-display text-2xl font-bold italic text-[#3A2E22]">Join BrewHub</h1>
                    <p class="mt-1 text-sm text-[#7B6A59]">Choose the account type that fits your business.</p>
                </div>

                @if ($hasRegistrationErrors || $hasResellerErrors)
                    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                        <p class="font-semibold">Please check the following details:</p>
                        <ul class="mt-1 list-disc pl-5">
                            @if($hasRegistrationErrors)
                                @foreach ($registrationErrorMessages as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            @endif
                            @if($hasResellerErrors)
                                @foreach ($resellerErrorMessages as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            @endif
                        </ul>
                    </div>
                @endif

                <!-- First screen: choose an account type. -->
                <section id="registration-type-picker" class="{{ $registrationType && in_array($registrationType, ['farm','cafe','roaster','reseller'], true) ? 'hidden' : '' }}">
                    <h2 class="font-display text-3xl font-bold italic text-[#3A2E22]">Create an Account</h2>
                    <p class="mt-2 text-sm leading-5 text-[#7B6A59]">What would you like to register with BrewHub?</p>

                    <div class="mt-6 grid grid-cols-2 gap-3 sm:gap-4">
                        <button type="button" class="registration-type-card group rounded-2xl p-4 text-left" data-registration-type="farm">
                            <span class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-[#E5ECDD] text-2xl">🌱</span>
                            <span class="block font-display text-base font-bold text-[#3A2E22]">Coffee Farm</span>
                            <span class="mt-1 block text-xs leading-4 text-[#7B6A59]">Grow and showcase your coffee farm.</span>
                        </button>
                        <button type="button" class="registration-type-card group rounded-2xl p-4 text-left" data-registration-type="cafe">
                            <span class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-[#F0E1D2] text-2xl">☕</span>
                            <span class="block font-display text-base font-bold text-[#3A2E22]">Coffee Shop</span>
                            <span class="mt-1 block text-xs leading-4 text-[#7B6A59]">Help customers find your café.</span>
                        </button>
                        <button type="button" class="registration-type-card group rounded-2xl p-4 text-left" data-registration-type="roaster">
                            <span class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-[#EAE3F0] text-2xl">🫘</span>
                            <span class="block font-display text-base font-bold text-[#3A2E22]">Roaster</span>
                            <span class="mt-1 block text-xs leading-4 text-[#7B6A59]">Showcase your coffee roasting business.</span>
                        </button>
                        <button type="button" class="registration-type-card group rounded-2xl p-4 text-left" data-registration-type="reseller">
                            <span class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-[#DDEAF0] text-2xl">🛍️</span>
                            <span class="block font-display text-base font-bold text-[#3A2E22]">Reseller</span>
                            <span class="mt-1 block text-xs leading-4 text-[#7B6A59]">Create an account to sell on BrewHub.</span>
                        </button>
                    </div>
                    <p class="mt-5 text-center text-sm text-[#3A2E22]">Already have an account? <a href="{{ route('login') }}" data-auth-transition class="font-semibold text-[#2E5A3D] underline underline-offset-2 hover:text-[#1E3A2A]">Log in here</a></p>
                </section>

                <!-- Guided two-step registration form. -->
                <section id="registration-form-panel" class="{{ $registrationType && in_array($registrationType, ['farm','cafe','roaster','reseller'], true) ? '' : 'hidden' }}">
                    <button id="registration-back-to-types" type="button" class="mb-4 inline-flex items-center gap-2 text-sm font-medium text-[#6B5B4A] transition hover:text-[#2E5A3D]">
                        <span aria-hidden="true">←</span> Change account type
                    </button>

                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h2 id="registration-heading" class="font-display text-2xl font-bold italic text-[#3A2E22] sm:text-3xl">Create an Account</h2>
                            <p id="registration-subheading" class="mt-1 text-sm text-[#7B6A59]">Start with your owner account details.</p>
                        </div>
                        <span id="registration-type-badge" class="shrink-0 rounded-full border border-[#D9CBB9] bg-white/70 px-3 py-1 text-xs font-semibold text-[#6B5B4A]">Coffee Farm</span>
                    </div>

                    <div class="mt-5 flex items-center gap-3" aria-label="Registration progress">
                        <div class="flex items-center gap-2">
                            <span id="registration-step-one-dot" class="registration-step-dot is-active">1</span>
                            <span id="registration-step-one-label" class="text-xs font-semibold text-[#2E5A3D] sm:text-sm">Owner account</span>
                        </div>
                        <div id="registration-step-line" class="registration-step-line"></div>
                        <div class="flex items-center gap-2">
                            <span id="registration-step-two-dot" class="registration-step-dot">2</span>
                            <span id="registration-step-two-label" class="text-xs font-medium text-[#8C7A68] sm:text-sm">Business details</span>
                        </div>
                    </div>

                    <form id="guided-registration-form" method="POST" action="{{ $isResellerRegistration ? route('reseller.register') : route('establishment.register') }}" class="mt-5" novalidate>
                        @csrf
                        <input type="hidden" name="registration_type" id="registration_type" value="{{ $registrationType }}">
                        <input type="hidden" name="establishment_type" id="establishment_type" value="{{ in_array($registrationType, ['farm','cafe','roaster'], true) ? $registrationType : '' }}">

                        <section id="registration-step-one" class="space-y-4">
                            <div class="rounded-xl border border-[#DED0BD] bg-white/45 p-4">
                                <h3 class="text-sm font-bold uppercase tracking-wider text-[#946042]">Step 1 · Owner account</h3>
                                <p class="mt-1 text-xs leading-5 text-[#7B6A59]">Use an email address you can access. It will be used to log in to BrewHub.</p>
                            </div>

                            <div class="registration-owner-only">
                                <label for="owner_name" class="mb-1 block text-sm font-medium text-[#3A2E22]">Owner's full name *</label>
                                <input id="owner_name" name="owner_name" value="{{ old('owner_name') }}" maxlength="255" autocomplete="name" class="registration-field" placeholder="Your full name">
                            </div>
                            <div class="registration-reseller-only hidden">
                                <label for="reseller_name" class="mb-1 block text-sm font-medium text-[#3A2E22]">Full name / business name *</label>
                                <input id="reseller_name" name="name" value="{{ old('name') }}" maxlength="255" autocomplete="name" class="registration-field" placeholder="Your full name or business name">
                            </div>
                            <div>
                                <label for="registration_email" class="mb-1 block text-sm font-medium text-[#3A2E22]">Email address *</label>
                                <input id="registration_email" name="email" type="email" value="{{ old('email') }}" maxlength="255" autocomplete="email" data-email-check-url="{{ route('register.email-check') }}" aria-describedby="registration-email-feedback" aria-invalid="{{ $emailRegistrationError ? 'true' : 'false' }}" class="registration-field" placeholder="you@example.com">
                                <p id="registration-email-feedback" class="mt-1 text-xs {{ $emailRegistrationError ? 'text-red-600' : 'hidden' }}" role="status" aria-live="polite">{{ $emailRegistrationError }}</p>
                            </div>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="registration_password" class="mb-1 block text-sm font-medium text-[#3A2E22]">Password *</label>
                                    <div class="relative">
                                        <input id="registration_password" name="password" type="password" minlength="8" maxlength="16" autocomplete="new-password" class="registration-field pr-12" placeholder="8–16 characters" required>
                                        <button type="button" data-toggle-password="registration_password" aria-label="Show password" aria-pressed="false" class="absolute inset-y-0 right-0 inline-flex w-11 items-center justify-center rounded-r-lg text-[#8C7A68] transition hover:text-[#2E5A3D] focus:outline-none focus:ring-2 focus:ring-inset focus:ring-[#2E5A3D]">
                                            <svg data-eye-icon viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.4-6.5 9.5-6.5 9.5 6.5 9.5 6.5-3.4 6.5-9.5 6.5S2.5 12 2.5 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </button>
                                    </div>
                                    <p class="mt-1 text-xs text-[#8C7A68]">Use 8–16 characters and at least one special character.</p>
                                </div>
                                <div>
                                    <label for="registration_password_confirmation" class="mb-1 block text-sm font-medium text-[#3A2E22]">Confirm password *</label>
                                    <div class="relative">
                                        <input id="registration_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="registration-field pr-12" placeholder="Re-enter password" required>
                                        <button type="button" data-toggle-password="registration_password_confirmation" aria-label="Show confirm password" aria-pressed="false" class="absolute inset-y-0 right-0 inline-flex w-11 items-center justify-center rounded-r-lg text-[#8C7A68] transition hover:text-[#2E5A3D] focus:outline-none focus:ring-2 focus:ring-inset focus:ring-[#2E5A3D]">
                                            <svg data-eye-icon viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.4-6.5 9.5-6.5 9.5 6.5 9.5 6.5-3.4 6.5-9.5 6.5S2.5 12 2.5 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button id="registration-next-step" type="button" class="w-full rounded-lg bg-[#2E5A3D] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1E3A2A] focus:outline-none focus:ring-2 focus:ring-[#2E5A3D]/40 focus:ring-offset-2">Continue to Step 2 <span aria-hidden="true">→</span></button>
                        </section>

                        <section id="registration-step-two" class="hidden space-y-4">
                            <div class="rounded-xl border border-[#DED0BD] bg-white/45 p-4">
                                <h3 id="registration-step-two-heading" class="text-sm font-bold uppercase tracking-wider text-[#946042]">Step 2 · Establishment details</h3>
                                <p id="registration-step-two-help" class="mt-1 text-xs leading-5 text-[#7B6A59]">Tell us about your establishment so it can be listed on the map.</p>
                            </div>

                            <div class="registration-establishment-only">
                                <label for="establishment_name" class="mb-1 block text-sm font-medium text-[#3A2E22]">Establishment name *</label>
                                <input id="establishment_name" name="establishment_name" value="{{ old('establishment_name') }}" maxlength="255" autocomplete="organization" class="registration-field" placeholder="e.g. Lipa Coffee Farm">
                            </div>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="registration_contact_number" class="mb-1 block text-sm font-medium text-[#3A2E22]">Contact number *</label>
                                    <input id="registration_contact_number" name="contact_number" type="tel" value="{{ old('contact_number') }}" maxlength="30" autocomplete="tel" inputmode="tel" pattern="[0-9+() -]{7,30}" title="Enter a valid contact number using digits and optional +, spaces, hyphens, or parentheses." class="registration-field" placeholder="09XX XXX XXXX">
                                </div>
                                <div>
                                    <label for="registration_barangay" class="mb-1 block text-sm font-medium text-[#3A2E22]">Barangay *</label>
                                    <input id="registration_barangay" name="barangay" value="{{ old('barangay') }}" maxlength="255" autocomplete="address-level3" class="registration-field" placeholder="Barangay, Lipa City">
                                </div>
                            </div>
                            <div class="registration-establishment-only">
                                <label for="registration_address" class="mb-1 block text-sm font-medium text-[#3A2E22]">Street / full address *</label>
                                <input id="registration_address" name="address" value="{{ old('address') }}" maxlength="255" autocomplete="street-address" class="registration-field" placeholder="Street and area">
                            </div>
                            <div class="registration-establishment-only">
                                <label for="registration_website" class="mb-1 block text-sm font-medium text-[#3A2E22]">Website *</label>
                                <input id="registration_website" name="website" type="url" value="{{ old('website') }}" maxlength="255" autocomplete="url" class="registration-field" placeholder="https://">
                            </div>

                            <label class="flex items-start gap-3 text-sm leading-5 text-[#5D4B3A]">
                                <input id="registration_terms" type="checkbox" name="terms_agreed" value="1" @checked(old('terms_agreed')) class="mt-1 h-4 w-4 rounded border-[#B9A58D] text-[#2E5A3D] focus:ring-[#2E5A3D]">
                                <span>I confirm the information is accurate and agree to BrewHub's <a href="{{ url('/') }}#terms" class="font-semibold text-[#2E5A3D] underline underline-offset-2">Terms &amp; Conditions</a>.</span>
                            </label>

                            <div class="flex gap-3">
                                <button id="registration-previous-step" type="button" class="w-2/5 rounded-lg border border-[#CDBCA7] bg-white px-4 py-3 text-sm font-semibold text-[#3A2E22] transition hover:bg-[#F8F2E8]">Back</button>
                                <button id="registration-submit" type="submit" class="flex-1 rounded-lg bg-[#2E5A3D] px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1E3A2A] focus:outline-none focus:ring-2 focus:ring-[#2E5A3D]/40 focus:ring-offset-2">Create Account</button>
                            </div>
                        </section>
                    </form>
                    <p class="mt-4 text-center text-sm text-[#3A2E22]"><a href="{{ route('login') }}" data-auth-transition class="font-medium text-[#2E5A3D] underline underline-offset-2 transition-colors hover:text-[#1E3A2A]">← Back to Log In</a></p>
                </section>

            </div>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    let currentSlide = 0;
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.dot');
    let autoSlideInterval;
    const goToSlide = (index) => {
        if (!slides.length || !dots.length) return;
        slides[currentSlide]?.classList.remove('active');
        dots[currentSlide]?.classList.remove('active');
        currentSlide = (index + slides.length) % slides.length;
        slides[currentSlide]?.classList.add('active');
        dots[currentSlide]?.classList.add('active');
    };
    if (slides.length && dots.length) {
        const startAutoSlide = () => {
            window.clearInterval(autoSlideInterval);
            autoSlideInterval = window.setInterval(() => goToSlide(currentSlide + 1), 4000);
        };
        dots.forEach((dot, index) => dot.addEventListener('click', () => {
            goToSlide(index);
            startAutoSlide();
        }));
        document.getElementById('prevBtn')?.addEventListener('click', () => {
            goToSlide(currentSlide - 1);
            startAutoSlide();
        });
        document.getElementById('nextBtn')?.addEventListener('click', () => {
            goToSlide(currentSlide + 1);
            startAutoSlide();
        });
        goToSlide(0);
        startAutoSlide();
    }

    document.querySelectorAll('[data-auth-transition]').forEach((link) => {
        link.addEventListener('click', (event) => {
            if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0) return;
            const destination = new URL(link.href, window.location.href);
            const path = destination.pathname.replace(/\/$/, '') || '/';
            if (destination.origin !== window.location.origin || !['/login', '/register'].includes(path)) return;
            event.preventDefault();

            const activePanel = link.closest('#registration-type-picker, #registration-form-panel');
            if (!activePanel || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                window.location.assign(destination.href);
                return;
            }

            // Reuse the same transition class as changing account type. Only this
            // right-panel section fades/moves; the outer card and feature panel do not.
            activePanel.classList.add('registration-leaving');
            window.setTimeout(() => window.location.assign(destination.href), 420);
        });
    });

    const picker = document.getElementById('registration-type-picker');
    const formPanel = document.getElementById('registration-form-panel');
    const form = document.getElementById('guided-registration-form');
    const typeInput = document.getElementById('registration_type');
    const establishmentTypeInput = document.getElementById('establishment_type');
    const stepOne = document.getElementById('registration-step-one');
    const stepTwo = document.getElementById('registration-step-two');
    const nextButton = document.getElementById('registration-next-step');
    const previousButton = document.getElementById('registration-previous-step');
    const backToTypes = document.getElementById('registration-back-to-types');
    const heading = document.getElementById('registration-heading');
    const subheading = document.getElementById('registration-subheading');
    const badge = document.getElementById('registration-type-badge');
    const stepOneDot = document.getElementById('registration-step-one-dot');
    const stepTwoDot = document.getElementById('registration-step-two-dot');
    const stepOneLabel = document.getElementById('registration-step-one-label');
    const stepTwoLabel = document.getElementById('registration-step-two-label');
    const stepLine = document.getElementById('registration-step-line');
    const sideIcon = document.getElementById('registration-side-icon');
    const sideTitle = document.getElementById('registration-side-title');
    const sideDescription = document.getElementById('registration-side-description');
    const typeDetails = {
        farm: { label: 'Coffee Farm', icon: '🌱', sideTitle: 'Grow your coffee story.', sideDescription: 'Register your farm, share its location, and help coffee lovers discover what you grow.', endpoint: '{{ route('establishment.register') }}' },
        cafe: { label: 'Coffee Shop', icon: '☕', sideTitle: 'Bring coffee lovers to your café.', sideDescription: 'Create your owner account and put your coffee shop on the BrewHub map.', endpoint: '{{ route('establishment.register') }}' },
        roaster: { label: 'Roaster', icon: '🫘', sideTitle: 'Showcase your roast.', sideDescription: 'Make your roasting business easier for coffee lovers to find.', endpoint: '{{ route('establishment.register') }}' },
        reseller: { label: 'Reseller', icon: '🛍️', sideTitle: 'Grow your coffee business.', sideDescription: 'Create a BrewHub reseller account and get started with the marketplace.', endpoint: '{{ route('reseller.register') }}' }
    };
    let currentStep = 1;

    const setRequired = (selector, required) => {
        form.querySelectorAll(selector).forEach((field) => { field.required = required; });
    };

    const emailInput = form.querySelector('#registration_email');
    const emailFeedback = document.getElementById('registration-email-feedback');
    let emailCheckTimer = null;
    let emailCheckController = null;
    let emailAvailability = emailInput?.getAttribute('aria-invalid') === 'true' ? 'taken' : 'idle';
    let checkedEmail = emailAvailability === 'taken' ? emailInput.value.trim().toLowerCase() : '';

    const setEmailFeedback = (message = '', state = 'idle') => {
        if (!emailInput || !emailFeedback) return;
        emailAvailability = state;
        emailFeedback.textContent = message;
        emailFeedback.classList.toggle('hidden', !message);
        emailFeedback.classList.toggle('text-red-600', state === 'taken' || state === 'invalid');
        emailFeedback.classList.toggle('text-[#8C7A68]', state === 'checking');
        emailInput.setAttribute('aria-invalid', state === 'taken' || state === 'invalid' ? 'true' : 'false');
        emailInput.setCustomValidity('');
    };

    const checkEmailAvailability = async (rawEmail) => {
        const email = rawEmail.trim().toLowerCase();
        if (!emailInput || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email) || email.length > 255) {
            setEmailFeedback('', 'idle');
            checkedEmail = '';
            return null;
        }

        if (emailCheckController) emailCheckController.abort();
        emailCheckController = new AbortController();
        const requestController = emailCheckController;
        checkedEmail = email;
        setEmailFeedback('Checking email...', 'checking');

        try {
            const response = await fetch(emailInput.dataset.emailCheckUrl + '?email=' + encodeURIComponent(email), {
                method: 'GET',
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin',
                signal: requestController.signal
            });
            const result = await response.json();
            if (requestController !== emailCheckController || emailInput.value.trim().toLowerCase() !== email) return null;

            if (result.available === false) {
                setEmailFeedback('This email is registered. Try another email.', 'taken');
                return false;
            }

            setEmailFeedback('', 'available');
            return true;
        } catch (error) {
            if (error.name === 'AbortError') return null;
            // The registration endpoint still enforces uniqueness if this check is unavailable.
            setEmailFeedback('', 'idle');
            return null;
        }
    };

    emailInput?.addEventListener('input', () => {
        window.clearTimeout(emailCheckTimer);
        if (emailCheckController) emailCheckController.abort();
        checkedEmail = '';
        setEmailFeedback('', 'idle');

        const email = emailInput.value.trim();
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email) || email.length > 255) return;

        emailCheckTimer = window.setTimeout(() => {
            checkEmailAvailability(email);
        }, 400);
    });

    emailInput?.addEventListener('blur', () => {
        window.clearTimeout(emailCheckTimer);
        const email = emailInput.value.trim();
        if (/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email) && email.length <= 255
            && checkedEmail !== email.toLowerCase()) {
            checkEmailAvailability(email);
        }
    });

    const setStep = (step) => {
        currentStep = step;
        const outgoingStep = step === 1 ? stepTwo : stepOne;
        const incomingStep = step === 1 ? stepOne : stepTwo;
        outgoingStep.classList.add('registration-step-leaving');
        window.setTimeout(() => {
            outgoingStep.classList.add('hidden');
            outgoingStep.classList.remove('registration-step-leaving');
            incomingStep.classList.remove('hidden');
            incomingStep.classList.remove('registration-enter');
            void incomingStep.offsetWidth;
            incomingStep.classList.add('registration-enter');
        }, window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 420);
        stepOneDot.classList.toggle('is-active', step === 1);
        stepOneDot.classList.toggle('is-complete', step === 2);
        stepTwoDot.classList.toggle('is-active', step === 2);
        stepOneLabel.classList.toggle('text-[#2E5A3D]', step === 1 || step === 2);
        stepOneLabel.classList.toggle('text-[#8C7A68]', false);
        stepTwoLabel.classList.toggle('text-[#2E5A3D]', step === 2);
        stepTwoLabel.classList.toggle('text-[#8C7A68]', step !== 2);
        stepLine.classList.toggle('is-complete', step === 2);

        setRequired('#owner_name, #reseller_name, #registration_email, #registration_password, #registration_password_confirmation', step === 1);
        setRequired('#establishment_name, #registration_contact_number, #registration_barangay, #registration_address, #registration_website', step === 2);
        setRequired('#registration_terms', step === 2);

        if (typeInput.value === 'reseller') {
            setRequired('#owner_name, #establishment_name, #registration_address', false);
            setRequired('#reseller_name', step === 1);
            setRequired('#registration_contact_number, #registration_barangay', step === 2);
        } else {
            setRequired('#reseller_name', false);
        }
        window.setTimeout(() => {
            const firstField = step === 2
                ? (typeInput.value === 'reseller' ? form.querySelector('#registration_contact_number') : form.querySelector('#establishment_name'))
                : (typeInput.value === 'reseller' ? form.querySelector('#reseller_name') : form.querySelector('#owner_name'));
            if (firstField && !firstField.closest('.hidden')) firstField.focus({ preventScroll: true });
        }, window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 430);
    };

    const chooseType = (type) => {
        const details = typeDetails[type];
        if (!details) return;
        typeInput.value = type;
        establishmentTypeInput.value = ['farm', 'cafe', 'roaster'].includes(type) ? type : '';
        form.action = details.endpoint;
        showScreen(picker, formPanel);
        badge.textContent = details.label;
        heading.textContent = type === 'reseller' ? 'Create a Reseller Account' : 'Create an Owner Account';
        subheading.textContent = 'Start with your owner account details.';
        sideIcon.textContent = details.icon;
        sideTitle.textContent = details.sideTitle;
        sideDescription.textContent = details.sideDescription;
        document.getElementById('registration-step-two-heading').textContent = type === 'reseller' ? 'Step 2 · Reseller details' : 'Step 2 · Establishment details';
        document.getElementById('registration-step-two-help').textContent = type === 'reseller'
            ? 'Add your contact information to complete your reseller profile.'
            : 'Tell us about your establishment so it can be listed on the map.';
        form.querySelectorAll('.registration-establishment-only').forEach((element) => element.classList.toggle('hidden', type === 'reseller'));
        form.querySelectorAll('.registration-owner-only').forEach((element) => element.classList.toggle('hidden', type === 'reseller'));
        form.querySelectorAll('.registration-reseller-only').forEach((element) => element.classList.toggle('hidden', type !== 'reseller'));
        setStep(1);
    };

    const showScreen = (from, to) => {
        if (from === to) return;
        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        from.classList.add('registration-leaving');
        window.setTimeout(() => {
            from.classList.add('hidden');
            from.classList.remove('registration-leaving');
            to.classList.remove('hidden');
            to.classList.remove('registration-enter');
            void to.offsetWidth;
            to.classList.add('registration-enter');
        }, reducedMotion ? 0 : 420);
    };

    document.querySelectorAll('[data-registration-type]').forEach((button) => {
        button.addEventListener('click', () => chooseType(button.dataset.registrationType));
    });

    const validateVisibleRequiredFields = (section) => {
        const fields = Array.from(section.querySelectorAll('input, select, textarea'))
            .filter((field) => field.type !== 'hidden' && field.type !== 'button' && !field.disabled && !field.closest('.hidden'));
        for (const field of fields) {
            const isOptional = field.dataset.optional === 'true';
            if (isOptional && !field.value.trim()) {
                field.setCustomValidity('');
                continue;
            }
            if (field.type === 'checkbox') {
                field.setCustomValidity(field.checked ? '' : 'Please accept the Terms & Conditions to continue.');
            } else if (!field.value.trim()) {
                field.setCustomValidity('This field is required.');
            } else {
                field.setCustomValidity('');
            }
            if (!field.checkValidity()) {
                field.reportValidity();
                field.focus({ preventScroll: false });
                return false;
            }
        }
        return true;
    };

    const validatePasswordFields = () => {
        const password = form.querySelector('#registration_password');
        const confirmation = form.querySelector('#registration_password_confirmation');
        password.setCustomValidity('');
        confirmation.setCustomValidity('');

        if (password.value.length < 8 || password.value.length > 16) {
            password.setCustomValidity('Password must be between 8 and 16 characters.');
            password.reportValidity();
            password.focus();
            return false;
        }
        if (!/[@$!%*#?&]/.test(password.value)) {
            password.setCustomValidity('Password must include at least one special character.');
            password.reportValidity();
            password.focus();
            return false;
        }
        if (password.value !== confirmation.value) {
            confirmation.setCustomValidity('Passwords do not match.');
            confirmation.reportValidity();
            confirmation.focus();
            return false;
        }
        return true;
    };

    nextButton.addEventListener('click', async () => {
        if (!validateVisibleRequiredFields(stepOne)) return;
        emailInput.setCustomValidity('');
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value.trim())) {
            emailInput.setCustomValidity('Enter a valid email address.');
            emailInput.reportValidity();
            emailInput.focus();
            return;
        }

        window.clearTimeout(emailCheckTimer);
        const emailIsAlreadyChecked = emailAvailability === 'available'
            && checkedEmail === emailInput.value.trim().toLowerCase();
        if (!emailIsAlreadyChecked) {
            const availability = await checkEmailAvailability(emailInput.value);
            if (availability === false) {
                emailInput.focus();
                return;
            }
        }
        if (emailAvailability === 'taken') {
            emailInput.focus();
            return;
        }

        if (!validatePasswordFields()) return;
        setStep(2);
    });

    form.querySelector('#registration_password').addEventListener('input', () => {
        form.querySelector('#registration_password').setCustomValidity('');
        form.querySelector('#registration_password_confirmation').setCustomValidity('');
    });
    form.querySelector('#registration_password_confirmation').addEventListener('input', (event) => {
        event.currentTarget.setCustomValidity('');
    });
    form.querySelector('#registration_email').addEventListener('input', (event) => {
        event.currentTarget.setCustomValidity('');
    });

    form.querySelectorAll('[data-toggle-password]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.togglePassword);
            if (!input) return;
            const shouldShow = input.type === 'password';
            input.type = shouldShow ? 'text' : 'password';
            button.setAttribute('aria-pressed', shouldShow ? 'true' : 'false');
            const isConfirmation = input.id === 'registration_password_confirmation';
            button.setAttribute('aria-label', shouldShow
                ? (isConfirmation ? 'Hide confirm password' : 'Hide password')
                : (isConfirmation ? 'Show confirm password' : 'Show password'));
            button.innerHTML = shouldShow
                ? '<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 10.7a2 2 0 002.7 2.7M9.9 5.6A10.8 10.8 0 0112 5.5c6.1 0 9.5 6.5 9.5 6.5a16 16 0 01-3.2 4.1M6.2 6.3C3.8 8 2.5 12 2.5 12s3.4 6.5 9.5 6.5a10 10 0 003.2-.5"/></svg>'
                : '<svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.4-6.5 9.5-6.5 9.5 6.5 9.5 6.5-3.4 6.5-9.5 6.5S2.5 12 2.5 12Z"/><circle cx="12" cy="12" r="3"/></svg>';
        });
    });

    previousButton.addEventListener('click', () => setStep(1));
    backToTypes.addEventListener('click', () => {
        showScreen(formPanel, picker);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    form.addEventListener('submit', (event) => {
        if (currentStep !== 2) {
            event.preventDefault();
            return;
        }
        if (!validateVisibleRequiredFields(stepTwo)) {
            event.preventDefault();
            return;
        }
        const contactNumber = form.querySelector('#registration_contact_number');
        const normalizedPhone = contactNumber.value.replace(/[\s()+-]/g, '');
        if (!/^\d{7,15}$/.test(normalizedPhone)) {
            event.preventDefault();
            contactNumber.setCustomValidity('Enter a valid contact number containing 7 to 15 digits.');
            contactNumber.reportValidity();
            contactNumber.focus();
            return;
        }
        contactNumber.setCustomValidity('');

        if (typeInput.value !== 'reseller' && form.querySelector('#registration_website').value.trim()) {
            const website = form.querySelector('#registration_website');
            website.setCustomValidity('');
            if (!website.checkValidity()) {
                event.preventDefault();
                website.reportValidity();
                website.focus();
                return;
            }
        }
        if (!validatePasswordFields()) {
            event.preventDefault();
            setStep(1);
            return;
        }
    });

    const initialType = typeInput.value || '';
    if (typeDetails[initialType]) {
        chooseType(initialType);
        picker.classList.add('hidden');
        formPanel.classList.remove('hidden');
        @if($hasRegistrationErrors || $hasResellerErrors)
            setStep({{ $registrationStep }});
        @endif
    }
});
</script>
@endsection
