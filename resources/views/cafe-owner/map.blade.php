@extends('cafe-owner.layouts.app')

@section('title', 'Map - BrewHub')

@push('head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin=""/>
    @vite('resources/css/app.css')
@endpush

@section('content')
<div class="cafe-owner-map-page h-screen -m-8 p-6 overflow-hidden bg-[#F5F0E8] text-[#3A2E22]">
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
    .cafe-owner-map-page {
        height: auto !important;
        min-height: calc(100dvh - 4.8rem);
        margin: 0 !important;
        padding: 0.75rem !important;
        overflow: visible !important;
    }

    .cafe-owner-map-page > .max-w-full {
        height: auto !important;
        min-height: calc(100dvh - 6rem);
        gap: 0.75rem !important;
    }

    .cafe-owner-map-page header {
        padding: 0.8rem !important;
        border-radius: 14px !important;
        align-items: flex-start;
    }

    .cafe-owner-map-page .map-header-search {
        width: 100%;
        flex-wrap: wrap;
        gap: 0.45rem !important;
    }

    .cafe-owner-map-page .map-header-search .relative {
        width: 100%;
    }

    .cafe-owner-map-page #map-filter {
        flex: 1;
        min-width: 8rem;
    }

    .cafe-owner-map-page #map-barangay-search {
        width: 100% !important;
    }

    .cafe-owner-map-page #map-barangay-search-btn {
        min-height: 2rem;
    }

    .cafe-owner-map-page .map-wrapper {
        border-radius: 14px !important;
        padding: 0.6rem !important;
        min-height: 62vh;
    }

    .cafe-owner-map-page #filter-panel {
        margin-bottom: 0.6rem !important;
        border-radius: 14px !important;
    }

    .cafe-owner-map-page #map {
        min-height: 52vh;
    }
}
</style>
@endpush

@push('scripts')
<script>
    window.IS_FARM_OWNER_MAP = true;
    window.MAPBOX_TOKEN = '{{ $mapboxToken }}';
    window.GOOGLE_MAPS_KEY = '{{ $googleMapsKey }}';
    window.JWT_TOKEN = '{{ auth()->user()->api_token ?? '' }}';
    window.CSRF_TOKEN = '{{ csrf_token() }}';
    window.ESTABLISHMENTS = @json($establishments);
    window.VERIFIED_RESELLERS = @json($verifiedResellers ?? []);
</script>
@vite('resources/js/map.js')
@endpush
