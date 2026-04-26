@extends('layouts.app')

@section('title', 'Log In - BrewHub')

@section('content')
@push('styles')
<style>
    body {
        overflow: hidden !important;
        height: 100vh !important;
    }
    html {
        overflow: hidden !important;
        height: 100vh !important;
    }

    @media (max-width: 767px) {
        body,
        html {
            overflow-y: auto !important;
            overflow-x: hidden !important;
            height: auto !important;
            min-height: 100vh !important;
        }
    }
</style>
@endpush

@section('bodyClass', 'login-page')

<div class="auth-shell flex h-screen w-screen overflow-hidden bg-[#E8DDD0] items-center justify-center">
    <a href="{{ url('/') }}" class="mobile-auth-logo md:hidden fixed top-5 left-5 z-30 inline-flex items-center text-[#3A2E22] hover:text-[#2E5A3D] transition-colors">
        <span class="brand-wordmark text-xl"><span class="brand-brew">Brew</span><span class="brand-hub">Hub</span></span>
    </a>

    <div class="auth-layout w-full max-w-5xl rounded-3xl overflow-hidden shadow-2xl flex" style="height: 540px;">
        <!-- Left Panel: Onboarding Slider -->
        <div class="w-full md:w-2/5 bg-gradient-to-b from-[#3A2E22] to-[#2E5A3D] hidden md:flex flex-col relative overflow-hidden rounded-l-3xl">
            <!-- Logo -->
            <div class="absolute top-6 left-6 z-20">
                <a href="{{ url('/') }}" class="inline-flex items-center text-white hover:text-[#F3E9D7] transition-colors" aria-label="Go to landing page">
                    <span class="brand-wordmark text-xl"><span class="brand-brew">Brew</span><span class="brand-hub">Hub</span></span>
                </a>
            </div>

            <!-- Carousel -->
            <div class="flex-1 relative overflow-hidden">
                <!-- Slides -->
                <div class="slide active">
                    <div class="text-6xl mb-4">🗺️</div>
                    <h3 class="text-2xl font-bold text-white font-display mb-2">GIS Mapping</h3>
                    <p class="text-sm text-[#F3E9D7] max-w-xs mx-auto">Interactive maps to view, register and manage coffee establishments with precise location data across Lipa, Batangas</p>
                </div>
                <div class="slide">
                    <div class="text-6xl mb-4">☕</div>
                    <h3 class="text-2xl font-bold text-white font-display mb-2">AI Coffee Trail</h3>
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
                    <h3 class="text-2xl font-bold text-white font-display mb-2">Marketplace</h3>
                    <p class="text-sm text-[#F3E9D7] max-w-xs mx-auto">Order premium Kapeng Barako directly from local farms to your doorstep</p>
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

        <!-- Right Panel: Login Form -->
        <div class="auth-form-column w-full md:w-3/5 bg-[#F3E9D7] flex items-center justify-center h-full">
            <div class="auth-form-inner max-w-xl w-full h-full px-6 md:px-8 py-4 md:py-0 flex items-center justify-center">
                @php
                    $hasResellerErrors = $errors->resellerRegistration->any();
                @endphp

                <!-- Mobile Logo -->
                <div class="md:hidden sr-only" aria-hidden="true"></div>

                <!-- Login Card -->
                <div id="formPanelsStage" class="relative w-full panel-stage">
                    <div id="loginFormPanel" class="form-panel {{ $hasResellerErrors ? 'is-hidden' : 'is-active' }}">
                        <h2 class="text-3xl font-bold italic text-[#3A2E22] font-display mb-1 auth-title">Welcome Back,</h2>
                        <p class="text-sm text-gray-500 font-body mb-8 auth-subtitle">Log in to access your dashboard</p>

                        <form method="POST" action="{{ route('login') }}" class="auth-login-form">
                            @csrf

                            <!-- Email Field -->
                            <div class="mb-4">
                                <label for="email" class="block text-sm text-[#3A2E22] font-body mb-1">Email</label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}" class="bg-white border border-gray-300 rounded-lg w-full px-4 py-2.5 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#2E5A3D]" placeholder="Enter your email" required>
                            </div>

                            <!-- Password Field -->
                            <div class="mb-6">
                                <label for="password" class="block text-sm text-[#3A2E22] font-body mb-1">Password</label>
                                <div class="relative">
                                    <input id="password" name="password" type="password" placeholder="Enter your password" class="bg-white border border-gray-300 rounded-lg w-full px-4 py-2.5 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E5A3D]">
                                    <button type="button" id="togglePassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Error Message -->
                            @if($errors->any())
                                <p class="text-red-500 text-sm text-center mb-3">
                                    {{ $errors->first() }}
                                </p>
                            @endif

                            <!-- Log In Button -->
                            <button type="submit" class="bg-[#2E5A3D] text-white w-full py-2.5 rounded-lg hover:bg-[#1E3A2A] font-body font-medium text-sm transition-colors duration-200">
                                Log In
                            </button>
                        </form>

                        <div class="text-center mt-4">
                            <p class="text-sm text-[#3A2E22]">
                                Coffee Reseller?
                                <a href="#" id="showRegisterForm" class="font-medium text-[#2E5A3D] hover:text-[#1E3A2A] underline underline-offset-2 transition-colors">Register here</a>
                            </p>
                        </div>

                        <div class="text-center mt-4">
                            <a href="{{ url('/') }}" class="text-sm text-[#3A2E22] hover:text-[#2E5A3D] transition-colors">&larr; Back to Home</a>
                        </div>
                    </div>

                    <div id="registerFormPanel" class="form-panel {{ $hasResellerErrors ? 'is-active' : 'is-hidden' }}">
                        <h2 class="text-2xl font-bold italic text-[#3A2E22] font-display mb-1 auth-title">Create an Account</h2>
                        <p class="text-sm text-gray-500 font-body mb-4 auth-subtitle">Join BrewHub as a Coffee Reseller</p>

                        <form method="POST" action="{{ route('reseller.register') }}" id="resellerRegistrationForm">
                            @csrf

                            <div class="register-grid grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4">
                            <div>
                                <label for="register_name" class="block text-sm text-[#3A2E22] font-body mb-1">Full Name</label>
                                <input type="text" id="register_name" name="name" value="{{ old('name') }}" class="bg-white border border-gray-300 rounded-lg w-full px-4 py-2.5 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#2E5A3D]" placeholder="Full name/business name" required>
                                @if($errors->resellerRegistration->has('name'))
                                    <p class="text-red-500 text-xs mt-1">{{ $errors->resellerRegistration->first('name') }}</p>
                                @endif
                            </div>

                            <div>
                                <label for="register_email" class="block text-sm text-[#3A2E22] font-body mb-1">Email</label>
                                <input type="email" id="register_email" name="email" value="{{ old('email') }}" class="bg-white border border-gray-300 rounded-lg w-full px-4 py-2.5 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#2E5A3D]" placeholder="Accessible email address" required>
                                @if($errors->resellerRegistration->has('email'))
                                    <p class="text-red-500 text-xs mt-1">{{ $errors->resellerRegistration->first('email') }}</p>
                                @endif
                            </div>

                            <div>
                                <label for="register_password" class="block text-sm text-[#3A2E22] font-body mb-1">Password</label>
                                <div class="relative">
                                    <input id="register_password" name="password" type="password" class="bg-white border border-gray-300 rounded-lg w-full px-4 py-2.5 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E5A3D]" placeholder="Enter your password" required>
                                    <button type="button" id="toggleRegisterPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                        <svg id="registerEyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                </div>
                                <p id="registerPasswordClientError" class="text-red-500 text-xs mt-1 hidden"></p>
                                @if($errors->resellerRegistration->has('password'))
                                    <p class="text-red-500 text-xs mt-1">{{ $errors->resellerRegistration->first('password') }}</p>
                                @endif
                            </div>

                            <div>
                                <label for="register_password_confirmation" class="block text-sm text-[#3A2E22] font-body mb-1">Confirm Password</label>
                                <div class="relative">
                                    <input id="register_password_confirmation" name="password_confirmation" type="password" class="bg-white border border-gray-300 rounded-lg w-full px-4 py-2.5 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E5A3D]" placeholder="Confirm your password" required>
                                    <button type="button" id="toggleRegisterPasswordConfirmation" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                        <svg id="registerConfirmEyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                </div>
                                <p id="registerPasswordConfirmationClientError" class="text-red-500 text-xs mt-1 hidden"></p>
                                @if($errors->resellerRegistration->has('password_confirmation'))
                                    <p class="text-red-500 text-xs mt-1">{{ $errors->resellerRegistration->first('password_confirmation') }}</p>
                                @endif
                            </div>

                            <div>
                                <label for="register_contact_number" class="block text-sm text-[#3A2E22] font-body mb-1">Phone Number</label>
                                <input type="text" id="register_contact_number" name="contact_number" value="{{ old('contact_number') }}" class="bg-white border border-gray-300 rounded-lg w-full px-4 py-2.5 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#2E5A3D]" placeholder="Enter your contact number" required>
                                @if($errors->resellerRegistration->has('contact_number'))
                                    <p class="text-red-500 text-xs mt-1">{{ $errors->resellerRegistration->first('contact_number') }}</p>
                                @endif
                            </div>

                            <div>
                                <label for="register_barangay" class="block text-sm text-[#3A2E22] font-body mb-1">Barangay</label>
                                <input type="text" id="register_barangay" name="barangay" value="{{ old('barangay') }}" class="bg-white border border-gray-300 rounded-lg w-full px-4 py-2.5 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#2E5A3D]" placeholder="Enter your barangay" required>
                                @if($errors->resellerRegistration->has('barangay'))
                                    <p class="text-red-500 text-xs mt-1">{{ $errors->resellerRegistration->first('barangay') }}</p>
                                @endif
                            </div>

                            <div class="md:col-span-2 mt-1">
                                <input type="checkbox" id="terms_agreed" name="terms_agreed" value="1" class="sr-only" {{ old('terms_agreed') ? 'checked' : '' }}>
                                <div class="text-sm text-[#3A2E22] font-body">
                                    I agree to the
                                    <button type="button" id="openTermsModal" class="font-medium text-[#2E5A3D] hover:text-[#1E3A2A] underline underline-offset-2 transition-colors">Terms &amp; Conditions</button>
                                    <span id="termsAgreedBadge" class="text-green-600 font-medium ml-1 {{ old('terms_agreed') ? '' : 'hidden' }}">✓ Agreed</span>
                                </div>
                                <p id="termsClientError" class="text-red-500 text-xs mt-1 hidden">You must agree to the Terms &amp; Conditions to continue.</p>
                                @if($errors->resellerRegistration->has('terms_agreed'))
                                    <p class="text-red-500 text-xs mt-1">{{ $errors->resellerRegistration->first('terms_agreed') }}</p>
                                @endif
                            </div>

                            <button type="submit" class="md:col-span-2 bg-[#2E5A3D] text-white w-full py-2.5 rounded-lg hover:bg-[#1E3A2A] font-body font-medium text-sm transition-colors duration-200 mt-1">
                                Create Account
                            </button>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <p class="text-sm text-[#3A2E22]">
                                Already have an account?
                                <a href="#" id="showLoginForm" class="font-medium text-[#2E5A3D] hover:text-[#1E3A2A] underline underline-offset-2 transition-colors">Log in here</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="termsModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-[#2E5A3D]/40 px-4">
    <div class="bg-[#F3E9D7] w-full max-w-2xl rounded-2xl shadow-2xl border border-[#D9CDBA]">
        <div class="flex items-center justify-between px-6 py-4 border-b border-[#D9CDBA]">
            <h3 class="text-lg font-bold text-[#3A2E22] font-display">Terms &amp; Conditions — BrewHub Reseller Agreement</h3>
            <button type="button" id="closeTermsModal" class="text-[#3A2E22] hover:text-[#1E3A2A] text-xl leading-none">&times;</button>
        </div>
        <div id="termsModalBody" class="px-6 py-5 text-sm text-[#3A2E22] leading-relaxed overflow-y-auto max-h-[400px]">
            <p class="mb-4">Welcome to BrewHub. By registering as a reseller, you agree to the following terms:</p>

            <p class="mb-2"><strong>1. Account Verification</strong></p>
            <p class="mb-4">BrewHub Admin will verify your reseller account within 1-2 business days. You will be notified via email once your account is approved. BrewHub reserves the right to reject applications that do not meet reseller qualifications.</p>

            <p class="mb-2"><strong>2. Reseller Eligibility</strong></p>
            <p class="mb-4">You must be a legitimate business entity or individual engaged in the reselling of coffee products. You agree to provide accurate and truthful information during registration and throughout your use of the platform.</p>

            <p class="mb-2"><strong>3. Product Listings</strong></p>
            <p class="mb-4">Resellers may only list coffee-related products (Coffee Beans and Ground Coffee). All product listings must comply with BrewHub's marketplace standards. Misleading or fraudulent listings will result in immediate account suspension.</p>

            <p class="mb-2"><strong>4. Orders &amp; Transactions</strong></p>
            <p class="mb-4">Resellers are responsible for fulfilling orders placed through the BrewHub marketplace in a timely manner. Failure to fulfill orders repeatedly may result in account suspension or permanent removal.</p>

            <p class="mb-2"><strong>5. Data Privacy</strong></p>
            <p class="mb-4">Your personal and business information will be collected, stored, and used in accordance with BrewHub's Privacy Policy. We do not sell your data to third parties.</p>

            <p class="mb-2"><strong>6. Code of Conduct</strong></p>
            <p class="mb-4">Resellers must maintain professional and respectful communication with consumers, cafe owners, farm owners, and BrewHub admin through the platform's messaging system.</p>

            <p class="mb-2"><strong>7. Account Suspension &amp; Termination</strong></p>
            <p class="mb-4">BrewHub reserves the right to suspend or permanently terminate any reseller account found to be in violation of these terms, without prior notice.</p>

            <p class="mb-2"><strong>8. Amendments</strong></p>
            <p class="mb-4">BrewHub may update these Terms &amp; Conditions at any time. Continued use of the platform after changes constitutes acceptance of the new terms.</p>

            <p>By clicking 'I Agree', you confirm that you have read, understood, and agree to all of the above terms and conditions.</p>
        </div>
        <div class="px-6 py-4 border-t border-[#D9CDBA] flex justify-end gap-3">
            <button type="button" id="cancelTermsModal" class="px-4 py-2 rounded-lg border border-[#CBBCA7] text-[#3A2E22] hover:bg-[#ECE1CF] transition-colors">Cancel</button>
            <button type="button" id="agreeTermsButton" disabled class="px-4 py-2 rounded-lg text-white bg-[#8C8C8C] cursor-not-allowed">I Agree</button>
        </div>
    </div>
