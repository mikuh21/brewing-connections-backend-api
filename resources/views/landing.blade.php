@extends('layouts.app')

@section('title', 'BrewHub')

@push('styles')
<style>
    /* Smooth reveal animations */
    .reveal {
        opacity: 0;
        transform: translateY(30px);
        transition: opacity 0.7s ease,
                    transform 0.7s ease;
    }
    .reveal.visible {
        opacity: 1;
        transform: translateY(0);
    }
    .reveal-left {
        opacity: 0;
        transform: translateX(-40px);
        transition: opacity 0.7s ease,
                    transform 0.7s ease;
    }
    .reveal-left.visible {
        opacity: 1;
        transform: translateX(0);
    }
    .reveal-right {
        opacity: 0;
        transform: translateX(40px);
        transition: opacity 0.7s ease,
                    transform 0.7s ease;
    }
    .reveal-right.visible {
        opacity: 1;
        transform: translateX(0);
    }
    .stagger-1 { transition-delay: 0.1s; }
    .stagger-2 { transition-delay: 0.2s; }
    .stagger-3 { transition-delay: 0.3s; }
    .stagger-4 { transition-delay: 0.4s; }
    .stagger-5 { transition-delay: 0.5s; }

    /* Hover card lift */
    .card-hover {
        transition: transform 0.3s ease,
                    box-shadow 0.3s ease;
    }
    .card-hover:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 40px
            rgba(58, 46, 34, 0.15);
    }

    /* Counter animation */
    .counter {
        font-family: 'Playfair Display', serif;
        font-size: 3rem;
        font-weight: 700;
        color: #2E5A3D;
    }

    /* Marquee */
    .marquee-wrapper {
        overflow: hidden;
        background: #3A2E22;
        padding: 12px 0;
    }
    .marquee-track {
        display: flex;
        gap: 3rem;
        animation: marquee 20s linear infinite;
        white-space: nowrap;
    }
    @keyframes marquee {
        from { transform: translateX(0); }
        to { transform: translateX(-50%); }
    }

    /* Section divider */
    .section-tag {
        font-family: 'Poppins', sans-serif;
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.2em;
        text-transform: uppercase;
        color: #946042;
    }

    .navbar-glass {
        background: rgba(58, 46, 34, 0.4) !important;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-bottom: 1px solid rgba(243, 233, 215, 0.15);
    }
    .navbar-solid {
        background: #3A2E22 !important;
    }
</style>
@endpush

