import L from 'leaflet';

let map;
let tempMarker = null;
let placementMode = false;
let editMode = false;
let resellerPlacementMode = false;
let editingMarker = null;
let originalLatLng = null;
let selectedResellerForMapping = null;
let pendingResellerLatLng = null;
let resellerTempMarker = null;
let toastTimer = null;
let barangaySuggestTimer = null;
let barangaySuggestAbortController = null;

// Fallback icons for placement/edit mode
const newPinIcon = L.divIcon({
    html: `<svg width="30" height="30" viewBox="0 0 30 30" xmlns="http://www.w3.org/2000/svg">
        <circle cx="15" cy="15" r="13" fill="#378ADD" stroke="white" stroke-width="2"/>
        <circle cx="15" cy="15" r="5" fill="white"/>
    </svg>`,
    className: 'custom-marker',
    iconSize: [30, 30],
    iconAnchor: [15, 15]
});

const editingIcon = L.divIcon({
    html: `<div style="position: relative;">
        <svg width="30" height="30" viewBox="0 0 30 30" xmlns="http://www.w3.org/2000/svg">
            <circle cx="15" cy="15" r="13" fill="#16a34a" stroke="white" stroke-width="2"/>
            <circle cx="15" cy="15" r="5" fill="white"/>
        </svg>
        <div class="pulse-ring" style="position: absolute; top: -2px; left: -2px; width: 34px; height: 34px; border: 2px solid #16a34a; border-radius: 50%; pointer-events: none;"></div>
    </div>`,
    className: 'custom-marker',
    iconSize: [30, 30],
    iconAnchor: [15, 15]
});

window.layers = {
    farms: L.layerGroup(),
    cafes: L.layerGroup(),
    roasters: L.layerGroup(),
    resellers: L.layerGroup(),
    all: L.layerGroup(),
    liberica: L.layerGroup(),
    excelsa: L.layerGroup(),
    robusta: L.layerGroup(),
    arabica: L.layerGroup()
};

window.markerIndex = {};
let currentPanelFeature = null;
window.currentPanelFeature = null;
function isFarmOwnerMapPage() {
    return window.IS_FARM_OWNER_MAP === true
        || window.location.pathname.startsWith('/farm-owner')
        || !!document.querySelector('.farm-owner-map-page');
}
window.userLocationGroup = null;
window.userLocationWatchId = null;
window.userLocation = null;
window.routeLayer = null;
window.filterState = {
    varieties: [],
    types: ['farm', 'cafe', 'roaster', 'reseller']
};

const ESTABLISHMENT_TYPE_THEME = {
    farm: { color: '#4A6741', label: 'Farm' },
    cafe: { color: '#8B4513', label: 'Café' },
    roaster: { color: '#6B3A2A', label: 'Roaster' },
    reseller: { color: '#2F6DAA', label: 'Reseller' },
    default: { color: '#3A2E22', label: 'Establishment' }
};

function getEstablishmentTypeTheme(type) {
    return ESTABLISHMENT_TYPE_THEME[(type || '').toLowerCase()] || ESTABLISHMENT_TYPE_THEME.default;
}

function hexToRgba(hex, alpha) {
    const normalized = hex.replace('#', '');
    const expanded = normalized.length === 3
        ? normalized.split('').map((char) => char + char).join('')
        : normalized;

    const red = Number.parseInt(expanded.slice(0, 2), 16);
    const green = Number.parseInt(expanded.slice(2, 4), 16);
    const blue = Number.parseInt(expanded.slice(4, 6), 16);

    return `rgba(${red}, ${green}, ${blue}, ${alpha})`;
}

function buildTypeFilterButton(type) {
    const theme = getEstablishmentTypeTheme(type);
    const isActive = window.filterState.types.includes(type);

    return `
        <button
            id="type-${type}"
            type="button"
            data-type="${type}"
            class="type-filter-btn px-3 py-2 rounded-lg text-xs font-medium transition-colors ${isActive ? 'active' : ''}"
            style="--type-color:${theme.color}; --type-color-soft:${hexToRgba(theme.color, 0.12)};"
        >
            <span class="type-filter-swatch" aria-hidden="true"></span>
            <span>${theme.label}</span>
        </button>
    `;
}

function showMapToast(type, title, message) {
    const toast = document.getElementById('map-toast')
    if (!toast) return

    // Keep toast visible even when the add-establishment modal is hidden.
    if (toast.parentElement !== document.body) {
        document.body.appendChild(toast)
    }

  const icon = document.getElementById('map-toast-icon')
  const titleEl = document.getElementById('map-toast-title')
  const msgEl = document.getElementById('map-toast-message')

    if (!icon || !titleEl || !msgEl) return

  const config = {
    success: { bg: '#4A6741', symbol: '✓' },
    error:   { bg: '#dc2626', symbol: '✕' },
    warning: { bg: '#B8860B', symbol: '!' },
    info:    { bg: '#378ADD', symbol: 'i' }
  }
  const c = config[type] || config.info

  icon.style.background = c.bg
  icon.textContent = c.symbol
  titleEl.textContent = title
  msgEl.textContent = message

  toast.style.pointerEvents = 'auto'
  toast.style.opacity = '1'
  toast.style.transform = 'translateY(0)'

  if (toastTimer) clearTimeout(toastTimer)
  toastTimer = setTimeout(() => hideMapToast(), 4000)
}

function hideMapToast() {
  const toast = document.getElementById('map-toast')
    if (!toast) return
  toast.style.opacity = '0'
  toast.style.transform = 'translateY(8px)'
  toast.style.pointerEvents = 'none'
}

document.addEventListener('DOMContentLoaded', async () => {
    initializeMap();
    initializeMapStyles();
    ensurePlacementHelper();
    removeLegacyFloatingMapUI();
    initializeMapPanes();
    initializeLocationLayers();
    initializeLocationControl();

    await loadEstablishments();
    buildFilterPanel();
    filterMarkers([], ['farm', 'cafe', 'roaster', 'reseller']);

    setupStaticEventListeners();
});

function initializeMap() {
    map = L.map('map', {
        center: [13.9411, 121.1634],
        zoom: 13,
        minZoom: 10,
        maxZoom: 18,
        maxBounds: [[13.5, 120.7], [14.4, 121.8]],
        scrollWheelZoom: false
    });
    window.adminMapInstance = map;

    const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    });

    const mapboxLayer = L.tileLayer(
        'https://api.mapbox.com/styles/v1/mapbox/streets-v11/tiles/{z}/{x}/{y}?access_token={accessToken}',
        {
            attribution:
                'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
            maxZoom: 18,
            id: 'mapbox/streets-v11',
            tileSize: 512,
            zoomOffset: -1,
            accessToken: window.MAPBOX_TOKEN
        }
    );

    osmLayer.addTo(map);

    const baseLayers = {
        OpenStreetMap: osmLayer,
        'Mapbox Streets': mapboxLayer
    };

    L.control.layers(baseLayers, {}, { position: 'topright' }).addTo(map);

    // Fade in map after tiles load
    map.once('load', () => {
        document.getElementById('map').style.opacity = '1';
    });
    // Fallback in case 'load' doesn't fire
    setTimeout(() => {
        document.getElementById('map').style.opacity = '1';
    }, 300);
}

function initializeMapStyles() {
    if (document.getElementById('map-dynamic-style')) return;

    const styleEl = document.createElement('style');
    styleEl.id = 'map-dynamic-style';
    styleEl.innerHTML = `
        @keyframes user-location-pulse {
            0% { transform: scale(1); opacity: 0.6; }
            100% { transform: scale(2.5); opacity: 0; }
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .user-location-pulse {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            border: 2px solid #378ADD;
            background: rgba(55, 138, 221, 0.15);
            animation: user-location-pulse 2s ease-out infinite;
        }

        .spinner-border {
            animation: spin 1s linear infinite;
        }

        /* Map shell */
        #map {
            border-radius: 18px;
            overflow: hidden;
        }

        .leaflet-container {
            font-family: inherit;
            background: #F7F4EF;
        }

        /* Better spacing for controls */
        .leaflet-top.leaflet-left,
        .leaflet-top.leaflet-right,
        .leaflet-bottom.leaflet-left,
        .leaflet-bottom.leaflet-right {
            margin: 10px;
        }

        /* General control card design */
        .leaflet-control-zoom,
        .leaflet-control-layers,
        .leaflet-control-custom {
            border: 1px solid #E7DED1 !important;
            box-shadow: 0 8px 24px rgba(58, 46, 34, 0.10) !important;
            border-radius: 14px !important;
            overflow: hidden;
            background: #FFFFFF !important;
        }

        /* Zoom buttons */
        .leaflet-control-zoom a {
            width: 40px !important;
            height: 40px !important;
            line-height: 40px !important;
            font-size: 20px !important;
            color: #3A2E22 !important;
            border: none !important;
            background: #FFFFFF !important;
            transition: background 0.2s ease, color 0.2s ease;
        }

        .leaflet-control-zoom a:hover {
            background: #F3EEE6 !important;
            color: #4A6741 !important;
        }

        .leaflet-control-zoom a:first-child {
            border-bottom: 1px solid #EEE4D7 !important;
        }

        .custom-marker-tooltip .tooltip-rating {
            color: #f5c518;
            font-weight: 700;
        }

        .custom-marker-tooltip .tooltip-star {
            color: #f5c518;
        }

        /* Layer control button */
        .leaflet-control-layers-toggle {
            width: 40px !important;
            height: 40px !important;
            background-size: 18px 18px !important;
        }

        .leaflet-control-layers-expanded {
            padding: 12px 14px !important;
            border-radius: 14px !important;
            color: #3A2E22 !important;
            font-size: 14px !important;
            min-width: 170px;
        }

        .custom-marker-tooltip,
        .leaflet-control-layers,
        .leaflet-control-layers-expanded,
        .leaflet-control-layers-list,
        .leaflet-control-layers-base label,
        .leaflet-control-layers-overlays label {
            font-family: 'Poppins', sans-serif !important;
        }

        /* My location control */
        .leaflet-control-custom {
            width: 40px !important;
            height: 40px !important;
            display: flex !important;
            align-items: center;
            justify-content: center;
            border-radius: 14px !important;
            background: #FFFFFF !important;
            transition: background 0.2s ease, transform 0.2s ease;
        }

        .leaflet-control-custom:hover {
            background: #F3EEE6 !important;
            transform: translateY(-1px);
        }

        /* Filter panel styling */
        #filter-panel {
            position: relative;
            z-index: 20;
            overflow-x: auto;
            overflow-y: hidden;
            white-space: nowrap;
        }

        #filter-panel .filter-content {
            display: flex;
            flex-wrap: nowrap;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            min-width: max-content;
        }

        #filter-panel .filter-section-types,
        #filter-panel .filter-section-varieties,
        #filter-panel .filter-section-summary {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: nowrap;
            white-space: nowrap;
        }

        #filter-panel .type-filters,
        #filter-panel .variety-filters {
            display: flex;
            flex-wrap: nowrap;
            gap: 6px;
        }

        #filter-panel .type-filters .type-filter-btn,
        #filter-panel .variety-btn {
            background: #fff;
            border: 1px solid #E7DED1;
            color: #7A6A58;
            border-radius: 10px;
            transition: all 0.2s ease;
            font-size: 11px;
            line-height: 1;
        }

        #filter-panel .type-filters .type-filter-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        #filter-panel .type-filters .type-filter-swatch {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: var(--type-color);
            flex-shrink: 0;
        }

        #filter-panel .type-filters .type-filter-btn:hover {
            border-color: var(--type-color);
            color: #3A2E22;
        }

        #filter-panel .type-filters .type-filter-btn.active,
        #filter-panel .variety-btn.active {
            background: #F7F2EA;
            color: #3A2E22;
            border-color: #D8C8B3;
            box-shadow: 0 4px 10px rgba(58, 46, 34, 0.06);
        }

        #filter-panel .type-filters .type-filter-btn.active {
            background: var(--type-color-soft);
            border-color: var(--type-color);
            box-shadow: 0 4px 10px rgba(58, 46, 34, 0.08);
        }

        #filter-panel .badge {
            background: #EFE7DB;
            color: #6B5A48;
            border-radius: 999px;
            padding: 1px 6px;
            font-size: 10px;
            font-weight: 700;
        }

        /* Make the original Add Establishment button visible inside map */
        #add-establishment {
            position: absolute !important;
            left: 16px !important;
            bottom: 16px !important;
            z-index: 650 !important;
            border-radius: 14px !important;
            box-shadow: 0 10px 24px rgba(58, 46, 34, 0.18) !important;
        }

        #placement-helper {
    position: absolute;
    left: 16px; /* 🔥 slightly left than button */
    bottom: 52px; /* sits just above button */
    z-index: 649;

    display: none;
    max-width: 220px;

    padding: 6px 10px;
    border-radius: 999px; /* pill look */

    background: rgba(255, 255, 255, 0.75);
    backdrop-filter: blur(6px);

    border: 1px solid rgba(231, 222, 209, 0.9);
    box-shadow: 0 4px 12px rgba(58, 46, 34, 0.08);

    color: #6B5A48;
    font-size: 11.5px;
    font-weight: 500;

    white-space: nowrap; /* cleaner */
    pointer-events: none;
}

#placement-helper.show {
    display: block;
}

        /* Keep route strip above the map if present */
        #route-strip,
        #clear-route-btn {
            z-index: 640 !important;
        }

        /* Hide old floating legend / old overlapping top strip if still rendered */
        .coffee-varieties-floating,
        .map-legend-floating,
        .floating-filter-bar,
        #map-legend-top,
        #floating-map-filters {
            display: none !important;
        }

        /* Details panel overlap fixes */
        #map.has-details-panel .leaflet-top.leaflet-right,
        #map.has-details-panel .leaflet-bottom.leaflet-right {
            right: 340px !important; /* Push controls left when panel is open */
        }

        /* Smaller action buttons in details panel */
        #details-panel .action-btn {
            height: 38px !important;
            padding: 8px 12px !important;
            font-size: 11px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            margin-bottom: 12px !important;
        }

        /* Reduce large bottom gap in details panel while preserving comfortable padding */
        #details-panel {
            padding-bottom: 20px !important;
            min-height: auto !important;
        }

        /* Keep action buttons visible with tighter bottom spacing */
        #details-panel .action-buttons {
            margin-bottom: 6px !important;
            padding-bottom: 0 !important;
        }

        #details-panel .action-btn {
            margin-bottom: 6px !important;
        }

        /* Restore Leaflet default attribution placement (bottom-right) */
        .leaflet-control-attribution {
            margin-bottom: 0 !important;
            margin-right: 0 !important;
            background: transparent !important;
            padding: 0 !important;
            border-radius: 0 !important;
        }
    `;
    document.head.appendChild(styleEl);
}