</div>

<style>
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
.slide.active {
    opacity: 1;
}
.dot.active {
    background: white;
    transform: scale(1.2);
}
.form-panel {
    will-change: opacity, transform;
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    justify-content: center;
    transition: opacity 420ms cubic-bezier(0.22, 1, 0.36, 1),
                transform 420ms cubic-bezier(0.22, 1, 0.36, 1),
                visibility 420ms linear;
}

.panel-stage {
    min-height: 520px;
    overflow: visible;
}

.form-panel.is-active {
    opacity: 1;
    transform: translateY(0) scale(1);
    visibility: visible;
    pointer-events: auto;
    z-index: 2;
}

.form-panel.is-hidden {
    opacity: 0;
    transform: translateY(10px) scale(0.985);
    visibility: hidden;
    pointer-events: none;
    z-index: 1;
}

#registerFormPanel {
    max-height: none;
    overflow: visible;
    padding-right: 0;
}

/* Thin, subtle scrollbar for Terms modal content */
#termsModalBody {
    scrollbar-width: thin;
    scrollbar-color: rgba(120, 120, 120, 0.55) transparent;
}

#termsModalBody::-webkit-scrollbar {
    width: 6px;
}

#termsModalBody::-webkit-scrollbar-track {
    background: transparent;
}