@section('content')
<div class="bg-[#F3E9D7] text-[#3A2E22] font-body">
    <!-- Navbar -->
    <nav id="navbar" class="fixed top-0 w-full z-50 navbar-solid transition-all duration-300 ease-in-out" x-data="{ open: false, active: 'home' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 md:h-20">
                <div class="flex items-center">
                    <a href="#home" class="nav-logo text-xl md:text-2xl font-bold text-white hover:text-[#F3E9D7]">BrewHub</a>
                </div>
                <div class="hidden md:flex items-center space-x-6 md:space-x-8">
                    <a href="#home" :class="active === 'home' ? 'nav-link text-white hover:text-[#F3E9D7] font-semibold' : 'nav-link text-white hover:text-[#F3E9D7]'" @click="active = 'home'" class="text-sm md:text-base">Home</a>
                    <div class="relative group">
                        <button class="text-white hover:text-[#F3E9D7] text-sm md:text-base">Featured Establishments ▾</button>
                        <div class="absolute top-full left-0 w-full h-2 bg-transparent"></div>
                        <div class="absolute left-0 top-full mt-2 w-52 bg-white rounded-md shadow-lg z-10 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                            <a href="#coffee-farms" class="block px-4 py-2 text-sm text-[#3A2E22] hover:bg-[#F3E9D7]">Coffee Farms</a>
                            <a href="#coffee-shops" class="block px-4 py-2 text-sm text-[#3A2E22] hover:bg-[#F3E9D7]">Coffee Shops</a>
                        </div>
                    </div>
                    <div class="relative group">
                        <button class="nav-link text-white hover:text-[#F3E9D7] text-sm md:text-base">About ▾</button>
                        <div class="absolute top-full left-0 w-full h-2 bg-transparent"></div>
                        <div class="absolute left-0 top-full mt-2 w-52 bg-white rounded-md shadow-lg z-10 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                            <a href="#about" class="block px-4 py-2 text-sm text-[#3A2E22] hover:bg-[#F3E9D7] font-body">System Features</a>
                            <a href="#coffee-varieties" class="block px-4 py-2 text-sm text-[#3A2E22] hover:bg-[#F3E9D7] font-body">Coffee Varieties</a>
                        </div>
                    </div>
                </div>
                <div class="flex items-center">
                    <a href="/login" class="border border-white text-white px-4 py-2 rounded-md hover:bg-[#F3E9D7] hover:text-[#3A2E22] hover:border-[#F3E9D7] transition-colors duration-200 text-sm md:text-base">Log In</a>
                    <button @click="open = !open" class="md:hidden ml-4 nav-arrow text-white hover:text-[#F3E9D7] text-sm md:text-base">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path x-show="!open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            <path x-show="open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div x-show="open" class="md:hidden">
                <a href="#home" class="block px-4 py-2 text-white hover:text-[#F3E9D7]">Home</a>
                <div class="px-4 py-2">
                    <p class="text-white font-semibold">Featured Establishments</p>
                    <a href="#coffee-farms" class="block pl-4 py-1 text-sm text-white hover:text-[#F3E9D7]">Coffee Farms</a>
                    <a href="#coffee-shops" class="block pl-4 py-1 text-sm text-white hover:text-[#F3E9D7]">Coffee Shops</a>
                </div>
                <div class="px-4 py-2">
                    <p class="text-white font-semibold">About</p>
                    <a href="#about" class="block pl-4 py-1 text-sm text-white hover:text-[#F3E9D7] font-body">System Features</a>
                    <a href="#coffee-varieties" class="block pl-4 py-1 text-sm text-white hover:text-[#F3E9D7] font-body">Coffee Varieties</a>
                </div>
                <a href="/login" class="border border-white text-white px-4 py-2 rounded-md block text-center mt-4 transition-all duration-200 hover:scale-105 active:scale-95 hover:bg-[#F3E9D7] hover:text-[#3A2E22] hover:border-[#F3E9D7]">Log In</a>
            </div>
        </div>
    </nav>


    <!-- Hero Section -->
    <section id="home" class="pt-32 pb-24 bg-[#F3E9D7] relative overflow-hidden">
        <div class="absolute inset-0 opacity-10 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=1600&q=80');"></div>
        <div class="absolute inset-0 bg-gradient-to-b from-[#F3E9D7]/50 via-[#F3E9D7]/75 to-[#F3E9D7]"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative">
            <div class="max-w-3xl mx-auto">
                <div class="animate-bounce inline-flex items-center gap-2 bg-white/20 backdrop-blur-sm text-[#3A2E22] text-xs px-3 py-1.5 rounded-full mb-4 font-body">
                    Proudly from Lipa, Batangas
                </div>
                <h1 class="text-4xl md:text-5xl font-bold text-[#3A2E22] mb-6 leading-tight font-display animate-fade-in-up">
                    Discover the 
                    <em>Rich Heritage</em> 
                    of the Coffee Industry
                </h1>
                <p class="text-lg md:text-xl text-[#3A2E22] mb-10 leading-relaxed font-body animate-fade-in-up-delay-1">
                    Connect with authentic coffee farms, 
                    explore local cafés, and experience 
                    farm-to-cup excellence in 
                    Lipa, Batangas
                </p>
                <a href="#" class="bg-[#2E5A3D] text-white px-8 py-3 rounded-md text-lg hover:bg-[#1E3A2A] font-body inline-block animate-fade-in-up-delay-2">
                    Explore →
                </a>
            </div>
        </div>
    </section>

    <div class="marquee-wrapper">
        <div class="marquee-track">
            <span class="text-[#F3E9D7] font-body text-sm inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 6l6-2 6 2v12l-6-2-6 2-6-2V4l6 2zm0 0v12m6-14v12"></path>
                </svg>
                GIS Coffee Establishment Mapping
            </span>
            <span class="text-[#946042]">&diams;</span>
            <span class="text-[#F3E9D7] font-body text-sm inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"></path>
                </svg>
                AI-Enabled Coffee Trail
            </span>
            <span class="text-[#946042]">&diams;</span>
            <span class="text-[#F3E9D7] font-body text-sm inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h8a2 2 0 012 2v4a2 2 0 01-2 2H7a2 2 0 01-2-2v-1a1 1 0 000-2v-1a2 2 0 012-2z"></path>
                    <circle cx="14" cy="12" r="1" fill="currentColor" stroke="none"></circle>
                </svg>
                Smart Coupon Promo Generator
            </span>
            <span class="text-[#946042]">&diams;</span>
            <span class="text-[#F3E9D7] font-body text-sm inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 19h16M8 15V9m4 6V6m4 9v-4"></path>
                </svg>
                Recommendation Insights
            </span>
            <span class="text-[#946042]">&diams;</span>
            <span class="text-[#F3E9D7] font-body text-sm inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h2l2 10h10l2-7H7"></path>
                    <circle cx="9" cy="19" r="1.5" fill="currentColor" stroke="none"></circle>
                    <circle cx="17" cy="19" r="1.5" fill="currentColor" stroke="none"></circle>
                </svg>
                Marketplace
            </span>
            <span class="text-[#946042]">&diams;</span>
            <span class="text-[#F3E9D7] font-body text-sm inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 6l6-2 6 2v12l-6-2-6 2-6-2V4l6 2zm0 0v12m6-14v12"></path>
                </svg>
                GIS Coffee Establishment Mapping
            </span>
            <span class="text-[#946042]">&diams;</span>
            <span class="text-[#F3E9D7] font-body text-sm inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"></path>
                </svg>
                AI-Enabled Coffee Trail
            </span>
            <span class="text-[#946042]">&diams;</span>
            <span class="text-[#F3E9D7] font-body text-sm inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h8a2 2 0 012 2v4a2 2 0 01-2 2H7a2 2 0 01-2-2v-1a1 1 0 000-2v-1a2 2 0 012-2z"></path>
                    <circle cx="14" cy="12" r="1" fill="currentColor" stroke="none"></circle>
                </svg>
                Smart Coupon Promo Generator
            </span>
            <span class="text-[#946042]">&diams;</span>
            <span class="text-[#F3E9D7] font-body text-sm inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 19h16M8 15V9m4 6V6m4 9v-4"></path>
                </svg>
                Recommendation Insights
            </span>
            <span class="text-[#946042]">&diams;</span>
            <span class="text-[#F3E9D7] font-body text-sm inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h2l2 10h10l2-7H7"></path>
                    <circle cx="9" cy="19" r="1.5" fill="currentColor" stroke="none"></circle>
                    <circle cx="17" cy="19" r="1.5" fill="currentColor" stroke="none"></circle>
                </svg>
                Marketplace
            </span>
            <span class="text-[#946042]">&diams;</span>
        </div>
    </div>

    <section id="barako-overview" class="py-24 bg-[#3A2E22] relative overflow-hidden">
        <div class="absolute top-0 left-0 text-white/5 font-display text-[12rem] font-bold leading-none select-none pointer-events-none">
            Barako
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="section-tag text-[#946042] text-center mb-4 reveal">
                City of Lipa's Coffee Heritage
            </p>

            <h2 class="text-4xl md:text-5xl font-bold text-white font-display text-center mb-4 reveal">
                Barako Coffee
                <span class="block text-2xl text-[#F3E9D7]/70 font-normal italic mt-2">
                    Philippine Liberica
                </span>
            </h2>

            <p class="text-center text-[#F3E9D7]/80 font-body max-w-2xl mx-auto mb-16 reveal">
                Barako coffee is a traditional
                Filipino coffee derived from Liberica
                and is deeply rooted in local heritage.
                Especially popular in Batangas and Cavite,
                known for its bold and intense character.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-8 card-hover reveal-left">
                    <div class="mb-4">
                        <svg class="w-8 h-8 text-[#F3E9D7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-white font-display mb-4">
                        Taste Profile
                    </h3>
                    <ul class="space-y-2 font-body">
                        <li class="flex items-center gap-2 text-[#F3E9D7]">
                            <span class="text-[#946042]">&diams;</span>
                            Strong, bold, and intense
                        </li>
                        <li class="flex items-center gap-2 text-[#F3E9D7]">
                            <span class="text-[#946042]">&diams;</span>
                            Notes: smoky, nutty, slightly sweet
                        </li>
                        <li class="flex items-center gap-2 text-[#F3E9D7]">
                            <span class="text-[#946042]">&diams;</span>
                            Lingering aftertaste
                        </li>
                    </ul>
                </div>

                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-8 card-hover reveal-right">
                    <div class="mb-4">
                        <svg class="w-8 h-8 text-[#F3E9D7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-white font-display mb-4">
                        Characteristics
                    </h3>
                    <ul class="space-y-2 font-body">
                        <li class="flex items-center gap-2 text-[#F3E9D7]">
                            <span class="text-[#946042]">&diams;</span>
                            Grown mainly in Batangas and Cavite
                        </li>
                        <li class="flex items-center gap-2 text-[#F3E9D7]">
                            <span class="text-[#946042]">&diams;</span>
                            Known for large beans and strong aroma
                        </li>
                        <li class="flex items-center gap-2 text-[#F3E9D7]">
                            <span class="text-[#946042]">&diams;</span>
                            Cultural and heritage significance
                        </li>
                    </ul>
                </div>
            </div>

            <p class="text-center text-[#F3E9D7]/40 text-xs font-body reveal">
                Reference: Slow Food Foundation;
                PCAARRD-DOST
            </p>
        </div>
    </section>

    <!-- Featured Establishments -->
    <section id="coffee-farms" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="section-tag text-center mb-3 reveal">
                Explore
            </p>
            <h2 class="text-3xl md:text-4xl font-bold text-[#3A2E22] text-center mb-4 font-display leading-snug reveal">Featured Coffee Farms</h2>
            <p class="text-center text-[#3A2E22] mb-12 text-base md:text-lg font-body max-w-2xl mx-auto leading-relaxed reveal">Discover our curated selection of top-rated Coffee Farms</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-[#F3E9D7] p-6 rounded-lg flex flex-col h-full card-hover reveal stagger-1">
                    <img src="https://placehold.co/300x200/F3E9D7/3A2E22" alt="Farm 1" class="w-full h-48 object-cover rounded-md mb-4">
                    <h3 class="text-xl font-semibold text-[#3A2E22]">Farm One</h3>
                    <p class="text-[#946042]">Lipa, Batangas</p>
                    <p class="text-[#3A2E22]">A premium coffee farm producing high-quality Philippine coffee.</p>
                </div>
                <div class="bg-[#F3E9D7] p-6 rounded-lg flex flex-col h-full card-hover reveal stagger-2">
                    <img src="https://placehold.co/300x200/F3E9D7/3A2E22" alt="Farm 2" class="w-full h-48 object-cover rounded-md mb-4">
                    <h3 class="text-xl font-semibold text-[#3A2E22]">Farm Two</h3>
                    <p class="text-[#946042]">Lipa, Batangas</p>
                    <p class="text-[#3A2E22]">Specializing in organic Barako beans with rich flavor.</p>
                </div>
                <div class="bg-[#F3E9D7] p-6 rounded-lg flex flex-col h-full card-hover reveal stagger-3">
                    <img src="https://placehold.co/300x200/F3E9D7/3A2E22" alt="Farm 3" class="w-full h-48 object-cover rounded-md mb-4">
                    <h3 class="text-xl font-semibold text-[#3A2E22]">Farm Three</h3>
                    <p class="text-[#946042]">Lipa, Batangas</p>
                    <p class="text-[#3A2E22]">Award-winning farm known for exceptional coffee quality.</p>
                </div>
            </div>
            <div class="text-center mt-10">
                <a href="#" class="inline-flex items-center gap-2 text-[#2E5A3D] font-body font-medium hover:gap-4 transition-all duration-300">
                    See All Products
                    <span>→</span>
                </a>
            </div>
        </div>
    </section>

    <section id="coffee-shops" class="py-20 bg-[#F3E9D7]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="section-tag text-center mb-3 reveal">
                Explore
            </p>
            <h2 class="text-3xl md:text-4xl font-bold text-[#3A2E22] text-center mb-4 font-display leading-snug reveal">Featured Coffee Shops</h2>
            <p class="text-center text-[#3A2E22] mb-12 text-base md:text-lg font-body max-w-2xl mx-auto leading-relaxed reveal">Explore the best local cafés in Lipa, Batangas</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-lg flex flex-col h-full card-hover reveal stagger-1">
                    <img src="https://placehold.co/300x200/F3E9D7/3A2E22" alt="Shop 1" class="w-full h-48 object-cover rounded-md mb-4">
                    <h3 class="text-xl font-semibold text-[#3A2E22]">Shop One</h3>
                    <p class="text-[#946042]">Lipa, Batangas</p>
                    <p class="text-[#3A2E22]">Cozy café serving freshly brewed Barako coffee.</p>
                </div>
                <div class="bg-white p-6 rounded-lg flex flex-col h-full card-hover reveal stagger-2">
                    <img src="https://placehold.co/300x200/F3E9D7/3A2E22" alt="Shop 2" class="w-full h-48 object-cover rounded-md mb-4">
                    <h3 class="text-xl font-semibold text-[#3A2E22]">Shop Two</h3>
                    <p class="text-[#946042]">Lipa, Batangas</p>
                    <p class="text-[#3A2E22]">Modern café with a variety of coffee specialties.</p>
                </div>
                <div class="bg-white p-6 rounded-lg flex flex-col h-full card-hover reveal stagger-3">
                    <img src="https://placehold.co/300x200/F3E9D7/3A2E22" alt="Shop 3" class="w-full h-48 object-cover rounded-md mb-4">
                    <h3 class="text-xl font-semibold text-[#3A2E22]">Shop Three</h3>
                    <p class="text-[#946042]">Lipa, Batangas</p>
                    <p class="text-[#3A2E22]">Artisan café focusing on local coffee culture.</p>
                </div>
            </div>
            <div class="text-center mt-10 reveal">
                <p class="text-[#3A2E22] font-body text-base md:text-lg mb-4">
                    Download <em>BrewHub</em> to experience more quality coffee!
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                    <a href="#" class="inline-flex items-center justify-center gap-2 w-40 h-11 rounded-md bg-[#2E5A3D] text-white font-body font-medium hover:bg-[#1E3A2A] transition-colors duration-200">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M16.7 12.5c0-2 1.7-3 1.8-3.1-1-1.4-2.5-1.6-3-1.7-1.3-.1-2.5.8-3.2.8s-1.8-.8-2.9-.8c-1.5 0-2.8.9-3.6 2.2-1.6 2.7-.4 6.7 1.1 8.9.7 1.1 1.6 2.2 2.8 2.1 1.1 0 1.6-.7 3-.7s1.8.7 3 .7c1.2 0 2-1.1 2.7-2.2.9-1.3 1.2-2.6 1.2-2.7 0 0-2.3-.9-2.3-3.5zM14.6 6.2c.6-.7 1-1.7.9-2.7-.9 0-2 .6-2.6 1.3-.6.6-1.1 1.7-.9 2.7 1 .1 2-.5 2.6-1.3z"></path>
                        </svg>
                        for iOS
                    </a>
                    <a href="#" class="inline-flex items-center justify-center gap-2 w-40 h-11 rounded-md border border-[#2E5A3D] text-[#2E5A3D] font-body font-medium hover:bg-[#2E5A3D] hover:text-white transition-colors duration-200">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M8 9h8a2 2 0 012 2v5a2 2 0 01-2 2H8a2 2 0 01-2-2v-5a2 2 0 012-2z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M9.5 9l-1-2m6 2l1-2"></path>
                            <circle cx="10.5" cy="12" r="0.8" fill="currentColor" stroke="none"></circle>
                            <circle cx="13.5" cy="12" r="0.8" fill="currentColor" stroke="none"></circle>
                        </svg>
                        for Android
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section id="farm-products-list" class="py-24 bg-[#3A2E22]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="section-tag text-[#946042] text-center mb-3 reveal">
                Fresh From Farms
            </p>
            <h2 class="text-4xl md:text-5xl font-bold text-white font-display text-center mb-4 reveal">
                Products
            </h2>
            <p class="text-center text-[#F3E9D7]/80 font-body max-w-2xl mx-auto mb-12 reveal">
                Reserve farm products directly from verified local farmers
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @forelse($farmProducts as $product)
                    @php
                        $imageSrc = $product->image_url
                            ? (str_starts_with($product->image_url, 'http')
                                ? $product->image_url
                                : asset('storage/' . ltrim($product->image_url, '/')))
                            : null;
                    @endphp
                    <div class="bg-white rounded-2xl overflow-hidden border border-white/10 card-hover reveal">
                        @if($imageSrc)
                            <img src="{{ $imageSrc }}" alt="{{ $product->name }}" class="w-full h-44 object-cover" onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');">
                            <div class="w-full h-44 bg-gray-100 hidden flex-col items-center justify-center">
                                <p class="text-xs text-[#9E8C78]">No image available</p>
                            </div>
                        @else
                            <div class="w-full h-44 bg-gray-100 flex flex-col items-center justify-center">
                                <p class="text-xs text-[#9E8C78]">No image available</p>
                            </div>
                        @endif
                        <div class="p-5">
                            <h3 class="text-lg font-bold text-[#3A2E22] font-display leading-tight">
                                {{ $product->name }}
                            </h3>
                            <p class="text-xs text-[#946042] font-body mt-1">
                                {{ $product->category ?? 'Coffee Product' }}
                                @if($product->establishment?->name)
                                    · {{ $product->establishment->name }}
                                @endif
                            </p>
                            <p class="text-sm text-[#3A2E22]/70 font-body mt-3 min-h-[42px]">
                                {{ \Illuminate\Support\Str::limit($product->description ?? 'Premium farm product available for reservation.', 90) }}
                            </p>

                            <div class="mt-3 grid grid-cols-2 gap-x-3 gap-y-1 text-xs font-body text-[#3A2E22]/75">
                                <p>Roast: <span class="text-[#3A2E22]">{{ $product->roast_level ?? 'N/A' }}</span></p>
                                <p>Grind: <span class="text-[#3A2E22]">{{ $product->grind_type ?? 'N/A' }}</span></p>
                                <p>MOQ: <span class="text-[#3A2E22]">{{ $product->moq ?? 1 }} {{ $product->unit ?? 'unit' }}</span></p>
                                <p>Stock: <span class="text-[#3A2E22]">{{ $product->stock_quantity ?? 0 }}</span></p>
                            </div>

                            <div class="flex items-center justify-between mt-4">
                                <p class="text-[#2E5A3D] font-semibold font-body">
                                    ₱{{ number_format((float) $product->price_per_unit, 2) }}
                                    <span class="text-xs text-[#3A2E22]/60">/{{ $product->unit ?? 'unit' }}</span>
                                </p>
                                <button
                                    type="button"
                                    class="reserve-product-btn bg-[#2E5A3D] text-white text-sm px-4 py-2 rounded-lg font-body hover:bg-[#1E3A2A] transition-colors duration-200"
                                    data-product="{{ $product->name }}"
                                    data-moq="{{ max(1, (int) ($product->moq ?? 1)) }}"
                                >
                                    Reserve
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white/10 rounded-2xl p-8 text-center reveal">
                        <p class="text-[#F3E9D7] font-body">
                            Farm products will appear here once available.
                        </p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <section id="farm-products" class="py-24 bg-[#3A2E22] border-t border-[#F3E9D7]/30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                <div class="reveal-left">
                    <p class="section-tag text-[#946042] mb-3">
                        Direct from Farm
                    </p>
                    <h2 class="text-4xl font-bold text-white font-display mb-4">
                        Reserve Farm Products
                    </h2>
                    <p class="text-[#F3E9D7]/80 font-body mb-6 leading-relaxed">
                        Browse and reserve authentic coffee
                        products from local farms in Lipa,
                        Batangas. This follows the same
                        reservation flow as mobile, with
                        website completion for full address.
                    </p>

                    <ul class="space-y-3 font-body text-[#F3E9D7]">
                        <li class="flex items-center gap-2">
                            <span class="text-[#2E5A3D] inline-flex items-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </span>
                            Direct from local farmers
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="text-[#2E5A3D] inline-flex items-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </span>
                            Fresh and authentic products
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="text-[#2E5A3D] inline-flex items-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </span>
                            Mobile app order completion
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="text-[#2E5A3D] inline-flex items-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </span>
                            Pickup options available
                        </li>
                    </ul>
                </div>

                <div class="bg-[#F3E9D7] rounded-2xl p-8 reveal-right">
                    <h3 class="text-xl font-bold text-[#3A2E22] font-display mb-6">
                        Complete Reservation
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm text-[#3A2E22] font-body mb-1">
                                Select Product
                            </label>
                            <select id="reservationProductSelect" class="w-full bg-white border border-gray-300 rounded-lg px-4 py-2.5 text-sm text-[#3A2E22] font-body focus:outline-none focus:ring-2 focus:ring-[#2E5A3D]">
                                @forelse($farmProducts as $product)
                                    <option value="{{ $product->name }}" data-moq="{{ max(1, (int) ($product->moq ?? 1)) }}">{{ $product->name }}</option>
                                @empty
                                    <option>Philippine Coffee (250g)</option>
                                    <option>Philippine Coffee (500g)</option>
                                    <option>Philippine Coffee (1kg)</option>
                                    <option>Ground Coffee (250g)</option>
                                    <option>Coffee Beans (500g)</option>
                                @endforelse
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm text-[#3A2E22] font-body mb-1">
                                Quantity
                            </label>
                            <input id="reservationQuantityInput" type="number" min="1" step="1" value="1" class="w-full bg-white border border-gray-300 rounded-lg px-4 py-2.5 text-sm text-[#3A2E22] focus:outline-none focus:ring-2 focus:ring-[#2E5A3D]">
                            <p id="reservationMoqHint" class="mt-1 text-xs text-[#3A2E22]/60 font-body">Minimum quantity: 1</p>
                        </div>

                        <div>
                            <label class="block text-sm text-[#3A2E22] font-body mb-1">
                                Full Name
                            </label>
                            <input type="text" placeholder="Enter your full name" class="w-full bg-white border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E5A3D]">
                        </div>

                        <div>
                            <label class="block text-sm text-[#3A2E22] font-body mb-1">
                                Delivery Address
                            </label>
                            <textarea rows="2" placeholder="Enter complete delivery address" class="w-full bg-white border border-gray-300 rounded-lg px-4 py-2.5 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-[#2E5A3D]"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm text-[#3A2E22] font-body mb-1">
                                Phone Number
                            </label>
                            <input type="tel" placeholder="e.g. 09XX XXX XXXX" class="w-full bg-white border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#2E5A3D]">
                        </div>

                        <p class="text-xs text-[#3A2E22]/60 font-body inline-flex items-start gap-1.5">
                            <span class="inline-flex mt-0.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <rect x="7" y="3" width="10" height="18" rx="2" ry="2" stroke-width="2"></rect>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17h2"></path>
                                </svg>
                            </span>
                            <span>
                                Mobile app consumers: after tapping
                                reserve in the app, you will be
                                redirected here to finish reservation
                                by entering your full address.
                            </span>
                        </p>

                        <button class="w-full bg-[#2E5A3D] text-white py-3 rounded-lg font-body font-medium hover:bg-[#1E3A2A] transition-colors duration-200">
                            Confirm Reservation →
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About / Barako Features -->
    <section id="about" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="section-tag text-center mb-3 reveal">
                Platform Highlights
            </p>
            <h2 class="text-3xl md:text-4xl font-bold text-[#3A2E22] text-center mb-4 font-display leading-snug reveal">Features</h2>
            <p class="text-center text-[#3A2E22] mb-12 text-base md:text-lg font-body max-w-2xl mx-auto leading-relaxed reveal">Everything you need to manage and grow your coffee business</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 lg:gap-3">
                <div id="gis-mapping" class="bg-[#F3E9D7] border-l-4 border-[#2E5A3D] p-4 rounded-lg text-center min-h-[250px] card-hover reveal stagger-1">
                    <div class="w-12 h-12 bg-[#2E5A3D] rounded-full mx-auto mb-3 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 6l6-2 6 2v12l-6-2-6 2-6-2V4l6 2zm0 0v12m6-14v12"></path>
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-[#3A2E22] mb-2 font-display leading-tight">GIS Coffee Establishment Mapping</h3>
                    <p class="text-[#3A2E22] text-sm font-body leading-relaxed">Interactive maps to view, register and manage coffee establishments with precise location data</p>
                </div>
                <div id="ai-coffee-trail" class="bg-[#F3E9D7] border-l-4 border-[#2E5A3D] p-4 rounded-lg text-center min-h-[250px] card-hover reveal stagger-2">
                    <div class="w-12 h-12 bg-[#2E5A3D] rounded-full mx-auto mb-3 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"></path>
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-[#3A2E22] mb-2 font-display leading-tight">AI-Enabled Coffee Trail</h3>
                    <p class="text-[#3A2E22] text-sm font-body leading-relaxed">Generate personalized coffee routes based on your location and preferences</p>
                </div>
                <div id="coupon-promo" class="bg-[#F3E9D7] border-l-4 border-[#2E5A3D] p-4 rounded-lg text-center min-h-[250px] card-hover reveal stagger-3">
                    <div class="w-12 h-12 bg-[#2E5A3D] rounded-full mx-auto mb-3 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h8a2 2 0 012 2v4a2 2 0 01-2 2H7a2 2 0 01-2-2v-1a1 1 0 000-2v-1a2 2 0 012-2z"></path>
                            <circle cx="14" cy="12" r="1" fill="currentColor" stroke="none"></circle>
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-[#3A2E22] mb-2 font-display leading-tight">Smart Coupon Promo Generator</h3>
                    <p class="text-[#3A2E22] text-sm font-body leading-relaxed">Create and distribute smart promotional coupons to attract more customers to your cafe</p>
                </div>
                <div id="recommendations" class="bg-[#F3E9D7] border-l-4 border-[#2E5A3D] p-4 rounded-lg text-center min-h-[250px] card-hover reveal stagger-4">
                    <div class="w-12 h-12 bg-[#2E5A3D] rounded-full mx-auto mb-3 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 19h16M8 15V9m4 6V6m4 9v-4"></path>
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-[#3A2E22] mb-2 font-display leading-tight">Recommendation Insights</h3>
                    <p class="text-[#3A2E22] text-sm font-body leading-relaxed">Helps coffee shops make data-driven decisions from consumer star ratings</p>
                </div>
                <div id="marketplace" class="bg-[#F3E9D7] border-l-4 border-[#2E5A3D] p-4 rounded-lg text-center min-h-[250px] card-hover reveal stagger-5">
                    <div class="w-12 h-12 bg-[#2E5A3D] rounded-full mx-auto mb-3 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h2l2 10h10l2-7H7"></path>
                            <circle cx="9" cy="19" r="1.5" fill="currentColor" stroke="none"></circle>
                            <circle cx="17" cy="19" r="1.5" fill="currentColor" stroke="none"></circle>
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-[#3A2E22] mb-2 font-display leading-tight">Marketplace</h3>
                    <p class="text-[#3A2E22] text-sm font-body leading-relaxed">Order premium Coffee directly from local farms</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Coffee Varieties Section -->
    <section id="coffee-varieties" class="py-24 bg-[#F3E9D7]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="section-tag text-center mb-3 reveal">
                Know Your Coffee
            </p>
            <h2 class="text-4xl md:text-5xl font-bold text-[#3A2E22] font-display text-center mb-4 reveal">
                Coffee Varieties
            </h2>
            <p class="text-center text-[#3A2E22]/70 font-body max-w-2xl mx-auto mb-16 reveal">
                Discover the unique characteristics
                of each coffee variety cultivated
                across the Philippines
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div id="arabica" class="bg-white rounded-2xl overflow-hidden card-hover reveal stagger-1 group">
                    <div class="bg-[#2E5A3D] p-6 flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-xl font-bold text-white font-display">
                                Arabica
                            </h3>
                            <p class="text-[#F3E9D7]/70 text-xs font-body">
                                Coffea arabica
                            </p>
                        </div>
                        <img src="{{ asset('images/arabica.png') }}" alt="Arabica" class="w-20 h-20 object-contain shrink-0 transition-transform duration-300 group-hover:scale-125" onerror="this.style.display='none'">
                    </div>
                    <div class="p-6">
                        <p class="text-[#3A2E22]/80 font-body text-sm mb-4 leading-relaxed">
                            The most widely consumed coffee
                            species in the world, prized for
                            superior quality and complex flavors.
                            Often considered premium coffee.
                        </p>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs font-semibold text-[#946042] font-body mb-2">
                                    Taste Profile
                                </p>
                                <ul class="space-y-1">
                                    <li class="text-xs text-[#3A2E22]/70 font-body">
                                        • Smooth, mild, aromatic
                                    </li>
                                    <li class="text-xs text-[#3A2E22]/70 font-body">
                                        • Fruity, floral, sweet
                                    </li>
                                    <li class="text-xs text-[#3A2E22]/70 font-body">
                                        • Lower bitterness
                                    </li>
                                </ul>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-[#946042] font-body mb-2">
                                    Characteristics
                                </p>
                                <ul class="space-y-1">
                                    <li class="text-xs text-[#3A2E22]/70 font-body">
                                        • High altitudes
                                    </li>
                                    <li class="text-xs text-[#3A2E22]/70 font-body">
                                        • Lower caffeine
                                    </li>
                                    <li class="text-xs text-[#3A2E22]/70 font-body">
                                        • Delicate cultivation
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <p class="text-[#3A2E22]/40 text-xs font-body mt-4">
                            Ref: Philippine Coffee Board;
                            CoffeeBeans.ph
                        </p>
                    </div>
                </div>

                <div id="excelsa" class="bg-white rounded-2xl overflow-hidden card-hover reveal stagger-2 group">
                    <div class="bg-[#946042] p-6 flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-xl font-bold text-white font-display">
                                Excelsa
                            </h3>
                            <p class="text-white/70 text-xs font-body">
                                Coffea excelsa / Liberica var.
                            </p>
                        </div>
                        <img src="{{ asset('images/excelsa.png') }}" alt="Excelsa" class="w-20 h-20 object-contain shrink-0 transition-transform duration-300 group-hover:scale-125" onerror="this.style.display='none'">
                    </div>
                    <div class="p-6">
                        <p class="text-[#3A2E22]/80 font-body text-sm mb-4 leading-relaxed">
                            Often classified as a variety
                            of Liberica, valued for adding
                            depth and complexity to coffee
                            blends. Plays an important role
                            in enhancing flavor profiles.
                        </p>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs font-semibold text-[#946042] font-body mb-2">
                                    Taste Profile
                                </p>
                                <ul class="space-y-1">
                                    <li class="text-xs text-[#3A2E22]/70 font-body">
                                        • Tart, fruity, dark
                                    </li>
                                    <li class="text-xs text-[#3A2E22]/70 font-body">
                                        • Berry-like, tangy
                                    </li>
                                    <li class="text-xs text-[#3A2E22]/70 font-body">
                                        • Adds complexity
                                    </li>
                                </ul>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-[#946042] font-body mb-2">
                                    Characteristics
                                </p>
                                <ul class="space-y-1">
                                    <li class="text-xs text-[#3A2E22]/70 font-body">
                                        • Southeast Asia
                                    </li>
                                    <li class="text-xs text-[#3A2E22]/70 font-body">
                                        • Depth in blends
                                    </li>
                                    <li class="text-xs text-[#3A2E22]/70 font-body">
                                        • Light-to-dark contrast
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <p class="text-[#3A2E22]/40 text-xs font-body mt-4">
                            Ref: CoffeeBeans.ph
                        </p>
                    </div>
                </div>

                <div id="liberica" class="bg-white rounded-2xl overflow-hidden card-hover reveal stagger-3 group">
                    <div class="bg-[#3A2E22] p-6 flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-xl font-bold text-white font-display">
                                Liberica
                            </h3>
                            <p class="text-[#F3E9D7]/70 text-xs font-body">
                                Coffea liberica
                            </p>
                        </div>
                        <img src="{{ asset('images/liberica.png') }}" alt="Liberica" class="w-20 h-20 object-contain shrink-0 transition-transform duration-300 group-hover:scale-125" onerror="this.style.display='none'">
                    </div>
                    <div class="p-6">
                        <p class="text-[#3A2E22]/80 font-body text-sm mb-4 leading-relaxed">
                            A rare coffee species globally
                            but holds cultural and agricultural
                            importance in the Philippines.
                            Known for its distinctive aroma
                            and unique flavor.
                        </p>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs font-semibold text-[#946042] font-body mb-2">
                                    Taste Profile
                                </p>
                                <ul class="space-y-1">
                                    <li class="text-xs text-[#3A2E22]/70 font-body">
                                        • Smoky, woody, floral
                                    </li>
                                    <li class="text-xs text-[#3A2E22]/70 font-body">
                                        • Unique, complex
                                    </li>
                                    <li class="text-xs text-[#3A2E22]/70 font-body">
                                        • Fruity, bold body
                                    </li>
                                </ul>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-[#946042] font-body mb-2">
                                    Characteristics
                                </p>
                                <ul class="space-y-1">
                                    <li class="text-xs text-[#3A2E22]/70 font-body">
                                        • Large, irregular beans
                                    </li>
                                    <li class="text-xs text-[#3A2E22]/70 font-body">
                                        • Tropical climates
                                    </li>
                                    <li class="text-xs text-[#3A2E22]/70 font-body">
                                        • Limited production
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <p class="text-[#3A2E22]/40 text-xs font-body mt-4">
                            Ref: Philippine Coffee Board;
                            PCAARRD-DOST
                        </p>
                    </div>
                </div>

                <div id="robusta" class="bg-white rounded-2xl overflow-hidden card-hover reveal stagger-4 group">
                    <div class="bg-[#4a7c59] p-6 flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-xl font-bold text-white font-display">
                                Robusta
                            </h3>
                            <p class="text-white/70 text-xs font-body">
                                Coffea canephora
                            </p>
                        </div>
                        <img src="{{ asset('images/robusta.png') }}" alt="Robusta" class="w-20 h-20 object-contain shrink-0 transition-transform duration-300 group-hover:scale-125" onerror="this.style.display='none'">
                    </div>
                    <div class="p-6">
                        <p class="text-[#3A2E22]/80 font-body text-sm mb-4 leading-relaxed">
                            Known for its strong, bold flavor,
                            commonly used in instant coffee
                            and espresso blends. Easier to
                            grow and more resilient, making
                            it practical for large-scale
                            production.
                        </p>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs font-semibold text-[#946042] font-body mb-2">
                                    Taste Profile
                                </p>
                                <ul class="space-y-1">
                                    <li class="text-xs text-[#3A2E22]/70 font-body">
                                        • Bold, strong, bitter
                                    </li>
                                    <li class="text-xs text-[#3A2E22]/70 font-body">
                                        • Earthy, nutty, woody
                                    </li>
                                    <li class="text-xs text-[#3A2E22]/70 font-body">
                                        • Less acidity
                                    </li>
                                </ul>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-[#946042] font-body mb-2">
                                    Characteristics
                                </p>
                                <ul class="space-y-1">
                                    <li class="text-xs text-[#3A2E22]/70 font-body">
                                        • Higher caffeine
                                    </li>
                                    <li class="text-xs text-[#3A2E22]/70 font-body">
                                        • Lower altitudes
                                    </li>
                                    <li class="text-xs text-[#3A2E22]/70 font-body">
                                        • Pest resistant
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <p class="text-[#3A2E22]/40 text-xs font-body mt-4">
                            Ref: Philippine Coffee Board;
                            CoffeeBeans.ph
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-[#3A2E22] text-[#F3E9D7] py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4 font-display">BrewHub</h3>
                    <p class="font-body">Connecting people to the land, the Farmers, and the flavor of Philippine Coffee.</p>
                </div>
                <div>
                    <h4 class="font-semibold mb-4 font-display">Explore</h4>
                    <ul>
                        <li><a href="#coffee-farms" class="hover:text-[#2E5A3D] font-body">Coffee Farms</a></li>
                        <li><a href="#coffee-shops" class="hover:text-[#2E5A3D] font-body">Coffee Shops</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4 font-display">About</h4>
                    <ul>
                        <li><a href="#about" class="hover:text-[#2E5A3D] font-body">Features</a></li>
                        <li><a href="#coffee-varieties" class="hover:text-[#2E5A3D] font-body">Coffee Varieties</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4 font-display">Connect</h4>
                    <div class="flex gap-4">
                        <a href="#" class="text-[#F3E9D7] hover:text-[#2E5A3D] transition-colors duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 hover:rotate-12 transition-transform duration-300" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/>
                            </svg>
                        </a>
                        <a href="#" class="text-[#F3E9D7] hover:text-[#2E5A3D] transition-colors duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 hover:rotate-12 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
                                <circle cx="12" cy="12" r="4"/>
                                <circle cx="17.5" cy="6.5" r="1.5" fill="currentColor"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            <div class="border-t border-[#F3E9D7] mt-8 pt-8 text-center">
                <p class="font-body">&copy; 2026 BrewHub. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Intersection Observer for active nav links
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('nav a[href^="#"]');

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const id = entry.target.getAttribute('id');
                    navLinks.forEach(link => {
                        link.classList.remove('text-[#2E5A3D]', 'font-semibold');
                        if (link.getAttribute('href') === `#${id}`) {
                            link.classList.add('text-[#2E5A3D]', 'font-semibold');
                        }
                    });
                }
            });
        }, { threshold: 0.5 });

        sections.forEach(section => observer.observe(section));

        // Intersection Observer for reveal animations
        const revealElements = document.querySelectorAll('.reveal, .reveal-left, .reveal-right');

        const revealObserver = new IntersectionObserver(
            (entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            },
            { threshold: 0.15 }
        );

        revealElements.forEach(el => {
            revealObserver.observe(el);
        });

        // Counter animation
        const counters = document.querySelectorAll('.counter');

        const counterObserver = new IntersectionObserver(
            (entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const target = parseInt(entry.target.dataset.target);
                        let count = 0;
                        const increment = target / 60;
                        const timer = setInterval(() => {
                            count += increment;
                            if (count >= target) {
                                count = target;
                                clearInterval(timer);
                            }
                            entry.target.textContent = Math.floor(count) + '+';
                        }, 16);
                        counterObserver.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.5 }
        );

        counters.forEach(counter => {
            counterObserver.observe(counter);
        });

        // Reserve button behavior for farm products
        const reserveButtons = document.querySelectorAll('.reserve-product-btn');
        const reservationProductSelect = document.getElementById('reservationProductSelect');
        const reservationQuantityInput = document.getElementById('reservationQuantityInput');
        const reservationMoqHint = document.getElementById('reservationMoqHint');
        const reservationSection = document.getElementById('farm-products');

        function applyReservationMoq(rawMoq) {
            const minimumQuantity = Math.max(1, Number(rawMoq || 1));

            if (reservationQuantityInput) {
                reservationQuantityInput.min = String(minimumQuantity);

                const currentQuantity = Number(reservationQuantityInput.value || 0);
                if (!Number.isFinite(currentQuantity) || currentQuantity < minimumQuantity) {
                    reservationQuantityInput.value = String(minimumQuantity);
                }
            }

            if (reservationMoqHint) {
                reservationMoqHint.textContent = `Minimum quantity: ${minimumQuantity}`;
            }
        }

        if (reservationProductSelect) {
            reservationProductSelect.addEventListener('change', () => {
                const selectedOption = reservationProductSelect.options[reservationProductSelect.selectedIndex];
                applyReservationMoq(selectedOption?.dataset?.moq || 1);
            });

            const initialOption = reservationProductSelect.options[reservationProductSelect.selectedIndex];
            applyReservationMoq(initialOption?.dataset?.moq || 1);
        }

        if (reservationQuantityInput) {
            reservationQuantityInput.addEventListener('change', () => {
                applyReservationMoq(reservationQuantityInput.min || 1);
            });
        }

        reserveButtons.forEach(button => {
            button.addEventListener('click', () => {
                const selectedProduct = button.dataset.product;
                const selectedMoq = button.dataset.moq || 1;

                if (reservationProductSelect && selectedProduct) {
                    const optionExists = Array.from(reservationProductSelect.options)
                        .some(option => option.value === selectedProduct);

                    if (!optionExists) {
                        const newOption = new Option(selectedProduct, selectedProduct);
                        newOption.dataset.moq = String(selectedMoq);
                        reservationProductSelect.add(newOption);
                    }

                    reservationProductSelect.value = selectedProduct;
                    reservationProductSelect.dispatchEvent(new Event('change'));
                }

                applyReservationMoq(selectedMoq);

                if (reservationSection) {
                    reservationSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

        // Navbar background change on scroll
        const navbar = document.getElementById('navbar');

        function updateNavbar() {
            if (window.scrollY > 50) {
                navbar.classList.remove('navbar-solid');
                navbar.classList.add('navbar-glass');
            } else {
                navbar.classList.remove('navbar-glass');
                navbar.classList.add('navbar-solid');
            }
        }

        window.addEventListener('scroll', updateNavbar);
        updateNavbar(); // run on page load
    </script>
</div>
@endsection