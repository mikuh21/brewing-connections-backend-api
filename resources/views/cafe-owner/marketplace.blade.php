@extends('cafe-owner.layouts.app')

@php
    $title = 'Store';
@endphp

@section('title', 'My Products - BrewHub')

@section('content')
<style>
    /* Match the landing page delivery-map modal dimensions and short-screen behavior. */
    .cafe-delivery-map-card {
        max-height: calc(100vh - 1rem);
        max-height: calc(100dvh - 1rem);
        display: flex;
        flex-direction: column;
    }
    .cafe-delivery-map-content {
        min-height: 0;
        overflow-y: auto;
    }
    .cafe-delivery-map-canvas {
        height: min(42vh, 340px) !important;
        min-height: 210px;
    }
    @media (max-height: 650px) {
        .cafe-delivery-map-card { max-height: calc(100dvh - 0.75rem); }
        .cafe-delivery-map-content { padding-top: 0.65rem; padding-bottom: 0.65rem; }
        .cafe-delivery-map-canvas { height: 34vh !important; min-height: 170px; }
    }
</style>
@php
    $productsMeta = $products->map(function ($product) {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'category' => $product->category,
            'stock_quantity' => $product->stock_quantity,
            'unit' => $product->unit,
            'moq' => $product->moq,
            'image_url' => $product->image_url,
            'is_active' => $product->is_active,
        ];
    })->values();

    $ownerTypeLabel = static function (?string $sellerType): string {
        return match (strtolower((string) $sellerType)) {
            'farm_owner' => 'Farm Owner',
            'cafe_owner' => 'Cafe Owner',
            'reseller' => 'Reseller',
            'roaster' => 'Roaster',
            default => 'Seller',
        };
    };
@endphp