function removeLegacyFloatingMapUI() {
    const selectors = [
        '.coffee-varieties-floating',
        '.map-legend-floating',
        '.floating-filter-bar',
        '#map-legend-top',
        '#floating-map-filters'
    ];

    selectors.forEach((selector) => {
        document.querySelectorAll(selector).forEach((el) => el.remove());
    });

    // Extra fallback:
    // removes the old floating white strip if it contains "Coffee Varieties:"
    document.querySelectorAll('div, section').forEach((el) => {
        const text = (el.textContent || '').trim();
        if (
            text.includes('Coffee Varieties:') &&
            !el.closest('#filter-panel') &&
            !el.closest('.leaflet-control')
        ) {
            el.style.display = 'none';
        }
    });
}

function ensurePlacementHelper() {
    let helper = document.getElementById('placement-banner');
    if (!helper) {
        // Fallback to creating one if not found
        const mapEl = document.getElementById('map');
        if (!mapEl) return null;

        helper = document.createElement('div');
        helper.id = 'placement-banner';
        helper.textContent = 'Click the map to place establishment';
        helper.className = 'absolute hidden px-3 py-1 rounded-lg bg-black bg-opacity-70 text-white text-xs font-medium transition-all duration-200';
        helper.style.cssText = 'bottom: 76px; left: 24px; z-index: 900;';
        mapEl.appendChild(helper);
    }

    return helper;
}



function initializeMapPanes() {
    map.createPane('userPane');
    map.getPane('userPane').style.zIndex = 650;
}

function initializeLocationLayers() {
    window.userLocationGroup = L.layerGroup().addTo(map);
}

function initializeLocationControl() {
    const MyLocationControl = L.Control.extend({
        options: { position: 'topright' },

        onAdd: function () {
            const container = L.DomUtil.create('div', 'leaflet-bar leaflet-control leaflet-control-custom');
            container.style.background = '#fff';
            container.style.width = '34px';
            container.style.height = '34px';
            container.style.display = 'flex';
            container.style.alignItems = 'center';
            container.style.justifyContent = 'center';
            container.style.cursor = 'pointer';
            container.style.border = '1px solid #ccc';
            container.style.borderRadius = '4px';
            container.title = 'My location';
            container.innerHTML =
                '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4" stroke="#333" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="12" r="5" stroke="#333" stroke-width="2"/></svg>';

            L.DomEvent.on(container, 'click', function (e) {
                L.DomEvent.stopPropagation(e);
                L.DomEvent.preventDefault(e);
                handleLocationButtonClick(container);
            });

            return container;
        }
    });

    map.addControl(new MyLocationControl());
}

function handleLocationButtonClick(buttonEl) {
    buttonEl.innerHTML =
        '<div style="width:18px;height:18px;border:2px solid #ccc;border-top-color:#378ADD;border-radius:50%;animation:spin 1s linear infinite"></div>';
    buttonEl.title = 'Locating...';

    if (!navigator.geolocation) {
        showToast('Geolocation is not supported by your browser', true);
        resetLocationButton(buttonEl);
        return;
    }

    navigator.geolocation.getCurrentPosition(
        (pos) => {
            resetLocationButton(buttonEl);
            setUserLocation(pos);
            startWatchPosition();
        },
        (err) => {
            resetLocationButton(buttonEl);
            let msg = 'Unable to get location';
            if (err.code === err.PERMISSION_DENIED) msg = 'Location permission denied';
            if (err.code === err.POSITION_UNAVAILABLE) msg = 'Location unavailable';
            if (err.code === err.TIMEOUT) msg = 'Location request timed out';
            showToast(msg, true);
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
}

function resetLocationButton(buttonEl) {
    if (!buttonEl) return;

    buttonEl.title = 'My location';
    buttonEl.innerHTML =
        '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4" stroke="#333" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="12" r="5" stroke="#333" stroke-width="2"/></svg>';
}

function setUserLocation(position) {
    const latlng = position.coords
        ? { lat: position.coords.latitude, lng: position.coords.longitude }
        : position;

    window.userLocation = latlng;

    if (window.userLocationGroup) {
        window.userLocationGroup.clearLayers();
    }

    if (position.coords && position.coords.accuracy) {
        L.circle(latlng, {
            radius: position.coords.accuracy,
            color: '#378ADD',
            fillOpacity: 0.08,
            weight: 0.5,
            pane: 'userPane'
        }).addTo(window.userLocationGroup);
    }

    L.marker(latlng, {
        pane: 'userPane',
        icon: L.divIcon({
            className: 'user-location-pulse',
            iconSize: [56, 56],
            iconAnchor: [28, 28]
        })
    }).addTo(window.userLocationGroup);

    L.circleMarker(latlng, {
        radius: 8,
        fillColor: '#378ADD',
        color: 'white',
        weight: 2,
        fillOpacity: 1,
        pane: 'userPane'
    }).addTo(window.userLocationGroup);

    map.setView(latlng, 15);

    fetch('/api/user-location', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.CSRF_TOKEN
        },
        body: JSON.stringify({ latitude: latlng.lat, longitude: latlng.lng })
    }).catch((err) => console.warn('User location PUT failed', err));
}

function startWatchPosition() {
    if (!navigator.geolocation) return;

    if (window.userLocationWatchId !== null) {
        navigator.geolocation.clearWatch(window.userLocationWatchId);
    }

    window.userLocationWatchId = navigator.geolocation.watchPosition(
        (pos) => setUserLocation(pos),
        (err) => {
            let msg = 'Unable to track location';
            if (err.code === err.PERMISSION_DENIED) msg = 'Location permission denied';
            if (err.code === err.POSITION_UNAVAILABLE) msg = 'Location unavailable';
            if (err.code === err.TIMEOUT) msg = 'Location timeout';
            showToast(msg, true);
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
}

function decodePolyline(encoded) {
    let index = 0;
    let lat = 0;
    let lng = 0;
    const coordinates = [];

    while (index < encoded.length) {
        let b;
        let shift = 0;
        let result = 0;

        do {
            b = encoded.charCodeAt(index++) - 63;
            result |= (b & 0x1f) << shift;
            shift += 5;
        } while (b >= 0x20);

        const deltaLat = (result & 1) ? ~(result >> 1) : result >> 1;
        lat += deltaLat;

        shift = 0;
        result = 0;

        do {
            b = encoded.charCodeAt(index++) - 63;
            result |= (b & 0x1f) << shift;
            shift += 5;
        } while (b >= 0x20);

        const deltaLng = (result & 1) ? ~(result >> 1) : result >> 1;
        lng += deltaLng;

        coordinates.push([lat / 1e5, lng / 1e5]);
    }

    return coordinates;
}

function clearRoute() {
    if (window.routeLayer) {
        map.removeLayer(window.routeLayer);
        window.routeLayer = null;
    }

    const strip = document.getElementById('directions-strip');
    if (strip) {
        strip.style.display = 'none';
        strip.innerHTML = '';
    }

    const clearRouteSidebarBtn = document.getElementById('clear-route-sidebar-btn');
    if (clearRouteSidebarBtn) {
        clearRouteSidebarBtn.style.display = 'none';
    }

    const clearRouteBtn = document.getElementById('clear-route-btn');
    if (clearRouteBtn) {
        clearRouteBtn.style.display = 'none';
    }
}

function handleClearRoute() {
    clearRoute();
}

function showDirectionsStrip(data) {
    let strip = document.getElementById('directions-strip')
    if (!strip) {
                const panel = document.getElementById('details-panel')
                if (!panel) {
                        console.log('showDirectionsStrip: details-panel not found')
                        return
                }

                strip = document.createElement('div')
                strip.id = 'directions-strip'
                strip.style.display = 'none'
                panel.appendChild(strip)
    }

        // Accept different provider payload shapes and normalize to a renderable step list.
        const steps = normalizeDirectionSteps(data)

                let stepsHtml = steps.map((step, i) => `
      <div style="display:flex;gap:10px;align-items:flex-start;padding:10px 0;border-bottom:${i < steps.length - 1 ? '1px solid #f0ece6' : 'none'}">
        <div style="width:20px;height:20px;border-radius:50%;background:#4A6741;color:white;font-size:10px;font-weight:600;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:2px">${i + 1}</div>
        <div style="flex:1;min-width:0">
                    <div style="font-size:13px;color:#3A2E22;line-height:1.5;margin-bottom:2px;font-family:'Poppins',sans-serif">${escapeHtml(step.instruction || 'Continue')}</div>
                    <div style="font-size:11px;color:#888780;font-family:'Poppins',sans-serif">${escapeHtml(step.distance || '')}</div>
        </div>
      </div>`).join('')

        if (!steps.length) {
            stepsHtml = `
                <div style="padding:10px 0;font-size:13px;color:#888780;font-family:'Poppins',sans-serif">
                    Turn-by-turn directions are currently unavailable for this route.
                </div>`
        }
    
    stepsHtml += `
      <div style="display:flex;gap:10px;align-items:center;padding-top:10px">
        <div style="width:20px;height:20px;border-radius:50%;background:#F5F0E8;border:2px solid #4A6741;display:flex;align-items:center;justify-content:center;flex-shrink:0">
          <div style="width:8px;height:8px;border-radius:50%;background:#4A6741"></div>
        </div>
        <div style="font-size:13px;font-weight:500;color:#4A6741;font-family:'Poppins',sans-serif">You have arrived</div>
      </div>`
    
    strip.innerHTML = `
      <hr style="border:none;border-top:1px solid #e5e0d8;margin:14px 0">
      <div style="padding:0 16px;margin-bottom:10px">
        <div style="font-size:10px;text-transform:uppercase;letter-spacing:0.08em;color:#888780;margin-bottom:6px;font-family:'Poppins',sans-serif">Directions</div>
        <div style="display:flex;align-items:center;justify-content:space-between">
          <div style="display:flex;align-items:center;gap:10px">
            <div style="display:flex;align-items:center;gap:5px">
              <span style="font-size:14px;font-weight:600;color:#3A2E22;font-family:'Poppins',sans-serif">${data.duration}</span>
            </div>
            <span style="font-size:12px;color:#888780">·</span>
            <span style="font-size:13px;color:#888780;font-family:'Poppins',sans-serif">${data.distance}</span>
          </div>
        </div>
      </div>
      <div style="padding:0 16px 16px">${stepsHtml}</div>
    `
    
    strip.style.display = 'block'

        const clearRouteSidebarBtn = document.getElementById('clear-route-sidebar-btn')
        if (clearRouteSidebarBtn) {
                clearRouteSidebarBtn.style.display = 'inline-flex'
        }

        const clearRouteBtn = document.getElementById('clear-route-btn')
        if (clearRouteBtn) {
            clearRouteBtn.style.display = 'inline-flex'
        }

    // Smooth scroll to bottom of panel
    const panel = document.getElementById('details-panel')
    if (panel) {
      setTimeout(() => {
        panel.scrollTo({ top: panel.scrollHeight, behavior: 'smooth' })
      }, 100)
    }
}

function normalizeDirectionSteps(data) {
    if (!data || typeof data !== 'object') return [];

    // Prefer already-normalized payload from backend controller.
    let rawSteps = Array.isArray(data.steps) ? data.steps : null;

    // Fallback for raw provider payloads.
    if (!rawSteps && Array.isArray(data.routes) && data.routes[0]?.legs?.[0]?.steps) {
        rawSteps = data.routes[0].legs[0].steps;
    }

    if (!rawSteps || !Array.isArray(rawSteps)) return [];

    return rawSteps
        .map((step) => {
            const instruction =
                step?.instruction ||
                step?.html_instructions ||
                step?.maneuver?.instruction ||
                step?.name ||
                '';

            let distance = '';
            if (typeof step?.distance === 'string') {
                distance = step.distance;
            } else if (step?.distance?.text) {
                distance = step.distance.text;
            } else if (typeof step?.distance === 'number') {
                const km = step.distance >= 1000 ? `${(step.distance / 1000).toFixed(1)} km` : `${Math.round(step.distance)} m`;
                distance = km;
            }

            // Strip any remaining HTML tags from provider responses.
            const plainInstruction = String(instruction).replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();

            return {
                instruction: plainInstruction,
                distance
            };
        })
        .filter((step) => step.instruction || step.distance);
}

function handleNavigate() {
    console.log('1. handleNavigate fired')
    console.log('2. currentPanelFeature:', currentPanelFeature)
    console.log('3. window.userLocation:', window.userLocation)

    const selectedFeature = currentPanelFeature || window.currentPanelFeature;

    if (!selectedFeature || !selectedFeature.properties) {
        showToast('No destination selected', true);
        return;
    }

    navigateToDestination(selectedFeature);
}

function navigateToDestination(feature) {
    if (!window.userLocation) {
        showToast('Getting user location first...', false);

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    window.userLocation = {
                        lat: pos.coords.latitude,
                        lng: pos.coords.longitude
                    };
                    handleLocationButtonClick(document.querySelector('.leaflet-control-custom'));
                    navigateToDestination(feature);
                },
                (err) => {
                    showToast('Location permission is required for routing', true);
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        } else {
            showToast('Geolocation is not supported by your browser', true);
        }

        return;
    }

    const navigateBtn = document.getElementById('navigate-btn');
    if (navigateBtn) {
        navigateBtn.disabled = true;
        navigateBtn.textContent = 'Getting directions...';
    }

    const csrftoken = document.querySelector('meta[name="csrf-token"]')?.content || window.CSRF_TOKEN;

    const destinationId = feature?.properties?.id;
    const destinationLat = Number(feature?.geometry?.coordinates?.[1]);
    const destinationLng = Number(feature?.geometry?.coordinates?.[0]);

    const isDestinationIdInteger = Number.isInteger(Number(destinationId));
    const navigationPayload = {
        origin: {
            lat: window.userLocation.lat,
            lng: window.userLocation.lng
        }
    };

    if (isDestinationIdInteger) {
        navigationPayload.destination_id = Number(destinationId);
    } else if (Number.isFinite(destinationLat) && Number.isFinite(destinationLng)) {
        navigationPayload.destination = {
            lat: destinationLat,
            lng: destinationLng,
        };
    } else {
        showToast('Destination coordinates not found', true);
        if (navigateBtn) {
            navigateBtn.disabled = false;
            navigateBtn.textContent = 'Navigate';
        }
        return;
    }

    fetch('/api/navigation/directions', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrftoken,
            'Accept': 'application/json'
        },
        body: JSON.stringify(navigationPayload)
    })
        .then(async (res) => {
            console.log('4. response.ok:', res.ok)
            if (!res.ok) {
                const body = await res.json().catch(() => ({}));
                throw new Error(body.message || `Navigation error (${res.status})`);
            }
            return res.json();
        })
        .then((data) => {
            console.log('5. data:', data)
            if (!data.polyline) {
                throw new Error('No route polyline in response');
            }

            const decoded = decodePolyline(data.polyline);
            clearRoute();

            window.routeLayer = L.polyline(decoded, {
                color: '#378ADD',
                weight: 4,
                opacity: 0.85
            }).addTo(map);

            map.fitBounds(window.routeLayer.getBounds(), { padding: [60, 60] });

            // Call the directions rendering function
            console.log('6. Calling showDirectionsStrip')
            showDirectionsStrip(data);

            showToast('Directions loaded');
        })
        .catch((err) => showToast(err.message || 'Error fetching directions', true))
        .finally(() => {
            if (navigateBtn) {
                navigateBtn.disabled = false;
                navigateBtn.textContent = 'Navigate';
            }
        });
}

