import L from 'leaflet';

let map;
const markerLayer = L.layerGroup();
const markersByType = {
    farm: L.layerGroup(),
    cafe: L.layerGroup(),
    roaster: L.layerGroup(),
};

const state = {
    activeTypes: ['farm', 'cafe', 'roaster'],
    allEstablishments: [],
};

function initMap() {
    map = L.map('map', {
        center: [13.9411, 121.1634],
        zoom: 13,
        minZoom: 10,
        maxZoom: 18,
        maxBounds: [[13.5, 120.7], [14.4, 121.8]],
        scrollWheelZoom: false,
    });

    const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
    });

    const mapboxLayer = L.tileLayer(
        'https://api.mapbox.com/styles/v1/mapbox/streets-v11/tiles/{z}/{x}/{y}?access_token={accessToken}',
        {
            attribution: '&copy; OpenStreetMap contributors, Imagery © Mapbox',
            maxZoom: 18,
            id: 'mapbox/streets-v11',
            tileSize: 512,
            zoomOffset: -1,
            accessToken: window.MAPBOX_TOKEN,
        }
    );

    osmLayer.addTo(map);

    L.control.layers(
        { OpenStreetMap: osmLayer, 'Mapbox Streets': mapboxLayer },
        {},
        { position: 'topright' }
    ).addTo(map);

    markerLayer.addTo(map);

    map.once('load', () => {
        const mapEl = document.getElementById('map');
        if (mapEl) mapEl.style.opacity = '1';
    });

    setTimeout(() => {
        const mapEl = document.getElementById('map');
        if (mapEl) mapEl.style.opacity = '1';
    }, 300);
}

function markerColor(type) {
    if (type === 'farm') return '#4A6741';
    if (type === 'cafe') return '#8B4513';
    if (type === 'roaster') return '#6B3A2A';
    return '#3A2E22';
}

function makeMarkerIcon(type) {
    const color = markerColor(type);

    return L.divIcon({
        className: 'custom-marker',
        html: `<svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10" fill="${color}" stroke="white" stroke-width="2"/><circle cx="12" cy="12" r="4" fill="white"/></svg>`,
        iconSize: [24, 24],
        iconAnchor: [12, 12],
    });
}

function renderDetails(establishment) {
    const panel = document.getElementById('details-panel');
    if (!panel) return;

    const imageHtml = establishment.image
        ? `<img src="${establishment.image}" alt="${establishment.name}" class="w-full h-40 object-cover rounded-lg mb-4">`
        : '<div class="w-full h-40 bg-[#F5F0E8] rounded-lg mb-4 flex items-center justify-center text-xs text-[#9E8C78]">No image available</div>';

    const varieties = (establishment.coffee_varieties || []).length
        ? establishment.coffee_varieties.map((v) => `<span class="inline-block text-xs rounded-full border border-[#D9C9B2] px-2 py-1 mr-1 mb-1">${v}</span>`).join('')
        : '<span class="text-xs text-[#9E8C78]">No varieties listed</span>';

    panel.innerHTML = `
        <div class="p-4">
            <div class="flex items-start justify-between mb-3">
                <h3 class="text-lg font-semibold text-[#3A2E22]">${establishment.name || 'Establishment'}</h3>
                <button id="close-details-btn" class="text-[#9E8C78] hover:text-[#3A2E22] text-xl leading-none">&times;</button>
            </div>
            ${imageHtml}
            <div class="space-y-2 text-sm text-[#6B5B4A]">
                <p><span class="font-semibold text-[#3A2E22]">Type:</span> ${establishment.type || 'N/A'}</p>
                <p><span class="font-semibold text-[#3A2E22]">Address:</span> ${establishment.address || 'N/A'}</p>
                <p><span class="font-semibold text-[#3A2E22]">Barangay:</span> ${establishment.barangay || 'N/A'}</p>
                <p><span class="font-semibold text-[#3A2E22]">Contact:</span> ${establishment.contact_number || 'N/A'}</p>
                <p><span class="font-semibold text-[#3A2E22]">Email:</span> ${establishment.email || 'N/A'}</p>
                <p><span class="font-semibold text-[#3A2E22]">Website:</span> ${establishment.website || 'N/A'}</p>
                <p><span class="font-semibold text-[#3A2E22]">Visit Hours:</span> ${establishment.visit_hours || 'N/A'}</p>
            </div>
            <hr class="my-3 border-[#E5E0D8]"/>
            <div>
                <p class="text-xs font-semibold tracking-wide text-[#9E8C78] mb-2">COFFEE VARIETIES</p>
                ${varieties}
            </div>
        </div>
    `;

    const isMobile = window.innerWidth < 768;
    panel.style.transform = isMobile ? 'translateY(0)' : 'translateX(0)';

    const closeBtn = panel.querySelector('#close-details-btn');
    closeBtn?.addEventListener('click', () => {
        panel.style.transform = isMobile ? 'translateY(100%)' : 'translateX(100%)';
    });
}

function clearMarkers() {
    markerLayer.clearLayers();
    markersByType.farm.clearLayers();
    markersByType.cafe.clearLayers();
    markersByType.roaster.clearLayers();
}

function applyFilters() {
    clearMarkers();

    const allowedTypes = new Set(state.activeTypes);

    state.allEstablishments.forEach((e) => {
        const type = (e.type || '').toLowerCase();
        if (!allowedTypes.has(type)) return;
        if (e.latitude === null || e.longitude === null) return;

        const marker = L.marker([Number(e.latitude), Number(e.longitude)], {
            icon: makeMarkerIcon(type),
        });

        marker.bindTooltip(`<div><strong>${e.name || 'Establishment'}</strong><br><span>${e.barangay || ''}</span></div>`, {
            direction: 'top',
            offset: [0, -12],
        });

        marker.on('click', () => renderDetails(e));
        markerLayer.addLayer(marker);

        if (markersByType[type]) markersByType[type].addLayer(marker);
    });
}

function setupControls() {
    const mapFilter = document.getElementById('map-filter');
    const searchInput = document.getElementById('map-barangay-search');
    const searchBtn = document.getElementById('map-barangay-search-btn');
    const suggestions = document.getElementById('map-barangay-suggestions');

    if (suggestions) {
        const barangays = [...new Set(state.allEstablishments.map((e) => (e.barangay || '').trim()).filter(Boolean))].sort();
        suggestions.innerHTML = barangays.map((b) => `<option value="${b}"></option>`).join('');
    }

    mapFilter?.addEventListener('change', (event) => {
        const value = event.target.value;
        state.activeTypes = value === 'all' ? ['farm', 'cafe', 'roaster'] : [value];
        applyFilters();
    });

    const runBarangaySearch = () => {
        const q = (searchInput?.value || '').trim().toLowerCase();
        if (!q) {
            map.setView([13.9411, 121.1634], 13);
            return;
        }

        const matches = state.allEstablishments.filter((e) => (e.barangay || '').toLowerCase().includes(q));
        if (!matches.length) return;

        const bounds = L.latLngBounds(
            matches
                .filter((e) => e.latitude !== null && e.longitude !== null)
                .map((e) => [Number(e.latitude), Number(e.longitude)])
        );

        if (bounds.isValid()) map.fitBounds(bounds.pad(0.2));
    };

    searchBtn?.addEventListener('click', runBarangaySearch);
    searchInput?.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            runBarangaySearch();
        }
    });
}

function initialize() {
    initMap();

    state.allEstablishments = Array.isArray(window.ESTABLISHMENTS) ? window.ESTABLISHMENTS : [];

    applyFilters();
    setupControls();
}

document.addEventListener('DOMContentLoaded', initialize);