#termsModalBody::-webkit-scrollbar-thumb {
    background: rgba(120, 120, 120, 0.5);
    border-radius: 999px;
}

#termsModalBody::-webkit-scrollbar-thumb:hover {
    background: rgba(120, 120, 120, 0.75);
}

@media (max-width: 767px) {
    .auth-shell {
        min-height: 100svh;
        height: auto !important;
        overflow: visible !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 4.5rem 0 1.5rem;
        background: linear-gradient(180deg, #e8ddd0 0%, #efe6d8 45%, #e8ddd0 100%);
    }

    .mobile-auth-logo {
        letter-spacing: -0.02em;
    }

    .auth-layout {
        max-width: none !important;
        width: 100% !important;
        height: auto !important;
        min-height: calc(100svh - 6rem);
        border-radius: 0 !important;
        box-shadow: none !important;
        overflow: visible !important;
        background: transparent !important;
    }

    .auth-form-column {
        background: transparent !important;
        align-items: center !important;
        justify-content: center !important;
        min-height: calc(100svh - 6rem) !important;
    }

    .auth-form-inner {
        max-width: none !important;
        height: auto !important;
        min-height: calc(100svh - 6rem) !important;
        padding: 0 1.05rem 1rem !important;
        align-items: center !important;
        justify-content: center !important;
    }

    .panel-stage {
        min-height: 0 !important;
        overflow: visible !important;
    }

    .form-panel {
        position: relative !important;
        inset: auto !important;
        justify-content: flex-start !important;
        padding: 0.8rem 0 0.9rem;
        background: transparent;
        border: 0;
        border-radius: 0;
        box-shadow: none;
        backdrop-filter: none;
    }

    .form-panel.is-active {
        display: flex;
    }

    .form-panel.is-hidden {
        display: none;
        opacity: 0 !important;
        visibility: hidden !important;
        transform: none !important;
    }

    .auth-title {
        font-size: 1.85rem !important;
        line-height: 1.05 !important;
        margin-top: 0.25rem;
    }

    .auth-subtitle {
        margin-bottom: 1rem !important;
    }

    .auth-login-form .mb-6 {
        margin-bottom: 1rem !important;
    }

    .register-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        gap: 0.7rem !important;
    }

    .register-grid > div {
        min-width: 0;
    }

    .register-grid .md\:col-span-2 {
        grid-column: 1 / -1;
    }

    .register-grid label,
    .auth-login-form label {
        font-size: 0.74rem !important;
        margin-bottom: 0.32rem !important;
    }

    .register-grid input,
    .auth-login-form input {
        padding: 0.72rem 0.85rem !important;
        font-size: 0.78rem !important;
        min-height: 44px;
    }

    .register-grid input.pr-10,
    .auth-login-form input.pr-10 {
        padding-right: 2.35rem !important;
    }

    .register-grid .text-xs,
    .auth-login-form .text-xs {
        font-size: 0.68rem !important;
        line-height: 1rem !important;
    }

    .register-grid .h-5.w-5,
    .auth-login-form .h-5.w-5 {
        width: 1rem !important;
        height: 1rem !important;
    }

    #showRegisterForm,
    #showLoginForm {
        white-space: nowrap;
    }

    #loginFormPanel .text-center.mt-4,
    #registerFormPanel .text-center.mt-4 {
        margin-top: 0.9rem !important;
    }

    #registerFormPanel .text-center.mt-4:last-child {
        margin-bottom: 0.1rem;
    }

    #termsModal {
        padding: 0.9rem !important;
    }

    #termsModal > div {
        max-height: calc(100svh - 1.8rem);
    }
}
</style>