async function loadEstablishments() {
    try {
        console.log('Loading establishments from server data');

        Object.values(window.layers).forEach((layerGroup) => layerGroup.clearLayers());
        window.markerIndex = {};

        // Use server-side data instead of fetching
        const establishments = window.ESTABLISHMENTS;

        if (!establishments || !Array.isArray(establishments)) {
            console.error('Establishments data is invalid', establishments);
            showToast('Invalid establishments data', true);
            return;
        }

        console.log('Establishments has', establishments.length, 'items');

        // Convert establishments to GeoJSON-like features and skip entries with invalid coordinates.
        const features = establishments
            .map((est) => {
                const longitude = parseFloat(est.longitude);
                const latitude = parseFloat(est.latitude);

                if (!Number.isFinite(longitude) || !Number.isFinite(latitude)) {
                    return null;
                }

                return {
                    type: 'Feature',
                    geometry: {
                        type: 'Point',
                        coordinates: [longitude, latitude]
                    },
                    properties: {
                        id: est.id,
                        name: est.name,
                        type: est.type,
                        description: est.description,
                        address: est.address,
                        barangay: est.barangay,
                        contact_number: est.contact_number,
                        email: est.email,
                        website: est.website,
                        visit_hours: est.visit_hours,
                        activities: est.activities,
                        image: est.image,
                        coffee_varieties: est.coffee_varieties,
                        rating_average: est.rating_average,
                        review_count: est.review_count,
                        taste_avg: est.taste_avg,
                        environment_avg: est.environment_avg,
                        cleanliness_avg: est.cleanliness_avg,
                        service_avg: est.service_avg,
                        active_promos: est.active_promos,
                    }
                };
            })
            .filter((feature) => feature !== null);

        const data = {
            type: 'FeatureCollection',
            features: features
        };

        L.geoJSON(data, {
            pointToLayer: function (feature, latlng) {
                const type = (feature.properties.type || '').toLowerCase();
                const color = getEstablishmentTypeTheme(type).color;

                const icon = L.divIcon({
                    html: `<svg width="28" height="28" viewBox="0 0 28 28" xmlns="http://www.w3.org/2000/svg"><circle cx="14" cy="14" r="13" fill="${color}" stroke="white" stroke-width="2"/><circle cx="14" cy="14" r="6" fill="white"/></svg>`,
                    className: 'custom-marker',
                    iconSize: [28, 28],
                    iconAnchor: [14, 14]
                });

                const marker = L.marker(latlng, { icon });

                if (type === 'farm') window.layers.farms.addLayer(marker);
                else if (type === 'cafe') window.layers.cafes.addLayer(marker);
                else if (type === 'roaster') window.layers.roasters.addLayer(marker);
                else if (type === 'reseller') window.layers.resellers.addLayer(marker);

                window.layers.all.addLayer(marker);

                const rawRating = feature.properties?.rating_average;
                const ratingVal = Number(rawRating);
                const hasRating = rawRating !== undefined && rawRating !== null && !Number.isNaN(ratingVal);
                const ratingLabel = hasRating ? ratingVal.toFixed(1) : 'N/A';
                const name = feature.properties?.name || 'Unnamed';
                const activePromo = feature.properties?.active_promos?.[0];
                const promoTooltipHtml = activePromo
                    ? `<div style="
                        display: inline-flex;
                        align-items: center;
                        gap: 4px;
                        background: #4A6741;
                        color: white;
                        font-size: 10px;
                        font-weight: 600;
                        padding: 2px 8px;
                        border-radius: 20px;
                        margin-top: 6px;
                      ">
                        <span>🏷</span>
                        <span>${activePromo.qr_code_token}</span>
                      </div>`
                    : '';
                const tooltipText = `${name} <span class="tooltip-star">★</span> ${ratingLabel}${promoTooltipHtml}`;

                // Debug output: verify rating is available for markers
                if (window.DEBUG_MAP_TOOLTIP) {
                    console.log('Marker tooltip:', { name, ratingVal, tooltipText, id: feature.properties?.id });
                }

                marker.bindTooltip(tooltipText, {
                    direction: 'top',
                    offset: [0, -8],
                    opacity: 0.85,
                    permanent: false,
                    sticky: true,
                    className: 'custom-marker-tooltip'
                });

                marker.on('mouseover', function () {
                    this.openTooltip();
                });
                marker.on('mouseout', function () {
                    this.closeTooltip();
                });

                const varieties = feature.properties.coffee_varieties || [];
                varieties.forEach((variety) => {
                    const key = String(variety).toLowerCase();
                    if (window.layers[key]) {
                        window.layers[key].addLayer(marker);
                    }
                });

                window.markerIndex[feature.properties.id] = { marker, feature };

                marker.on('click', function (e) {
                    L.DomEvent.stopPropagation(e);
                    window.currentPanelFeature = feature;

                    if (typeof openDetailsPanel === 'function') {
                        openDetailsPanel(feature);
                    } else {
                        console.log('Marker clicked:', feature.properties?.name || feature.properties?.id);
                    }
                });

                return marker;
            }
        });

        renderVerifiedResellerMarkers();

        map.addLayer(window.layers.all);
        updateCounts();
        updateSummary(Object.keys(window.markerIndex).length, Object.keys(window.markerIndex).length);
    } catch (error) {
        console.error('Error loading establishments:', error);
        showToast('Error loading establishments', true);
    }
}

function getVarietyColor(varietyName) {
    const colorMap = {
        liberica: { hex: '#4A6741', name: 'Liberica' },
        excelsa: { hex: '#B8860B', name: 'Excelsa' },
        robusta: { hex: '#6B3A2A', name: 'Robusta' },
        arabica: { hex: '#8B1A1A', name: 'Arabica' }
    };

    return colorMap[String(varietyName).toLowerCase()] || {
        hex: '#999999',
        name: varietyName
    };
}

function generateStarRating(rating, count) {
    if (count === 0) {
        return '<span class="text-[#9E8C78] italic text-13px">No ratings yet</span>';
    }

    const safeRating = Number(rating || 0);
    return `<span class="font-bold">${safeRating.toFixed(1)}</span> <span class="text-[#9E8C78]">(${count} ratings)</span>`;
}

function openMapSidebar() {
    const modal = document.getElementById('add-establishment-modal');
    if (!modal) return;

    modal.classList.remove('hidden', 'opacity-0');
    modal.classList.add('opacity-100');
    modal.style.zIndex = '99999';

    const inner = modal.querySelector('.transform') || modal.firstElementChild;
    if (inner) {
        inner.style.position = 'relative';
        inner.style.zIndex = '100000';
        inner.classList.remove('scale-95');
        inner.classList.add('scale-100');
    }

    document.body.style.overflow = 'hidden';
}

function closeMapSidebar() {
    const modal = document.getElementById('add-establishment-modal');
    if (!modal) return;

    // Clean up temp marker when modal closes
    if (tempMarker) {
        map.removeLayer(tempMarker);
        tempMarker = null;
    }

    // Reset placement mode state
    placementMode = false;
    addBtn.style.display = '';
    addBtn.style.zIndex = '900';
    cancelBtn.style.display = 'none';
    const mapResellerBtn = document.getElementById('map-reseller-location-btn');
    if (mapResellerBtn) mapResellerBtn.style.display = '';
    syncMapActionButtonsLayout();

    modal.classList.remove('opacity-100');
    modal.classList.add('opacity-0');

    const inner = modal.querySelector('.transform') || modal.firstElementChild;
    if (inner) {
        inner.classList.remove('scale-100');
        inner.classList.add('scale-95');
    }

    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);

    document.body.style.overflow = '';
}

function closeDetailsPanel() {
    const panel = document.getElementById('details-panel');
    if (!panel) return;

    const isMobile = window.innerWidth < 768;

    panel.style.transform = isMobile
        ? 'translateY(100%)'
        : 'translateX(100%)';

    window.currentPanelFeature = null;

    // Remove class to restore Leaflet controls position
    const mapEl = document.getElementById('map');
    if (mapEl) {
        mapEl.classList.remove('has-details-panel');
    }
}

