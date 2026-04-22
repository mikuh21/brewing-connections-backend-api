                <!-- ...existing code... -->
@extends('layouts.app')

@section('title', 'Map - BrewHub')

@push('head')
    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin=""/>
    {{-- App CSS for admin map page styling --}}
    @vite('resources/css/app.css')
@endpush

@section('content')
<div class="admin-map-page min-h-screen flex bg-[#F5F0E8] text-[#3A2E22]">
    <!-- Sidebar -->
    <aside class="admin-sidebar fixed left-0 top-0 h-screen w-64 bg-[#3A2E22] text-[#F5F0E8] flex flex-col justify-between py-6 px-4 rounded-r-xl shadow-lg z-40 -translate-x-full md:translate-x-0 transition-transform duration-300 ease-out">
        <div>
            <div class="flex items-center mb-8">
                <svg class="w-6 h-6 mr-3 text-[#F5F0E8]" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
                <span class="text-lg font-display font-bold">BrewHub</span>
            </div>

            <nav class="space-y-1">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center rounded-lg px-4 py-2 text-xs font-medium gap-2 transition-all duration-200 hover:bg-[#4E3D2B] hover:translate-x-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Dashboard
                </a>
                <a href="{{ route('admin.map') }}" class="flex items-center bg-[#4E3D2B] rounded-lg px-4 py-2 text-xs font-medium gap-2 transition-all duration-200 hover:bg-[#5A4A3A] hover:translate-x-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                    Map
                </a>
                <a href="{{ route('admin.establishments.index') }}" class="flex items-center rounded-lg px-4 py-2 text-xs font-medium gap-2 transition-all duration-200 hover:bg-[#4E3D2B] hover:translate-x-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Establishments
                </a>
                <a href="{{ route('admin.registrations.index') }}" class="flex items-center rounded-lg px-4 py-2 text-xs font-medium gap-2 transition-all duration-200 hover:bg-[#4E3D2B] hover:translate-x-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                    Registrations
                </a>
                <a href="{{ route('admin.resellers.index') }}" class="flex items-center rounded-lg px-4 py-2 text-xs font-medium gap-2 transition-all duration-200 hover:bg-[#4E3D2B] hover:translate-x-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l9-4 9 4v8l-9 4-9-4V8z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l9 4 9-4" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16" />
                    </svg>
                    Resellers
                </a>
                <a href="{{ route('admin.coupon-promos.index') }}" class="flex items-center rounded-lg px-4 py-2 text-xs font-medium gap-2 transition-all duration-200 hover:bg-[#4E3D2B] hover:translate-x-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    Coupon Promos
                </a>
                <a href="{{ route('admin.rating-moderation.index') }}" class="flex items-center rounded-lg px-4 py-2 text-xs font-medium gap-2 transition-all duration-200 hover:bg-[#4E3D2B] hover:translate-x-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                    Rating Moderation
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
                    Marketplace
                </a>
                <a href="{{ route('chat.index') }}" class="flex items-center rounded-lg px-4 py-2 text-xs font-medium gap-2 transition-all duration-200 hover:bg-[#4E3D2B] hover:translate-x-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    Messages
                    @php
                        $authUser = Auth::user();
                        $totalUnread = $authUser->conversations()
                            ->get()
                            ->sum(function($conv) use ($authUser) {
                                return $conv->unreadCount($authUser->id);
                            });
                    @endphp
                    @if($totalUnread > 0)
                        <span class="ml-auto inline-flex items-center justify-center min-w-[20px] h-5 px-1 text-[10px] font-bold text-white bg-red-600 rounded-full">
                            {{ $totalUnread }}
                        </span>
                    @endif
                </a>
            </nav>
        </div>

        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center min-w-0">
                <div class="w-10 h-10 bg-[#4A6741] rounded-full flex items-center justify-center text-white font-bold text-sm mr-3">A</div>
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

    <!-- Main content -->
    <main class="admin-main flex-1 ml-0 md:ml-64 h-screen overflow-hidden p-6">
        <div class="max-w-full h-full flex flex-col gap-6">
            <header class="bg-white rounded-[20px] shadow-sm p-6 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-display font-bold text-[#3A2E22] mb-1">Map</h1>
                    <p class="text-xs text-[#9E8C78] leading-tight">Interactive GIS view of all registered establishments</p>
                </div>
                <div class="flex items-center gap-2 map-header-search">
                    <select id="map-filter" class="map-filter-dropdown text-xs px-3 py-1.5 text-[#3A2E22]">
                        <option value="all">All</option>
                        <option value="farm">Farms</option>
                        <option value="cafe">Cafes</option>
                        <option value="roaster">Roasters</option>
                        <option value="reseller">Resellers</option>
                    </select>
                    <div class="relative">
                        <svg class="w-4 h-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-[#9E8C78]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input
                            id="map-barangay-search"
                            type="text"
                            list="map-barangay-suggestions"
                            placeholder="Search barangay..."
                            class="pl-9 pr-3 py-1.5 text-xs bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#4A6741] focus:border-transparent w-64"
                        />
                    </div>
                    <datalist id="map-barangay-suggestions"></datalist>
                    <button
                        id="map-barangay-search-btn"
                        type="button"
                        class="map-search-trigger text-xs px-3 py-1.5 rounded-lg bg-[#4A6741] text-white font-medium hover:bg-[#3A2E22] transition-colors"
                    >
                        Search
                    </button>
                </div>
            </header>

            <!-- Filter Panel -->

            <section class="map-wrapper flex-1 rounded-[20px] shadow-lg bg-white p-4 relative overflow-hidden flex flex-col">
                <!-- Filter Panel Inside Map Wrapper -->
                <div id="filter-panel" class="filter-panel-container w-full bg-white rounded-[20px] shadow-sm mb-4">
                    <!-- Content will be populated by JS -->
                </div>

                <div id="map" class="h-full w-full rounded-xl overflow-hidden relative" style="opacity: 0; transition: opacity 0.2s; position: relative; overflow: hidden;">
                    <div id="details-panel" style="
                      position: absolute;
                      top: 0;
                      right: 0;
                      width: 340px;
                      height: 100%;
                      background: white;
                      z-index: 900;
                      transform: translateX(100%);
                      transition: transform 320ms cubic-bezier(0.25,0.46,0.45,0.94);
                      overflow-y: auto;
                      box-shadow: -4px 0 20px rgba(0,0,0,0.10);
                      scrollbar-width: thin;
                      scrollbar-color: #d1ccc4 transparent;
                    "></div>

                    <div id="placement-banner"
                        class="absolute hidden px-3 py-1 rounded-lg bg-black bg-opacity-70 text-white text-xs font-medium transition-all duration-200"
                        style="bottom: 76px; left: 24px; z-index: 900;">
                        Click the map to place establishment
                    </div>

                    <div id="edit-banner"
                        class="absolute hidden px-3 py-2 rounded-lg bg-green-600 text-white text-sm font-medium transition-all duration-200 flex items-center gap-2"
                        style="top: 20px; left: 50%; transform: translateX(-50%); z-index: 1200; pointer-events: auto;">
                        <span id="edit-banner-text" style="font-family: 'Poppins', sans-serif;">Drag establishment to reposition — click Save when done</span>
                        <button id="edit-save" type="button" style="font-family: 'Poppins', sans-serif;" class="px-3 py-1 bg-white text-green-600 rounded text-xs font-semibold hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed">Save</button>
                        <button id="edit-cancel" type="button" style="font-family: 'Poppins', sans-serif;" class="px-3 py-1 bg-white text-green-600 rounded text-xs font-semibold hover:bg-gray-100">Cancel</button>
                    </div>

                    <div id="map-action-buttons" class="absolute left-6 bottom-7 z-[901] flex flex-col items-start gap-3">
                        <button id="add-establishment-btn" class="bg-[#4A6741] text-white rounded-full px-3 py-2 shadow-lg text-xs font-semibold transition-all duration-300 hover:shadow-xl focus:outline-none whitespace-nowrap">
                            + Add Establishment
                        </button>

                        <button id="map-reseller-location-btn" class="inline-flex items-center gap-1.5 bg-[#2F6DAA] text-white rounded-full px-3 py-2 shadow-lg text-xs font-semibold transition-all duration-300 hover:shadow-xl focus:outline-none whitespace-nowrap">
                            <span>+ Reseller Location</span>
                            <span
                                id="map-reseller-unmapped-count"
                                class="hidden inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold leading-none text-white bg-red-600 rounded-full"
                                aria-label="Unmapped reseller count"
                            >0</span>
                        </button>

                        <button id="cancel-placement-btn" style="display: none;" class="bg-red-500 text-white rounded-full px-4 py-2.5 shadow-lg text-sm font-semibold transition-all duration-300 hover:shadow-xl focus:outline-none whitespace-nowrap">
                            Cancel
                        </button>
                    </div>
                </div>

                <!-- Route strip -->
                <div id="route-strip" class="absolute bottom-4 left-1/2 transform -translate-x-1/2 bg-white rounded-lg shadow-lg p-3 hidden z-40 max-w-sm">
                    <div id="route-summary" class="text-sm font-medium text-gray-800"></div>
                    <div id="route-steps" class="mt-2 max-h-32 overflow-y-auto text-xs text-gray-600"></div>
                </div>
                <button id="clear-route-btn" class="absolute bottom-20 left-1/2 transform -translate-x-1/2 bg-red-500 text-white rounded-full p-2 shadow-lg hidden z-40">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>





                <!-- Modal for Add Establishment -->
                <div id="add-establishment-modal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center transition-opacity duration-300 opacity-0 px-4">
                    <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden transform scale-95 transition-transform duration-300">
                        <div class="h-[90vh] overflow-y-auto p-6 modal-body">
                            <div class="flex items-center justify-between mb-5">
                                <h2 class="text-2xl font-semibold text-[#3A2E22]">Add Establishment</h2>
                                <button id="close-modal" class="text-gray-500 hover:text-gray-800 text-2xl">&times;</button>
                            </div>
                            <form id="add-establishment-form" class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-semibold">Name *</label>
                                    <input name="name" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]" />
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold">Type *</label>
                                    <select name="type" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]">
                                        <option value="">Select type</option>
                                        <option value="farm">Farm</option>
                                        <option value="cafe">Cafe</option>
                                        <option value="roaster">Roaster</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold">Contact Number</label>
                                    <input name="contact_number" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]" />
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold">Email</label>
                                    <input type="email" name="email" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]" />
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold">Website</label>
                                    <input type="url" name="website" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]" />
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold">Visit Hours</label>
                                    <input name="visit_hours" placeholder="e.g. Mon-Sat, 8:00 AM - 5:00 PM" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]" />
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold">Activities</label>
                                    <input name="activities" placeholder="e.g. Farm tour, Cupping, Roasting demo" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]" />
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-semibold">Address *</label>
                                    <input name="address" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]" />
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-semibold">Barangay *</label>
                                    <input name="barangay" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]" />
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-semibold">Description</label>
                                    <textarea name="description" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]" rows="3"></textarea>
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-semibold">Image</label>
                                    <input type="file" name="image" id="image-input" accept="image/*" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm shadow-sm file:mr-3 file:rounded-md file:border-0 file:bg-[#4A6741] file:px-2.5 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-[#3A2E22] focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]" />
                                    <img id="image-preview" class="mt-3 max-h-32 w-full object-contain rounded-lg border border-gray-200 hidden" alt="Image Preview" />
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold">Latitude</label>
                                    <input name="latitude" id="latitude-input" readonly class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-2" />
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold">Longitude</label>
                                    <input name="longitude" id="longitude-input" readonly class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-2" />
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-semibold">Coffee Varieties</label>
                                    <div class="mt-2 grid gap-2" id="variety-options">
                                        <label class="flex items-center gap-2 rounded-lg border border-gray-200 bg-[#F5F0E8] px-2 py-1">
                                            <input type="checkbox" name="varieties[]" value="1" data-variety="1" />
                                            <span class="w-3 h-3 rounded-full" style="background:#4A6741"></span>
                                            <span class="flex-1 text-sm">Liberica</span>
                                            <input type="radio" name="primary_variety" value="1" />
                                        </label>
                                        <label class="flex items-center gap-2 rounded-lg border border-gray-200 bg-[#F5F0E8] px-2 py-1">
                                            <input type="checkbox" name="varieties[]" value="2" data-variety="2" />
                                            <span class="w-3 h-3 rounded-full" style="background:#B8860B"></span>
                                            <span class="flex-1 text-sm">Excelsa</span>
                                            <input type="radio" name="primary_variety" value="2" />
                                        </label>
                                        <label class="flex items-center gap-2 rounded-lg border border-gray-200 bg-[#F5F0E8] px-2 py-1">
                                            <input type="checkbox" name="varieties[]" value="3" data-variety="3" />
                                            <span class="w-3 h-3 rounded-full" style="background:#6B3A2A"></span>
                                            <span class="flex-1 text-sm">Robusta</span>
                                            <input type="radio" name="primary_variety" value="3" />
                                        </label>
                                        <label class="flex items-center gap-2 rounded-lg border border-gray-200 bg-[#F5F0E8] px-2 py-1">
                                            <input type="checkbox" name="varieties[]" value="4" data-variety="4" />
                                            <span class="w-3 h-3 rounded-full" style="background:#8B1A1A"></span>
                                            <span class="flex-1 text-sm">Arabica</span>
                                            <input type="radio" name="primary_variety" value="4" />
                                        </label>
                                    </div>
                                    <p class="text-xs text-[#6B3A2A]">Select one primary variety after choosing at least one variety.</p>
                                </div>
                                <div class="sm:col-span-2 flex justify-between pt-2">
                                    <button type="button" id="cancel-placement" class="px-4 py-2 rounded-lg bg-gray-200 text-gray-800 hover:bg-gray-300">Cancel</button>
                                    <button type="submit" class="px-4 py-2 rounded-lg bg-[#4A6741] text-white hover:bg-[#3A2E22]">Save Establishment</button>
                                </div>
                            </form>
                        </div>

                        <!-- Map Toast -->
                        <div id="map-toast" style="
                          position: fixed;
                          top: 20px;
                          right: 20px;
                          bottom: auto;
                          z-index: 1100;
                          min-width: 280px;
                          max-width: 360px;
                          background: white;
                          border-radius: 10px;
                          box-shadow: 0 4px 24px rgba(0,0,0,0.12);
                          padding: 14px 18px;
                          display: flex;
                          align-items: flex-start;
                          gap: 12px;
                          opacity: 0;
                          transform: translateY(8px);
                          transition: opacity 200ms ease, transform 200ms ease;
                          pointer-events: none;
                        ">
                          <div id="map-toast-icon" style="
                            width: 20px; height: 20px; border-radius: 50%;
                            flex-shrink: 0; margin-top: 1px;
                            display: flex; align-items: center; 
                            justify-content: center;
                            font-size: 11px; font-weight: 700; color: white;
                          "></div>
                          <div style="flex: 1;">
                            <div id="map-toast-title" style="
                              font-size: 13px; font-weight: 600; 
                              color: #3A2E22; margin-bottom: 2px;
                            "></div>
                            <div id="map-toast-message" style="
                              font-size: 12px; color: #888780; line-height: 1.4;
                            "></div>
                          </div>
                          <button onclick="hideMapToast()" style="
                            background: none; border: none; cursor: pointer;
                            color: #b0a89e; font-size: 16px; line-height: 1;
                            padding: 0; margin-left: 4px;
                          ">×</button>
                        </div>
                    </div>
                </div>

                <div id="map-reseller-modal" class="fixed inset-0 hidden bg-black bg-opacity-50 flex items-center justify-center transition-opacity duration-300 opacity-0 px-4" style="z-index: 12000;">
                    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[85vh] overflow-hidden transform scale-95 transition-transform duration-300" style="z-index: 12001; position: relative;">
                        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                            <div>
                                <h2 class="text-xl font-semibold text-[#3A2E22]">Map Reseller Location</h2>
                                <p class="text-xs text-[#9E8C78] mt-1">Select a verified reseller to map latitude and longitude</p>
                            </div>
                            <button id="close-map-reseller-modal" class="text-gray-500 hover:text-gray-800 text-2xl leading-none">&times;</button>
                        </div>

                        <div class="p-6 max-h-[65vh] overflow-y-auto">
                            <div id="map-reseller-empty" class="hidden py-10 text-center text-sm text-[#9E8C78]">
                                No verified resellers available.
                            </div>

                            <div id="map-reseller-list" class="space-y-2"></div>
                        </div>
                    </div>
                </div>
                <!-- ...existing code... -->
            </section>
        </div>
    </main>
</div>

@push('styles')
<style>
/* ...existing code... */
#panel-content-mobile.fade-out {
    opacity: 0;
}

