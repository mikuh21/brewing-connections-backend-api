@extends('cafe-owner.layouts.app')

@php
    $title = 'Marketplace';
@endphp

@section('title', 'My Products - BrewHub')

@section('content')
@php
    $productsMeta = $products->map(function ($product) {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'category' => $product->category,
            'price_per_unit' => $product->price_per_unit,
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
            default => 'Seller',
        };
    };
@endphp

<div
    x-data="{
        showCreateModal: false,
        showEditModal: false,
        showViewModal: false,
        editImagePreview: '',
        createImagePreview: '',
        activeTab: 'my-listings',
        statusFilter: 'all',
        orderSearch: '',
        searchTerm: '',
        csrfToken: '{{ csrf_token() }}',
        hiddenProductIds: [],
        productsMeta: {{ Js::from($productsMeta) }},
        updateUrlTemplate: '{{ route('cafe-owner.marketplace.products.update', ['product' => '__PRODUCT_ID__']) }}',
        visibilityUrlTemplate: '{{ route('cafe-owner.marketplace.products.visibility', ['product' => '__PRODUCT_ID__']) }}',
        createForm: {
            name: '',
            description: '',
            category: 'Coffee Beans',
            roast_level: '',
            grind_type: '',
            price_per_unit: '',
            stock_quantity: '',
            unit: 'kg',
            moq: '1',
            image_url: ''
        },
        form: {
            id: null,
            name: '',
            description: '',
            category: 'Coffee Beans',
            price_per_unit: '',
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
            price_per_unit: '',
            stock_quantity: 0,
            unit: '',
            moq: '',
            image_url: '',
            owner_label: '',
            owner_name: ''
        },
        openViewer(product) {
            this.viewProduct = {
                id: product.id ?? null,
                name: product.name || '',
                description: product.description || '',
                category: product.category || '',
                price_per_unit: product.price_per_unit || 0,
                stock_quantity: Number(product.stock_quantity || 0),
                unit: product.unit || 'unit',
                moq: product.moq || 'N/A',
                image_url: product.image_url || '',
                owner_label: product.owner_label || 'Seller',
                owner_name: product.owner_name || 'Unknown',
            };
            this.showViewModal = true;
        },
        openEditor(product) {
            this.form.id = product.id;
            this.form.name = product.name || '';
            this.form.description = product.description || '';
            this.form.category = product.category || 'Coffee Beans';
            this.form.price_per_unit = product.price_per_unit || '';
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
        init() {
            const hashToTab = {
                '#my-listings': 'my-listings',
                '#products': 'my-listings',
                '#browse': 'browse',
                '#orders': 'orders',
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
        displayType(category) {
            return String(category || '').toLowerCase() === 'ground coffee' ? 'Ground Coffee' : 'Coffee Beans';
        },
        hiddenProducts() {
            const term = this.searchTerm.toLowerCase().trim();
            return this.productsMeta.filter((product) => {
                const haystack = `${product.name} ${product.category}`.toLowerCase();
                return this.isHidden(product.id) && haystack.includes(term);
            });
        },
        formAction() {
            return this.updateUrlTemplate.replace('__PRODUCT_ID__', this.form.id);
        }
    }"
>
    <div class="mb-8 flex items-start justify-between gap-4 flex-wrap">
        <div>
            <h1 class="text-3xl font-display font-bold text-[#3A2E22] mb-1">
                My <span class="italic text-[#4A6741]">Products</span>
            </h1>
            <p class="text-[#9E8C78] text-sm font-medium">
                {{ $products->total() }} {{ \Illuminate\Support\Str::plural('product', $products->total()) }} in your catalog
            </p>
        </div>

        <button
            type="button"
            @click="showCreateModal = true; createImagePreview = ''"
            class="px-4 py-2 rounded-lg bg-[#4A6741] text-white text-sm font-semibold hover:bg-[#3A2E22] transition-colors"
        >
            <span class="mr-1.5">+</span>Add Product
        </button>
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
            Orders
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

                $displayType = strtolower((string) $product->category) === 'ground coffee'
                    ? 'Ground Coffee'
                    : 'Coffee Beans';
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
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $stockClass }} whitespace-nowrap">{{ $stockLabel }}</span>
                    </div>

                    <p class="text-xs italic text-[#9E8C78] mb-1.5">{{ $displayType }}</p>

                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <p class="font-bold text-sm text-[#2C1A0E]">PHP {{ number_format($product->price_per_unit, 2) }}</p>
                            <p class="text-xs text-[#9E8C78]">per {{ $product->unit ?? 'unit' }}</p>
                        </div>
                        <p class="text-xs text-[#9E8C78]">{{ $stock }} units</p>
                    </div>

                    <div class="flex justify-end gap-2 mt-2">
                        <button
                            type="button"
                            @click="openEditor({{ json_encode(['id' => $product->id, 'name' => $product->name, 'description' => $product->description, 'category' => $product->category, 'price_per_unit' => $product->price_per_unit, 'stock_quantity' => $product->stock_quantity, 'unit' => $product->unit, 'moq' => $product->moq, 'image_url' => $product->image_url]) }})"
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
                            <h3 class="font-semibold text-sm text-[#2C1A0E] leading-tight truncate" x-text="product.name"></h3>
                            <span class="text-xs px-2 py-0.5 rounded-full whitespace-nowrap" :class="stockMeta(product).class" x-text="stockMeta(product).label"></span>
                        </div>

                        <p class="text-xs italic text-[#9E8C78] mb-1.5" x-text="displayType(product.category)"></p>

                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <p class="font-bold text-sm text-[#2C1A0E]">PHP <span x-text="Number(product.price_per_unit || 0).toFixed(2)"></span></p>
                                <p class="text-xs text-[#9E8C78]">per <span x-text="product.unit || 'unit'"></span></p>
                            </div>
                            <p class="text-xs text-[#9E8C78]"><span x-text="stockMeta(product).qty"></span> units</p>
                        </div>

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