<script>
let currentSlide = 0;
const slides = document.querySelectorAll('.slide');
const dots = document.querySelectorAll('.dot');
let autoSlideInterval;

function goToSlide(index) {
    slides[currentSlide].classList.remove('active');
    dots[currentSlide].classList.remove('active');
    currentSlide = (index + slides.length) % slides.length;
    slides[currentSlide].classList.add('active');
    dots[currentSlide].classList.add('active');
}

function startAutoSlide() {
    autoSlideInterval = setInterval(() => {
        goToSlide(currentSlide + 1);
    }, 4000);
}

function resetAutoSlide() {
    clearInterval(autoSlideInterval);
    startAutoSlide();
}

dots.forEach((dot, i) => {
    dot.addEventListener('click', () => {
        goToSlide(i);
        resetAutoSlide();
    });
});

document.getElementById('prevBtn').addEventListener('click', () => {
    goToSlide(currentSlide - 1);
    resetAutoSlide();
});

document.getElementById('nextBtn').addEventListener('click', () => {
    goToSlide(currentSlide + 1);
    resetAutoSlide();
});

slides[0].classList.add('active');
dots[0].classList.add('active');
startAutoSlide();

const togglePassword = document.getElementById('togglePassword');
const passwordInput = document.getElementById('password');
const toggleRegisterPassword = document.getElementById('toggleRegisterPassword');
const registerPasswordInput = document.getElementById('register_password');
const toggleRegisterPasswordConfirmation = document.getElementById('toggleRegisterPasswordConfirmation');
const registerPasswordConfirmationInput = document.getElementById('register_password_confirmation');