<div
    class="cafe-marketplace-page"
    x-data="{
        showCreateModal: false,
        showEditModal: false,
        showMenuImportModal: false,
        showViewModal: false,
        showOrderModal: false,
        showOrderConfirmModal: false,
        showCancelOrderModal: false,
        cancelOrderAction: '',
        cancelOrderReference: '',
        isCreatingProduct: false,
        isSubmittingOrder: false,
        browseSellerTypeFilter: @js(in_array(request('seller_type_filter', 'all'), ['farm_owner', 'reseller', 'roaster'], true) ? request('seller_type_filter') : 'all'),
        editImagePreview: '',
        createImagePreview: '',
        menuImportImagePreview: '',
        isExtractingMenu: false,
        isSavingExtractedMenuItems: false,
        menuExtractionError: '',
        extractedMenuItems: [],
        menuImportCategories: [
            'Coffee Beans',
            'Ground Coffee',
            'Hot Drinks',
            'Iced Drinks',
            'Iced Blended Drinks',
            'Tea',
            'Rice Meals',
            'Pastas',
            'Appetizers',
            'Sandwiches',
            'Burgers',
            'Pastries',
        ],
        activeTab: @js(in_array(request('tab'), ['browse', 'orders'], true) ? request('tab') : 'my-listings'),
        searchTerm: '',
        csrfToken: '{{ csrf_token() }}',
        hiddenProductIds: [],
        productsMeta: {{ Js::from($productsMeta) }},
        updateUrlTemplate: '{{ route('cafe-owner.marketplace.products.update', ['product' => '__PRODUCT_ID__']) }}',
        visibilityUrlTemplate: '{{ route('cafe-owner.marketplace.products.visibility', ['product' => '__PRODUCT_ID__']) }}',
        menuExtractUrl: '{{ route('cafe-owner.marketplace.products.menu.extract') }}',
        menuConfirmUrl: '{{ route('cafe-owner.marketplace.products.menu.confirm') }}',
        createForm: {
            name: '',
            description: '',
            category: 'Hot Drinks',
            image_url: ''
        },
        form: {
            id: null,
            name: '',
            description: '',
            category: 'Coffee Beans',
            stock_quantity: '',
            unit: 'kg',
            moq: '1',
            image_url: ''
        },
        viewProduct: {
            id: null,
            name: '',
            description: '',
            category: '',
            stock_quantity: 0,
            unit: '',
            image_url: '',
            owner_label: '',
            owner_name: '',
            seller_type: ''
        },
        orderProduct: {
            id: null,
            name: '',
            category: '',
            unit: 'kg',
            stock_quantity: 0,
            moq: 1,
            price: 0,
            seller_type: '',
            seller_name: ''
        },
        defaultDeliveryAddress: @js((string) (Auth::user()->address ?? '')),
        defaultDeliveryPhone: @js((string) (Auth::user()->contact_number ?? '')),
        defaultReceiptEmail: @js((string) (Auth::user()->email ?? '')),
        orderForm: {
            quantity: 1,
            payment_mode: '',
            pickup_date: '',
            pickup_time: '',
            address: @js((string) (Auth::user()->address ?? '')),
            phone: @js((string) (Auth::user()->contact_number ?? '')),
            email: @js((string) (Auth::user()->email ?? '')),
            buyer_notes: '',
            delivery_latitude: null,
            delivery_longitude: null,
            delivery_location_label: ''
        },
        showDeliveryMap: false,
        deliveryMapStatus: 'Choose your delivery point on the map.',
        deliveryMapInstance: null,
        deliveryMarker: null,
        deliveryLeafletPromise: null,
        deliveryMapError: '',
        async loadDeliveryLeaflet() {
            if (window.L) return window.L;
            if (!this.deliveryLeafletPromise) {
                this.deliveryLeafletPromise = new Promise((resolve, reject) => {
                    if (!document.querySelector('link[data-cafe-delivery-leaflet]')) {
                        const link = document.createElement('link');
                        link.rel = 'stylesheet';
                        link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                        link.dataset.cafeDeliveryLeaflet = 'true';
                        document.head.appendChild(link);
                    }
                    const script = document.createElement('script');
                    script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                    script.onload = () => resolve(window.L);
                    script.onerror = () => reject(new Error('Unable to load the map. Please check your connection and try again.'));
                    document.head.appendChild(script);
                });
            }
            return this.deliveryLeafletPromise;
        },
        async openDeliveryMap() {
            this.showDeliveryMap = true;
            this.deliveryMapError = '';
            this.deliveryMapStatus = 'Loading map…';
            await this.$nextTick();
            try {
                const L = await this.loadDeliveryLeaflet();
                const hasPin = this.orderForm.delivery_latitude !== null && this.orderForm.delivery_longitude !== null;
                const initial = hasPin
                    ? [Number(this.orderForm.delivery_latitude), Number(this.orderForm.delivery_longitude)]
                    : [13.9411, 121.1634];
                if (!this.deliveryMapInstance) {
                    this.deliveryMapInstance = L.map(this.$refs.deliveryMap, { scrollWheelZoom: true }).setView(initial, hasPin ? 16 : 12);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors',
                        maxZoom: 19
                    }).addTo(this.deliveryMapInstance);
                    this.deliveryMapInstance.on('click', async (event) => {
                        this.setDeliveryPin(event.latlng.lat, event.latlng.lng);
                    });
                } else {
                    this.deliveryMapInstance.setView(initial, hasPin ? 16 : 12);
                }
                if (hasPin) {
                    this.setDeliveryPin(initial[0], initial[1], false);
                } else if (this.deliveryMarker) {
                    this.deliveryMapInstance.removeLayer(this.deliveryMarker);
                }
                setTimeout(() => this.deliveryMapInstance?.invalidateSize(), 180);
                this.deliveryMapStatus = hasPin ? 'Pinned location selected. You can move the pin if needed.' : 'Click the map or use your current location to place a pin.';
            } catch (error) {
                this.deliveryMapStatus = error?.message || 'Unable to load the map.';
            }
        },
        async setDeliveryPin(latitude, longitude, reverseGeocode = true) {
            this.orderForm.delivery_latitude = Number(latitude);
            this.orderForm.delivery_longitude = Number(longitude);
            this.deliveryMapError = '';
            if (this.deliveryMapInstance && window.L) {
                if (!this.deliveryMarker) {
                    this.deliveryMarker = window.L.marker([latitude, longitude], { draggable: true }).addTo(this.deliveryMapInstance);
                    this.deliveryMarker.on('dragend', (event) => {
                        const point = event.target.getLatLng();
                        this.setDeliveryPin(point.lat, point.lng);
                    });
                } else {
                    this.deliveryMarker.setLatLng([latitude, longitude]).addTo(this.deliveryMapInstance);
                }
            }
            if (reverseGeocode) this.orderForm.delivery_location_label = '';
            this.deliveryMapStatus = reverseGeocode ? 'Pin selected. Looking up address…' : 'Pinned location selected.';
            if (reverseGeocode) {
                try {
                    const url = new URL('https://nominatim.openstreetmap.org/reverse');
                    url.search = new URLSearchParams({
                        format: 'jsonv2',
                        lat: String(latitude),
                        lon: String(longitude),
                        zoom: '18',
                        addressdetails: '1'
                    }).toString();
                    const response = await fetch(url.toString(), { headers: { Accept: 'application/json' } });
                    if (response.ok) {
                        const data = await response.json();
                        this.orderForm.delivery_location_label = String(data.display_name || '').slice(0, 255);
                    }
                } catch (_) {}
            }
            this.deliveryMapStatus = this.orderForm.delivery_location_label
                ? 'Pinned: ' + this.orderForm.delivery_location_label
                : 'Pinned coordinates: ' + Number(latitude).toFixed(6) + ', ' + Number(longitude).toFixed(6);
        },
        useCurrentDeliveryLocation() {
            if (!navigator.geolocation) {
                this.deliveryMapStatus = 'Your browser does not support current location. Click the map to place a pin.';
                return;
            }
            this.deliveryMapStatus = 'Getting your current location…';
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.setDeliveryPin(position.coords.latitude, position.coords.longitude);
                    this.deliveryMapInstance?.setView([position.coords.latitude, position.coords.longitude], 16);
                },
                () => {
                    this.deliveryMapStatus = 'Could not get your location. Allow location access or click the map to place a pin.';
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        },
        openOrderModal(product) {
            this.orderProduct = {
                id: Number(product.id),
                name: product.name || '',
                category: product.category || '',
                unit: product.unit || 'kg',
                stock_quantity: Number(product.stock_quantity || 0),
                moq: Math.max(1, Number(product.moq || 1)),
                price: Number(product.order_price || product.price_per_unit || 0),
                weight_grams: Number(product.weight_grams || 0),
                seller_type: String(product.seller_type || '').toLowerCase(),
                seller_name: product.seller_name || 'Supplier'
            };
            this.orderForm = {
                quantity: Math.max(1, Number(product.moq || 1)),
                payment_mode: '',
                pickup_date: '',
                pickup_time: '',
                address: this.defaultDeliveryAddress,
                phone: this.defaultDeliveryPhone,
                email: this.defaultReceiptEmail,
                buyer_notes: '',
                delivery_latitude: null,
                delivery_longitude: null,
                delivery_location_label: ''
            };
            this.deliveryMapError = '';
            this.deliveryMapStatus = 'Choose your delivery point on the map.';
            this.showOrderModal = true;
        },
        get productTotal() {
            return Math.max(0, Number(this.orderForm.quantity || 0)) * Math.max(0, Number(this.orderProduct.price || 0));
        },
        get totalItemWeightGrams() {
            let grams = Number(this.orderProduct.weight_grams || 0);
            if (grams <= 0) {
                const unit = String(this.orderProduct.unit || '').trim().toLowerCase();
                const amount = Number.parseFloat(unit);
                if (Number.isFinite(amount) && unit.includes('kg')) grams = Math.round(amount * 1000);
                else if (Number.isFinite(amount) && unit.includes('g')) grams = Math.round(amount);
                else grams = 500;
            }
            return Math.max(0, grams) * Math.max(1, Number(this.orderForm.quantity || 1));
        },
        get deliveryFee() {
            // Delivery fees are paid separately by the consumer and are not included in the order total.
            return '0.00';
        },
        get orderTotal() {
            return (this.productTotal + Number(this.deliveryFee || 0)).toFixed(2);
        },
        requestOrderConfirmation(event) {
            event.preventDefault();
            const form = event.currentTarget;
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            if (this.orderForm.payment_mode === 'cod' && (this.orderForm.delivery_latitude === null || this.orderForm.delivery_longitude === null)) {
                this.deliveryMapError = 'Please pin and confirm your delivery location before submitting.';
                return;
            }
            this.deliveryMapError = '';
            this.showOrderConfirmModal = true;
        },
        confirmOrderSubmission() {
            if (!this.$refs.supplierOrderForm || this.isSubmittingOrder) return;
            this.showOrderConfirmModal = false;
            this.isSubmittingOrder = true;
            this.$nextTick(() => this.$refs.supplierOrderForm.submit());
        },
        openViewer(product) {
            this.viewProduct = {
                id: product.id ?? null,
                name: product.name || '',
                description: product.description || '',
                category: product.category || '',
                stock_quantity: Number(product.stock_quantity || 0),
                unit: product.unit || 'unit',
                moq: product.moq || 'N/A',
                image_url: product.image_url || '',
                owner_label: product.owner_label || 'Seller',
                owner_name: product.owner_name || 'Unknown',
                seller_type: String(product.seller_type || '').toLowerCase(),
                can_order: Boolean(product.can_order),
                order_price: Number(product.order_price || 0),
                weight_grams: Number(product.weight_grams || 0),
                seller_name: product.seller_name || product.owner_name || 'Supplier',
            };
            this.showViewModal = true;
        },
        openEditor(product) {
            this.form.id = product.id;
            this.form.name = product.name || '';
            this.form.description = product.description || '';
            this.form.category = product.category || 'Coffee Beans';
            this.form.stock_quantity = product.stock_quantity ?? 0;
            this.form.unit = product.unit || 'kg';
            this.form.moq = product.moq || 1;
            this.form.image_url = product.image_url || '';
            this.editImagePreview = product.image_url || '';
            this.showEditModal = true;
        },
        handleEditImageChange(event) {
            const file = event.target.files && event.target.files[0] ? event.target.files[0] : null;
            if (!file) {
                this.editImagePreview = this.form.image_url || '';
                return;
            }

            this.editImagePreview = URL.createObjectURL(file);
        },
        handleCreateImageChange(event) {
            const file = event.target.files && event.target.files[0] ? event.target.files[0] : null;
            if (!file) {
                this.createImagePreview = '';
                return;
            }

            this.createImagePreview = URL.createObjectURL(file);
        },
        clearEditImage() {
            this.editImagePreview = '';
            if (this.$refs.editImageInput) {
                this.$refs.editImageInput.value = '';
            }
        },
        clearCreateImage() {
            this.createImagePreview = '';
            if (this.$refs.createImageInput) {
                this.$refs.createImageInput.value = '';
            }
        },
        openMenuImportModal() {
            this.showMenuImportModal = true;
            this.menuExtractionError = '';
            this.extractedMenuItems = [];
            this.menuImportImagePreview = '';
            if (this.$refs.menuImportImageInput) {
                this.$refs.menuImportImageInput.value = '';
            }
        },
        closeMenuImportModal() {
            this.showMenuImportModal = false;
            this.menuExtractionError = '';
            this.extractedMenuItems = [];
            this.menuImportImagePreview = '';
            this.isExtractingMenu = false;
            this.isSavingExtractedMenuItems = false;
            if (this.$refs.menuImportImageInput) {
                this.$refs.menuImportImageInput.value = '';
            }
        },
        handleMenuImportImageChange(event) {
            const file = event.target.files && event.target.files[0] ? event.target.files[0] : null;
            if (!file) {
                this.menuImportImagePreview = '';
                return;
            }

            this.menuImportImagePreview = URL.createObjectURL(file);
            this.menuExtractionError = '';
        },
        async extractMenuItemsFromImage() {
            const selectedFile = this.$refs.menuImportImageInput?.files?.[0];
            if (!selectedFile) {
                this.menuExtractionError = 'Please select a menu image first.';
                return;
            }

            this.menuExtractionError = '';
            this.isExtractingMenu = true;

            const formData = new FormData();
            formData.append('menu_image', selectedFile);

            try {
                const response = await fetch(this.menuExtractUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                    body: formData,
                });

                const payload = await response.json().catch(() => ({}));
                if (!response.ok) {
                    throw new Error(payload.message || 'Unable to extract items from this menu image.');
                }

                const extractedItems = Array.isArray(payload.items) ? payload.items : [];
                if (extractedItems.length === 0) {
                    throw new Error('No menu items were detected. Please try another image.');
                }

                this.extractedMenuItems = extractedItems.map((item) => ({
                    name: String(item.name || '').trim(),
                    description: String(item.description || '').trim(),
                    category: this.normalizeMenuCategory(item.category),
                    confidence: typeof item.confidence === 'number' ? item.confidence : undefined,
                }));
            } catch (error) {
                this.menuExtractionError = error?.message || 'Unable to extract items from this menu image.';
            } finally {
                this.isExtractingMenu = false;
            }
        },
        addExtractedMenuItem() {
            this.extractedMenuItems.push({
                name: '',
                description: '',
                category: 'Hot Drinks',
            });
        },
        normalizeMenuCategory(category) {
            const normalized = String(category || '').trim();
            if (this.menuImportCategories.includes(normalized)) {
                return normalized;
            }

            const lower = normalized.toLowerCase();
            if (lower === 'brewed coffee') {
                return 'Hot Drinks';
            }
            if (lower === 'cold drinks') {
                return 'Iced Drinks';
            }
            if (lower === 'other') {
                return 'Hot Drinks';
            }

            return 'Hot Drinks';
        },
        removeExtractedMenuItem(index) {
            this.extractedMenuItems.splice(index, 1);
        },
        async saveExtractedMenuItems() {
            if (!Array.isArray(this.extractedMenuItems) || this.extractedMenuItems.length === 0) {
                this.menuExtractionError = 'No extracted items to save.';
                return;
            }

            const normalizedItems = this.extractedMenuItems.map((item) => ({
                name: String(item.name || '').trim(),
                description: String(item.description || '').trim(),
                category: this.normalizeMenuCategory(item.category),
            })).filter((item) => item.name !== '');

            if (normalizedItems.length === 0) {
                this.menuExtractionError = 'Please keep at least one item with a product name.';
                return;
            }

            this.menuExtractionError = '';
            this.isSavingExtractedMenuItems = true;

            try {
                const response = await fetch(this.menuConfirmUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ items: normalizedItems }),
                });

                const payload = await response.json().catch(() => ({}));
                if (!response.ok) {
                    throw new Error(payload.message || 'Failed to import extracted menu items.');
                }

                window.location.reload();
            } catch (error) {
                this.menuExtractionError = error?.message || 'Failed to import extracted menu items.';
            } finally {
                this.isSavingExtractedMenuItems = false;
            }
        },
        init() {
            const hashToTab = {
                '#my-listings': 'my-listings',
                '#products': 'my-listings',
                '#browse': 'browse',
                    };

            const applyHashTab = () => {
                const nextTab = hashToTab[String(window.location.hash || '').toLowerCase()];
                if (nextTab) {
                    this.activeTab = nextTab;
                }
            };

            applyHashTab();
            window.addEventListener('hashchange', applyHashTab);

            this.hiddenProductIds = this.productsMeta
                .filter((product) => product.is_active === false)
                .map((product) => Number(product.id))
                .filter((id) => Number.isFinite(id));
        },
        isHidden(productId) {
            return this.hiddenProductIds.includes(Number(productId));
        },
        async updateProductVisibility(productId, isActive) {
            const normalizedId = Number(productId);
            const url = this.visibilityUrlTemplate.replace('__PRODUCT_ID__', String(normalizedId));

            const response = await fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ is_active: isActive }),
            });

            if (!response.ok) {
                throw new Error('Failed to update product visibility.');
            }

            this.productsMeta = this.productsMeta.map((product) => {
                if (Number(product.id) === normalizedId) {
                    return { ...product, is_active: isActive };
                }
                return product;
            });
        },
        async hideProduct(productId) {
            const normalizedId = Number(productId);
            try {
                await this.updateProductVisibility(normalizedId, false);
                if (!this.hiddenProductIds.includes(normalizedId)) {
                    this.hiddenProductIds.push(normalizedId);
                }
            } catch (_error) {
                alert('Unable to hide product right now.');
            }
        },
        async unhideProduct(productId) {
            const normalizedId = Number(productId);
            try {
                await this.updateProductVisibility(normalizedId, true);
                this.hiddenProductIds = this.hiddenProductIds.filter((id) => id !== normalizedId);
            } catch (_error) {
                alert('Unable to unhide product right now.');
            }
        },
        stockMeta(product) {
            const stock = Number(product.stock_quantity || 0);
            if (stock <= 0) {
                return { qty: stock, label: 'Out of Stock', class: 'bg-red-100 text-red-700' };
            }
            if (stock <= 10) {
                return { qty: stock, label: 'Low Stock', class: 'bg-amber-100 text-amber-700' };
            }
            return { qty: stock, label: 'In Stock', class: 'bg-green-100 text-green-700' };
        },
        isMenuCategory(category) {
            const normalized = String(category || '').trim().toLowerCase();
            return normalized !== 'coffee beans' && normalized !== 'ground coffee';
        },
        displayType(category) {
            const normalized = String(category || '').trim();
            return normalized || 'Coffee Beans';
        },
        hiddenProducts() {
            const term = this.searchTerm.toLowerCase().trim();
            return this.productsMeta.filter((product) => {
                const haystack = `${product.name} ${product.category}`.toLowerCase();
                return this.isHidden(product.id) && haystack.includes(term);
            });
        },
        hasBrowseProducts() {
            const term = String(this.searchTerm || '').toLowerCase().trim();
            return Array.from(document.querySelectorAll('#browseMarketplaceContent [data-marketplace-product]')).some((product) => {
                const matchesSearch = (product.dataset.search || '').includes(term);
                const matchesSeller = this.browseSellerTypeFilter === 'all'
                    || (product.dataset.sellerType || '') === this.browseSellerTypeFilter;
                return matchesSearch && matchesSeller;
            });
        },
        setBrowseSellerType(filter) {
            this.browseSellerTypeFilter = filter;
            const url = new URL(window.location.href);
            if (filter === 'all') {
                url.searchParams.delete('seller_type_filter');
            } else {
                url.searchParams.set('seller_type_filter', filter);
            }
            url.searchParams.set('tab', 'browse');
            url.hash = 'browse';
            window.history.replaceState({}, '', url.toString());
        },
        formAction() {
            return this.updateUrlTemplate.replace('__PRODUCT_ID__', this.form.id);
        }
    }"