function showPlacementBanner(message = 'Click the map to place establishment') {
    const helper = ensurePlacementHelper();
    if (!helper) return;

    helper.textContent = message;
    helper.classList.remove('hidden');
}

function hidePlacementBanner() {
    const helper = document.getElementById('placement-banner');
    if (helper) {
        helper.classList.add('hidden');
    }
}

function updateLatLngFields(latlng) {
    const latInput = document.getElementById('latitude-input');
    const lngInput = document.getElementById('longitude-input');

    if (latInput) latInput.value = latlng.lat.toFixed(8);
    if (lngInput) lngInput.value = latlng.lng.toFixed(8);
}

function showToast(message, isError = false) {
    let toast = document.getElementById('global-toast');

    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'global-toast';
        toast.style.position = 'fixed';
        toast.style.bottom = '20px';
        toast.style.right = '20px';
        toast.style.padding = '12px 16px';
        toast.style.borderRadius = '8px';
        toast.style.color = '#fff';
        toast.style.zIndex = '9999';
        toast.style.boxShadow = '0 6px 18px rgba(0,0,0,0.25)';
        document.body.appendChild(toast);
    }

    toast.textContent = message;
    toast.style.background = isError ? '#c53030' : '#38a169';
    toast.style.transform = 'translateY(0)';
    toast.style.opacity = '1';

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(10px)';
    }, 3000);
}

function removeTempMarker() {
    if (tempMarker) {
        map.removeLayer(tempMarker);
        tempMarker = null;
    }
}

function enterEditMode(id) {
    if (editMode) return;

    // Ensure shared save/cancel banner is in pure edit mode state.
    resellerPlacementMode = false;
    selectedResellerForMapping = null;
    pendingResellerLatLng = null;
    if (resellerTempMarker) {
        map.removeLayer(resellerTempMarker);
        resellerTempMarker = null;
    }

    const markerData = window.markerIndex[id];
    if (!markerData) return;

    editingMarker = markerData.marker;
    originalLatLng = editingMarker.getLatLng();

    closeDetailsPanel();

    if (editingMarker.dragging) {
        editingMarker.dragging.enable();
    }

    editingMarker.setIcon(editingIcon);

    const bannerText = document.getElementById('edit-banner-text');
    const banner = document.getElementById('edit-banner');

    if (bannerText) {
        bannerText.textContent = `Drag ${markerData.feature.properties.name} to reposition — click Save when done`;
    }
    if (banner) banner.classList.remove('hidden');

    const saveBtn = document.getElementById('edit-save');
    if (saveBtn) saveBtn.disabled = true;

    editMode = true;

    const addBtn = document.getElementById('add-establishment');
    if (addBtn) {
        addBtn.disabled = true;
        addBtn.classList.add('opacity-50', 'cursor-not-allowed');
    }

    map.off('click', closeDetailsPanel);

    Object.values(window.markerIndex).forEach(({ marker }) => {
        marker.off('click');
    });

    editingMarker.on('dragend', handleEditDragEnd);
}

function handleEditDragEnd(e) {
    const latlng = e.target.getLatLng();
    const text = document.getElementById('edit-banner-text');
    const saveBtn = document.getElementById('edit-save');

    if (text) {
        text.textContent = `New position: ${latlng.lat.toFixed(6)}, ${latlng.lng.toFixed(6)} — click Save when done`;
    }
    if (saveBtn) saveBtn.disabled = false;
}

function saveEdit() {
    if (!editingMarker) return;

    const markerRef = editingMarker;
    const latlng = markerRef.getLatLng();
    const id = Object.keys(window.markerIndex).find((key) => window.markerIndex[key].marker === markerRef);

    if (!id) return;

    const markerData = window.markerIndex[id];
    const isResellerUserMarker = Boolean(markerData?.feature?.properties?.is_reseller_user);

    if (isResellerUserMarker) {
        const resellerUserId = markerData?.feature?.properties?.reseller_user_id;
        if (!resellerUserId) {
            showToast('Reseller reference is missing', true);
            return;
        }

        fetch(`/admin/map/resellers/${resellerUserId}/location`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.CSRF_TOKEN,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                latitude: latlng.lat.toFixed(8),
                longitude: latlng.lng.toFixed(8)
            })
        })
            .then(async (response) => {
                const payload = await response.json().catch(() => ({}));
                if (!response.ok) {
                    const validationErrors = payload?.errors
                        ? Object.values(payload.errors).flat().join(' ')
                        : '';
                    throw new Error(validationErrors || payload?.message || 'Failed to update reseller location.');
                }

                markerData.feature.geometry.coordinates = [latlng.lng, latlng.lat];
                markerData.feature.properties.latitude = latlng.lat;
                markerData.feature.properties.longitude = latlng.lng;

                if (markerRef?.dragging) {
                    markerRef.dragging.disable();
                }

                const color = getEstablishmentTypeTheme('reseller').color;
                const resellerIcon = L.divIcon({
                    html: `<svg width="28" height="28" viewBox="0 0 28 28" xmlns="http://www.w3.org/2000/svg"><circle cx="14" cy="14" r="13" fill="${color}" stroke="white" stroke-width="2"/><circle cx="14" cy="14" r="6" fill="white"/></svg>`,
                    className: 'custom-marker',
                    iconSize: [28, 28],
                    iconAnchor: [14, 14]
                });
                markerRef?.setIcon(resellerIcon);

                if (Array.isArray(window.VERIFIED_RESELLERS)) {
                    window.VERIFIED_RESELLERS = window.VERIFIED_RESELLERS.map((reseller) => {
                        return Number(reseller.id) === Number(resellerUserId)
                            ? { ...reseller, latitude: latlng.lat, longitude: latlng.lng }
                            : reseller;
                    });
                }

                const banner = document.getElementById('edit-banner');
                if (banner) banner.classList.add('hidden');

                showToast('Reseller location updated successfully');
                exitEditMode();
            })
            .catch((error) => {
                console.error('Error updating reseller location:', error);
                showToast(error.message || 'Error updating reseller location', true);
            });

        return;
    }

    fetch(`/admin/map/${id}`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.CSRF_TOKEN
        },
        body: JSON.stringify({
            latitude: latlng.lat.toFixed(8),
            longitude: latlng.lng.toFixed(8)
        })
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.message) {
                window.markerIndex[id].feature.properties.latitude = latlng.lat;
                window.markerIndex[id].feature.properties.longitude = latlng.lng;

                if (markerRef?.dragging) {
                    markerRef.dragging.disable();
                }

                                const type = window.markerIndex[id].feature.properties.type;
                                const color = getEstablishmentTypeTheme(type).color;

                const originalIcon = L.divIcon({
                    html: `<svg width="28" height="28" viewBox="0 0 28 28" xmlns="http://www.w3.org/2000/svg"><circle cx="14" cy="14" r="13" fill="${color}" stroke="white" stroke-width="2"/><circle cx="14" cy="14" r="6" fill="white"/></svg>`,
                    className: 'custom-marker',
                    iconSize: [28, 28],
                    iconAnchor: [14, 14]
                });

                markerRef?.setIcon(originalIcon);

                const banner = document.getElementById('edit-banner');
                if (banner) banner.classList.add('hidden');

                showToast('Location updated successfully');
                exitEditMode();
            } else {
                showToast('Error updating location', true);
            }
        })
        .catch((error) => {
            console.error('Error:', error);
            showToast('Error updating location', true);
        });
}

function cancelEdit() {
    if (resellerPlacementMode) {
        cancelResellerPlacementMode();
        return;
    }

    if (!editingMarker) return;

    editingMarker.setLatLng(originalLatLng);

    if (editingMarker.dragging) {
        editingMarker.dragging.disable();
    }

    const id = Object.keys(window.markerIndex).find((key) => window.markerIndex[key].marker === editingMarker);
    if (id) {
                const type = window.markerIndex[id].feature.properties.type;
                const color = getEstablishmentTypeTheme(type).color;

        const originalIcon = L.divIcon({
            html: `<svg width="28" height="28" viewBox="0 0 28 28" xmlns="http://www.w3.org/2000/svg"><circle cx="14" cy="14" r="13" fill="${color}" stroke="white" stroke-width="2"/><circle cx="14" cy="14" r="6" fill="white"/></svg>`,
            className: 'custom-marker',
            iconSize: [28, 28],
            iconAnchor: [14, 14]
        });

        editingMarker.setIcon(originalIcon);
    }

    const banner = document.getElementById('edit-banner');
    if (banner) banner.classList.add('hidden');

    exitEditMode();
}

function exitEditMode() {
    editMode = false;
    editingMarker = null;
    originalLatLng = null;

    const addBtn = document.getElementById('add-establishment');
    if (addBtn) {
        addBtn.disabled = false;
        addBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    }

    map.off('click');
    map.on('click', () => {
        if (!editMode && !placementMode && !resellerPlacementMode) {
            closeDetailsPanel();
        }
    });

    // Rebind marker click events
    Object.values(window.markerIndex).forEach(({ marker, feature }) => {
        marker.off('click');
        marker.on('click', function (e) {
            L.DomEvent.stopPropagation(e);
            window.currentPanelFeature = feature;

            if (typeof openDetailsPanel === 'function') {
                openDetailsPanel(feature);
            } else {
                console.log('Marker clicked:', feature.properties?.name || feature.properties?.id);
            }
        });
    });
}

function buildFilterPanel() {
    const panel = document.getElementById('filter-panel');
    if (!panel) return;

    const varieties = [
        { name: 'Liberica', color: '#4A6741' },
        { name: 'Excelsa', color: '#B8860B' },
        { name: 'Robusta', color: '#6B3A2A' },
        { name: 'Arabica', color: '#8B1A1A' }
    ];

    panel.innerHTML = `
        <div id="filter-panel-header" class="filter-header flex items-center justify-between">
            <h3 class="text-sm font-bold text-[#3A2E22]">Filters</h3>
        </div>
        <div id="filter-content" class="filter-content">
            <div class="filter-section-types">
                <div class="type-filters">
                    ${buildTypeFilterButton('farm')}
                    ${buildTypeFilterButton('cafe')}
                    ${buildTypeFilterButton('roaster')}
                    ${buildTypeFilterButton('reseller')}
                </div>
            </div>
            <div class="filter-section-varieties">
                <div class="variety-filters">
                    ${varieties
                        .map(
                            (v) => `
                        <button id="variety-${v.name.toLowerCase()}" class="variety-btn ${window.filterState.varieties.includes(v.name.toLowerCase()) ? 'active' : ''}"
                            style="border-left: 3px solid ${v.color}; font-size:11px; display:flex; align-items:center; gap:5px; padding:5px 9px; margin:0;">
                            <span style="display:inline-block;width:9px;height:9px;border-radius:50%;background:${v.color};margin-right:4px;vertical-align:middle;flex-shrink:0;"></span>
                            <span>${v.name}</span>
                            <span class="badge" style="margin-left:auto;">0</span>
                        </button>
                    `
                        )
                        .join('')}
                </div>
            </div>
            <div class="filter-section-summary">
                <div id="summary" class="summary text-[11px] text-[#9E8C78] font-medium">Showing 0 of 0</div>
                <button id="show-all" class="px-2.5 py-1 bg-[#4A6741] text-white text-[11px] font-semibold rounded-md hover:bg-[#3A2E22] transition-colors">Reset</button>
            </div>
        </div>
    `;

    document.getElementById('type-farm')?.addEventListener('click', () => toggleType('farm'));
    document.getElementById('type-cafe')?.addEventListener('click', () => toggleType('cafe'));
    document.getElementById('type-roaster')?.addEventListener('click', () => toggleType('roaster'));
    document.getElementById('type-reseller')?.addEventListener('click', () => toggleType('reseller'));

    document.querySelectorAll('.variety-btn').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            const variety = e.currentTarget.id.split('-')[1];
            toggleVariety(variety);
        });
    });

    document.getElementById('show-all')?.addEventListener('click', resetFilters);
}

function toggleType(type) {
    if (window.filterState.types.includes(type)) {
        if (window.filterState.types.length > 1) {
            window.filterState.types = window.filterState.types.filter((t) => t !== type);
        }
    } else {
        window.filterState.types.push(type);
    }

    updateTypeButtons();
    filterMarkers(window.filterState.varieties, window.filterState.types);
}

function updateTypeButtons() {
    document.getElementById('type-farm')?.classList.toggle('active', window.filterState.types.includes('farm'));
    document.getElementById('type-cafe')?.classList.toggle('active', window.filterState.types.includes('cafe'));
    document.getElementById('type-roaster')?.classList.toggle('active', window.filterState.types.includes('roaster'));
    document.getElementById('type-reseller')?.classList.toggle('active', window.filterState.types.includes('reseller'));
}