#panel-content.fade-in,
#panel-content-mobile.fade-in {
    opacity: 1;
}

/* Variety Badge Styles */
.variety-badge {
    display: inline-flex;
    align-items: center;
    border-radius: 99px;
    padding: 3px 10px;
    font-size: 12px;
    font-weight: 500;
    border: 1px solid;
    margin-right: 6px;
    margin-bottom: 6px;
}

.variety-badge.liberica {
    background-color: rgba(74, 103, 65, 0.15);
    border-color: #4A6741;
    color: #2d4029;
}

.variety-badge.excelsa {
    background-color: rgba(184, 134, 11, 0.15);
    border-color: #B8860B;
    color: #6b4e00;
}

.variety-badge.robusta {
    background-color: rgba(107, 58, 42, 0.15);
    border-color: #6B3A2A;
    color: #3a2016;
}

.variety-badge.arabica {
    background-color: rgba(139, 26, 26, 0.15);
    border-color: #8B1A1A;
    color: #550d0d;
}

/* Thin Divider */
.panel-divider {
    border: none;
    border-top: 1px solid #e5e0d8;
    margin: 12px 0;
}

/* Progress Bar */
.rating-bar {
    height: 4px;
    background: #e5e0d8;
    border-radius: 2px;
    overflow: hidden;
    margin: 4px 0 8px 0;
}