>
    <div class="marketplace-header mb-8 flex items-start justify-between gap-4 flex-wrap">
        <div>
            <h1 class="text-3xl font-display font-bold text-[#3A2E22] mb-1">
                My <span class="italic text-[#4A6741]">Products</span>
            </h1>
            <p class="text-[#9E8C78] text-sm font-medium">
                {{ $products->total() }} {{ \Illuminate\Support\Str::plural('product', $products->total()) }} in your catalog
            </p>
        </div>

        <div class="marketplace-actions flex items-center gap-2">
            <button
                type="button"
                @click="openMenuImportModal()"
                class="px-4 py-2 rounded-lg border border-[#4A6741] text-[#4A6741] text-sm font-semibold hover:bg-[#F5F0E8] transition-colors"
            >
                Upload Menu Image
            </button>

            <button
                type="button"
                @click="showCreateModal = true; createImagePreview = ''; isCreatingProduct = false"
                class="px-4 py-2 rounded-lg bg-[#4A6741] text-white text-sm font-semibold hover:bg-[#3A2E22] transition-colors"
            >
                <span class="mr-1.5">+</span>Add Product
            </button>
        </div>
</div>

@if(session('status'))
    <div id="success-alert" class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 flex items-start gap-3">
        <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <div class="flex-1">
            <p class="text-sm font-medium text-green-800">{{ session('status') }}</p>
        </div>
        <button onclick="document.getElementById('success-alert').remove()" class="text-green-600 hover:text-green-900 flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
@endif

@if($errors->any())
    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="mb-6 border-b border-gray-200 pb-4">
    <div class="flex gap-2">
        <button
            type="button"
            @click="activeTab = 'my-listings'"
            class="filter-tab px-4 py-2 text-sm font-medium transition-colors"
            :style="activeTab === 'my-listings'
                ? 'color: #3B2F2F; border-bottom: 3px solid #3B2F2F; background: #F5F0E8;'
                : 'color: #9E8C78; border-bottom: 3px solid transparent;'"
        >
            My Listings
        </button>

        <button
            type="button"
            @click="activeTab = 'browse'"
            class="filter-tab px-4 py-2 text-sm font-medium transition-colors"
            :style="activeTab === 'browse'
                ? 'color: #3B2F2F; border-bottom: 3px solid #3B2F2F; background: #F5F0E8;'
                : 'color: #9E8C78; border-bottom: 3px solid transparent;'"
        >
            Browse
        </button>

        <button
            type="button"
            @click="activeTab = 'orders'"
            class="filter-tab px-4 py-2 text-sm font-medium transition-colors"
            :style="activeTab === 'orders'
                ? 'color: #3B2F2F; border-bottom: 3px solid #3B2F2F; background: #F5F0E8;'
                : 'color: #9E8C78; border-bottom: 3px solid transparent;'"
        >
            My Orders
        </button>
    </div>
</div>

<div x-show="activeTab === 'my-listings'">

<div class="bg-white rounded-xl shadow-sm p-4 mb-6">
    <form method="GET" action="{{ route('cafe-owner.marketplace') }}" class="flex flex-col md:flex-row gap-3 md:items-center">
        <div class="relative flex-1">
            <svg class="w-3.5 h-3.5 absolute left-2.5 top-1/2 -translate-y-1/2 text-[#9E8C78]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" x-model="searchTerm" autocomplete="off" placeholder="Search products..." class="w-full pl-8 pr-2.5 py-1.5 text-xs bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#4A6741] focus:border-transparent" />
        </div>

        <select name="type" onchange="this.form.submit()" class="text-xs border border-gray-200 rounded-lg px-2.5 py-1.5 bg-white text-[#3A2E22] focus:outline-none focus:ring-2 focus:ring-[#4A6741]">
            <option value="">All Types</option>
            <option value="coffee_beans" @selected(request('type') === 'coffee_beans')>Coffee Beans</option>
            <option value="ground_coffee" @selected(request('type') === 'ground_coffee')>Ground Coffee</option>
            <option value="hot_drinks" @selected(request('type') === 'hot_drinks')>Hot Drinks</option>
            <option value="iced_drinks" @selected(request('type') === 'iced_drinks')>Iced Drinks</option>
            <option value="iced_blended_drinks" @selected(request('type') === 'iced_blended_drinks')>Iced Blended Drinks</option>
            <option value="tea" @selected(request('type') === 'tea')>Tea</option>
            <option value="rice_meals" @selected(request('type') === 'rice_meals')>Rice Meals</option>
            <option value="pastas" @selected(request('type') === 'pastas')>Pastas</option>
            <option value="appetizers" @selected(request('type') === 'appetizers')>Appetizers</option>
            <option value="sandwiches" @selected(request('type') === 'sandwiches')>Sandwiches</option>
            <option value="burgers" @selected(request('type') === 'burgers')>Burgers</option>
            <option value="pastries" @selected(request('type') === 'pastries')>Pastries</option>
        </select>
    </form>
</div>