const openEyePath = `
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>`;

const closedEyePath = `
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>`;

togglePassword.addEventListener('click', () => {
    const type = passwordInput.type === 'password' ? 'text' : 'password';
    passwordInput.type = type;
    
    const eyeIcon = document.getElementById('eyeIcon');
    if (type === 'text') {
        eyeIcon.innerHTML = closedEyePath;
    } else {
        eyeIcon.innerHTML = openEyePath;
    }
});

toggleRegisterPassword.addEventListener('click', () => {
    const type = registerPasswordInput.type === 'password' ? 'text' : 'password';
    registerPasswordInput.type = type;

    const registerEyeIcon = document.getElementById('registerEyeIcon');
    registerEyeIcon.innerHTML = type === 'text' ? closedEyePath : openEyePath;
});

toggleRegisterPasswordConfirmation.addEventListener('click', () => {
    const type = registerPasswordConfirmationInput.type === 'password' ? 'text' : 'password';
    registerPasswordConfirmationInput.type = type;

    const registerConfirmEyeIcon = document.getElementById('registerConfirmEyeIcon');
    registerConfirmEyeIcon.innerHTML = type === 'text' ? closedEyePath : openEyePath;
});

const loginFormPanel = document.getElementById('loginFormPanel');
const registerFormPanel = document.getElementById('registerFormPanel');
const formPanelsStage = document.getElementById('formPanelsStage');
const showRegisterForm = document.getElementById('showRegisterForm');
const showLoginForm = document.getElementById('showLoginForm');
const panelTransitionMs = 420;