.rating-bar-fill {
    height: 100%;
    background: #4A6741;
    border-radius: 2px;
}

/* Label Styles */
.panel-label {
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 0.08em;
    color: #888780;
    margin-bottom: 4px;
    text-transform: uppercase;
}

/* Photo Placeholder */
.establishment-photo-placeholder {
    width: 100%;
    height: 200px;
    background: #F5F0E8;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    margin-bottom: 16px;
}

.establishment-photo-placeholder svg {
    width: 48px;
    height: 48px;
    color: #4A6741;
}

/* Promo Styles */
.sidebar-section {
    margin-bottom: 20px;
}

.section-title {
    font-size: 14px;
    font-weight: 600;
    color: #3A2E22;
    margin-bottom: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.promo-card {
    background: #F5F0E8;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 8px;
    border: 1px solid #e5e0d8;
}

.promo-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.promo-title {
    font-size: 14px;
    font-weight: 600;
    color: #3A2E22;
}

.promo-discount {
    background: #4A6741;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}

.promo-description {
    font-size: 13px;
    color: #6B3A2A;
    margin-bottom: 8px;
    line-height: 1.4;
}

.promo-code {
    font-size: 12px;
    color: #3A2E22;
    margin-bottom: 4px;
}

.promo-validity {
    font-size: 11px;
    color: #9E8C78;
}
</style>
@endpush

@push('styles')
<style>
/* Add Establishment Button Styles */
@keyframes mapBtnFadeUp {
    from {
        opacity: 0;
        transform: translateY(6px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

#add-establishment-btn,
#map-reseller-location-btn,
#cancel-placement-btn,
#cancel-placement,
#edit-cancel {
    font-family: 'Poppins', sans-serif !important;
}

#add-establishment {
    box-shadow: 0 2px 8px rgba(74, 103, 65, 0.30) !important;
    transition: background-color 150ms ease-out,
                box-shadow 150ms ease-out !important;
    animation: mapBtnFadeUp 180ms ease-out both;
}

#add-establishment:hover {
    background-color: #3A2E22 !important;
}

#add-establishment:active {
    background-color: #2A2118 !important;
}

#add-establishment:focus-visible {
    outline: 2px solid #4A6741;
    outline-offset: 3px;
}

/* Cancel state when in placement mode */
#add-establishment.bg-red-500 {
    box-shadow: 0 2px 8px rgba(185, 28, 28, 0.25);
}

#add-establishment.bg-red-500:hover {
    background-color: #dc2626 !important;
}

#add-establishment.bg-red-500:active {
    background-color: #b91c1c !important;
}
</style>
@endpush

@push('scripts')
<script>
    window.MAPBOX_TOKEN = '{{ $mapboxToken }}';
    window.GOOGLE_MAPS_KEY = '{{ $googleMapsKey }}';
    window.JWT_TOKEN = '{{ auth()->user()->api_token ?? '' }}';
    window.CSRF_TOKEN = '{{ csrf_token() }}';
    window.ESTABLISHMENTS = @json($establishments);
    window.VERIFIED_RESELLERS = @json($verifiedResellers ?? []);
</script>
@vite('resources/js/map.js')