function toggleVariety(variety) {
    if (window.filterState.varieties.includes(variety)) {
        window.filterState.varieties = window.filterState.varieties.filter((v) => v !== variety);
    } else {
        window.filterState.varieties.push(variety);
    }

    updateVarietyButtons();
    filterMarkers(window.filterState.varieties, window.filterState.types);
}

function updateVarietyButtons() {
    document.querySelectorAll('.variety-btn').forEach((btn) => {
        const variety = btn.id.split('-')[1];
        btn.classList.toggle('active', window.filterState.varieties.includes(variety));
    });
}

function resetFilters() {
    window.filterState = {
        varieties: [],
        types: ['farm', 'cafe', 'roaster', 'reseller']
    };

    updateTypeButtons();
    updateVarietyButtons();
    filterMarkers([], ['farm', 'cafe', 'roaster', 'reseller']);
}

function filterMarkers(activeVarieties, activeTypes) {
    closeDetailsPanel();

    let visibleCount = 0;
    const totalCount = Object.keys(window.markerIndex).length;

    Object.values(window.markerIndex).forEach(({ marker, feature }) => {
        const type = String(feature.properties.type || '').toLowerCase();
        const varieties = (feature.properties.coffee_varieties || []).map((v) => String(v).toLowerCase());

        const typeMatch = activeTypes.includes(type);
        const varietyMatch =
            activeVarieties.length === 0 || activeVarieties.some((v) => varieties.includes(v));

        if (typeMatch && varietyMatch) {
            if (!map.hasLayer(marker)) map.addLayer(marker);
            visibleCount++;
        } else {
            if (map.hasLayer(marker)) map.removeLayer(marker);
        }
    });

    updateCounts();
    updateSummary(visibleCount, totalCount);
}

function updateCounts() {
    const counts = {
        liberica: 0,
        excelsa: 0,
        robusta: 0,
        arabica: 0
    };

    Object.values(window.markerIndex).forEach(({ feature }) => {
        const varieties = feature.properties.coffee_varieties || [];
        varieties.forEach((v) => {
            const key = String(v).toLowerCase();
            if (counts[key] !== undefined) counts[key]++;
        });
    });

    Object.keys(counts).forEach((v) => {
        const badge = document.querySelector(`#variety-${v} .badge`);
        if (badge) badge.textContent = counts[v];
    });
}

function updateSummary(visible, total) {
    const summary = document.getElementById('summary');
    if (summary) {
        summary.textContent = `Showing ${visible} of ${total}`;
    }
}

function normalizeBarangayName(value) {
    return String(value || '')
        .toLowerCase()
        .replace(/[.,]/g, ' ')
        .replace(/\b(barangay|brgy|brgy\.)\b/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();
}

function toBarangayDisplayName(value) {
    const cleaned = String(value || '').replace(/\s+/g, ' ').trim();
    if (!cleaned) return '';
    return cleaned
        .toLowerCase()
        .replace(/\b\w/g, (char) => char.toUpperCase());
}

function getLocalBarangaySuggestions(rawQuery = '') {
    const query = normalizeBarangayName(rawQuery);
    const byKey = new Map();

    Object.values(window.markerIndex).forEach(({ feature }) => {
        const rawBarangay = String(feature?.properties?.barangay || '').trim();
        const normalized = normalizeBarangayName(rawBarangay);
        if (!normalized) return;

        const current = byKey.get(normalized);
        if (!current || rawBarangay.length < current.length) {
            byKey.set(normalized, rawBarangay);
        }
    });

    return Array.from(byKey.entries())
        .filter(([normalized]) => !query || normalized.includes(query))
        .map(([, raw]) => toBarangayDisplayName(raw))
        .sort((a, b) => a.localeCompare(b))
        .slice(0, 10);
}

function extractBarangayFromMapboxFeature(feature) {
    const placeName = String(feature?.place_name || '').trim();
    if (!placeName) return '';

    const firstSegment = placeName.split(',')[0] || '';
    const cleaned = firstSegment
        .replace(/\b(city|lipa|batangas|philippines)\b/gi, ' ')
        .replace(/\s+/g, ' ')
        .trim();

    if (!cleaned) return '';
    return toBarangayDisplayName(cleaned);
}

async function fetchLipaBarangaySuggestions(rawQuery, signal) {
    const trimmed = String(rawQuery || '').trim();
    if (!window.MAPBOX_TOKEN || trimmed.length < 2) return [];

    const query = encodeURIComponent(`${trimmed}, Lipa City, Batangas, Philippines`);
    const bbox = '121.073,13.860,121.235,14.050';
    const proximity = '121.1631,13.9411';
    const url = `https://api.mapbox.com/geocoding/v5/mapbox.places/${query}.json?access_token=${window.MAPBOX_TOKEN}&bbox=${bbox}&proximity=${proximity}&autocomplete=true&limit=8&language=en`;

    const response = await fetch(url, { method: 'GET', signal });
    if (!response.ok) return [];

    const payload = await response.json();
    const features = Array.isArray(payload?.features) ? payload.features : [];

    return features
        .filter((item) => {
            const placeName = String(item?.place_name || '').toLowerCase();
            const contextText = (item?.context || [])
                .map((ctx) => String(ctx?.text || '').toLowerCase())
                .join(' ');
            return placeName.includes('lipa') || contextText.includes('lipa');
        })
        .map(extractBarangayFromMapboxFeature)
        .filter(Boolean);
}

async function fetchLipaBarangaySuggestionsNominatim(rawQuery, signal) {
    const trimmed = String(rawQuery || '').trim();
    if (trimmed.length < 2) return [];

    const query = encodeURIComponent(`Barangay ${trimmed}, Lipa City, Batangas, Philippines`);
    const viewbox = '121.073,14.050,121.235,13.860';
    const url = `https://nominatim.openstreetmap.org/search?format=jsonv2&addressdetails=1&limit=10&bounded=1&viewbox=${viewbox}&q=${query}`;

    const response = await fetch(url, {
        method: 'GET',
        signal,
        headers: {
            Accept: 'application/json'
        }
    });
    if (!response.ok) return [];

    const items = await response.json();
    if (!Array.isArray(items)) return [];

    return items
        .filter((item) => {
            const display = String(item?.display_name || '').toLowerCase();
            const city = String(item?.address?.city || item?.address?.town || '').toLowerCase();
            return display.includes('lipa') || city.includes('lipa');
        })
        .map((item) => {
            const address = item?.address || {};
            const raw = address.suburb || address.village || address.neighbourhood || address.quarter || '';
            return toBarangayDisplayName(raw);
        })
        .filter(Boolean);
}

function updateBarangayDatalistOptions(values) {
    const datalist = document.getElementById('map-barangay-suggestions');
    if (!datalist) return;

    datalist.innerHTML = '';
    values.forEach((value) => {
        const option = document.createElement('option');
        option.value = value;
        datalist.appendChild(option);
    });
}

function setupBarangayAutocomplete(searchInput) {
    if (!searchInput) return;

    // Initial local suggestions from currently loaded markers.
    updateBarangayDatalistOptions(getLocalBarangaySuggestions(''));

    searchInput.addEventListener('input', () => {
        const raw = searchInput.value || '';
        const localSuggestions = getLocalBarangaySuggestions(raw);
        updateBarangayDatalistOptions(localSuggestions);

        if (barangaySuggestTimer) clearTimeout(barangaySuggestTimer);
        if (barangaySuggestAbortController) barangaySuggestAbortController.abort();

        if (String(raw).trim().length < 2) return;

        barangaySuggestTimer = setTimeout(async () => {
            barangaySuggestAbortController = new AbortController();
            try {
                let remoteSuggestions = await fetchLipaBarangaySuggestions(
                    raw,
                    barangaySuggestAbortController.signal
                );

                // If Mapbox is unavailable or returns nothing, try OSM geocoding.
                if (!remoteSuggestions.length) {
                    remoteSuggestions = await fetchLipaBarangaySuggestionsNominatim(
                        raw,
                        barangaySuggestAbortController.signal
                    );
                }

                const merged = Array.from(new Set([...localSuggestions, ...remoteSuggestions]))
                    .sort((a, b) => a.localeCompare(b))
                    .slice(0, 12);

                updateBarangayDatalistOptions(merged);
            } catch (error) {
                if (error?.name !== 'AbortError') {
                    console.error('Failed to load barangay suggestions:', error);
                }
            }
        }, 220);
    });
}

async function geocodeBarangayInLipa(rawQuery) {
    if (!window.MAPBOX_TOKEN) return null;

    const query = encodeURIComponent(`${rawQuery}, Lipa City, Batangas, Philippines`);
    // Approximate bbox for Lipa City: [minLon,minLat,maxLon,maxLat]
    const bbox = '121.073,13.860,121.235,14.050';
    const proximity = '121.1631,13.9411';

    const url = `https://api.mapbox.com/geocoding/v5/mapbox.places/${query}.json?access_token=${window.MAPBOX_TOKEN}&bbox=${bbox}&proximity=${proximity}&limit=5&language=en`;
    const response = await fetch(url, { method: 'GET' });
    if (!response.ok) return null;

    const payload = await response.json();
    const features = Array.isArray(payload?.features) ? payload.features : [];
    if (features.length === 0) return null;

    // Keep only candidates clearly inside Lipa City by place_name/context match.
    const lipaCandidates = features.filter((item) => {
        const placeName = String(item?.place_name || '').toLowerCase();
        const contextText = (item?.context || [])
            .map((ctx) => String(ctx?.text || '').toLowerCase())
            .join(' ');
        return placeName.includes('lipa') || contextText.includes('lipa');
    });

    const best = lipaCandidates[0] || features[0];
    const center = best?.center;
    if (!Array.isArray(center) || center.length < 2) return null;

    return {
        lat: Number(center[1]),
        lng: Number(center[0]),
        placeName: best.place_name || ''
    };
}

async function geocodeBarangayInLipaNominatim(rawQuery) {
    const cleaned = String(rawQuery || '').trim();
    if (!cleaned) return null;

    const query = encodeURIComponent(`Barangay ${cleaned}, Lipa City, Batangas, Philippines`);
    const viewbox = '121.073,14.050,121.235,13.860';
    const url = `https://nominatim.openstreetmap.org/search?format=jsonv2&addressdetails=1&limit=5&bounded=1&viewbox=${viewbox}&q=${query}`;

    const response = await fetch(url, {
        method: 'GET',
        headers: {
            Accept: 'application/json'
        }
    });
    if (!response.ok) return null;

    const items = await response.json();
    if (!Array.isArray(items) || items.length === 0) return null;

    const filtered = items.filter((item) => {
        const display = String(item?.display_name || '').toLowerCase();
        const city = String(item?.address?.city || item?.address?.town || '').toLowerCase();
        return display.includes('lipa') || city.includes('lipa');
    });

    const best = filtered[0] || items[0];
    const lat = Number(best?.lat);
    const lng = Number(best?.lon);
    if (!Number.isFinite(lat) || !Number.isFinite(lng)) return null;

    const bounds = Array.isArray(best?.boundingbox) && best.boundingbox.length === 4
        ? {
            south: Number(best.boundingbox[0]),
            north: Number(best.boundingbox[1]),
            west: Number(best.boundingbox[2]),
            east: Number(best.boundingbox[3])
        }
        : null;

    return {
        lat,
        lng,
        bounds,
        placeName: best?.display_name || ''
    };
}

async function handleBarangaySearch() {
    const searchInput = document.getElementById('map-barangay-search');
    const rawQuery = searchInput?.value || '';
    const query = normalizeBarangayName(rawQuery);

    if (!query) {
        showMapToast('warning', 'Search required', 'Please enter a barangay name to search.');
        return;
    }

    const markerEntries = Object.values(window.markerIndex).filter(({ feature }) => {
        const barangay = normalizeBarangayName(feature?.properties?.barangay);
        return Boolean(barangay);
    });

    // Prefer exact barangay match first, then startsWith, then includes.
    let matchingMarkers = markerEntries
        .filter(({ feature }) => normalizeBarangayName(feature?.properties?.barangay) === query)
        .map(({ marker }) => marker)
        .filter(Boolean);

    if (matchingMarkers.length === 0) {
        matchingMarkers = markerEntries
            .filter(({ feature }) => normalizeBarangayName(feature?.properties?.barangay).startsWith(query))
            .map(({ marker }) => marker)
            .filter(Boolean);
    }

    if (matchingMarkers.length === 0) {
        matchingMarkers = markerEntries
        .filter(({ feature }) => {
            const barangay = normalizeBarangayName(feature?.properties?.barangay);
            return barangay.includes(query);
        })
        .map(({ marker }) => marker)
        .filter(Boolean);
    }

    if (matchingMarkers.length === 0) {
        // Fallback to geocoding so barangays without registered markers can still be searched.
        try {
            let geocodeResult = await geocodeBarangayInLipa(rawQuery.trim());

            if (!geocodeResult) {
                geocodeResult = await geocodeBarangayInLipaNominatim(rawQuery.trim());
            }

            if (geocodeResult && Number.isFinite(geocodeResult.lat) && Number.isFinite(geocodeResult.lng)) {
                closeDetailsPanel();

                if (geocodeResult.bounds) {
                    const { south, north, west, east } = geocodeResult.bounds;
                    const hasBounds = [south, north, west, east].every((v) => Number.isFinite(v));
                    if (hasBounds) {
                        map.fitBounds(
                            [
                                [south, west],
                                [north, east]
                            ],
                            { padding: [40, 40], maxZoom: 15, animate: true }
                        );
                    } else {
                        map.flyTo([geocodeResult.lat, geocodeResult.lng], 14, {
                            animate: true,
                            duration: 0.9
                        });
                    }
                } else {
                    map.flyTo([geocodeResult.lat, geocodeResult.lng], 14, {
                        animate: true,
                        duration: 0.9
                    });
                }

                showMapToast('success', 'Barangay found', `Centered map near ${geocodeResult.placeName || rawQuery.trim()}.`);
                return;
            }
        } catch (error) {
            console.error('Barangay geocode fallback failed:', error);
        }

        showMapToast('info', 'No matching barangay', `No results found in Lipa City for "${rawQuery.trim()}".`);
        return;
    }

    closeDetailsPanel();

    if (matchingMarkers.length === 1) {
        const targetLatLng = matchingMarkers[0].getLatLng();
        map.flyTo(targetLatLng, Math.max(map.getZoom(), 15), {
            animate: true,
            duration: 0.8
        });
    } else {
        const bounds = L.latLngBounds(matchingMarkers.map((marker) => marker.getLatLng()));
        map.fitBounds(bounds, {
            padding: [60, 60],
            maxZoom: 16,
            animate: true
        });
    }

    showMapToast('success', 'Barangay found', `Showing ${matchingMarkers.length} result(s) for "${rawQuery.trim()}".`);
}

function getResellerMarkerKey(resellerId) {
    return `reseller-user-${resellerId}`;
}

function hasRenderableResellerCoordinates(reseller) {
    const latitude = Number(reseller?.latitude);
    const longitude = Number(reseller?.longitude);

    if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) {
        return false;
    }

    // Match map bounds; coordinates outside this area are treated as not yet mapped.
    return latitude >= 13.5 && latitude <= 14.4 && longitude >= 120.7 && longitude <= 121.8;
}