function syncPanelStageHeight() {
    if (window.innerWidth <= 767) {
        const activePanel = loginFormPanel.classList.contains('is-active') ? loginFormPanel : registerFormPanel;
        formPanelsStage.style.height = `${activePanel.scrollHeight}px`;
        return;
    }

    const maxPanelHeight = Math.max(loginFormPanel.scrollHeight, registerFormPanel.scrollHeight);
    formPanelsStage.style.height = `${maxPanelHeight}px`;
}

function setPanelState(activePanel, inactivePanel) {
    activePanel.classList.remove('is-hidden');
    activePanel.classList.add('is-active');
    inactivePanel.classList.remove('is-active');
    inactivePanel.classList.add('is-hidden');
}

function showRegister() {
    setPanelState(registerFormPanel, loginFormPanel);
    setTimeout(syncPanelStageHeight, panelTransitionMs);
}

function showLogin() {
    setPanelState(loginFormPanel, registerFormPanel);
    setTimeout(syncPanelStageHeight, panelTransitionMs);
}

syncPanelStageHeight();
window.addEventListener('resize', syncPanelStageHeight);

showRegisterForm.addEventListener('click', (event) => {
    event.preventDefault();
    showRegister();
});

showLoginForm.addEventListener('click', (event) => {
    event.preventDefault();
    showLogin();
});