@if($products->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach($products as $product)
            @php
                $stock = (int) ($product->stock_quantity ?? 0);
                if ($stock <= 0) {
                    $stockLabel = 'Out of Stock';
                    $stockClass = 'bg-red-100 text-red-700';
                } elseif ($stock <= 10) {
                    $stockLabel = 'Low Stock';
                    $stockClass = 'bg-amber-100 text-amber-700';
                } else {
                    $stockLabel = 'In Stock';
                    $stockClass = 'bg-green-100 text-green-700';
                }

                $displayType = trim((string) ($product->category ?? '')) ?: 'Coffee Beans';
                $isMenuProduct = !in_array(strtolower($displayType), ['coffee beans', 'ground coffee'], true);
            @endphp

            <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition overflow-hidden"
                data-search="{{ strtolower(($product->name ?? '') . ' ' . ($product->category ?? '')) }}"
                x-show="($el.dataset.search || '').includes(searchTerm.toLowerCase().trim()) && !isHidden({{ $product->id }})">
                <div class="relative">
                    @if($product->image_url)
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-36 object-cover" />
                    @else
                        <div class="w-full h-36 bg-gray-100 flex flex-col items-center justify-center rounded-t-xl">
                            <p class="text-xs text-[#9E8C78]">No image available</p>
                        </div>
                    @endif
                </div>

                <div class="p-3">
                    <div class="flex items-start justify-between gap-2 mb-1">
                        <h3 class="font-semibold text-sm text-[#2C1A0E] leading-tight truncate" style="font-family: 'Poppins', sans-serif;">{{ $product->name }}</h3>
                        @if(!$isMenuProduct)
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $stockClass }} whitespace-nowrap">{{ $stockLabel }}</span>
                        @endif
                    </div>

                    <p class="text-xs italic text-[#9E8C78] mb-1.5">{{ $displayType }}</p>

                    @if(!$isMenuProduct)
                        <div class="mb-2"><p class="text-xs text-[#9E8C78]">{{ $stock }} units available</p></div>
                    @else
                        <div class="mb-2"><p class="text-xs text-[#6B5B4A] truncate">{{ trim((string) ($product->description ?? '')) ?: 'No description provided.' }}</p></div>
                    @endif

                    <div class="flex justify-end gap-2 mt-2">
                        <button
                            type="button"
                            @click="openEditor({{ json_encode(['id' => $product->id, 'name' => $product->name, 'description' => $product->description, 'category' => $product->category, 'stock_quantity' => $product->stock_quantity, 'unit' => $product->unit, 'image_url' => $product->image_url]) }})"
                            class="w-8 h-8 inline-flex items-center justify-center rounded-lg border border-[#4A6741] text-[#4A6741] hover:bg-[#4A6741] hover:text-white transition-colors"
                            title="Edit product"
                            aria-label="Edit product"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </button>

                        <button
                            type="button"
                            @click="hideProduct({{ $product->id }})"
                            class="w-8 h-8 inline-flex items-center justify-center rounded-lg border border-gray-300 text-[#6B5B4A] hover:text-red-600 hover:border-red-200 hover:bg-red-50 transition-colors"
                            title="Hide product"
                            aria-label="Hide product"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-8">
        {{ $products->links() }}
    </div>

    <div x-show="hiddenProducts().length > 0" x-cloak class="mt-8">
        <div class="border-t border-dashed border-[#D8CFC2] pt-4 mb-3 flex items-center justify-between gap-3">
            <h3 class="text-sm font-semibold text-[#3A2E22]">Hidden Products</h3>
            <p class="text-xs text-[#9E8C78]">Temporarily hidden items</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            <template x-for="product in hiddenProducts()" :key="`hidden-${product.id}`">
                <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition overflow-hidden border border-[#E7DED1]">
                    <div class="relative">
                        <template x-if="product.image_url">
                            <img :src="product.image_url" :alt="product.name" class="w-full h-36 object-cover" />
                        </template>
                        <template x-if="!product.image_url">
                            <div class="w-full h-36 bg-gray-100 flex flex-col items-center justify-center rounded-t-xl">
                                <p class="text-xs text-[#9E8C78]">No image available</p>
                            </div>
                        </template>
                    </div>

                    <div class="p-3">
                        <div class="flex items-start justify-between gap-2 mb-1">
                            <h3 class="font-semibold text-sm text-[#2C1A0E] leading-tight truncate" style="font-family: 'Poppins', sans-serif;" x-text="product.name"></h3>
                            <template x-if="!isMenuCategory(product.category)">
                                <span class="text-xs px-2 py-0.5 rounded-full whitespace-nowrap" :class="stockMeta(product).class" x-text="stockMeta(product).label"></span>
                            </template>
                        </div>

                        <p class="text-xs italic text-[#9E8C78] mb-1.5" x-text="displayType(product.category)"></p>

                        <template x-if="!isMenuCategory(product.category)">
                            <div class="mb-2"><p class="text-xs text-[#9E8C78]"><span x-text="stockMeta(product).qty"></span> units available</p></div>
                        </template>
                        <template x-if="isMenuCategory(product.category)">
                            <div class="mb-2"><p class="text-xs text-[#6B5B4A] truncate" x-text="(String(product.description || '').trim() || 'No description provided.')"></p></div>
                        </template>

                        <div class="flex justify-end gap-2 mt-2">
                            <button
                                type="button"
                                @click="openEditor(product)"
                                class="w-8 h-8 inline-flex items-center justify-center rounded-lg border border-[#4A6741] text-[#4A6741] hover:bg-[#4A6741] hover:text-white transition-colors"
                                title="Edit product"
                                aria-label="Edit product"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>

                            <button
                                type="button"
                                @click="unhideProduct(product.id)"
                                class="w-8 h-8 inline-flex items-center justify-center rounded-lg border border-[#4A6741] text-[#4A6741] hover:bg-[#4A6741] hover:text-white transition-colors"
                                title="Unhide product"
                                aria-label="Unhide product"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
@else
    <div class="bg-white rounded-xl shadow-sm p-12 text-center">
        <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293L6.586 13H4"/>
        </svg>
        <p class="text-lg font-display font-semibold text-gray-500">No products found</p>
        <p class="text-sm text-gray-400 mt-1">Try adjusting your search or filter.</p>
    </div>
@endif

</div>

<div x-show="activeTab === 'browse'" class="mt-6">
<div class="mb-4">
    <h2 class="text-xl font-display font-bold text-[#3A2E22]">Browse Marketplace</h2>
    <p class="text-[#9E8C78] text-xs">Products listed by other sellers</p>
</div>

@php
    $browseSellerTypeFilter = request('seller_type_filter', 'all');
    $browseSellerTypeOptions = [
        'all' => 'All Products',
        'farm_owner' => 'Farm Products',
        'reseller' => 'Reseller Products',
        'roaster' => 'Roaster Products',
    ];
@endphp

<div id="browseMarketplaceContent">
<div class="mb-5 flex flex-wrap gap-2">
    @foreach($browseSellerTypeOptions as $filterKey => $filterLabel)
        <button
            type="button"
            @click="setBrowseSellerType('{{ $filterKey }}')"
            :class="browseSellerTypeFilter === '{{ $filterKey }}' ? 'border-[#4A6741] bg-[#EEF4EA] text-[#2C4A2E]' : 'border-[#E7DCCF] bg-white text-[#8E7B67] hover:bg-[#F8F4EE]'"
            class="inline-flex min-h-[38px] items-center justify-center rounded-full border px-4 py-2 text-xs font-semibold transition-colors"
            :aria-pressed="browseSellerTypeFilter === '{{ $filterKey }}' ? 'true' : 'false'"
        >
            {{ $filterLabel }}
        </button>
    @endforeach
</div>

@if(($marketplaceProducts ?? collect())->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach($marketplaceProducts as $product)
            @php
                $displayType = trim((string) ($product->category ?? '')) ?: 'Coffee Beans';
                $ownerLabel = $ownerTypeLabel($product->seller_type);
                $ownerName = $product->seller_name ?: 'Unknown';
                $sellerType = strtolower((string) ($product->seller_type ?? ''));
                $isCafeOwnerSeller = $sellerType === 'cafe_owner';
                $orderPrice = (float) ($product->order_price ?? $product->price_per_unit ?? 0);
                $canOrderProduct = in_array($sellerType, ['farm_owner', 'reseller', 'roaster'], true)
                    && in_array($displayType, ['Coffee Beans', 'Ground Coffee'], true)
                    && $orderPrice > 0
                    && (int) ($product->stock_quantity ?? 0) > 0;
            @endphp

            <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition overflow-hidden"
                data-marketplace-product
                data-search="{{ strtolower(($product->name ?? '') . ' ' . ($product->category ?? '')) }}"
                data-seller-type="{{ strtolower((string) ($product->seller_type ?? '')) }}"
                x-show="(($el.dataset.search || '').includes(searchTerm.toLowerCase().trim())) && (browseSellerTypeFilter === 'all' || ($el.dataset.sellerType || '') === browseSellerTypeFilter)">
                <div class="relative">
                    @if($product->image_url)
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-36 object-cover" />
                    @else
                        <div class="w-full h-36 bg-gray-100 flex flex-col items-center justify-center rounded-t-xl">
                            <p class="text-xs text-[#9E8C78]">No image available</p>
                        </div>
                    @endif
                </div>

                <div class="p-3">
                    <div class="flex items-start justify-between gap-2 mb-1">
                        <h3 class="font-semibold text-sm text-[#2C1A0E] leading-tight truncate" style="font-family: 'Poppins', sans-serif;">{{ $product->name }}</h3>
                        <button
                            type="button"
                            @click='openViewer({{ json_encode([
                                "id" => $product->id,
                                "name" => $product->name,
                                "description" => $product->description,
                                "category" => $product->category,
                                "stock_quantity" => $product->stock_quantity,
                                "unit" => $product->unit,
                                "moq" => $product->moq,
                                "image_url" => $product->image_url,
                                "owner_label" => $ownerLabel,
                                "owner_name" => $ownerName,
                                "seller_type" => $sellerType,
                                "can_order" => $canOrderProduct,
                                "order_price" => $orderPrice,
                                "weight_grams" => $product->weight_grams ?? null,
                                "seller_name" => $ownerName,
                            ]) }})'
                            class="w-8 h-8 inline-flex items-center justify-center rounded-lg border border-[#4A6741] text-[#4A6741] hover:bg-[#4A6741] hover:text-white transition-colors"
                            title="View full details"
                            aria-label="View full details"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>

                    <p class="text-xs italic text-[#9E8C78] mb-1.5">{{ $displayType }}</p>
                    <p class="text-[11px] text-[#6B5B4A] mb-2">
                        Seller: <span class="font-semibold text-[#3A2E22]">{{ $ownerLabel }}</span> -
                        <span class="font-semibold text-[#3A2E22]">{{ $ownerName }}</span>
                    </p>

                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <p class="text-sm font-semibold text-[#3A2E22]">₱{{ number_format($orderPrice, 2) }} <span class="text-xs font-normal text-[#9E8C78]">/ {{ $product->unit ?? 'kg' }}</span></p>
                        </div>
                        <p class="text-xs text-[#9E8C78]">{{ (int) ($product->stock_quantity ?? 0) }} available</p>
                    </div>

                    @if($canOrderProduct)
                        <button
                            type="button"
                            @click='openOrderModal({{ json_encode([
                                "id" => $product->id,
                                "name" => $product->name,
                                "category" => $product->category,
                                "unit" => $product->unit ?: "kg",
                                "stock_quantity" => $product->stock_quantity,
                                "moq" => $product->moq,
                                "order_price" => $orderPrice,
                                "weight_grams" => $product->weight_grams ?? null,
                                "seller_type" => $sellerType,
                                "seller_name" => $ownerName,
                            ]) }})'
                            class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-[#4A6741] px-3 py-2 text-xs font-semibold text-white hover:bg-[#3A2E22] transition-colors"
                        >
                            <span>Order / Reserve</span>
                        </button>
                    @else
                        <button type="button" disabled class="w-full rounded-lg bg-gray-100 px-3 py-2 text-xs font-semibold text-gray-400 cursor-not-allowed">
                            Unavailable
                        </button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

@else
    <div class="bg-white rounded-xl shadow-sm p-12 text-center">
        <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293L6.586 13H4m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5"/>
        </svg>
        <p class="text-lg font-display font-semibold text-gray-500">No products found</p>
        <p class="text-sm text-gray-400 mt-1">There are currently no products available for this seller category. Try another filter.</p>
    </div>
@endif
<div x-cloak x-show="!hasBrowseProducts()" class="bg-white rounded-xl shadow-sm p-12 text-center">
    <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0h-2.586a2 2 0 00-.707.293l-2.414 2.414a2 2 0 01-.707.293h-3.172a2 2 0 01-.707-.293L6.586 13H4m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5"/>
    </svg>
    <p class="text-lg font-display font-semibold text-gray-500">No products found</p>
    <p class="text-sm text-gray-400 mt-1">There are currently no products available for this seller category. Try another filter.</p>
</div>
</div>
</div>

<div x-show="activeTab === 'orders'" id="orders" class="mt-6 space-y-4">
    <div class="mb-4">
        <h2 class="text-xl font-display font-bold text-[#3A2E22]">My Orders</h2>
        <p class="text-xs text-[#9E8C78]">Track coffee bean and ground coffee reservations sent to suppliers.</p>
    </div>

    @forelse(($orders ?? collect()) as $order)
        @php
            $orderMetadata = json_decode((string) ($order->notes ?? ''), true);
            $orderMetadata = is_array($orderMetadata) ? $orderMetadata : [];
            $orderStatus = strtolower((string) ($order->status ?? 'pending'));
            $orderPaymentMode = strtolower((string) ($order->payment_mode ?? ($orderMetadata['payment_mode'] ?? 'pickup')));
            $orderProductTotal = (float) ($order->total_price ?? 0);
            $orderDeliveryFee = (float) ($order->delivery_fee ?? ($orderMetadata['delivery_fee'] ?? 0));
            $orderTotalAmount = (float) ($order->total_amount ?? ($orderProductTotal + $orderDeliveryFee));
            $orderProduct = $order->product;
            $orderSupplierName = optional(optional($orderProduct)->seller)->name ?: 'Supplier';
            $receiptToken = (string) ($orderMetadata['receipt_token'] ?? '');
        @endphp
        <div class="rounded-xl border border-[#E7DED1] bg-white p-4 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-[11px] uppercase tracking-wide text-[#9E8C78]">BRH-ORDER-{{ str_pad((string) $order->id, 6, '0', STR_PAD_LEFT) }}</p>
                    <h3 class="mt-1 text-sm font-semibold text-[#3A2E22]">{{ $orderProduct->name ?? 'Product unavailable' }}</h3>
                    <p class="mt-1 text-xs text-[#8E7B67]">{{ $orderProduct->category ?? 'Coffee product' }} · Supplier: {{ $orderSupplierName }}</p>
                </div>
                <span class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-semibold
                    {{ $orderStatus === 'confirmed' ? 'bg-blue-50 text-blue-700' : ($orderStatus === 'completed' ? 'bg-green-50 text-green-700' : (in_array($orderStatus, ['cancelled', 'canceled'], true) ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700')) }}">
                    {{ ucfirst($orderStatus) }}
                </span>
            </div>
            <div class="mt-3 grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                <div><p class="text-[#9E8C78]">Quantity</p><p class="mt-1 font-semibold text-[#3A2E22]">{{ (int) $order->quantity }} {{ $orderProduct->unit ?? 'kg' }}</p></div>
                <div><p class="text-[#9E8C78]">Mode of Payment</p><p class="mt-1 font-semibold text-[#3A2E22]">{{ $orderPaymentMode === 'cod' ? 'Cash on Delivery' : 'Pickup' }}</p></div>
                <div><p class="text-[#9E8C78]">Product Total</p><p class="mt-1 font-semibold text-[#3A2E22]">₱{{ number_format($orderProductTotal, 2) }}</p></div>
                <div><p class="text-[#9E8C78]">Submitted</p><p class="mt-1 font-semibold text-[#3A2E22]">{{ optional($order->created_at)->format('M d, Y') }}</p></div>
            </div>
            @if($orderPaymentMode === 'cod')
                <div class="mt-3 rounded-lg bg-[#F8F4ED] p-3 space-y-2 text-xs">
                    <div class="flex items-center justify-between gap-3"><span class="text-[#8E7B67]">Estimated Delivery Fee</span><span class="font-semibold text-[#3A2E22]">₱{{ number_format($orderDeliveryFee, 2) }}</span></div>
                    <div class="flex items-center justify-between gap-3 border-t border-[#E7DED1] pt-2"><span class="font-semibold text-[#3A2E22]">Total Amount</span><span class="font-bold text-[#2C4A2E]">₱{{ number_format($orderTotalAmount, 2) }}</span></div>
                    @if(!empty($orderMetadata['address']))
                        <p class="pt-1 text-[#6B5B4A]"><span class="font-semibold">Delivery address:</span> {{ $orderMetadata['address'] }}</p>
                    @endif
                </div>
            @else
                <p class="mt-3 text-xs text-[#6B5B4A]">Preferred pickup: {{ $order->pickup_date ? \Illuminate\Support\Carbon::parse($order->pickup_date)->format('M d, Y') : 'Not set' }}{{ $order->pickup_time ? ' at ' . \Illuminate\Support\Carbon::parse($order->pickup_time)->format('h:i A') : '' }}</p>
            @endif
            @if(!empty($orderMetadata['buyer_notes']))
                <p class="mt-3 text-xs text-[#6B5B4A]"><span class="font-semibold">Your note:</span> {{ $orderMetadata['buyer_notes'] }}</p>
            @endif
            <div class="mt-4 flex flex-wrap justify-end gap-2">
                @if($receiptToken !== '')
                    <a href="{{ route('reservations.orders.receipt', ['order' => $order->id, 'token' => $receiptToken]) }}" target="_blank" rel="noopener" class="rounded-lg border border-[#4A6741] px-3 py-2 text-xs font-semibold text-[#4A6741] hover:bg-[#EEF4EA]">View Receipt</a>
                @endif
                @if($orderStatus === 'pending')
                    <button
                        type="button"
                        data-cancel-url="{{ route('cafe-owner.marketplace.orders.cancel', $order) }}"
                        data-order-reference="{{ $order->product->name ?? 'this order' }}"
                        @click="cancelOrderAction = $el.dataset.cancelUrl; cancelOrderReference = $el.dataset.orderReference; showCancelOrderModal = true"
                        class="rounded-lg border border-red-200 px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-50"
                    >Cancel Order</button>
                @endif
            </div>
        </div>
    @empty
        <div class="rounded-xl border border-[#E7DED1] bg-white p-10 text-center shadow-sm">
            <p class="text-sm font-semibold text-[#3A2E22]">No orders yet</p>
            <p class="mt-1 text-xs text-[#9E8C78]">Browse supplier listings and select Order / Reserve to place your first request.</p>
            <button type="button" @click="activeTab = 'browse'" class="mt-4 rounded-lg bg-[#4A6741] px-4 py-2 text-xs font-semibold text-white hover:bg-[#3A2E22]">Browse Suppliers</button>
        </div>
    @endforelse
</div>

<div
        x-show="showViewModal"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
    >
        <div @click.away="showViewModal = false" class="bg-white rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden">
            <div class="p-4 border-b border-[#E7DED1] flex items-center justify-between">
                <h2 class="text-base font-display font-bold text-[#3A2E22]">Product Details</h2>
                <button type="button" @click="showViewModal = false" class="text-gray-500 hover:text-gray-800 text-2xl leading-none">&times;</button>
            </div>

            <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <template x-if="viewProduct.image_url">
                        <img :src="viewProduct.image_url" :alt="viewProduct.name" class="w-full h-56 object-cover rounded-lg border border-[#E7DED1]" />
                    </template>
                    <template x-if="!viewProduct.image_url">
                        <div class="w-full h-56 bg-gray-100 flex items-center justify-center rounded-lg border border-[#E7DED1]">
                            <p class="text-xs text-[#9E8C78]">No image available</p>
                        </div>
                    </template>
                </div>

                <div class="space-y-2">
                    <h3 class="text-lg font-display font-bold text-[#2C1A0E]" x-text="viewProduct.name || 'Unnamed Product'"></h3>
                    <p class="text-xs italic text-[#9E8C78]" x-text="displayType(viewProduct.category)"></p>
                    <p class="text-[12px] text-[#6B5B4A]">
                        Seller: <span class="font-semibold text-[#3A2E22]" x-text="viewProduct.owner_label"></span> -
                        <span class="font-semibold text-[#3A2E22]" x-text="viewProduct.owner_name"></span>
                    </p>

                    <div class="pt-2 space-y-1">
                        
                        <p x-show="viewProduct.seller_type !== 'cafe_owner'" class="text-sm text-[#3A2E22]"><span class="font-semibold">Stock:</span> <span x-text="Number(viewProduct.stock_quantity || 0)"></span> units</p>
                        <p x-show="viewProduct.seller_type !== 'cafe_owner'" class="text-sm text-[#3A2E22]"><span class="font-semibold">Unit:</span> <span x-text="viewProduct.unit || 'unit'"></span></p>
                        
                    </div>
                </div>

                <div class="md:col-span-2 pt-2 border-t border-[#EFE7DA]">
                    <h4 class="text-sm font-semibold text-[#3A2E22] mb-1">Description</h4>
                    <p class="text-sm text-[#6B5B4A] leading-relaxed" x-text="(viewProduct.description || '').trim() || 'No description provided.'"></p>
                </div>
            </div>

            <div class="px-4 pb-4 flex flex-wrap justify-end gap-2">
                <button type="button" @click="showViewModal = false" class="px-4 py-2 text-sm rounded-lg border border-[#D8CBBB] bg-white text-[#3A2E22] hover:bg-[#F8F4ED] transition-colors">Close</button>
                <button
                    x-show="viewProduct.can_order"
                    type="button"
                    @click="openOrderModal({ id: viewProduct.id, name: viewProduct.name, category: viewProduct.category, unit: viewProduct.unit || 'kg', stock_quantity: viewProduct.stock_quantity, moq: viewProduct.moq, order_price: viewProduct.order_price, weight_grams: viewProduct.weight_grams, seller_type: viewProduct.seller_type, seller_name: viewProduct.seller_name }); showViewModal = false"
                    class="px-4 py-2 text-sm font-semibold rounded-lg bg-[#4A6741] text-white hover:bg-[#3A2E22] transition-colors"
                >
                    Order / Reserve
                </button>
            </div>
        </div>
    </div>

<div
    x-show="showOrderModal"
    x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
>
    <div @click.away="if (!isSubmittingOrder) showOrderModal = false" class="bg-white rounded-xl shadow-2xl w-full max-w-xl p-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base font-display font-bold text-[#3A2E22]">Order / Reserve Coffee</h2>
                <p class="mt-1 text-xs text-[#9E8C78]">Submit a request to the supplier. Stock is reserved when the supplier confirms.</p>
            </div>
            <button type="button" @click="showOrderModal = false" :disabled="isSubmittingOrder" class="text-gray-500 hover:text-gray-800 text-2xl leading-none disabled:opacity-50">&times;</button>
        </div>

        <form x-ref="supplierOrderForm" method="POST" action="{{ route('cafe-owner.marketplace.orders.store') }}" @submit.prevent="requestOrderConfirmation($event)" :aria-busy="isSubmittingOrder.toString()" class="space-y-3">
            @csrf
            <input type="hidden" name="product_id" :value="orderProduct.id" />
            <input type="hidden" name="delivery_latitude" :value="orderForm.delivery_latitude ?? ''" />
            <input type="hidden" name="delivery_longitude" :value="orderForm.delivery_longitude ?? ''" />
            <input type="hidden" name="delivery_location_label" :value="orderForm.delivery_location_label || ''" />

            <div class="rounded-lg border border-[#E7DED1] bg-[#F8F4ED] p-3">
                <p class="text-sm font-semibold text-[#3A2E22]" x-text="orderProduct.name"></p>
                <p class="text-xs text-[#8E7B67] mt-1"><span x-text="orderProduct.category"></span> · <span x-text="orderProduct.seller_name"></span></p>
                <div class="mt-2 flex items-center justify-between gap-3">
                    <span class="text-sm font-semibold text-[#3A2E22]">₱<span x-text="Number(orderProduct.price || 0).toFixed(2)"></span> / <span x-text="orderProduct.unit"></span></span>
                    <span class="text-xs text-[#8E7B67]"><span x-text="orderProduct.stock_quantity"></span> available</span>
                </div>
            </div>

            <div>
                <label for="supplier-order-quantity" class="block text-xs font-semibold text-[#3A2E22]">Quantity (<span x-text="orderProduct.unit"></span>) *</label>
                <input id="supplier-order-quantity" type="number" name="quantity" x-model.number="orderForm.quantity" :min="orderProduct.moq" :max="orderProduct.stock_quantity" step="1" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A6741]" />
                <p class="mt-1 text-[11px] text-[#8E7B67]">Minimum order: <span x-text="orderProduct.moq"></span> <span x-text="orderProduct.unit"></span></p>
            </div>

            <div>
                <label class="block text-sm text-[#3A2E22] font-body mb-1">Mode of Payment</label>
                <select id="supplier-payment-mode" name="payment_mode" x-model="orderForm.payment_mode" required class="w-full bg-white border border-gray-300 rounded-lg px-4 py-2.5 text-sm text-[#3A2E22] font-body focus:outline-none focus:ring-2 focus:ring-[#2E5A3D]">
                    <option value="" disabled>Select Mode of Payment</option>
                    <option value="pickup">Pickup</option>
                    <option value="cod">Cash on Delivery</option>
                </select>
                <p class="mt-1 text-xs text-[#B43F3F] font-body" x-show="!orderForm.payment_mode" x-cloak>Please select a mode of payment.</p>
            </div>

            <div x-show="orderForm.payment_mode === 'pickup'" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="supplier-order-date" class="block text-xs font-semibold text-[#3A2E22]">Preferred Pickup Date *</label>
                    <input id="supplier-order-date" type="date" name="pickup_date" x-model="orderForm.pickup_date" :required="orderForm.payment_mode === 'pickup'" min="{{ now()->toDateString() }}" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A6741]" />
                </div>
                <div>
                    <label for="supplier-order-time" class="block text-xs font-semibold text-[#3A2E22]">Preferred Pickup Time *</label>
                    <input id="supplier-order-time" type="time" name="pickup_time" x-model="orderForm.pickup_time" :required="orderForm.payment_mode === 'pickup'" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A6741]" />
                </div>
            </div>

            <div>
                <label for="supplier-order-email" class="block text-sm text-[#3A2E22] font-body mb-1">Email</label>
                <input id="supplier-order-email" x-ref="supplierOrderEmail" type="email" name="email" x-model="orderForm.email" autocomplete="email" placeholder="Enter your email" maxlength="255" required class="w-full bg-white border border-gray-300 rounded-lg px-4 py-2.5 text-sm text-[#3A2E22] font-body focus:outline-none focus:ring-2 focus:ring-[#2E5A3D]" />
                <p class="mt-1 text-xs text-[#B43F3F] font-body" x-show="orderForm.email && !$refs.supplierOrderEmail?.checkValidity()" x-cloak>Please enter a valid email address.</p>
            </div>

            <div>
                <label for="supplier-order-address" class="block text-sm text-[#3A2E22] font-body mb-1">Address (House No., Street, Barangay, City, Province)</label>
                <textarea id="supplier-order-address" name="address" x-model="orderForm.address" autocomplete="street-address" maxlength="255" rows="2" placeholder="Enter complete address" required class="w-full bg-white border border-gray-300 rounded-lg px-4 py-2.5 text-sm resize-none text-[#3A2E22] font-body focus:outline-none focus:ring-2 focus:ring-[#2E5A3D]"></textarea>
            </div>

            <div x-show="orderForm.payment_mode === 'cod'" x-cloak class="space-y-2">
                <button type="button" @click="openDeliveryMap()" class="w-full inline-flex items-center justify-center gap-2 rounded-lg border border-[#2E5A3D] bg-white px-4 py-2.5 text-sm font-medium text-[#2E5A3D] hover:bg-[#F3E9D7] transition-colors">
                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"></path><circle cx="12" cy="10" r="2.5"></circle></svg>
                    <span x-text="orderForm.delivery_latitude !== null ? 'Change Pinned Location' : 'Pin Delivery Location'"></span>
                </button>
                <p x-show="orderForm.delivery_latitude !== null" x-cloak class="text-xs text-[#6B5B4A] break-words">
                    <span class="font-semibold">Pinned location:</span>
                    <span x-text="orderForm.delivery_location_label || (Number(orderForm.delivery_latitude).toFixed(6) + ', ' + Number(orderForm.delivery_longitude).toFixed(6))"></span>
                </p>
                <p x-show="deliveryMapError" x-cloak class="text-xs text-[#B43F3F]" x-text="deliveryMapError"></p>
                <p class="text-[11px] text-[#8E7B67]">A pinned map location is required for Cash on Delivery.</p>
            </div>

            <div>
                <label for="supplier-order-phone" class="block text-sm text-[#3A2E22] font-body mb-1">Phone Number</label>
                <input id="supplier-order-phone" type="text" name="phone" x-model="orderForm.phone" @input="orderForm.phone = $event.target.value.replace(/[^0-9]/g, '').slice(0, 11); $event.target.value = orderForm.phone" inputmode="numeric" pattern="[0-9]*" autocomplete="tel" maxlength="11" placeholder="e.g. 09XXXXXXXXX" required class="w-full bg-white border border-gray-300 rounded-lg px-4 py-2.5 text-sm text-[#3A2E22] font-body focus:outline-none focus:ring-2 focus:ring-[#2E5A3D]" />
                <p class="mt-1 text-xs text-[#B43F3F] font-body" x-show="orderForm.phone && !/^[0-9]{11}$/.test(orderForm.phone)" x-cloak>Phone number must contain exactly 11 digits.</p>
            </div>

            <div>
                <label for="supplier-order-notes" class="block text-xs font-semibold text-[#3A2E22]">Notes to Supplier</label>
                <textarea id="supplier-order-notes" name="buyer_notes" x-model="orderForm.buyer_notes" rows="2" maxlength="1000" placeholder="Optional instructions or questions..." class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A6741]"></textarea>
            </div>

            <div class="rounded-lg border border-[#E7DED1] bg-white p-3 space-y-2">
                <div class="flex items-center justify-between gap-3 text-sm">
                    <span class="text-[#8E7B67]">Product Total</span>
                    <span class="font-semibold text-[#3A2E22]">₱<span x-text="productTotal.toFixed(2)"></span></span>
                </div>
                <div x-show="orderForm.payment_mode === 'cod'" x-cloak class="flex items-center justify-between gap-3 text-sm">
                    <span class="text-[#8E7B67]">Total Item Weight</span>
                    <span class="font-semibold text-[#3A2E22]" x-text="totalItemWeightGrams.toLocaleString() + ' g (' + (totalItemWeightGrams / 1000).toFixed(2) + ' kg)'"></span>
                </div>
                <div class="flex items-center justify-between gap-3 border-t border-[#E7DED1] pt-2">
                    <span class="text-sm font-semibold text-[#3A2E22]">Total</span>
                    <span class="text-base font-bold text-[#2C4A2E]">₱<span x-text="orderTotal"></span></span>
                </div>
                <p x-show="orderForm.payment_mode === 'cod'" x-cloak class="rounded-md border border-[#E2D5C1] bg-[#FAF6EE] px-2.5 py-2 text-[11px] leading-relaxed text-[#8E7B67]">
                    <span class="font-semibold text-[#3A2E22]">Note:</span> Delivery fees are shouldered by the consumer and are separate from the order total.
                </p>
            </div>

            <p class="text-[11px] leading-relaxed text-[#8E7B67]">This is a reservation request, not an immediate payment. For Cash on Delivery, pay the displayed total in cash when the order is delivered. The supplier must confirm the request before stock is reserved.</p>

            <div class="flex justify-end gap-2 pt-1">
                <button type="button" @click="showOrderModal = false" :disabled="isSubmittingOrder" class="rounded-lg bg-gray-100 px-4 py-2 text-sm text-gray-700 hover:bg-gray-200 disabled:opacity-50">Cancel</button>
                <button type="submit" :disabled="isSubmittingOrder || !orderProduct.id || orderForm.quantity < orderProduct.moq || orderForm.quantity > orderProduct.stock_quantity" class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#4A6741] px-4 py-2 text-sm font-semibold text-white hover:bg-[#3A2E22] disabled:cursor-not-allowed disabled:opacity-60">
                    <span x-text="isSubmittingOrder ? 'Submitting...' : 'Submit Reservation'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

<div
    x-show="showDeliveryMap"
    x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-[70] flex items-center justify-center bg-black/60 p-3 sm:p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="cafeDeliveryMapTitle"
>
    <div class="cafe-delivery-map-card w-full max-w-3xl overflow-hidden rounded-2xl border border-[#DCCBB2] bg-[#F3E9D7] shadow-2xl">
        <div class="flex items-center justify-between border-b border-[#DCCBB2] px-4 py-3 sm:px-5">
            <div>
                <h2 id="cafeDeliveryMapTitle" class="text-lg font-display font-bold text-[#3A2E22]">Pin Delivery Location</h2>
                <p class="mt-1 text-xs text-[#6B5B4A]">Click the map or drag the pin to the exact delivery point.</p>
            </div>
            <button type="button" @click="showDeliveryMap = false" class="rounded-lg px-3 py-2 text-xl text-[#6B5B4A] hover:bg-white" aria-label="Close map">&times;</button>
        </div>
        <div class="cafe-delivery-map-content p-3 sm:p-4">
            <div class="mb-2 flex flex-wrap items-center gap-2">
                <button type="button" @click="useCurrentDeliveryLocation()" class="rounded-lg border border-[#2E5A3D] bg-white px-3 py-2 text-sm font-medium text-[#2E5A3D] hover:bg-[#edf4ea]">Use My Current Location</button>
                <span class="self-center text-xs text-[#6B5B4A]" x-text="deliveryMapStatus" aria-live="polite"></span>
            </div>
            <div x-ref="deliveryMap" class="cafe-delivery-map-canvas h-72 sm:h-96 w-full rounded-xl border border-[#DCCBB2] bg-white"></div>
            <p class="mt-2 text-xs text-[#6B5B4A]">Your pin is saved with this order. Please ensure the typed address above matches the pinned location.</p>
            <div class="mt-3 flex justify-end gap-2">
                <button type="button" @click="showDeliveryMap = false" class="rounded-lg border border-[#DCCBB2] bg-white px-4 py-2 text-sm text-[#3A2E22]">Cancel</button>
                <button type="button" @click="if (orderForm.delivery_latitude !== null && orderForm.delivery_longitude !== null) { showDeliveryMap = false; deliveryMapError = ''; } else { deliveryMapError = 'Please select a point on the map first.'; }" class="rounded-lg bg-[#2E5A3D] px-4 py-2 text-sm font-semibold text-white hover:bg-[#1E3A2A]">Confirm Location</button>
            </div>
        </div>
    </div>
</div>

<div
    x-show="showOrderConfirmModal"
    x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-[80] flex items-center justify-center bg-black/60 p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="cafeOrderConfirmTitle"
>
    <div class="w-full max-w-lg max-h-[90vh] overflow-y-auto rounded-2xl bg-white p-5 shadow-2xl sm:p-6">
        <h2 id="cafeOrderConfirmTitle" class="text-xl font-display font-bold text-[#3A2E22]">Confirm Order / Reservation?</h2>
        <p class="mt-2 text-sm text-[#6B5B4A]">Review your order and contact details before submitting your request.</p>
        <div class="mt-4 space-y-2 rounded-xl border border-[#E7DED1] bg-[#F8F4ED] p-4 text-sm text-[#3A2E22]">
            <div class="flex justify-between gap-3"><span class="text-[#946042]">Product</span><span class="text-right font-medium" x-text="orderProduct.name"></span></div>
            <div class="flex justify-between gap-3"><span class="text-[#946042]">Seller</span><span class="text-right" x-text="orderProduct.seller_name"></span></div>
            <div class="flex justify-between gap-3"><span class="text-[#946042]">Quantity</span><span x-text="orderForm.quantity + ' ' + orderProduct.unit"></span></div>
            <div class="flex justify-between gap-3"><span class="text-[#946042]">Mode of Payment</span><span x-text="orderForm.payment_mode === 'cod' ? 'Cash on Delivery' : 'Pickup'"></span></div>
            <template x-if="orderForm.payment_mode === 'pickup'">
                <div class="space-y-2 border-t border-[#E7DED1] pt-2">
                    <div class="flex justify-between gap-3"><span class="text-[#946042]">Pickup Date</span><span x-text="orderForm.pickup_date || '—'"></span></div>
                    <div class="flex justify-between gap-3"><span class="text-[#946042]">Pickup Time</span><span x-text="orderForm.pickup_time || '—'"></span></div>
                </div>
            </template>
            <template x-if="orderForm.payment_mode === 'cod'">
                <div class="space-y-2 border-t border-[#E7DED1] pt-2">
                    <div class="flex justify-between gap-3"><span class="text-[#946042]">Address</span><span class="max-w-[65%] break-words text-right" x-text="orderForm.address"></span></div>
                    <div class="flex justify-between gap-3"><span class="text-[#946042]">Pinned Location</span><span class="max-w-[65%] break-words text-right" x-text="orderForm.delivery_location_label || (Number(orderForm.delivery_latitude).toFixed(6) + ', ' + Number(orderForm.delivery_longitude).toFixed(6))"></span></div>
                    <div class="flex justify-between gap-3"><span class="text-[#946042]">Phone</span><span x-text="orderForm.phone"></span></div>
                </div>
            </template>
            <div class="flex justify-between gap-3 border-t border-[#E7DED1] pt-2"><span class="text-[#946042]">Email</span><span class="max-w-[65%] break-words text-right" x-text="orderForm.email"></span></div>
            <div class="flex justify-between gap-3 border-t border-[#E7DED1] pt-2"><span class="font-semibold">Product Total</span><span class="font-bold" x-text="'₱' + productTotal.toFixed(2)"></span></div>
            <template x-if="orderForm.payment_mode === 'cod'">
                <div class="flex justify-between gap-3"><span class="text-[#946042]">Total Item Weight</span><span x-text="totalItemWeightGrams.toLocaleString() + ' g (' + (totalItemWeightGrams / 1000).toFixed(2) + ' kg)'"></span></div>
            </template>
            <div class="flex justify-between gap-3 border-t border-[#E7DED1] pt-2"><span class="font-semibold">Order Total</span><span class="text-base font-bold text-[#2E5A3D]" x-text="'₱' + orderTotal"></span></div>
            <p x-show="orderForm.payment_mode === 'cod'" class="rounded-md border border-[#E2D5C1] bg-white px-3 py-2 text-xs leading-relaxed text-[#8E7B67]">Note: Delivery fees are shouldered by the consumer and are separate from the order total.</p>
            <div x-show="orderForm.buyer_notes.trim()" class="border-t border-[#E7DED1] pt-2"><span class="text-[#946042]">Notes:</span> <span x-text="orderForm.buyer_notes"></span></div>
        </div>
        <div class="mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <button type="button" @click="showOrderConfirmModal = false" :disabled="isSubmittingOrder" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold text-[#3A2E22] hover:bg-gray-50 disabled:opacity-50">Go Back</button>
            <button type="button" @click="confirmOrderSubmission()" :disabled="isSubmittingOrder" class="rounded-lg bg-[#2E5A3D] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#1E3A2A] disabled:opacity-50" x-text="isSubmittingOrder ? 'Submitting…' : 'Confirm and Submit'"></button>
        </div>
    </div>
</div>

<!-- Cancel Order Confirmation Modal (matches the Admin deletion modal pattern) -->
<div class="fixed inset-0 z-[90] flex items-center justify-center px-4"
    x-show="showCancelOrderModal"
    @keydown.escape.window="showCancelOrderModal = false"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 scale-95"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    @click="showCancelOrderModal = false"
    style="display: none;"
    role="dialog"
    aria-modal="true"
    aria-labelledby="cancelSupplierOrderTitle">

    <div class="fixed inset-0 bg-black bg-opacity-40 backdrop-blur-sm"
        @click.stop="showCancelOrderModal = false"></div>

    <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full"
        @click.stop>
        <div class="p-6">
            <h2 id="cancelSupplierOrderTitle" class="text-2xl font-display font-bold text-[#3A2E22] mb-4">
                Cancel Pending Order?
            </h2>
            <p class="text-[#3A2E22] mb-6">
                Are you sure you want to cancel
                <span class="font-semibold" x-text="cancelOrderReference"></span>?
                This action cannot be undone.
            </p>
            <form method="POST" :action="cancelOrderAction" @submit="showCancelOrderModal = false">
                @csrf
                <div class="flex gap-3">
                    <button type="button" @click="showCancelOrderModal = false"
                        class="flex-1 px-4 py-2 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-colors">
                        Keep Order
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-2 rounded-lg bg-red-600 text-white font-medium hover:bg-red-700 transition-colors">
                        Cancel Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div
    x-show="showEditModal"
    x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="cafe-edit-product-modal fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
>
    <div @click.away="showEditModal = false" class="bg-white rounded-xl shadow-2xl w-full max-w-xl p-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-2">
            <h2 class="text-base font-display font-bold text-[#3A2E22]">Edit Product</h2>
            <button type="button" @click="showEditModal = false" class="text-gray-500 hover:text-gray-800 text-2xl leading-none">&times;</button>
        </div>

        <form method="POST" :action="formAction()" enctype="multipart/form-data" class="grid grid-cols-1 gap-2.5">
            @csrf
            @method('PATCH')

            <div class="w-full">
                <label class="block text-[11px] font-semibold">Name *</label>
                <input x-model="form.name" name="name" required class="mt-1 w-full rounded-lg border border-gray-300 px-2 py-1 text-xs shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]" />
            </div>

            <div>
                <label class="block text-[11px] font-semibold">Type *</label>
                <template x-if="!isMenuCategory(form.category)">
                    <select x-model="form.category" name="category" required class="mt-1 w-full rounded-lg border border-gray-300 px-2 py-1 text-xs shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]">
                        <option value="Coffee Beans">Coffee Beans</option>
                        <option value="Ground Coffee">Ground Coffee</option>
                    </select>
                </template>
                <template x-if="isMenuCategory(form.category)">
                    <select x-model="form.category" name="category" required class="mt-1 w-full rounded-lg border border-gray-300 px-2 py-1 text-xs shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]">
                        <template x-for="category in menuImportCategories" :key="`edit-menu-cat-${category}`">
                            <option :value="category" x-text="category"></option>
                        </template>
                    </select>
                </template>
            </div>

<template x-if="!isMenuCategory(form.category)">
                <div>
                    <label class="block text-[11px] font-semibold">Stock Quantity *</label>
                    <input x-model="form.stock_quantity" type="number" min="0" step="1" name="stock_quantity" required class="mt-1 w-full rounded-lg border border-gray-300 px-2 py-1 text-xs shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]" />
                </div>
            </template>

            <template x-if="!isMenuCategory(form.category)">
                <div>
                    <label class="block text-[11px] font-semibold">Unit</label>
                    <input x-model="form.unit" name="unit" class="mt-1 w-full rounded-lg border border-gray-300 px-2 py-1 text-xs shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]" />
                </div>
            </template>

<div class="w-full">
                <label class="block text-[11px] font-semibold">Product Image</label>
                <input x-ref="editImageInput" type="file" name="image" accept="image/*" @change="handleEditImageChange($event)" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm shadow-sm file:mr-3 file:rounded-md file:border-0 file:bg-[#4A6741] file:px-2.5 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-[#3A2E22] focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]" />
                <div x-show="editImagePreview" x-cloak class="mt-2 relative rounded-lg border border-gray-200 bg-[#F5F0E8] p-2">
                    <button type="button" @click="clearEditImage()" class="absolute top-2 right-2 w-5 h-5 rounded-full bg-white/95 border border-gray-300 text-gray-600 text-xs leading-none hover:bg-red-50 hover:text-red-600" aria-label="Remove selected image">&times;</button>
                    <p class="text-[11px] text-[#9E8C78] mb-1">Live image preview</p>
                    <img :src="editImagePreview" alt="Live product preview" class="max-h-16 w-full object-contain rounded-md border border-gray-200 bg-white" x-on:error="$el.style.display='none'" x-on:load="$el.style.display='block'" />
                </div>
            </div>

            <div class="w-full">
                <label class="block text-[11px] font-semibold">Description</label>
                <textarea x-model="form.description" name="description" rows="2" class="mt-1 w-full rounded-lg border border-gray-300 px-2 py-1 text-xs shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]"></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" @click="showEditModal = false" class="px-3 py-1.5 text-sm rounded-lg bg-gray-200 text-gray-800 hover:bg-gray-300 whitespace-nowrap">Cancel</button>
                <button type="submit" class="inline-flex items-center justify-center px-3 py-1.5 text-sm rounded-lg bg-[#4A6741] text-white hover:bg-[#3A2E22] whitespace-nowrap">Save Product</button>
            </div>
        </form>
    </div>
</div>

<div
    x-show="showCreateModal"
    x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
>
    <div @click.away="if (!isCreatingProduct) showCreateModal = false" class="bg-white rounded-xl shadow-2xl w-full max-w-xl p-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-2">
            <h2 class="text-base font-display font-bold text-[#3A2E22]">Add Product</h2>
            <button type="button" @click="showCreateModal = false" :disabled="isCreatingProduct" class="text-gray-500 hover:text-gray-800 text-2xl leading-none disabled:cursor-not-allowed disabled:opacity-50">&times;</button>
        </div>

        <form method="POST" action="{{ route('cafe-owner.marketplace.products.store') }}" enctype="multipart/form-data" @submit="isCreatingProduct = true" :aria-busy="isCreatingProduct.toString()" class="grid grid-cols-1 gap-2.5">
            @csrf

            <div class="w-full">
                <label class="block text-[11px] font-semibold">Name *</label>
                <input x-model="createForm.name" name="name" required class="mt-1 w-full rounded-lg border border-gray-300 px-2 py-1 text-xs shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]" />
            </div>

            <div>
                <label class="block text-[11px] font-semibold">Type *</label>
                <select x-model="createForm.category" name="category" required class="mt-1 w-full rounded-lg border border-gray-300 px-2 py-1 text-xs shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]">
                    <template x-for="category in menuImportCategories" :key="`create-menu-cat-${category}`">
                        <option :value="category" x-text="category"></option>
                    </template>
                </select>
            </div>

<div class="w-full">
                <label class="block text-[11px] font-semibold">Product Image</label>
                <input x-ref="createImageInput" type="file" name="image" accept="image/*" @change="handleCreateImageChange($event)" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm shadow-sm file:mr-3 file:rounded-md file:border-0 file:bg-[#4A6741] file:px-2.5 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-[#3A2E22] focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]" />
                <div x-show="createImagePreview" x-cloak class="mt-2 relative rounded-lg border border-gray-200 bg-[#F5F0E8] p-2">
                    <button type="button" @click="clearCreateImage()" class="absolute top-2 right-2 w-5 h-5 rounded-full bg-white/95 border border-gray-300 text-gray-600 text-xs leading-none hover:bg-red-50 hover:text-red-600" aria-label="Remove selected image">&times;</button>
                    <p class="text-[11px] text-[#9E8C78] mb-1">Live image preview</p>
                    <img :src="createImagePreview" alt="Live product preview" class="max-h-16 w-full object-contain rounded-md border border-gray-200 bg-white" x-on:error="$el.style.display='none'" x-on:load="$el.style.display='block'" />
                </div>
            </div>

            <div class="w-full">
                <label class="block text-[11px] font-semibold">Description</label>
                <textarea x-model="createForm.description" name="description" rows="2" class="mt-1 w-full rounded-lg border border-gray-300 px-2 py-1 text-xs shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]"></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" @click="showCreateModal = false" :disabled="isCreatingProduct" class="px-3 py-1.5 text-sm rounded-lg bg-gray-200 text-gray-800 hover:bg-gray-300 whitespace-nowrap disabled:cursor-not-allowed disabled:opacity-50">Cancel</button>
                <button type="submit" :disabled="isCreatingProduct" class="inline-flex items-center justify-center gap-2 px-3 py-1.5 text-sm rounded-lg bg-[#4A6741] text-white hover:bg-[#3A2E22] whitespace-nowrap disabled:cursor-not-allowed disabled:opacity-80">
                    <svg x-show="isCreatingProduct" x-cloak class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"></path>
                    </svg>
                    <span x-text="isCreatingProduct ? 'Creating product...' : 'Create Product'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

<div
    x-show="showMenuImportModal"
    x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center overflow-hidden bg-black/40 p-4"
>
    <div @click.away="closeMenuImportModal()" class="bg-white rounded-xl shadow-2xl w-full max-w-6xl max-h-[92vh] overflow-y-auto p-4">
        <div class="flex items-center justify-between mb-3">
            <div>
                <h2 class="text-base font-display font-bold text-[#3A2E22]">Import Menu Image</h2>
                <p class="text-xs text-[#9E8C78] mt-0.5">Upload a menu photo, review extracted products, then save as cards.</p>
            </div>
            <button type="button" @click="closeMenuImportModal()" class="text-gray-500 hover:text-gray-800 text-2xl leading-none">&times;</button>
        </div>

        <div class="rounded-lg border border-gray-200 bg-[#F8F4ED] p-3 mb-3">
            <label class="block text-[11px] font-semibold text-[#3A2E22]">Menu Image</label>
            <div class="mt-1.5 flex flex-col md:flex-row gap-2 md:items-center">
                <input
                    x-ref="menuImportImageInput"
                    type="file"
                    accept="image/*"
                    @change="handleMenuImportImageChange($event)"
                    class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm shadow-sm file:mr-3 file:rounded-md file:border-0 file:bg-[#4A6741] file:px-2.5 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-[#3A2E22] focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]"
                />

                <button
                    type="button"
                    @click="extractMenuItemsFromImage()"
                    :disabled="isExtractingMenu"
                    class="px-3 py-1.5 rounded-lg bg-[#4A6741] text-white text-sm font-semibold hover:bg-[#3A2E22] disabled:opacity-60 disabled:cursor-not-allowed"
                >
                    <span x-show="!isExtractingMenu">Extract Items</span>
                    <span x-show="isExtractingMenu" x-cloak>Extracting...</span>
                </button>
            </div>

            <div x-show="menuImportImagePreview" x-cloak class="mt-2 rounded-lg border border-gray-200 bg-white p-2">
                <img :src="menuImportImagePreview" alt="Menu image preview" class="max-h-36 w-full object-contain rounded-md" />
            </div>

            <p x-show="menuExtractionError" x-text="menuExtractionError" x-cloak class="mt-2 text-xs text-red-600"></p>
        </div>

        <div x-show="extractedMenuItems.length > 0" x-cloak>
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-semibold text-[#3A2E22]">Review Extracted Products</h3>
                <button
                    type="button"
                    @click="addExtractedMenuItem()"
                    class="px-2.5 py-1 rounded-md border border-[#4A6741] text-[#4A6741] text-xs font-semibold hover:bg-[#F5F0E8]"
                >
                    + Add Row
                </button>
            </div>

            <div class="max-h-[50vh] overflow-auto border border-gray-200 rounded-lg">
                <table class="min-w-full text-xs">
                    <thead class="bg-[#F8F4ED] sticky top-0 z-10">
                        <tr>
                            <th class="px-2 py-2 text-left">Name</th>
                            <th class="px-2 py-2 text-left">Category</th>
                            <th class="px-2 py-2 text-left">Description</th>
                            <th class="px-2 py-2 text-left"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(item, index) in extractedMenuItems" :key="`extract-${index}`">
                            <tr class="border-t border-gray-100">
                                <td class="px-2 py-1.5 min-w-[180px]">
                                    <input x-model="item.name" class="w-full rounded-md border border-gray-300 px-2 py-1" />
                                </td>
                                <td class="px-2 py-1.5 min-w-[140px]">
                                    <select x-model="item.category" class="w-full rounded-md border border-gray-300 px-2 py-1">
                                        <option value="Coffee Beans">Coffee Beans</option>
                                        <option value="Ground Coffee">Ground Coffee</option>
                                        <option value="Hot Drinks">Hot Drinks</option>
                                        <option value="Iced Drinks">Iced Drinks</option>
                                        <option value="Iced Blended Drinks">Iced Blended Drinks</option>
                                        <option value="Tea">Tea</option>
                                        <option value="Rice Meals">Rice Meals</option>
                                        <option value="Pastas">Pastas</option>
                                        <option value="Appetizers">Appetizers</option>
                                        <option value="Sandwiches">Sandwiches</option>
                                        <option value="Burgers">Burgers</option>
                                        <option value="Pastries">Pastries</option>
                                    </select>
                                </td>
                                <td class="px-2 py-1.5 min-w-[220px]">
                                    <input x-model="item.description" class="w-full rounded-md border border-gray-300 px-2 py-1" />
                                </td>
                                <td class="px-2 py-1.5">
                                    <button
                                        type="button"
                                        @click="removeExtractedMenuItem(index)"
                                        class="px-2 py-1 rounded-md text-red-600 hover:bg-red-50"
                                        title="Remove row"
                                    >
                                        Remove
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-end gap-2 mt-4">
            <button
                type="button"
                @click="closeMenuImportModal()"
                class="px-3 py-1.5 text-sm rounded-lg bg-gray-200 text-gray-800 hover:bg-gray-300"
            >
                Cancel
            </button>
            <button
                type="button"
                @click="saveExtractedMenuItems()"
                :disabled="isSavingExtractedMenuItems || extractedMenuItems.length === 0"
                class="px-3 py-1.5 text-sm rounded-lg bg-[#4A6741] text-white hover:bg-[#3A2E22] disabled:opacity-60 disabled:cursor-not-allowed"
            >
                <span x-show="!isSavingExtractedMenuItems">Save as Product Cards</span>
                <span x-show="isSavingExtractedMenuItems" x-cloak>Saving...</span>
            </button>
        </div>
    </div>
</div>
</div>

<style>
    @media (max-width: 767px) {
        .cafe-marketplace-page .marketplace-header {
            flex-wrap: nowrap;
            align-items: flex-start;
            gap: 0.65rem;
        }

        .cafe-marketplace-page .marketplace-header > div:first-child {
            flex: 1 1 auto;
            min-width: 0;
        }

        .cafe-marketplace-page .marketplace-actions {
            flex: 0 0 auto;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.4rem;
        }

        .cafe-marketplace-page .marketplace-actions button {
            padding: 0.42rem 0.62rem;
            font-size: 0.72rem;
            line-height: 1.1;
            white-space: nowrap;
        }
    }
</style>

<button
    id="scrollToTopButton"
    type="button"
    class="fixed bottom-6 right-6 z-40 w-12 h-12 rounded-full bg-[#4A6741] text-white shadow-lg hover:bg-[#3f5b38] focus:outline-none focus:ring-2 focus:ring-[#4A6741]/50 opacity-0 pointer-events-none transition-opacity duration-300"
    aria-label="Scroll to top"
>
    <svg class="w-5 h-5 mx-auto" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 19V5"/>
        <path d="m5 12 7-7 7 7"/>
    </svg>
</button>

<script>
    (function () {
        const scrollToTopButton = document.getElementById('scrollToTopButton');
        if (!scrollToTopButton) {
            return;
        }

        const toggleScrollToTopButton = () => {
            const shouldShow = window.scrollY > 300;
            scrollToTopButton.classList.toggle('opacity-0', !shouldShow);
            scrollToTopButton.classList.toggle('pointer-events-none', !shouldShow);
            scrollToTopButton.classList.toggle('opacity-100', shouldShow);
        };

        window.addEventListener('scroll', toggleScrollToTopButton, { passive: true });
        toggleScrollToTopButton();

        scrollToTopButton.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    })();
</script>
@endsection