function removeMarkerFromAllLayers(marker) {
    if (!marker) return;

    Object.values(window.layers || {}).forEach((layerGroup) => {
        if (layerGroup?.removeLayer) {
            layerGroup.removeLayer(marker);
        }
    });

    if (map?.hasLayer(marker)) {
        map.removeLayer(marker);
    }
}

function upsertMappedResellerMarker(reseller) {
    if (!reseller) return;

    if (!hasRenderableResellerCoordinates(reseller)) {
        return;
    }

    const latitude = Number(reseller.latitude);
    const longitude = Number(reseller.longitude);

    if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) {
        return;
    }

    const markerKey = getResellerMarkerKey(reseller.id);
    const existing = window.markerIndex[markerKey];
    if (existing?.marker) {
        removeMarkerFromAllLayers(existing.marker);
    }

    const resellerTheme = getEstablishmentTypeTheme('reseller');
    const icon = L.divIcon({
        html: `<svg width="28" height="28" viewBox="0 0 28 28" xmlns="http://www.w3.org/2000/svg"><circle cx="14" cy="14" r="13" fill="${resellerTheme.color}" stroke="white" stroke-width="2"/><circle cx="14" cy="14" r="6" fill="white"/></svg>`,
        className: 'custom-marker',
        iconSize: [28, 28],
        iconAnchor: [14, 14]
    });

    const marker = L.marker([latitude, longitude], { icon });

    window.layers.resellers.addLayer(marker);
    window.layers.all.addLayer(marker);

    const feature = {
        type: 'Feature',
        geometry: {
            type: 'Point',
            coordinates: [longitude, latitude],
        },
        properties: {
            id: markerKey,
            name: reseller.name || 'Reseller',
            type: 'reseller',
            description: '',
            address: '',
            barangay: reseller.barangay || '',
            contact_number: reseller.contact_number || '',
            email: reseller.email || '',
            website: '',
            visit_hours: '',
            activities: '',
            image: null,
            coffee_varieties: [],
            rating_average: null,
            review_count: 0,
            taste_avg: null,
            environment_avg: null,
            cleanliness_avg: null,
            service_avg: null,
            active_promos: [],
            is_reseller_user: true,
            reseller_user_id: reseller.id,
        }
    };

    window.markerIndex[markerKey] = { marker, feature };

    marker.bindTooltip(`${escapeHtml(reseller.name || 'Reseller')} (Reseller)`, {
        direction: 'top',
        offset: [0, -8],
        opacity: 0.85,
        permanent: false,
        sticky: true,
        className: 'custom-marker-tooltip',
    });

    marker.on('mouseover', function () {
        this.openTooltip();
    });

    marker.on('mouseout', function () {
        this.closeTooltip();
    });

    marker.on('click', function (e) {
        L.DomEvent.stopPropagation(e);
        window.currentPanelFeature = feature;
        openDetailsPanel(feature);
    });
}

function renderVerifiedResellerMarkers() {
    const verifiedResellers = Array.isArray(window.VERIFIED_RESELLERS)
        ? window.VERIFIED_RESELLERS
        : [];

    verifiedResellers.forEach((reseller) => {
        upsertMappedResellerMarker(reseller);
    });
}

function formatVerifiedTimestamp(isoValue) {
    if (!isoValue) return 'N/A';
    const date = new Date(isoValue);
    if (Number.isNaN(date.getTime())) return 'N/A';

    return date.toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
}

function getUnmappedVerifiedResellers() {
    const verifiedResellers = Array.isArray(window.VERIFIED_RESELLERS)
        ? window.VERIFIED_RESELLERS
        : [];

    return verifiedResellers.filter((reseller) => !hasRenderableResellerCoordinates(reseller));
}

async function refreshVerifiedResellers() {
    try {
        const response = await fetch('/admin/map/resellers/verified', {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.CSRF_TOKEN,
            },
        });

        if (!response.ok) {
            return;
        }

        const payload = await response.json().catch(() => ({}));
        if (Array.isArray(payload?.resellers)) {
            window.VERIFIED_RESELLERS = payload.resellers;
        }
    } catch (error) {
        console.warn('Unable to refresh verified resellers:', error);
    }
}

function updateResellerUnmappedCountBadge() {
    const badge = document.getElementById('map-reseller-unmapped-count');
    if (!badge) return;

    const unmappedCount = getUnmappedVerifiedResellers().length;
    badge.textContent = String(unmappedCount);
    badge.classList.toggle('hidden', unmappedCount <= 0);
}

async function openMapResellerModal() {
    const modal = document.getElementById('map-reseller-modal');
    const list = document.getElementById('map-reseller-list');
    const empty = document.getElementById('map-reseller-empty');

    if (!modal || !list || !empty) return;

    await refreshVerifiedResellers();

    const verifiedResellers = getUnmappedVerifiedResellers();

    updateResellerUnmappedCountBadge();

    list.innerHTML = '';

    if (verifiedResellers.length === 0) {
        empty.classList.remove('hidden');
    } else {
        empty.classList.add('hidden');

        verifiedResellers.forEach((reseller) => {
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'w-full text-left p-4 rounded-lg border border-[#E7DED1] hover:bg-[#F5F0E8] transition-colors';
            item.innerHTML = `
                <div class="text-sm font-semibold text-[#3A2E22]">${escapeHtml(reseller.name || 'Unknown')}</div>
                <div class="text-xs text-[#9E8C78] mt-1">Verified: ${escapeHtml(formatVerifiedTimestamp(reseller.verified_at))}</div>
                <div class="text-xs text-[#9E8C78]">Barangay: ${escapeHtml(reseller.barangay || 'N/A')}</div>
            `;

            item.addEventListener('click', () => {
                selectedResellerForMapping = reseller;
                closeMapResellerModal();
                enterResellerPlacementMode();
            });

            list.appendChild(item);
        });
    }

    modal.classList.remove('hidden', 'opacity-0');
    modal.classList.add('opacity-100');
    modal.style.zIndex = '12000';

    const inner = modal.querySelector('.transform') || modal.firstElementChild;
    if (inner) {
        inner.style.zIndex = '12001';
        inner.classList.remove('scale-95');
        inner.classList.add('scale-100');
    }
}

function closeMapResellerModal() {
    const modal = document.getElementById('map-reseller-modal');
    if (!modal) return;

    modal.classList.remove('opacity-100');
    modal.classList.add('opacity-0');

    const inner = modal.querySelector('.transform') || modal.firstElementChild;
    if (inner) {
        inner.classList.remove('scale-100');
        inner.classList.add('scale-95');
    }

    setTimeout(() => {
        modal.classList.add('hidden');
    }, 180);
}

function syncMapActionButtonsLayout() {
    const actionButtons = document.getElementById('map-action-buttons');
    const addEstablishmentBtn = document.getElementById('add-establishment-btn');
    const mapResellerBtn = document.getElementById('map-reseller-location-btn');

    if (actionButtons) {
        actionButtons.style.left = '24px';
        actionButtons.style.bottom = '28px';
        actionButtons.style.zIndex = '901';
    }

    if (addEstablishmentBtn) {
        addEstablishmentBtn.style.zIndex = '901';
    }

    if (mapResellerBtn) {
        mapResellerBtn.style.zIndex = '900';
    }
}

function bindEditBannerInteractions() {
    const banner = document.getElementById('edit-banner');
    const saveBtn = document.getElementById('edit-save');
    const cancelBtn = document.getElementById('edit-cancel');

    if (!banner || !saveBtn || !cancelBtn) return;

    // Keep banner interactions from bubbling to Leaflet map handlers.
    if (!banner.dataset.interactionsBound) {
        [banner, saveBtn, cancelBtn].forEach((el) => {
            el.addEventListener('click', (e) => e.stopPropagation());
            el.addEventListener('mousedown', (e) => e.stopPropagation());
            el.addEventListener('touchstart', (e) => e.stopPropagation(), { passive: true });
        });
        banner.dataset.interactionsBound = '1';
    }

    if (L?.DomEvent) {
        L.DomEvent.disableClickPropagation(banner);
        L.DomEvent.disableScrollPropagation(banner);
    }

    saveBtn.onclick = (e) => {
        e.preventDefault();
        e.stopPropagation();
        if (saveBtn.disabled) return;

        if (editMode) {
            saveEdit();
            return;
        }

        if (selectedResellerForMapping || resellerPlacementMode) {
            saveResellerLocation();
            return;
        }

        saveEdit();
    };

    cancelBtn.onclick = (e) => {
        e.preventDefault();
        e.stopPropagation();
        cancelEdit();
    };
}

function enterResellerPlacementMode() {
    if (!selectedResellerForMapping || !map) return;

    closeDetailsPanel();
    bindEditBannerInteractions();
    resellerPlacementMode = true;
    pendingResellerLatLng = null;

    const addEstablishmentBtn = document.getElementById('add-establishment-btn');
    const mapResellerBtn = document.getElementById('map-reseller-location-btn');
    const placementCancelBtn = document.getElementById('cancel-placement-btn');
    if (addEstablishmentBtn) addEstablishmentBtn.style.display = 'none';
    if (mapResellerBtn) mapResellerBtn.style.display = 'none';
    if (placementCancelBtn) {
        placementCancelBtn.style.display = '';
        placementCancelBtn.style.zIndex = '900';
    }
    syncMapActionButtonsLayout();

    const banner = document.getElementById('edit-banner');
    const text = document.getElementById('edit-banner-text');
    const saveBtn = document.getElementById('edit-save');
    const cancelBtn = document.getElementById('edit-cancel');
    if (banner) banner.classList.add('hidden');
    if (text) text.textContent = `Click map to place ${selectedResellerForMapping.name}`;
    if (saveBtn) saveBtn.disabled = true;
    if (cancelBtn) cancelBtn.disabled = false;

    showPlacementBanner(`Click map to place reseller ${selectedResellerForMapping.name}`);
    map.getContainer().style.cursor = 'crosshair';
    map.on('click', onResellerMapClick);
}