@if(($marketplaceProducts ?? collect())->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach($marketplaceProducts as $product)
            @php
                $displayType = strtolower((string) $product->category) === 'ground coffee'
                    ? 'Ground Coffee'
                    : 'Coffee Beans';
                $ownerLabel = $ownerTypeLabel($product->seller_type);
                $ownerName = $product->seller_name ?: 'Unknown';
            @endphp

            <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition overflow-hidden"
                data-search="{{ strtolower(($product->name ?? '') . ' ' . ($product->category ?? '')) }}"
                x-show="($el.dataset.search || '').includes(searchTerm.toLowerCase().trim())">
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
                        <h3 class="font-semibold text-sm text-[#2C1A0E] leading-tight truncate">{{ $product->name }}</h3>
                        <button
                            type="button"
                            @click='openViewer({{ json_encode([
                                "id" => $product->id,
                                "name" => $product->name,
                                "description" => $product->description,
                                "category" => $product->category,
                                "price_per_unit" => $product->price_per_unit,
                                "stock_quantity" => $product->stock_quantity,
                                "unit" => $product->unit,
                                "moq" => $product->moq,
                                "image_url" => $product->image_url,
                                "owner_label" => $ownerLabel,
                                "owner_name" => $ownerName,
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

                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <p class="font-bold text-sm text-[#2C1A0E]">PHP {{ number_format($product->price_per_unit, 2) }}</p>
                            <p class="text-xs text-[#9E8C78]">per {{ $product->unit ?? 'unit' }}</p>
                        </div>
                        <p class="text-xs text-[#9E8C78]">{{ (int) ($product->stock_quantity ?? 0) }} units</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-8">
        {{ $marketplaceProducts->links() }}
    </div>
@else
    <div class="bg-white rounded-xl shadow-sm p-10 text-center">
        <p class="text-sm text-gray-400">No marketplace products from other sellers found.</p>
    </div>
@endif
</div>

<div x-show="activeTab === 'orders'" class="mt-6">
    @if($orders->count() > 0)
        <div class="flex items-center justify-between mb-4 gap-3 flex-wrap">
            <select x-model="statusFilter" class="text-xs border border-gray-200 rounded-lg px-3 py-1.5 bg-white text-[#2C1A0E] focus:outline-none focus:ring-2 focus:ring-[#2C4A2E]">
                <option value="all">All Status</option>
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="completed">Completed</option>
                <option value="canceled">Canceled</option>
            </select>

            <div class="relative">
                <svg class="w-3.5 h-3.5 absolute left-2.5 top-1/2 -translate-y-1/2 text-[#9E8C78]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" x-model="orderSearch" placeholder="Search customer or product..." class="pl-8 pr-2.5 py-1.5 text-xs bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2C4A2E] focus:border-transparent w-64" />
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-[#F8F4EC] border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-[#6B5B4A] uppercase tracking-wide">Order</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-[#6B5B4A] uppercase tracking-wide">Customer</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-[#6B5B4A] uppercase tracking-wide">Product</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-[#6B5B4A] uppercase tracking-wide">Qty</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-[#6B5B4A] uppercase tracking-wide">Total</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-[#6B5B4A] uppercase tracking-wide">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-[#6B5B4A] uppercase tracking-wide">Manage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr
                                class="border-b border-gray-100 hover:bg-[#FAF7F2] transition-colors"
                                x-show="(statusFilter === 'all' || statusFilter === '{{ strtolower($order->status) }}') && (orderSearch === '' || '{{ strtolower($order->user->name ?? '') }}'.includes(orderSearch.toLowerCase()) || '{{ strtolower($order->product->name ?? '') }}'.includes(orderSearch.toLowerCase()))"
                            >
                                <td class="px-4 py-3 text-sm font-medium text-[#3A2E22]">#{{ $order->id }}</td>
                                <td class="px-4 py-3 text-sm text-[#6B5B4A]">{{ $order->user->name ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-sm text-[#6B5B4A]" style="font-family: 'Poppins', sans-serif;">{{ $order->product->name ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-sm text-[#6B5B4A]">{{ $order->quantity }}</td>
                                <td class="px-4 py-3 text-sm text-[#6B5B4A]">PHP {{ number_format((float) $order->total_price, 2) }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $status = strtolower((string) $order->status);
                                        $isCanceled = in_array($status, ['canceled', 'cancelled'], true);
                                    @endphp
                                    @if($status === 'pending')
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">Pending</span>
                                    @elseif($status === 'confirmed')
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Confirmed</span>
                                    @elseif($status === 'completed')
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Completed</span>
                                    @else
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Canceled</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <form method="POST" action="{{ route('cafe-owner.marketplace.orders.update', $order) }}" class="flex items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" @disabled($isCanceled) class="text-xs border border-gray-200 rounded-lg px-2 py-1 bg-white text-[#2C1A0E] focus:outline-none focus:ring-2 focus:ring-[#2C4A2E] disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed">
                                            <option value="pending" @selected($status === 'pending')>Pending</option>
                                            <option value="confirmed" @selected($status === 'confirmed')>Confirmed</option>
                                            <option value="completed" @selected($status === 'completed')>Completed</option>
                                            <option value="canceled" @selected(in_array($status, ['canceled', 'cancelled'], true))>Canceled</option>
                                        </select>
                                        <button type="submit" @disabled($isCanceled) class="px-2.5 py-1 text-xs rounded-lg bg-[#4A6741] text-white hover:bg-[#3A2E22] transition-colors disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed">Save</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm p-12 text-center">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-lg font-display font-semibold text-gray-500">No orders yet</p>
            <p class="text-sm text-gray-400 mt-1">Orders will appear here once customers purchase your products.</p>
        </div>
    @endif
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
                        <p class="text-sm text-[#3A2E22]"><span class="font-semibold">Price:</span> PHP <span x-text="Number(viewProduct.price_per_unit || 0).toFixed(2)"></span></p>
                        <p class="text-sm text-[#3A2E22]"><span class="font-semibold">Stock:</span> <span x-text="Number(viewProduct.stock_quantity || 0)"></span> units</p>
                        <p class="text-sm text-[#3A2E22]"><span class="font-semibold">Unit:</span> <span x-text="viewProduct.unit || 'unit'"></span></p>
                        <p class="text-sm text-[#3A2E22]"><span class="font-semibold">Minimum Order Quantity:</span> <span x-text="viewProduct.moq || 'N/A'"></span></p>
                    </div>
                </div>

                <div class="md:col-span-2 pt-2 border-t border-[#EFE7DA]">
                    <h4 class="text-sm font-semibold text-[#3A2E22] mb-1">Description</h4>
                    <p class="text-sm text-[#6B5B4A] leading-relaxed" x-text="(viewProduct.description || '').trim() || 'No description provided.'"></p>
                </div>
            </div>

            <div class="px-4 pb-4 flex justify-end">
                <button type="button" @click="showViewModal = false" class="px-4 py-2 text-sm rounded-lg bg-[#4A6741] text-white hover:bg-[#3A2E22] transition-colors">Close</button>
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
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
>
    <div @click.away="showEditModal = false" class="bg-white rounded-xl shadow-2xl w-full max-w-4xl p-3">
        <div class="flex items-center justify-between mb-2">
            <h2 class="text-base font-display font-bold text-[#3A2E22]">Edit Product</h2>
            <button type="button" @click="showEditModal = false" class="text-gray-500 hover:text-gray-800 text-2xl leading-none">&times;</button>
        </div>

        <form method="POST" :action="formAction()" enctype="multipart/form-data" class="grid grid-cols-2 md:grid-cols-3 gap-1.5">
            @csrf
            @method('PATCH')

            <div class="md:col-span-3">
                <label class="block text-[11px] font-semibold">Name *</label>
                <input x-model="form.name" name="name" required class="mt-1 w-full rounded-lg border border-gray-300 px-2 py-1 text-xs shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]" />
            </div>

            <div>
                <label class="block text-[11px] font-semibold">Type *</label>
                <select x-model="form.category" name="category" required class="mt-1 w-full rounded-lg border border-gray-300 px-2 py-1 text-xs shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]">
                    <option value="Coffee Beans">Coffee Beans</option>
                    <option value="Ground Coffee">Ground Coffee</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-semibold">Price *</label>
                <input x-model="form.price_per_unit" type="number" min="0" step="0.01" name="price_per_unit" required class="mt-1 w-full rounded-lg border border-gray-300 px-2 py-1 text-xs shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]" />
            </div>

            <div>
                <label class="block text-[11px] font-semibold">Stock Quantity *</label>
                <input x-model="form.stock_quantity" type="number" min="0" step="1" name="stock_quantity" required class="mt-1 w-full rounded-lg border border-gray-300 px-2 py-1 text-xs shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]" />
            </div>

            <div>
                <label class="block text-[11px] font-semibold">Unit</label>
                <input x-model="form.unit" name="unit" class="mt-1 w-full rounded-lg border border-gray-300 px-2 py-1 text-xs shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]" />
            </div>

            <div>
                <label class="block text-[11px] font-semibold">Minimum Order Quantity</label>
                <input x-model="form.moq" type="number" min="1" step="1" name="moq" class="mt-1 w-full rounded-lg border border-gray-300 px-2 py-1 text-xs shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]" />
            </div>

            <div class="md:col-span-3">
                <label class="block text-[11px] font-semibold">Product Image</label>
                <input x-ref="editImageInput" type="file" name="image" accept="image/*" @change="handleEditImageChange($event)" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm shadow-sm file:mr-3 file:rounded-md file:border-0 file:bg-[#4A6741] file:px-2.5 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-[#3A2E22] focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]" />
                <div x-show="editImagePreview" x-cloak class="mt-2 relative rounded-lg border border-gray-200 bg-[#F5F0E8] p-2">
                    <button type="button" @click="clearEditImage()" class="absolute top-2 right-2 w-5 h-5 rounded-full bg-white/95 border border-gray-300 text-gray-600 text-xs leading-none hover:bg-red-50 hover:text-red-600" aria-label="Remove selected image">&times;</button>
                    <p class="text-[11px] text-[#9E8C78] mb-1">Live image preview</p>
                    <img :src="editImagePreview" alt="Live product preview" class="max-h-16 w-full object-contain rounded-md border border-gray-200 bg-white" x-on:error="$el.style.display='none'" x-on:load="$el.style.display='block'" />
                </div>
            </div>

            <div class="md:col-span-3">
                <label class="block text-[11px] font-semibold">Description</label>
                <textarea x-model="form.description" name="description" rows="2" class="mt-1 w-full rounded-lg border border-gray-300 px-2 py-1 text-xs shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]"></textarea>
            </div>

            <div class="md:col-span-3 flex justify-end gap-2 pt-2">
                <button type="button" @click="showEditModal = false" class="px-3 py-1.5 text-sm rounded-lg bg-gray-200 text-gray-800 hover:bg-gray-300">Cancel</button>
                <button type="submit" class="px-3 py-1.5 text-sm rounded-lg bg-[#4A6741] text-white hover:bg-[#3A2E22]">Save Product</button>
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
    <div @click.away="showCreateModal = false" class="bg-white rounded-xl shadow-2xl w-full max-w-4xl p-3">
        <div class="flex items-center justify-between mb-2">
            <h2 class="text-base font-display font-bold text-[#3A2E22]">Add Product</h2>
            <button type="button" @click="showCreateModal = false" class="text-gray-500 hover:text-gray-800 text-2xl leading-none">&times;</button>
        </div>

        <form method="POST" action="{{ route('cafe-owner.marketplace.products.store') }}" enctype="multipart/form-data" class="grid grid-cols-2 md:grid-cols-3 gap-1.5">
            @csrf

            <div class="md:col-span-3">
                <label class="block text-[11px] font-semibold">Name *</label>
                <input x-model="createForm.name" name="name" required class="mt-1 w-full rounded-lg border border-gray-300 px-2 py-1 text-xs shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]" />
            </div>

            <div>
                <label class="block text-[11px] font-semibold">Type *</label>
                <select x-model="createForm.category" name="category" required class="mt-1 w-full rounded-lg border border-gray-300 px-2 py-1 text-xs shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]">
                    <option value="Coffee Beans">Coffee Beans</option>
                    <option value="Ground Coffee">Ground Coffee</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-semibold">Price *</label>
                <input x-model="createForm.price_per_unit" type="number" min="0" step="0.01" name="price_per_unit" required class="mt-1 w-full rounded-lg border border-gray-300 px-2 py-1 text-xs shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]" />
            </div>

            <div>
                <label class="block text-[11px] font-semibold">Roast Level</label>
                <input x-model="createForm.roast_level" name="roast_level" class="mt-1 w-full rounded-lg border border-gray-300 px-2 py-1 text-xs shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]" placeholder="Light, Medium, Dark" />
            </div>

            <div>
                <label class="block text-[11px] font-semibold">Grind Type</label>
                <input x-model="createForm.grind_type" name="grind_type" class="mt-1 w-full rounded-lg border border-gray-300 px-2 py-1 text-xs shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]" placeholder="Whole Bean / Fine / Coarse" />
            </div>

            <div>
                <label class="block text-[11px] font-semibold">Stock Quantity *</label>
                <input x-model="createForm.stock_quantity" type="number" min="0" step="1" name="stock_quantity" required class="mt-1 w-full rounded-lg border border-gray-300 px-2 py-1 text-xs shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]" />
            </div>

            <div>
                <label class="block text-[11px] font-semibold">Unit</label>
                <input x-model="createForm.unit" name="unit" class="mt-1 w-full rounded-lg border border-gray-300 px-2 py-1 text-xs shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]" />
            </div>

            <div>
                <label class="block text-[11px] font-semibold">Minimum Order Quantity</label>
                <input x-model="createForm.moq" type="number" min="1" step="1" name="moq" class="mt-1 w-full rounded-lg border border-gray-300 px-2 py-1 text-xs shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]" />
            </div>

            <div class="md:col-span-3">
                <label class="block text-[11px] font-semibold">Product Image</label>
                <input x-ref="createImageInput" type="file" name="image" accept="image/*" @change="handleCreateImageChange($event)" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm shadow-sm file:mr-3 file:rounded-md file:border-0 file:bg-[#4A6741] file:px-2.5 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-[#3A2E22] focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]" />
                <div x-show="createImagePreview" x-cloak class="mt-2 relative rounded-lg border border-gray-200 bg-[#F5F0E8] p-2">
                    <button type="button" @click="clearCreateImage()" class="absolute top-2 right-2 w-5 h-5 rounded-full bg-white/95 border border-gray-300 text-gray-600 text-xs leading-none hover:bg-red-50 hover:text-red-600" aria-label="Remove selected image">&times;</button>
                    <p class="text-[11px] text-[#9E8C78] mb-1">Live image preview</p>
                    <img :src="createImagePreview" alt="Live product preview" class="max-h-16 w-full object-contain rounded-md border border-gray-200 bg-white" x-on:error="$el.style.display='none'" x-on:load="$el.style.display='block'" />
                </div>
            </div>

            <div class="md:col-span-3">
                <label class="block text-[11px] font-semibold">Description</label>
                <textarea x-model="createForm.description" name="description" rows="2" class="mt-1 w-full rounded-lg border border-gray-300 px-2 py-1 text-xs shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]"></textarea>
            </div>

            <div class="md:col-span-3 flex justify-end gap-2 pt-2">
                <button type="button" @click="showCreateModal = false" class="px-3 py-1.5 text-sm rounded-lg bg-gray-200 text-gray-800 hover:bg-gray-300">Cancel</button>
                <button type="submit" class="px-3 py-1.5 text-sm rounded-lg bg-[#4A6741] text-white hover:bg-[#3A2E22]">Create Product</button>
            </div>
        </form>
    </div>
</div>
</div>

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
