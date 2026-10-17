@extends('farm-owner.layouts.app')

@section('title', 'Map - BrewHub')

@push('head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin=""/>
@endpush

@section('content')
@php
    $isRoasterWorkspace = auth()->user()?->role === 'roaster'
        || request()->routeIs('roaster-owner.*')
        || (($establishment->type ?? null) === 'roaster');
    $workspaceLabel = $isRoasterWorkspace ? 'Roaster' : 'Farm';
    $mapStoreRouteName = $isRoasterWorkspace ? 'roaster-owner.roasters.store' : 'farm-owner.farms.store';
    $mapEstablishmentIdParam = $isRoasterWorkspace ? 'roaster_id' : 'farm_id';
@endphp

<div class="farm-owner-map-page h-screen -m-8 p-6 overflow-hidden bg-[#F5F0E8] text-[#3A2E22]">
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
                        placeholder="Search location..."
                        class="pl-9 pr-3 py-1.5 text-xs bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#4A6741] focus:border-transparent w-64"
                    />
                </div>
                <datalist id="map-barangay-suggestions"></datalist>
                <button
                    id="map-barangay-search-btn"
                    type="button"
                    class="text-xs px-3 py-1.5 rounded-lg bg-[#4A6741] text-white font-medium hover:bg-[#3A2E22] transition-colors"
                >
                    Search
                </button>
            </div>
        </header>

        <section class="map-wrapper flex-1 rounded-[20px] shadow-lg bg-white p-4 relative overflow-hidden flex flex-col">
            <div id="filter-panel" class="filter-panel-container w-full bg-white rounded-[20px] shadow-sm mb-4"></div>

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
            </div>

            <div id="owner-pin-controls" class="absolute bottom-7 left-7 z-[950] flex flex-col items-start gap-2">
                <button id="add-farm-location-btn" type="button" class="inline-flex items-center gap-1.5 rounded-full border border-[#D9CDBA] bg-white px-3 py-2 text-xs font-semibold text-[#3A2E22] shadow-lg transition hover:-translate-y-0.5 hover:bg-[#F5F0E8] focus:outline-none focus:ring-2 focus:ring-[#2E5A3D]/40">
                    <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14"/></svg>
                    Add {{ $workspaceLabel }} Location
                </button>
                <button id="pin-my-establishment-btn" type="button" class="inline-flex items-center gap-1.5 rounded-full bg-[#2E5A3D] px-3 py-2.5 text-xs font-semibold text-white shadow-lg transition hover:-translate-y-0.5 hover:bg-[#1E3A2A] focus:outline-none focus:ring-2 focus:ring-[#2E5A3D]/40">
                    <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5" stroke-width="2"/></svg>
                    <span id="pin-my-establishment-label">{{ $activeFarm?->latitude !== null && $activeFarm?->longitude !== null ? 'Edit Pinned Location' : 'Pin My Establishment' }}</span>
                </button>
                <div id="owner-pin-confirm" class="hidden w-[min(20rem,calc(100vw-3rem))] rounded-xl border border-[#D9CDBA] bg-white p-4 shadow-xl">
                    <p id="owner-pin-confirm-text" class="text-sm font-semibold text-[#3A2E22]">Use this location for your establishment?</p>
                    <p id="owner-pin-confirm-help" class="mt-1 text-xs leading-5 text-[#7B6A59]">Click another point to adjust the pin, or save the selected location.</p>
                    <div class="mt-3 flex gap-2">
                        <button id="owner-pin-save-btn" type="button" class="flex-1 rounded-lg bg-[#2E5A3D] px-3 py-2 text-xs font-semibold text-white transition hover:bg-[#1E3A2A] disabled:cursor-not-allowed disabled:opacity-60">Save Location</button>
                        <button id="owner-pin-cancel-btn" type="button" class="rounded-lg border border-[#D4C5B2] bg-white px-3 py-2 text-xs font-semibold text-[#3A2E22] transition hover:bg-[#F5F0E8]">Cancel</button>
                    </div>
                </div>
            </div>

            <div id="add-farm-location-modal" data-open-on-load="{{ $errors->any() ? 'true' : 'false' }}" class="fixed inset-0 z-[1200] hidden items-center justify-center bg-black/40 px-4 py-6" role="dialog" aria-modal="true" aria-labelledby="add-farm-location-title">
                <div class="w-full max-w-lg overflow-hidden rounded-2xl border border-[#D9CDBA] bg-[#F8F2E8] shadow-2xl">
                    <div class="flex items-start justify-between border-b border-[#E4D8C6] px-5 py-4 sm:px-6">
                        <div>
                            <h2 id="add-farm-location-title" class="font-display text-xl font-bold text-[#3A2E22]">Add {{ $workspaceLabel }} Location</h2>
                            <p class="mt-1 text-sm text-[#8C7A68]">Create another {{ strtolower($workspaceLabel) }} establishment under your account, then pin its location on the map.</p>
                        </div>
                        <button type="button" id="close-add-farm-location-modal" class="rounded-lg p-1 text-xl leading-none text-[#6B5B4A] hover:bg-[#ECE1CF]" aria-label="Close">&times;</button>
                    </div>
                    <form method="POST" action="{{ route($mapStoreRouteName) }}" class="space-y-4 px-5 py-5 sm:px-6">
                        @csrf
                        <div>
                            <label for="new-farm-name" class="mb-1 block text-sm font-semibold text-[#3A2E22]">{{ $workspaceLabel }} name *</label>
                            <input id="new-farm-name" name="name" required maxlength="255" class="w-full rounded-lg border border-[#D9CDBA] bg-white px-3 py-2.5 text-sm text-[#3A2E22] outline-none focus:border-[#4A6741] focus:ring-2 focus:ring-[#4A6741]/20" placeholder="{{ $workspaceLabel === 'Roaster' ? 'e.g. Lipa Coffee Roastery' : 'e.g. Lipa Coffee Farm' }}">
                        </div>
                        <div>
                            <label for="new-farm-address" class="mb-1 block text-sm font-semibold text-[#3A2E22]">Street / full address *</label>
                            <input id="new-farm-address" name="address" required maxlength="255" class="w-full rounded-lg border border-[#D9CDBA] bg-white px-3 py-2.5 text-sm text-[#3A2E22] outline-none focus:border-[#4A6741] focus:ring-2 focus:ring-[#4A6741]/20" placeholder="Street and area">
                        </div>
                        <div>
                            <label for="new-farm-barangay" class="mb-1 block text-sm font-semibold text-[#3A2E22]">Barangay *</label>
                            <input id="new-farm-barangay" name="barangay" required maxlength="255" class="w-full rounded-lg border border-[#D9CDBA] bg-white px-3 py-2.5 text-sm text-[#3A2E22] outline-none focus:border-[#4A6741] focus:ring-2 focus:ring-[#4A6741]/20" placeholder="Barangay, Lipa City">
                        </div>
                        @if($errors->any())
                            <p class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ $errors->first() }}</p>
                        @endif
                        <div class="flex justify-end gap-2 border-t border-[#E4D8C6] pt-4">
                            <button type="button" id="cancel-add-farm-location-modal" class="rounded-lg border border-[#D9CDBA] bg-white px-4 py-2 text-sm font-semibold text-[#3A2E22] transition hover:bg-[#ECE1CF]">Cancel</button>
                            <button type="submit" class="rounded-lg bg-[#2E5A3D] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1E3A2A]">Add {{ $workspaceLabel }}</button>
                        </div>
                    </form>
                </div>
            </div>
            <div id="route-strip" class="absolute bottom-4 left-1/2 transform -translate-x-1/2 bg-white rounded-lg shadow-lg p-3 hidden z-40 max-w-sm">
                <div id="route-summary" class="text-sm font-medium text-gray-800"></div>
                <div id="route-steps" class="mt-2 max-h-32 overflow-y-auto text-xs text-gray-600"></div>
            </div>
            <button id="clear-route-btn" class="absolute bottom-20 left-1/2 transform -translate-x-1/2 bg-red-500 text-white rounded-full p-2 shadow-lg hidden z-40">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </section>
    </div>