function onResellerMapClick(e) {
    if (!resellerPlacementMode || !selectedResellerForMapping) return;

    pendingResellerLatLng = e.latlng;

    if (resellerTempMarker) {
        map.removeLayer(resellerTempMarker);
    }

    resellerTempMarker = L.marker([e.latlng.lat, e.latlng.lng], { icon: newPinIcon }).addTo(map);
    resellerTempMarker.bindTooltip(`${escapeHtml(selectedResellerForMapping.name)} (Reseller)`, {
        permanent: false,
        direction: 'top',
        offset: [0, -12],
        className: 'custom-marker-tooltip',
    }).openTooltip();

    hidePlacementBanner();

    const banner = document.getElementById('edit-banner');
    const text = document.getElementById('edit-banner-text');
    const saveBtn = document.getElementById('edit-save');

    if (text) {
        text.textContent = `Map ${selectedResellerForMapping.name} at ${e.latlng.lat.toFixed(6)}, ${e.latlng.lng.toFixed(6)}?`;
    }

    if (saveBtn) saveBtn.disabled = false;
    if (banner) banner.classList.remove('hidden');
}

function saveResellerLocation() {
    const saveBtn = document.getElementById('edit-save');
    const originalSaveLabel = saveBtn ? saveBtn.textContent : '';

    if (!selectedResellerForMapping || !pendingResellerLatLng) {
        showMapToast('warning', 'Location required', 'Click on the map first to select reseller coordinates.');
        return;
    }

    if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving...';
    }

    fetch(`/admin/map/resellers/${selectedResellerForMapping.id}/location`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.CSRF_TOKEN,
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            latitude: pendingResellerLatLng.lat.toFixed(8),
            longitude: pendingResellerLatLng.lng.toFixed(8),
        }),
    })
        .then(async (response) => {
            const payload = await response.json().catch(() => ({}));
            if (!response.ok) {
                const validationErrors = payload?.errors
                    ? Object.values(payload.errors).flat().join(' ')
                    : '';
                const serverMessage = payload?.message || '';
                const fallback = `Failed to update reseller location (HTTP ${response.status}).`;
                throw new Error(validationErrors || serverMessage || fallback);
            }

            const savedReseller = {
                ...selectedResellerForMapping,
                ...(payload?.reseller || {}),
            };

            if (Array.isArray(window.VERIFIED_RESELLERS)) {
                window.VERIFIED_RESELLERS = window.VERIFIED_RESELLERS.map((reseller) => {
                    return Number(reseller.id) === Number(savedReseller.id)
                        ? { ...reseller, ...savedReseller }
                        : reseller;
                });
            }

            updateResellerUnmappedCountBadge();

            upsertMappedResellerMarker(savedReseller);
            filterMarkers(window.filterState.varieties, window.filterState.types);

            if (resellerTempMarker) {
                map.removeLayer(resellerTempMarker);
                resellerTempMarker = null;
            }

            showMapToast('success', 'Reseller mapped', `${selectedResellerForMapping.name} location saved successfully.`);
            cancelResellerPlacementMode();
        })
        .catch((error) => {
            console.error('Error mapping reseller location:', error);
            showMapToast('error', 'Something went wrong', error.message || 'Failed to update reseller location.');

            const text = document.getElementById('edit-banner-text');
            if (text) {
                text.textContent = error.message || 'Failed to update reseller location. Please try another point.';
            }
        })
        .finally(() => {
            if (saveBtn) {
                saveBtn.textContent = originalSaveLabel || 'Save';
                // Enable only if still in placement mode and map point exists.
                saveBtn.disabled = !(selectedResellerForMapping && pendingResellerLatLng);
            }
        });
}

function cancelResellerPlacementMode() {
    resellerPlacementMode = false;
    pendingResellerLatLng = null;
    selectedResellerForMapping = null;

    const addEstablishmentBtn = document.getElementById('add-establishment-btn');
    const mapResellerBtn = document.getElementById('map-reseller-location-btn');
    const placementCancelBtn = document.getElementById('cancel-placement-btn');
    if (addEstablishmentBtn) addEstablishmentBtn.style.display = '';
    if (mapResellerBtn) mapResellerBtn.style.display = '';
    if (placementCancelBtn) placementCancelBtn.style.display = 'none';
    syncMapActionButtonsLayout();

    const banner = document.getElementById('edit-banner');
    if (banner) banner.classList.add('hidden');

    map.getContainer().style.cursor = '';
    hidePlacementBanner();
    map.off('click', onResellerMapClick);

    if (resellerTempMarker) {
        map.removeLayer(resellerTempMarker);
        resellerTempMarker = null;
    }
}

function setupStaticEventListeners() {
    syncMapActionButtonsLayout();
    bindEditBannerInteractions();
    refreshVerifiedResellers().finally(() => {
        updateResellerUnmappedCountBadge();
    });

    const panelCloseBtn = document.getElementById('panel-close-btn');
    const panelCloseBtnMobile = document.getElementById('panel-close-btn-mobile');

    panelCloseBtn?.addEventListener('click', closeDetailsPanel);
    panelCloseBtnMobile?.addEventListener('click', closeDetailsPanel);

    const detailsPanel = document.getElementById('details-panel');
    detailsPanel?.addEventListener('click', function (e) {
        if (e.target.closest('#navigate-btn')) {
            e.preventDefault();
            handleNavigate();
            return;
        }

        if (e.target.closest('#edit-location-btn')) {
            e.preventDefault();
            const featureId = e.target.closest('#edit-location-btn').getAttribute('data-feature-id');
            if (featureId) {
                enterEditMode(featureId);
            }
            return;
        }

        if (e.target.closest('#clear-route-sidebar-btn') || e.target.closest('#clear-route-btn')) {
            e.preventDefault();
            clearRoute();
            return;
        }

        if (e.target.closest('#close-details-btn')) {
            e.preventDefault();
            this.style.transform = window.innerWidth < 768 ? 'translateY(100%)' : 'translateX(100%)';
            const mapEl = document.getElementById('map');
            if (mapEl) mapEl.classList.remove('has-details-panel');
            return;
        }
    });

    map.on('click', () => {
        if (!editMode && !placementMode && !resellerPlacementMode) {
            closeDetailsPanel();
        }
    });

    const clearRouteBtn = document.getElementById('clear-route-btn');
    clearRouteBtn?.addEventListener('click', (e) => {
        e.preventDefault();
        clearRoute();
    });

    const addBtn = document.getElementById('add-establishment-btn');
    const mapResellerBtn = document.getElementById('map-reseller-location-btn');
    const mapResellerModal = document.getElementById('map-reseller-modal');
    const mapResellerCloseBtn = document.getElementById('close-map-reseller-modal');
    const closeModalBtn = document.getElementById('close-modal');
    const modal = document.getElementById('add-establishment-modal');
    const imageInput = document.getElementById('image-input');
    const imagePreview = document.getElementById('image-preview');
    const form = document.getElementById('add-establishment-form');
    const editSaveBtn = document.getElementById('edit-save');
    const editCancelBtn = document.getElementById('edit-cancel');
    const mapFilter = document.getElementById('map-filter');
    const barangaySearchInput = document.getElementById('map-barangay-search');
    const barangaySearchBtn = document.getElementById('map-barangay-search-btn');
    const cancelPlacementBtn = document.getElementById('cancel-placement');

    setupBarangayAutocomplete(barangaySearchInput);

    // Removed old addBtn click listener

    mapResellerBtn?.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        openMapResellerModal();
    });

    mapResellerCloseBtn?.addEventListener('click', closeMapResellerModal);
    mapResellerModal?.addEventListener('click', (e) => {
        if (e.target === mapResellerModal) {
            closeMapResellerModal();
        }
    });

    closeModalBtn?.addEventListener('click', closeMapSidebar);
    cancelPlacementBtn?.addEventListener('click', closeMapSidebar);

    modal?.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeMapSidebar();
        }
    });

    imageInput?.addEventListener('change', () => {
        const file = imageInput.files?.[0];
        if (!file) {
            if (imagePreview) {
                imagePreview.src = '';
                imagePreview.classList.add('hidden');
            }
            return;
        }

        const reader = new FileReader();
        reader.onload = (e) => {
            if (imagePreview) {
                imagePreview.src = e.target.result;
                imagePreview.classList.remove('hidden');
            }
        };
        reader.readAsDataURL(file);
    });

    mapFilter?.addEventListener('change', (e) => {
        const value = e.target.value;

        if (value === 'all') {
            window.filterState.types = ['farm', 'cafe', 'roaster', 'reseller'];
            updateTypeButtons();
            filterMarkers([], ['farm', 'cafe', 'roaster', 'reseller']);
        } else if (['farm', 'cafe', 'roaster', 'reseller'].includes(value)) {
            window.filterState.types = [value];
            updateTypeButtons();
            filterMarkers(window.filterState.varieties, [value]);
        }
    });

    barangaySearchBtn?.addEventListener('click', handleBarangaySearch);
    barangaySearchInput?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            handleBarangaySearch();
        }
    });

    // Resilient fallback in case the header controls are re-rendered dynamically.
    if (!window.__barangaySearchDelegated) {
        window.__barangaySearchDelegated = true;

        document.addEventListener('click', (e) => {
            if (e.target?.closest('#map-barangay-search-btn')) {
                e.preventDefault();
                handleBarangaySearch();
            }

            if (e.target?.closest('#map-reseller-location-btn')) {
                e.preventDefault();
                openMapResellerModal();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key !== 'Enter') return;
            const target = e.target;
            if (target && target.id === 'map-barangay-search') {
                e.preventDefault();
                handleBarangaySearch();
            }
        });
    }

    if (form) {
        document.querySelectorAll('input[name="varieties[]"]').forEach((box) => {
            box.addEventListener('change', () => {
                const id = box.value;
                const radio = form.querySelector(`input[name="primary_variety"][value="${id}"]`);
                if (radio && !box.checked && radio.checked) {
                    radio.checked = false;
                }
            });
        });

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Check if lat/lng fields are filled
            const latField = document.getElementById('latitude-input');
            const lngField = document.getElementById('longitude-input');
            const latVal = latField?.value || '';
            const lngVal = lngField?.value || '';

            if (!latVal || !lngVal || latVal.trim() === '' || lngVal.trim() === '') {
                showMapToast('warning', 'Location required', 
                  'Please click the map first to set the location.');
                return;
            }

            const varieties = Array.from(
                form.querySelectorAll('input[name="varieties[]"]:checked')
            ).map((el) => el.value);

            const primary = form.querySelector('input[name="primary_variety"]:checked')?.value;

            if (varieties.length === 0) {
                showMapToast('warning', 'Variety required',
                  'Please select at least one coffee variety.');
                return;
            }

            if (!primary || !varieties.includes(primary)) {
                showMapToast('warning', 'Primary variety required',
                  'Please select one primary coffee variety that is checked.');
                return;
            }

            const formData = new FormData();
            formData.append('_token', window.CSRF_TOKEN);
            formData.append('name', form.name.value.trim());
            formData.append('type', form.type.value);
            formData.append('description', form.description.value.trim());
            formData.append('address', form.address.value.trim());
            formData.append('barangay', form.barangay.value.trim());

            if (form.contact_number.value) formData.append('contact_number', form.contact_number.value.trim());
            if (form.email.value) formData.append('email', form.email.value.trim());
            if (form.website.value) formData.append('website', form.website.value.trim());
            if (form.visit_hours.value) formData.append('visit_hours', form.visit_hours.value.trim());
            if (form.activities.value) formData.append('activities', form.activities.value.trim());

            formData.append('latitude', form.latitude.value);
            formData.append('longitude', form.longitude.value);

            if (form.image?.files?.[0]) {
                formData.append('image', form.image.files[0]);
            }

            varieties.forEach((v) => formData.append('varieties[]', v));
            formData.append('primary_variety', primary);

            try {
                const response = await fetch('/admin/map', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': window.CSRF_TOKEN
                    },
                    body: formData
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    // Success - close modal and clean up
                    closeMapSidebar();

                    // Remove temp marker
                    if (tempMarker) {
                        map.removeLayer(tempMarker);
                        tempMarker = null;
                    }

                    // Reset placement mode
                    placementMode = false;
                    addBtn.style.display = '';
                    addBtn.style.zIndex = '900';
                    cancelBtn.style.display = 'none';

                    // Reload establishments and show success message
                    await loadEstablishments();
                    showMapToast('success', 'Establishment added',
                      'The establishment has been saved successfully.');

                    // Reset form
                    form.reset();
                    if (imagePreview) {
                        imagePreview.src = '';
                        imagePreview.classList.add('hidden');
                    }
                } else {
                    // Error - show error message
                    const errorText =
                        (result.errors && Object.values(result.errors).flat().join('\n')) ||
                        result.message ||
                        'Failed to save establishment';
                    showMapToast('error', 'Something went wrong',
                      errorText);
                }
            } catch (error) {
                console.error('Error saving establishment:', error);
                showMapToast('error', 'Something went wrong',
                  'Network error — please try again');
            }
        });
    }

    editSaveBtn?.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();

        if (editSaveBtn.disabled) return;

        if (editMode) {
            saveEdit();
            return;
        }

        if (selectedResellerForMapping || resellerPlacementMode) {
            saveResellerLocation();
            return;
        }

        saveEdit();
    });

    editCancelBtn?.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        cancelEdit();
    });

    if (!window.__editBannerDelegated) {
        window.__editBannerDelegated = true;

        document.addEventListener('click', (e) => {
            const saveTrigger = e.target?.closest('#edit-save');
            if (saveTrigger) {
                e.preventDefault();
                e.stopPropagation();

                if (saveTrigger.disabled) return;

                if (editMode) {
                    saveEdit();
                    return;
                }

                if (selectedResellerForMapping || resellerPlacementMode) {
                    saveResellerLocation();
                } else {
                    saveEdit();
                }
                return;
            }

            const cancelTrigger = e.target?.closest('#edit-cancel');
            if (cancelTrigger) {
                e.preventDefault();
                e.stopPropagation();
                cancelEdit();
            }
        });
    }

}