const termsModal = document.getElementById('termsModal');
const openTermsModal = document.getElementById('openTermsModal');
const closeTermsModal = document.getElementById('closeTermsModal');
const cancelTermsModal = document.getElementById('cancelTermsModal');
const termsModalBody = document.getElementById('termsModalBody');
const agreeTermsButton = document.getElementById('agreeTermsButton');
const termsAgreedCheckbox = document.getElementById('terms_agreed');
const termsAgreedBadge = document.getElementById('termsAgreedBadge');
const termsClientError = document.getElementById('termsClientError');

function enableAgreeButton() {
    agreeTermsButton.disabled = false;
    agreeTermsButton.classList.remove('bg-[#8C8C8C]', 'cursor-not-allowed');
    agreeTermsButton.classList.add('bg-[#2E5A3D]', 'hover:bg-[#1E3A2A]');
}

function disableAgreeButton() {
    agreeTermsButton.disabled = true;
    agreeTermsButton.classList.add('bg-[#8C8C8C]', 'cursor-not-allowed');
    agreeTermsButton.classList.remove('bg-[#2E5A3D]', 'hover:bg-[#1E3A2A]');
}

function openModal() {
    termsModal.classList.remove('hidden');
    termsModal.classList.add('flex');
    termsModalBody.scrollTop = 0;
    disableAgreeButton();
}

function closeModal() {
    termsModal.classList.remove('flex');
    termsModal.classList.add('hidden');
}

openTermsModal.addEventListener('click', openModal);
closeTermsModal.addEventListener('click', closeModal);
cancelTermsModal.addEventListener('click', closeModal);

termsModal.addEventListener('click', (event) => {
    if (event.target === termsModal) {
        closeModal();
    }
});

termsModalBody.addEventListener('scroll', () => {
    if (termsModalBody.scrollTop + termsModalBody.clientHeight >= termsModalBody.scrollHeight - 5) {
        enableAgreeButton();
    }
});

agreeTermsButton.addEventListener('click', () => {
    termsAgreedCheckbox.checked = true;
    termsAgreedBadge.classList.remove('hidden');
    termsClientError.classList.add('hidden');
    closeModal();
});

const resellerRegistrationForm = document.getElementById('resellerRegistrationForm');
const registerPassword = document.getElementById('register_password');
const registerPasswordConfirmation = document.getElementById('register_password_confirmation');
const registerPasswordClientError = document.getElementById('registerPasswordClientError');
const registerPasswordConfirmationClientError = document.getElementById('registerPasswordConfirmationClientError');

function validateRegistrationPassword() {
    const passwordValue = registerPassword.value;
    const hasSpecialCharacter = /[@$!%*#?&]/.test(passwordValue);
    let isValid = true;

    if (passwordValue.length < 8 || passwordValue.length > 16) {
        registerPasswordClientError.textContent = 'Password must be between 8 and 16 characters.';
        registerPasswordClientError.classList.remove('hidden');
        isValid = false;
    } else if (!hasSpecialCharacter) {
        registerPasswordClientError.textContent = 'Password must include at least one special character.';
        registerPasswordClientError.classList.remove('hidden');
        isValid = false;
    } else {
        registerPasswordClientError.classList.add('hidden');
    }

    if (registerPasswordConfirmation.value && registerPassword.value !== registerPasswordConfirmation.value) {
        registerPasswordConfirmationClientError.textContent = 'Password confirmation does not match.';
        registerPasswordConfirmationClientError.classList.remove('hidden');
        isValid = false;
    } else {
        registerPasswordConfirmationClientError.classList.add('hidden');
    }

    return isValid;
}

registerPassword.addEventListener('input', validateRegistrationPassword);
registerPasswordConfirmation.addEventListener('input', validateRegistrationPassword);

resellerRegistrationForm.addEventListener('submit', (event) => {
    let isFormValid = true;

    if (!validateRegistrationPassword()) {
        isFormValid = false;
    }

    if (!termsAgreedCheckbox.checked) {
        termsClientError.classList.remove('hidden');
        isFormValid = false;
    }

    if (!isFormValid) {
        event.preventDefault();
    }
});
</script>
@endsection