</div>
@endsection

@push('styles')
<style>
#panel-content-mobile.fade-out {
    opacity: 0;
}

#panel-content.fade-in,
#panel-content-mobile.fade-in {
    opacity: 1;
}

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

.panel-divider {
    border: none;
    border-top: 1px solid #e5e0d8;
    margin: 12px 0;
}

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

.panel-label {
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 0.08em;
    color: #888780;
    margin-bottom: 4px;
    text-transform: uppercase;
}

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

#edit-location-btn {
    display: none !important;
}

@media (max-width: 767px) {
    .farm-owner-map-page {
        height: auto !important;
        min-height: calc(100dvh - 4.8rem);
        margin: 0 !important;
        padding: 0.75rem !important;
        overflow: visible !important;
    }

    .farm-owner-map-page > .max-w-full {
        height: auto !important;
        min-height: calc(100dvh - 6rem);
        gap: 0.75rem !important;
    }

    .farm-owner-map-page header {
        padding: 0.8rem !important;
        border-radius: 14px !important;
        align-items: flex-start;
    }

    .farm-owner-map-page .map-header-search {
        width: 100%;
        flex-wrap: wrap;
        gap: 0.45rem !important;
    }

    .farm-owner-map-page .map-header-search .relative {
        width: 100%;
    }

    .farm-owner-map-page #map-filter {
        flex: 1;
        min-width: 8rem;
    }

    .farm-owner-map-page #map-barangay-search {
        width: 100% !important;
    }

    .farm-owner-map-page #map-barangay-search-btn {
        min-height: 2rem;
    }

    .farm-owner-map-page .map-wrapper {
        border-radius: 14px !important;
        padding: 0.6rem !important;
        min-height: 62vh;
    }

    .farm-owner-map-page #filter-panel {
        margin-bottom: 0.6rem !important;
        border-radius: 14px !important;
    }

    .farm-owner-map-page #map {
        min-height: 52vh;
    }
}
</style>
@endpush

@push('scripts')
<script>
    window.IS_FARM_OWNER_MAP = true;
    window.IS_ESTABLISHMENT_OWNER_MAP = true;
    window.ESTABLISHMENT_OWNER_PIN_URL = '{{ route('my-establishment.location.update') }}';
    window.OWNER_ESTABLISHMENT_ID = @json($activeFarm?->id);
    window.OWNER_ESTABLISHMENT_PINNED = @json($activeFarm?->latitude !== null && $activeFarm?->longitude !== null);
    window.OWNER_REGISTRATION_NOTICE = @json(session('success'));
    window.MAPBOX_TOKEN = '{{ $mapboxToken }}';
    window.GOOGLE_MAPS_KEY = '{{ $googleMapsKey }}';
    window.JWT_TOKEN = '{{ auth()->user()->api_token ?? '' }}';
    window.CSRF_TOKEN = '{{ csrf_token() }}';
    window.ESTABLISHMENTS = @json($establishments);
    window.VERIFIED_RESELLERS = @json($verifiedResellers ?? []);
</script>
@vite('resources/js/map.js')
@endpush
