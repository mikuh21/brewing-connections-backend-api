@extends('layouts.app')

@section('title', 'BrewHub')

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
    <section id="home" class="pt-32 pb-24 bg-[#F3E9D7]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="max-w-3xl mx-auto">
                <h1 class="text-4xl md:text-5xl font-bold text-[#3A2E22] mb-6 leading-tight font-display animate-fade-in-up">
                    Discover the 
                    <em>Rich Heritage</em> 
                    of Kapeng Barako
                </h1>
                <p class="text-lg md:text-xl text-[#3A2E22] mb-10 leading-relaxed font-body animate-fade-in-up-delay-1">
                    Connect with authentic coffee farms, 
                    explore local cafés, and experience 
                    farm-to-cup excellence in 
                    Lipa, Batangas
                </p>
                <a href="#" class="bg-[#2E5A3D] text-white px-8 py-3 rounded-md text-lg hover:bg-[#1E3A2A] font-body inline-block animate-fade-in-up-delay-2">
                    Get Started →
                </a>
            </div>
        </div>
    </section>

    <!-- Featured Establishments -->
    <section id="coffee-farms" class="py-20 bg-white scroll-reveal">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl md:text-4xl font-bold text-[#3A2E22] text-center mb-4 font-display leading-snug">Featured Coffee Farms</h2>
            <p class="text-center text-[#3A2E22] mb-12 text-base md:text-lg font-body max-w-2xl mx-auto leading-relaxed">Discover our curated selection of top-rated Barako Farms</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-[#F3E9D7] p-6 rounded-lg flex flex-col h-full hover:-translate-y-2 transition-all duration-300 ease-in-out hover:shadow-xl">
                    <img src="https://placehold.co/300x200/F3E9D7/3A2E22" alt="Farm 1" class="w-full h-48 object-cover rounded-md mb-4">
                    <h3 class="text-xl font-semibold text-[#3A2E22]">Farm One</h3>
                    <p class="text-[#946042]">Lipa, Batangas</p>
                    <p class="text-[#3A2E22]">A premium coffee farm producing high-quality Kapeng Barako.</p>
                </div>
                <div class="bg-[#F3E9D7] p-6 rounded-lg flex flex-col h-full hover:-translate-y-2 transition-all duration-300 ease-in-out hover:shadow-xl">
                    <img src="https://placehold.co/300x200/F3E9D7/3A2E22" alt="Farm 2" class="w-full h-48 object-cover rounded-md mb-4">
                    <h3 class="text-xl font-semibold text-[#3A2E22]">Farm Two</h3>
                    <p class="text-[#946042]">Lipa, Batangas</p>
                    <p class="text-[#3A2E22]">Specializing in organic Barako beans with rich flavor.</p>
                </div>
                <div class="bg-[#F3E9D7] p-6 rounded-lg flex flex-col h-full hover:-translate-y-2 transition-all duration-300 ease-in-out hover:shadow-xl">
                    <img src="https://placehold.co/300x200/F3E9D7/3A2E22" alt="Farm 3" class="w-full h-48 object-cover rounded-md mb-4">
                    <h3 class="text-xl font-semibold text-[#3A2E22]">Farm Three</h3>
                    <p class="text-[#946042]">Lipa, Batangas</p>
                    <p class="text-[#3A2E22]">Award-winning farm known for exceptional coffee quality.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="coffee-shops" class="py-20 bg-[#F3E9D7] scroll-reveal">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl md:text-4xl font-bold text-[#3A2E22] text-center mb-4 font-display leading-snug">Featured Coffee Shops</h2>
            <p class="text-center text-[#3A2E22] mb-12 text-base md:text-lg font-body max-w-2xl mx-auto leading-relaxed">Explore the best local cafés in Lipa, Batangas</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-lg flex flex-col h-full hover:-translate-y-2 transition-all duration-300 ease-in-out hover:shadow-xl">
                    <img src="https://placehold.co/300x200/F3E9D7/3A2E22" alt="Shop 1" class="w-full h-48 object-cover rounded-md mb-4">
                    <h3 class="text-xl font-semibold text-[#3A2E22]">Shop One</h3>
                    <p class="text-[#946042]">Lipa, Batangas</p>
                    <p class="text-[#3A2E22]">Cozy café serving freshly brewed Barako coffee.</p>
                </div>
                <div class="bg-white p-6 rounded-lg flex flex-col h-full hover:-translate-y-2 transition-all duration-300 ease-in-out hover:shadow-xl">
                    <img src="https://placehold.co/300x200/F3E9D7/3A2E22" alt="Shop 2" class="w-full h-48 object-cover rounded-md mb-4">
                    <h3 class="text-xl font-semibold text-[#3A2E22]">Shop Two</h3>
                    <p class="text-[#946042]">Lipa, Batangas</p>
                    <p class="text-[#3A2E22]">Modern café with a variety of coffee specialties.</p>
                </div>
                <div class="bg-white p-6 rounded-lg flex flex-col h-full hover:-translate-y-2 transition-all duration-300 ease-in-out hover:shadow-xl">
                    <img src="https://placehold.co/300x200/F3E9D7/3A2E22" alt="Shop 3" class="w-full h-48 object-cover rounded-md mb-4">
                    <h3 class="text-xl font-semibold text-[#3A2E22]">Shop Three</h3>
                    <p class="text-[#946042]">Lipa, Batangas</p>
                    <p class="text-[#3A2E22]">Artisan café focusing on local coffee culture.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About / Barako Features -->
    <section id="about" class="py-20 bg-white scroll-reveal">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl md:text-4xl font-bold text-[#3A2E22] text-center mb-4 font-display leading-snug">Barako Features</h2>
            <p class="text-center text-[#3A2E22] mb-12 text-base md:text-lg font-body max-w-2xl mx-auto leading-relaxed">Everything you need to manage and grow your coffee business</p>
            <div class="flex flex-wrap justify-center gap-8">
                <div id="gis-mapping" class="bg-[#F3E9D7] p-6 rounded-lg text-center flex-shrink-0 w-full md:w-1/2 lg:w-1/3">
                    <div class="w-16 h-16 bg-[#2E5A3D] rounded-full mx-auto mb-4 flex items-center justify-center">
                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-[#3A2E22] mb-2 font-display">GIS Farm Mapping</h3>
                    <p class="text-[#3A2E22] font-body">Interactive maps to view, register and manage coffee farms with precise location data</p>
                </div>
                <div id="ai-coffee-trail" class="bg-[#F3E9D7] p-6 rounded-lg text-center flex-shrink-0 w-full md:w-1/2 lg:w-1/3">
                    <div class="w-16 h-16 bg-[#2E5A3D] rounded-full mx-auto mb-4 flex items-center justify-center">
                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-[#3A2E22] mb-2 font-display">AI Coffee Trail</h3>
                    <p class="text-[#3A2E22] font-body">Generate personalized coffee routes based on your location and preferences</p>
                </div>
                <div id="coupon-promo" class="bg-[#F3E9D7] p-6 rounded-lg text-center flex-shrink-0 w-full md:w-1/2 lg:w-1/3">
                    <div class="w-16 h-16 bg-[#2E5A3D] rounded-full mx-auto mb-4 flex items-center justify-center">
                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-[#3A2E22] mb-2 font-display">Smart Coupon Promo Generator</h3>
                    <p class="text-[#3A2E22] font-body">Create and distribute smart promotional coupons to attract more customers to your establishment</p>
                </div>
                <div id="recommendations" class="bg-[#F3E9D7] p-6 rounded-lg text-center flex-shrink-0 w-full md:w-1/2 lg:w-1/3">
                    <div class="w-16 h-16 bg-[#2E5A3D] rounded-full mx-auto mb-4 flex items-center justify-center">
                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-[#3A2E22] mb-2 font-display">AI Driven Recommendation Analytics</h3>
                    <p class="text-[#3A2E22] font-body">Discover farms, cafes and products tailored to your taste and visits</p>
                </div>
                <div id="marketplace" class="bg-[#F3E9D7] p-6 rounded-lg text-center flex-shrink-0 w-full md:w-1/2 lg:w-1/3">
                    <div class="w-16 h-16 bg-[#2E5A3D] rounded-full mx-auto mb-4 flex items-center justify-center">
                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-[#3A2E22] mb-2 font-display">Marketplace</h3>
                    <p class="text-[#3A2E22] font-body">Order premium Kapeng Barako directly from local farms</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Coffee Varieties Section -->
    <section id="coffee-varieties" class="py-20 bg-white scroll-reveal">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl md:text-4xl font-bold text-[#3A2E22] text-center mb-4 font-display leading-snug">Coffee Varieties</h2>
            <p class="text-center text-[#3A2E22] mb-12 text-base md:text-lg font-body max-w-2xl mx-auto leading-relaxed">Discover the unique coffee varieties cultivated in the Philippines</p>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div id="liberica" class="bg-[#F3E9D7] border border-[#946042]/30 p-6 rounded-lg shadow-md hover:-translate-y-2 transition-all duration-300 ease-in-out hover:shadow-xl flex flex-col h-full">
                    <div class="text-4xl mb-4">☕</div>
                    <h3 class="text-xl font-semibold text-[#3A2E22] mb-2 font-display">Liberica (Kapeng Barako)</h3>
                    <p class="text-[#3A2E22] mb-4 font-body flex-grow">Known for its bold, smoky flavor and large beans. Native to the Philippines and predominantly grown in Lipa, Batangas. Has a distinctive woody and floral aroma.</p>
                    <span class="inline-block bg-[#2E5A3D] text-white px-3 py-1 rounded-full text-sm font-body">Most Popular in Lipa</span>
                </div>
                <div id="excelsa" class="bg-[#F3E9D7] border border-[#946042]/30 p-6 rounded-lg shadow-md hover:-translate-y-2 transition-all duration-300 ease-in-out hover:shadow-xl flex flex-col h-full">
                    <div class="text-4xl mb-4">🌿</div>
                    <h3 class="text-xl font-semibold text-[#3A2E22] mb-2 font-display">Excelsa</h3>
                    <p class="text-[#3A2E22] mb-4 font-body flex-grow">A unique variety with a tart, fruity flavor profile. Often used to add complexity to coffee blends. Thrives in the tropical climate of the Philippines.</p>
                    <span class="inline-block bg-[#2E5A3D] text-white px-3 py-1 rounded-full text-sm font-body">Rare & Unique</span>
                </div>
                <div id="arabica" class="bg-[#F3E9D7] border border-[#946042]/30 p-6 rounded-lg shadow-md hover:-translate-y-2 transition-all duration-300 ease-in-out hover:shadow-xl flex flex-col h-full">
                    <div class="text-4xl mb-4">⭐</div>
                    <h3 class="text-xl font-semibold text-[#3A2E22] mb-2 font-display">Arabica</h3>
                    <p class="text-[#3A2E22] mb-4 font-body flex-grow">The world's most popular coffee variety. Known for its smooth, mild flavor with hints of fruit and sugar. Grown in higher elevations across the Philippines.</p>
                    <span class="inline-block bg-[#2E5A3D] text-white px-3 py-1 rounded-full text-sm font-body">World's Favorite</span>
                </div>
                <div id="robusta" class="bg-[#F3E9D7] border border-[#946042]/30 p-6 rounded-lg shadow-md hover:-translate-y-2 transition-all duration-300 ease-in-out hover:shadow-xl flex flex-col h-full">
                    <div class="text-4xl mb-4">💪</div>
                    <h3 class="text-xl font-semibold text-[#3A2E22] mb-2 font-display">Robusta</h3>
                    <p class="text-[#3A2E22] mb-4 font-body flex-grow">Strong and full-bodied with higher caffeine content. Used in espresso blends for its rich crema. Resilient and widely grown across the Philippines.</p>
                    <span class="inline-block bg-[#2E5A3D] text-white px-3 py-1 rounded-full text-sm font-body">High Caffeine</span>
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
                    <p class="font-body">Connecting people to the land, the Farmers, and the flavor of Kapeng Barako.</p>
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

    <style>
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

        // Scroll reveal animations
        const scrollReveals = document.querySelectorAll('.scroll-reveal');
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-in-up');
                }
            });
        }, { threshold: 0.1 });

        scrollReveals.forEach(el => revealObserver.observe(el));

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