const addBtn = document.getElementById('add-establishment-btn')
const cancelBtn = document.getElementById('cancel-placement-btn')
const mapResellerBtnGlobal = document.getElementById('map-reseller-location-btn')

function enterPlacementMode() {
        if (!addBtn || !cancelBtn || !map) return
  placementMode = true
  addBtn.style.display = 'none'
    if (mapResellerBtnGlobal) mapResellerBtnGlobal.style.display = 'none'
  cancelBtn.style.display = ''
  cancelBtn.style.zIndex = '900'
  showPlacementBanner('Click the map to place establishment')
  map.getContainer().style.cursor = 'crosshair'
  map.once('click', onMapClick)
}

function exitPlacementMode() {
        if (!addBtn || !cancelBtn || !map) return
  placementMode = false
  addBtn.style.display = ''
  addBtn.style.zIndex = '900'
    if (mapResellerBtnGlobal) mapResellerBtnGlobal.style.display = ''
  cancelBtn.style.display = 'none'
  hidePlacementBanner()
  map.getContainer().style.cursor = ''
  map.off('click', onMapClick)
  if (tempMarker) {
    map.removeLayer(tempMarker)
    tempMarker = null
  }
}

function onMapClick(e) {
    if (!addBtn || !cancelBtn || !map) return
  if (!placementMode) return
  const lat = e.latlng.lat
  const lng = e.latlng.lng
  
  // Place temp marker
  if (tempMarker) map.removeLayer(tempMarker)
  tempMarker = L.marker([lat, lng]).addTo(map)
  
  // Fill lat/lng fields in the form
  const latField = document.getElementById('latitude-input')
  const lngField = document.getElementById('longitude-input')
  if (latField) latField.value = lat.toFixed(8)
  if (lngField) lngField.value = lng.toFixed(8)
  
  // Reset UI state but DO NOT remove tempMarker
  placementMode = false
  addBtn.style.display = ''
  addBtn.style.zIndex = '900'
    if (mapResellerBtnGlobal) mapResellerBtnGlobal.style.display = 'none'
  cancelBtn.style.display = 'none'
  hidePlacementBanner()
  map.getContainer().style.cursor = ''
  map.off('click', onMapClick)
  
  // Open the modal LAST
  openMapSidebar()
}

if (addBtn && cancelBtn) {
    addBtn.addEventListener('click', function(e) {
        e.preventDefault()
        e.stopPropagation()
        enterPlacementMode()
    })

    cancelBtn.addEventListener('click', function(e) {
        e.preventDefault()
        e.stopPropagation()
        if (resellerPlacementMode) {
            cancelResellerPlacementMode()
            return
        }
        exitPlacementMode()
    })
}

// Ensure initial button state with proper z-index
document.addEventListener('DOMContentLoaded', () => {
    if (!addBtn || !cancelBtn) return
    addBtn.style.display = ''
    addBtn.style.zIndex = '900'
    if (mapResellerBtnGlobal) mapResellerBtnGlobal.style.display = ''
    cancelBtn.style.display = 'none'
})

// ─────────────────────────────────────────────────────────────────
// DETAILS PANEL FUNCTIONS
// ─────────────────────────────────────────────────────────────────

function openDetailsPanel(feature) {
  const panel = document.getElementById('details-panel')
  if (!panel) return

  const isMobile = window.innerWidth < 768

  renderDetailsPanel(feature)

  panel.style.transform = isMobile
    ? 'translateY(0)'
    : 'translateX(0)'

  // Add class to push Leaflet controls left
  const mapEl = document.getElementById('map')
  if (mapEl && !isMobile) {
    mapEl.classList.add('has-details-panel')
  }
}

function renderDetailsPanel(feature) {
  const panel = document.getElementById('details-panel')
  if (!panel) return

  currentPanelFeature = feature
  window.currentPanelFeature = feature

  const props = feature.properties || {}
  const id = props.id
    const isResellerUserMarker = Boolean(props.is_reseller_user)
  const name = props.name || 'Unknown'
  const type = (props.type || 'default').toLowerCase()
  const description = props.description || ''
  const address = props.address || ''
  const barangay = props.barangay || ''
  const contact = props.contact_number || ''
  const email = props.email || ''
  const website = props.website || ''
    const visitHours = props.visit_hours || ''
    const activities = props.activities || ''
  const image = props.image || null
  const ownerName = props.owner_name || ''

  // Featured Properties
  const varieties = props.coffee_varieties || []
  const primaryVariety = props.primary_variety || null
  const ratingAvg = props.rating_average || null
  const reviewCount = props.review_count || 0
  const tasteAvg = props.taste_avg || null
  const envAvg = props.environment_avg || null
  const cleanAvg = props.cleanliness_avg || null
  const serviceAvg = props.service_avg || null

  // Type color and label
    const typeTheme = getEstablishmentTypeTheme(type)
    const typeConfig = { bg: typeTheme.color, label: typeTheme.label }

  // Render HTML
  const html = `
    <div class="panel-header">
      <h2 class="panel-title">${escapeHtml(name)}</h2>
      <button id="close-details-btn" class="panel-close-btn" aria-label="Close">&times;</button>
    </div>

    ${image ? `<img src="${escapeHtml(image)}" alt="${escapeHtml(name)}" class="establishment-photo" onerror="this.style.display='none'">` : '<div class="photo-placeholder">No image available</div>'}

    <div class="info-section">
      <span class="type-badge type-${type}" style="background: ${typeConfig.bg}; color: white;">
        ${typeConfig.label}
      </span>
    </div>

    ${ratingAvg !== null || reviewCount > 0 ? `
      <div class="rating-section">
        <div class="rating-label">Overall Rating</div>
        <div class="rating-value">
          ${ratingAvg !== null ? `<span>${ratingAvg.toFixed(1)}</span>` : '<span class="text-[#9E8C78] italic">No rating yet</span>'}
          ${reviewCount > 0 ? `<span class="rating-stars">★</span><span class="text-[#9E8C78]">(${reviewCount})</span>` : ''}
        </div>
        ${tasteAvg !== null || envAvg !== null || cleanAvg !== null || serviceAvg !== null ? `
          <div class="sub-ratings">
            ${tasteAvg !== null ? `<div class="sub-rating"><div class="sub-rating-label">Taste</div><div class="sub-rating-value">${tasteAvg.toFixed(1)}</div></div>` : ''}
            ${envAvg !== null ? `<div class="sub-rating"><div class="sub-rating-label">Environment</div><div class="sub-rating-value">${envAvg.toFixed(1)}</div></div>` : ''}
            ${cleanAvg !== null ? `<div class="sub-rating"><div class="sub-rating-label">Cleanliness</div><div class="sub-rating-value">${cleanAvg.toFixed(1)}</div></div>` : ''}
            ${serviceAvg !== null ? `<div class="sub-rating"><div class="sub-rating-label">Service</div><div class="sub-rating-value">${serviceAvg.toFixed(1)}</div></div>` : ''}
          </div>
        ` : ''}
      </div>
    ` : ''}

    ${props.active_promos && props.active_promos.length > 0 ? `
      <hr style="border: none; border-top: 1px solid #e5e0d8; margin: 12px 0;">
      <div class="sidebar-section">
          <div class="panel-label">Active Promotions</div>
          ${props.active_promos.map(promo => `
              <div class="promo-card">
                  <div class="promo-header">
                      <span class="promo-title">${promo.title}</span>
                      <span class="promo-discount">${promo.discount_value}% OFF</span>
                  </div>
                  <p class="promo-description">${promo.description || ''}</p>
                  <div class="promo-code">
                      🏷 Code: <strong>${promo.qr_code_token}</strong>
                  </div>
                  <div class="promo-validity">
                      Valid until: ${new Date(promo.valid_until).toLocaleDateString('en-US', {
                          month: 'short', day: 'numeric', year: 'numeric'
                      })}
                  </div>
              </div>
          `).join('')}
      </div>
    ` : ''}

    ${varieties && varieties.length > 0 ? `
      <div class="info-section">
        <div class="info-label">Coffee Varieties</div>
        <div class="varieties-list">
          ${varieties.map(v => {
            const variety = getVarietyColor(v)
            const isPrimary = primaryVariety && String(primaryVariety).toLowerCase() === String(v).toLowerCase()
            return `<span class="variety-tag" title="${isPrimary ? 'Primary variety' : ''}"
              style="background: ${variety.hex}20; border: 1px solid ${variety.hex}40;">
              <span class="variety-dot" style="background: ${variety.hex};"></span>
              ${escapeHtml(variety.name)}
              ${isPrimary ? ' ✓' : ''}
            </span>`
          }).join('')}
        </div>
      </div>
    ` : ''}

    ${description ? `
      <div class="info-section">
        <div class="info-label">Description</div>
        <div class="info-value">${escapeHtml(description)}</div>
      </div>
    ` : ''}

    ${address ? `
      <div class="info-section">
        <div class="info-label">Address</div>
        <div class="info-value">${escapeHtml(address)}</div>
      </div>
    ` : ''}

    ${barangay ? `
      <div class="info-section">
        <div class="info-label">Barangay</div>
        <div class="info-value">${escapeHtml(barangay)}</div>
      </div>
    ` : ''}

        ${visitHours ? `
            <div class="info-section">
                <div class="info-label">Visit Hours</div>
                <div class="info-value">${escapeHtml(visitHours)}</div>
            </div>
        ` : ''}

        ${activities ? `
            <div class="info-section">
                <div class="info-label">Activities</div>
                <div class="info-value">${escapeHtml(activities)}</div>
            </div>
        ` : ''}

    ${ownerName ? `
      <div class="owner-info">
        <strong>Owner:</strong> ${escapeHtml(ownerName)}
      </div>
    ` : ''}

    <div class="info-section">
      ${contact ? `<div style="margin-bottom: 8px;"><strong style="font-size: 12px; color: #9E8C78;">Contact:</strong> <a href="tel:${encodeURIComponent(contact)}" style="color: #4A6741; text-decoration: none;">${escapeHtml(contact)}</a></div>` : ''}
      ${email ? `<div style="margin-bottom: 8px;"><strong style="font-size: 12px; color: #9E8C78;">Email:</strong> <a href="mailto:${encodeURIComponent(email)}" style="color: #4A6741; text-decoration: none; word-break: break-all;">${escapeHtml(email)}</a></div>` : ''}
      ${website ? `<div><strong style="font-size: 12px; color: #9E8C78;">Website:</strong> <a href="${escapeHtml(website)}" target="_blank" style="color: #4A6741; text-decoration: none; word-break: break-all;">${escapeHtml(website.replace(/^https?:\/\//, ''))}</a></div>` : ''}
    </div>

    <div class="action-buttons">
      <button class="action-btn primary" id="navigate-btn" type="button">
        Navigate
      </button>
            ${isFarmOwnerMapPage() ? '' : `
            <button class="action-btn" id="edit-location-btn" type="button" data-feature-id="${id}">
                Edit Location
            </button>
            `}
            <button class="action-btn" id="clear-route-sidebar-btn" type="button" style="display:none">
                Clear Route
            </button>
    </div>

    <div id="directions-strip" style="display:none">
    </div>
  `

  panel.innerHTML = html

    // Farm-owner map must not expose location editing controls.
    if (isFarmOwnerMapPage()) {
        panel.querySelector('#edit-location-btn')?.remove()
    }

  // Re-attach close button handler
  const closeBtn = panel.querySelector('.panel-close-btn')
  if (closeBtn) {
    closeBtn.addEventListener('click', closeDetailsPanel)
  }

  window.currentPanelFeature = feature
}

function escapeHtml(text) {
  if (!text) return ''
  const div = document.createElement('div')
  div.textContent = text
  return div.innerHTML
}