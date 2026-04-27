@extends('layouts.app')

@section('title', 'Marketplace - BrewHub')

@section('content')
<div class="min-h-screen bg-[#F5F0E8] flex">
    <!-- Sidebar -->
    <aside class="admin-sidebar fixed left-0 top-0 h-screen w-64 bg-[#3A2E22] text-[#F5F0E8] flex flex-col justify-between py-6 px-4 rounded-r-xl shadow-lg overflow-hidden z-40 -translate-x-full md:translate-x-0 transition-transform duration-300 ease-out">
        <div>
            <!-- Logo -->
            <div class="flex items-center mb-8">
              <img src="{{ asset('images/brewhublogo2.png') }}" alt="BrewHub logo" class="w-7 h-7 mr-2 object-contain shrink-0">
              <span class="brand-wordmark text-lg leading-none"><span class="brand-brew">Brew</span><span class="brand-hub">Hub</span></span>
            </div>

            <!-- Navigation -->
            <nav class="space-y-1">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center {{ request()->routeIs('admin.dashboard') ? 'bg-[#4E3D2B]' : '' }} rounded-lg px-4 py-2 text-xs font-medium gap-2 transition-all duration-200 hover:bg-[#4E3D2B] hover:translate-x-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Dashboard
                </a>
                <a href="{{ route('admin.map') }}" class="flex items-center {{ request()->routeIs('admin.map') ? 'bg-[#4E3D2B]' : '' }} rounded-lg px-4 py-2 text-xs font-medium gap-2 transition-all duration-200 hover:bg-[#4E3D2B] hover:translate-x-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                    Map
                </a>
                <a href="{{ route('admin.establishments.index') }}" class="flex items-center {{ request()->routeIs('admin.establishments.*') ? 'bg-[#4E3D2B]' : '' }} rounded-lg px-4 py-2 text-xs font-medium gap-2 transition-all duration-200 hover:bg-[#4E3D2B] hover:translate-x-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Establishments
                </a>
                <a href="{{ route('admin.registrations.index') }}" class="flex items-center {{ request()->routeIs('admin.registrations.*') ? 'bg-[#4E3D2B]' : '' }} rounded-lg px-4 py-2 text-xs font-medium gap-2 transition-all duration-200 hover:bg-[#4E3D2B] hover:translate-x-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                    Registrations
                </a>
                <a href="{{ route('admin.resellers.index') }}" class="flex items-center {{ request()->routeIs('admin.resellers.*') ? 'bg-[#4E3D2B]' : '' }} rounded-lg px-4 py-2 text-xs font-medium gap-2 transition-all duration-200 hover:bg-[#4E3D2B] hover:translate-x-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l9-4 9 4v8l-9 4-9-4V8z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l9 4 9-4" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16" />
                    </svg>
                    Resellers
                </a>
                <a href="{{ route('admin.coupon-promos.index') }}" class="flex items-center {{ request()->routeIs('admin.coupon-promos.*') ? 'bg-[#4E3D2B]' : '' }} rounded-lg px-4 py-2 text-xs font-medium gap-2 transition-all duration-200 hover:bg-[#4E3D2B] hover:translate-x-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    Coupon Promos
                </a>
                <a href="{{ route('admin.rating-moderation.index') }}" class="flex items-center {{ request()->routeIs('admin.rating-moderation.*') ? 'bg-[#4E3D2B]' : '' }} rounded-lg px-4 py-2 text-xs font-medium gap-2 transition-all duration-200 hover:bg-[#4E3D2B] hover:translate-x-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                    Rating Moderation
                </a>
                <a href="{{ route('admin.recommendations') }}" class="flex items-center {{ request()->routeIs('admin.recommendations') ? 'bg-[#4E3D2B]' : '' }} rounded-lg px-4 py-2 text-xs font-medium gap-2 transition-all duration-200 hover:bg-[#4E3D2B] hover:translate-x-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Recommendations
                </a>
                <a href="{{ route('admin.marketplace.index') }}" class="flex items-center {{ request()->routeIs('admin.marketplace.*') ? 'bg-[#4E3D2B]' : '' }} rounded-lg px-4 py-2 text-xs font-medium gap-2 transition-all duration-200 hover:bg-[#4E3D2B] hover:translate-x-1">
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

        <!-- User Profile -->
        <div class="flex items-center justify-between gap-3">
          <div class="flex items-center min-w-0">
            <div class="w-10 h-10 bg-[#4A6741] rounded-full flex items-center justify-center text-white font-bold text-sm mr-3">
                {{ substr(auth()->user()->name, 0, 1) }}
            </div>
            <div>
                <div class="font-medium text-sm">{{ auth()->user()->name }}</div>
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

    <!-- Main Content -->
    <main class="ml-0 md:ml-64 flex-1 p-8 overflow-y-auto" 
      x-data="marketplaceState()" 
      @open-delete="openDelete($event.detail.id, $event.detail.title, $event.detail.type)">
        <!-- Flash Message Alert -->
        @if(session('success'))
            <div id="success-alert" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg flex items-start gap-3 animate-fade-in-up">
                <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <div class="flex-1">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
                <button onclick="document.getElementById('success-alert').remove()" class="text-green-600 hover:text-green-900 flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        @endif

        <!-- Page Header -->
        <div class="flex items-center justify-between mb-8 sticky top-0 z-10 bg-[#F5F0E8]">
            <div>
                <h1 class="text-3xl font-display font-bold text-[#3A2E22] mb-1">
                    Marketplace
                </h1>
                <p class="text-[#9E8C78] text-sm font-medium">View and manage products across all seller types</p>
            </div>
        </div>

        <!-- Stats Row -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <!-- Total Products -->
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-l-[#2C4A2E] p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[#9E8C78] text-sm font-medium">Total Products</p>
                        <p class="text-3xl font-bold text-[#3A2E22] mt-1">{{ $stats['total_products'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-[#2C4A2E]/15 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#2C4A2E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Reseller Listings -->
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-l-[#2C4A2E] p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[#9E8C78] text-sm font-medium">Reseller Listings</p>
                        <p class="text-3xl font-bold text-[#3A2E22] mt-1">{{ $stats['total_reseller_products'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-[#2C4A2E]/15 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#2C4A2E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Orders -->
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-l-[#2C4A2E] p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[#9E8C78] text-sm font-medium">Total Orders</p>
                        <p class="text-3xl font-bold text-[#3A2E22] mt-1">{{ $stats['total_orders'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-[#2C4A2E]/15 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#2C4A2E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Bulk Orders -->
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-l-[#2C4A2E] p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[#9E8C78] text-sm font-medium">Bulk Orders</p>
                        <p class="text-3xl font-bold text-[#3A2E22] mt-1">{{ $stats['total_bulk_orders'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-[#2C4A2E]/15 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#2C4A2E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div x-data="{ 
          tab: 'products', 
          statusFilter: 'all', 
          orderSearch: '', 
          bulkOrderSearch: '', 
          showProductModal: false, 
          selectedProduct: null, 
          showRPModal: false, 
          selectedRP: null
        }" class="mb-8">
            <div class="mb-6 border-b border-gray-200 pb-4">
                <div class="flex gap-2">

                    <button @click="tab = 'products'"
                        class="filter-tab px-4 py-2 text-sm font-medium transition-colors"
                        :style="tab === 'products'
                            ? 'color: #3B2F2F; border-bottom: 3px solid #3B2F2F; background: #F5F0E8;'
                            : 'color: #9E8C78; border-bottom: 3px solid transparent;'">
                        Products
                    </button>

                    <button @click="tab = 'reseller-listings'"
                        class="filter-tab px-4 py-2 text-sm font-medium transition-colors"
                        :style="tab === 'reseller-listings'
                            ? 'color: #3B2F2F; border-bottom: 3px solid #3B2F2F; background: #F5F0E8;'
                            : 'color: #9E8C78; border-bottom: 3px solid transparent;'">
                        Reseller Listings
                    </button>

                    <button @click="tab = 'orders'"
                        class="filter-tab px-4 py-2 text-sm font-medium transition-colors"
                        :style="tab === 'orders'
                            ? 'color: #3B2F2F; border-bottom: 3px solid #3B2F2F; background: #F5F0E8;'
                            : 'color: #9E8C78; border-bottom: 3px solid transparent;'">
                        Orders
                    </button>

                    <button @click="tab = 'bulk-orders'"
                        class="filter-tab px-4 py-2 text-sm font-medium transition-colors"
                        :style="tab === 'bulk-orders'
                            ? 'color: #3B2F2F; border-bottom: 3px solid #3B2F2F; background: #F5F0E8;'
                            : 'color: #9E8C78; border-bottom: 3px solid transparent;'">
                        Bulk Orders
                    </button>

                </div>
            </div>

            <!-- Products Tab -->
            <div x-show="tab === 'products'" class="mt-6">
                @if($products->count() > 0)
                    <div class="flex items-center mb-4">
  <select x-model="categoryFilter"
    class="text-xs border border-gray-200 rounded-lg px-3 py-1.5 bg-white 
    text-[#2C1A0E] focus:outline-none focus:ring-2 focus:ring-[#2C4A2E]">
    <option value="all">All Categories</option>
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
</div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        @foreach($products as $product)
                        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition overflow-hidden"
x-show="categoryFilter === 'all' || categoryFilter === '{{ $product->category }}'"
x-transition:enter="transition ease-out duration-200"
x-transition:enter-start="opacity-0 translate-y-1"
x-transition:enter-end="opacity-100 translate-y-0">
  
  {{-- Image: shorter height --}}
  <div class="relative">
    @if($product->image_url)
      <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
        class="w-full h-36 object-cover">
    @else
      <div class="w-full h-36 bg-gray-100 flex flex-col items-center 
  justify-center rounded-t-xl">
  <p class="text-xs text-[#9E8C78]">No image available</p>
</div>
    @endif

    {{-- View Details icon button - top right of image --}}
    <button 
      @click="selectedProduct = {{ json_encode([
        'name' => $product->name,
        'category' => $product->category,
        'roast_level' => $product->roast_level,
        'grind_type' => $product->grind_type,
        'description' => $product->description,
        'price_per_unit' => $product->price_per_unit,
        'unit' => $product->unit,
        'moq' => $product->moq,
        'seller_name' => $product->seller?->name,
        'seller_type' => $product->seller_label,
        'seller_kind' => $product->seller_type,
        'establishment' => $product->establishment?->name,
        'barangay' => $product->establishment?->barangay,
        'image_url' => $product->image_url,
        'is_active' => $product->is_active,
        'stock_quantity' => $product->stock_quantity,
        'created_at' => $product->created_at?->format('M d, Y'),
      ]) }}; showProductModal = true"
      class="absolute top-2 right-2 bg-white bg-opacity-90 rounded-full p-1.5 
      shadow hover:bg-opacity-100 transition">
      <svg class="w-3.5 h-3.5 text-[#2C1A0E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
      </svg>
    </button>
  </div>

  {{-- Card Body --}}
  <div class="p-3">
    <p class="font-semibold text-sm text-[#2C1A0E] leading-tight truncate">
      {{ $product->name }}
    </p>
    <p class="text-xs italic text-[#9E8C78] mb-1.5">{{ $product->category }}</p>
    
    @if($product->roast_level || $product->grind_type)
    <div class="flex flex-wrap gap-1 mb-2">
      @if($product->roast_level)
        <span class="text-xs bg-[#F5F0E8] text-[#2C1A0E] px-2 py-0.5 rounded-full">
          {{ $product->roast_level }}
        </span>
      @endif
      @if($product->grind_type)
        <span class="text-xs bg-[#F5F0E8] text-[#2C1A0E] px-2 py-0.5 rounded-full">
          {{ $product->grind_type }}
        </span>
      @endif
    </div>
    @endif

    @if($product->seller_type !== 'cafe_owner')
    <div class="flex items-center gap-1 mb-1.5">
      <span class="text-xs text-[#9E8C78]">Available:</span>
      <span class="text-xs font-semibold 
        {{ $product->stock_quantity > 0 ? 'text-green-600' : 'text-red-400' }}">
        {{ $product->stock_quantity }} 
        {{ $product->stock_quantity === 1 ? 'unit' : 'units' }}
      </span>
    </div>
    @endif

    {{-- Seller Info --}}
    <div class="flex items-center gap-2 mb-2">
      <span class="text-xs text-[#2C1A0E]">{{ $product->seller->name }}</span>
      @if($product->seller_type === 'farm_owner')
        <span class="px-2 py-0.5 bg-amber-100 text-amber-800 text-xs rounded-full">Farm Owner</span>
      @elseif($product->seller_type === 'cafe_owner')
        <span class="px-2 py-0.5 bg-green-100 text-green-800 text-xs rounded-full">Café Owner</span>
      @elseif($product->seller_type === 'reseller')
        <span class="px-2 py-0.5 bg-blue-100 text-blue-800 text-xs rounded-full">Reseller</span>
      @endif
    </div>

    @if($product->establishment)
      <p class="text-xs text-[#9E8C78] flex items-center gap-1 mt-1">
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        {{ $product->establishment->name }}
      </p>
    @endif

    <div class="flex items-center {{ $product->seller_type === 'cafe_owner' ? 'justify-start' : 'justify-between' }} mt-2">
      <div>
        <span class="font-bold text-sm text-[#2C1A0E]">
          ₱{{ number_format($product->price_per_unit, 2) }}
        </span>
        @if($product->seller_type !== 'cafe_owner')
        <span class="text-xs text-[#9E8C78]">/{{ $product->unit }}</span>
        @endif
      </div>
      @if($product->seller_type !== 'cafe_owner')
      <span class="text-xs text-[#9E8C78]">MOQ: {{ $product->moq }}</span>
      @endif
    </div>

    {{-- Delete button --}}
    <button 
      @click.stop="$dispatch('open-delete', { 
        id: {{ $product->id }}, 
        title: '{{ addslashes($product->name) }}', 
        type: 'product' 
      })"
      class="w-full flex items-center justify-center gap-1 text-xs text-red-400 
      hover:text-red-600 transition py-1 border border-red-100 
      hover:border-red-300 rounded-lg mt-2">
      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
      </svg>
      Delete
    </button>
  </div>
</div>
                        @endforeach
                    </div>
                    <div class="mt-6">
                        {{ $products->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                        <p class="text-gray-500 text-lg">No products found</p>
                    </div>
                @endif

<div x-show="showProductModal" 
  x-transition:enter="transition ease-out duration-200"
  x-transition:enter-start="opacity-0"
  x-transition:enter-end="opacity-100"
  x-transition:leave="transition ease-in duration-150"
  x-transition:leave-start="opacity-100"
  x-transition:leave-end="opacity-0"
  class="marketplace-details-overlay fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40"
  @click.self="showProductModal = false">

  <div x-show="showProductModal"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 scale-95"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    class="marketplace-details-modal bg-white rounded-2xl shadow-xl w-full max-w-4xl mx-4 overflow-hidden 
max-h-[90vh]">

    <div class="marketplace-details-content flex min-h-72">
      
      {{-- Left: Image --}}
      <div class="marketplace-details-media w-72 flex-shrink-0 min-h-full">
        <template x-if="selectedProduct && selectedProduct.image_url">
          <img :src="selectedProduct.image_url" :alt="selectedProduct.name"
            class="marketplace-details-image w-full h-full object-cover min-h-48">
        </template>
        <template x-if="!selectedProduct || !selectedProduct.image_url">
          <div class="w-full h-full min-h-48 bg-gray-100 flex flex-col 
  items-center justify-center">
  <p class="text-sm text-[#9E8C78]">No image available</p>
</div>
        </template>
      </div>

      {{-- Right: Details --}}
      <div class="marketplace-details-body flex-1 p-7 flex flex-col justify-between overflow-y-auto">
        
        {{-- Header --}}
        <div>
          <div class="marketplace-details-header flex items-start justify-between mb-1">
            <div>
              <h2 class="marketplace-details-title text-lg font-bold text-[#2C1A0E]" x-text="selectedProduct?.name"></h2>
              <p class="text-sm italic text-[#9E8C78]" x-text="selectedProduct?.category"></p>
            </div>
            <button @click="showProductModal = false"
              class="text-gray-400 hover:text-gray-600 transition ml-4">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          {{-- Badges --}}
          <div class="flex flex-wrap gap-1.5 mt-2 mb-3">
            <template x-if="selectedProduct?.roast_level">
              <span class="text-xs bg-[#F5F0E8] text-[#2C1A0E] px-2 py-0.5 rounded-full"
                x-text="selectedProduct.roast_level"></span>
            </template>
            <template x-if="selectedProduct?.grind_type">
              <span class="text-xs bg-[#F5F0E8] text-[#2C1A0E] px-2 py-0.5 rounded-full"
                x-text="selectedProduct.grind_type"></span>
            </template>
          </div>

          <p class="marketplace-details-description text-sm text-[#9E8C78] line-clamp-3" x-text="selectedProduct?.description"></p>
        </div>

        {{-- Info Grid --}}
        <div class="marketplace-details-grid grid grid-cols-3 gap-3 mt-3">
          <div class="bg-[#F5F0E8] rounded-lg p-2.5">
            <p class="text-xs text-[#9E8C78] mb-0.5">Price</p>
            <p class="text-sm font-bold text-[#2C1A0E]">
              ₱<span x-text="parseFloat(selectedProduct?.price_per_unit).toLocaleString('en-PH', {minimumFractionDigits:2})"></span>
              <template x-if="selectedProduct?.seller_kind !== 'cafe_owner'">
                <span>/ <span x-text="selectedProduct?.unit"></span></span>
              </template>
            </p>
          </div>
          <template x-if="selectedProduct?.seller_kind !== 'cafe_owner'">
          <div class="bg-[#F5F0E8] rounded-lg p-2.5">
            <p class="text-xs text-[#9E8C78] mb-0.5">MOQ</p>
            <p class="text-sm font-bold text-[#2C1A0E]" x-text="selectedProduct?.moq"></p>
          </div>
          </template>
          <div class="bg-[#F5F0E8] rounded-lg p-2.5">
            <p class="text-xs text-[#9E8C78] mb-0.5">Published</p>
            <p class="text-sm font-bold"
              :class="selectedProduct?.is_active ? 'text-green-600' : 'text-red-400'"
              x-text="selectedProduct?.is_active ? 'Listed' : 'Unlisted'"></p>
          </div>
          <template x-if="selectedProduct?.seller_kind !== 'cafe_owner'">
          <div class="bg-[#F5F0E8] rounded-lg p-2.5">
            <p class="text-xs text-[#9E8C78] mb-0.5">Available Stock</p>
            <p class="text-sm font-bold"
              :class="selectedProduct?.stock_quantity > 0 
                ? 'text-green-600' : 'text-red-400'"
              x-text="(selectedProduct?.stock_quantity ?? 0) + ' units'">
            </p>
          </div>
          </template>
          <div class="bg-[#F5F0E8] rounded-lg p-2.5">
            <p class="text-xs text-[#9E8C78] mb-0.5">Seller</p>
            <p class="text-sm font-semibold text-[#2C1A0E] truncate" 
              x-text="selectedProduct?.seller_name"></p>
          </div>
          <div class="bg-[#F5F0E8] rounded-lg p-2.5">
            <p class="text-xs text-[#9E8C78] mb-0.5">Role</p>
            <p class="text-sm font-semibold text-[#2C1A0E]" 
              x-text="selectedProduct?.seller_type"></p>
          </div>
          <div class="bg-[#F5F0E8] rounded-lg p-2.5">
            <p class="text-xs text-[#9E8C78] mb-0.5">Listed</p>
            <p class="text-sm font-semibold text-[#2C1A0E]" 
              x-text="selectedProduct?.created_at"></p>
          </div>
          <template x-if="selectedProduct?.establishment">
            <div class="bg-[#F5F0E8] rounded-lg p-2.5">
              <p class="text-xs text-[#9E8C78] mb-0.5">Establishment</p>
              <p class="text-sm font-semibold text-[#2C1A0E]" 
                x-text="selectedProduct.establishment"></p>
            </div>
          </template>
          <template x-if="selectedProduct?.barangay">
            <div class="bg-[#F5F0E8] rounded-lg p-2.5">
              <p class="text-xs text-[#9E8C78] mb-0.5">Barangay</p>
              <p class="text-sm font-semibold text-[#2C1A0E]" 
                x-text="selectedProduct.barangay"></p>
            </div>
          </template>
        </div>

      </div>
    </div>
  </div>
</div>
            </div>

            <!-- Reseller Listings Tab -->
            <div x-show="tab === 'reseller-listings'" class="mt-6">
                @if($resellerProducts->count() > 0)
                    <div class="flex items-center mb-4">
  <select x-model="categoryFilter"
    class="text-xs border border-gray-200 rounded-lg px-3 py-1.5 bg-white 
    text-[#2C1A0E] focus:outline-none focus:ring-2 focus:ring-[#2C4A2E]">
    <option value="all">All Categories</option>
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
</div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        @foreach($resellerProducts as $resellerProduct)
                        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition overflow-hidden"
x-show="categoryFilter === 'all' || categoryFilter === '{{ $resellerProduct->product->category }}'"
x-transition:enter="transition ease-out duration-200"
x-transition:enter-start="opacity-0 translate-y-1"
x-transition:enter-end="opacity-100 translate-y-0">
  
  {{-- Image: shorter height --}}
  <div class="relative">
    @if($resellerProduct->product->image_url)
      <img src="{{ $resellerProduct->product->image_url }}" alt="{{ $resellerProduct->product->name }}"
        class="w-full h-36 object-cover">
    @else
      <div class="w-full h-36 bg-gray-100 flex flex-col items-center 
  justify-center rounded-t-xl">
  <p class="text-xs text-[#9E8C78]">No image available</p>
</div>
    @endif

    {{-- View Details icon button - top right of image --}}
    <button 
      @click="selectedRP = {{ json_encode([
        'product_name' => $resellerProduct->product->name,
        'category' => $resellerProduct->product->category,
        'roast_level' => $resellerProduct->product->roast_level,
        'grind_type' => $resellerProduct->product->grind_type,
        'description' => $resellerProduct->product->description,
        'reseller_price' => $resellerProduct->reseller_price,
        'original_price' => $resellerProduct->product->price_per_unit,
        'unit' => $resellerProduct->product->unit,
        'stock_quantity' => $resellerProduct->stock_quantity,
        'reseller_name' => $resellerProduct->reseller->name,
        'establishment' => $resellerProduct->product->establishment?->name,
        'barangay' => $resellerProduct->product->establishment?->barangay,
        'image_url' => $resellerProduct->product->image_url,
        'created_at' => $resellerProduct->created_at?->format('M d, Y'),
      ]) }}; showRPModal = true"
      class="absolute top-2 right-2 bg-white bg-opacity-90 rounded-full p-1.5 
      shadow hover:bg-opacity-100 transition">
      <svg class="w-3.5 h-3.5 text-[#2C1A0E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
      </svg>
    </button>
  </div>

  {{-- Card Body --}}
  <div class="p-3">
    <p class="font-semibold text-sm text-[#2C1A0E] leading-tight truncate">
      {{ $resellerProduct->product->name }}
    </p>
    <p class="text-xs italic text-[#9E8C78] mb-1.5">{{ $resellerProduct->product->category }}</p>
    
    @if($resellerProduct->product->roast_level || $resellerProduct->product->grind_type)
    <div class="flex flex-wrap gap-1 mb-2">
      @if($resellerProduct->product->roast_level)
        <span class="text-xs bg-[#F5F0E8] text-[#2C1A0E] px-2 py-0.5 rounded-full">
          {{ $resellerProduct->product->roast_level }}
        </span>
      @endif
      @if($resellerProduct->product->grind_type)
        <span class="text-xs bg-[#F5F0E8] text-[#2C1A0E] px-2 py-0.5 rounded-full">
          {{ $resellerProduct->product->grind_type }}
        </span>
      @endif
    </div>
    @endif

    <div class="flex items-center gap-1 mb-1.5">
      <span class="text-xs text-[#9E8C78]">Available:</span>
      <span class="text-xs font-semibold 
        {{ $resellerProduct->stock_quantity > 0 ? 'text-green-600' : 'text-red-400' }}">
        {{ $resellerProduct->stock_quantity }} 
        {{ $resellerProduct->stock_quantity === 1 ? 'unit' : 'units' }}
      </span>
    </div>

    <div class="flex items-center justify-between mt-2">
      <div>
        <span class="font-bold text-sm text-[#2C1A0E]">
          ₱{{ number_format($resellerProduct->reseller_price, 2) }}
        </span>
        <span class="text-xs text-[#9E8C78]">/{{ $resellerProduct->product->unit }}</span>
      </div>
    </div>

    {{-- Delete button --}}
    <button 
      @click.stop="$dispatch('open-delete', { 
        id: {{ $resellerProduct->id }}, 
        title: '{{ addslashes($resellerProduct->product->name) }}', 
        type: 'reseller_product' 
      })"
      class="w-full flex items-center justify-center gap-1 text-xs text-red-400 
      hover:text-red-600 transition py-1 border border-red-100 
      hover:border-red-300 rounded-lg mt-2">
      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
      </svg>
      Delete
    </button>
  </div>
</div>
                        @endforeach
                    </div>
                    <div class="mt-6">
                        {{ $resellerProducts->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <p class="text-gray-500 text-lg">No reseller listings found</p>
                    </div>
                @endif

<div x-show="showRPModal" 
  x-transition:enter="transition ease-out duration-200"
  x-transition:enter-start="opacity-0"
  x-transition:enter-end="opacity-100"
  x-transition:leave="transition ease-in duration-150"
  x-transition:leave-start="opacity-100"
  x-transition:leave-end="opacity-0"
  class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40"
  @click.self="showRPModal = false">

  <div x-show="showRPModal"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 scale-95"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    class="bg-white rounded-2xl shadow-xl w-full max-w-4xl mx-4 overflow-hidden 
max-h-[90vh]">

    <div class="flex min-h-72">
      
      {{-- Left: Image --}}
      <div class="w-72 flex-shrink-0 min-h-full">
        <template x-if="selectedRP && selectedRP.image_url">
          <img :src="selectedRP.image_url" :alt="selectedRP.product_name"
            class="w-full h-full object-cover min-h-48">
        </template>
        <template x-if="!selectedRP || !selectedRP.image_url">
          <div class="w-full h-full min-h-48 bg-gray-100 flex flex-col 
  items-center justify-center">
  <p class="text-sm text-[#9E8C78]">No image available</p>
</div>
        </template>
      </div>

      {{-- Right: Details --}}
      <div class="flex-1 p-7 flex flex-col justify-between overflow-y-auto">
        
        {{-- Header --}}
        <div>
          <div class="flex items-start justify-between mb-1">
            <div>
              <h2 class="text-lg font-bold text-[#2C1A0E]" x-text="selectedRP?.product_name"></h2>
              <p class="text-sm italic text-[#9E8C78]" x-text="selectedRP?.category"></p>
            </div>
            <button @click="showRPModal = false"
              class="text-gray-400 hover:text-gray-600 transition ml-4">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          {{-- Badges --}}
          <div class="flex flex-wrap gap-1.5 mt-2 mb-3">
            <template x-if="selectedRP?.roast_level">
              <span class="text-xs bg-[#F5F0E8] text-[#2C1A0E] px-2 py-0.5 rounded-full"
                x-text="selectedRP.roast_level"></span>
            </template>
            <template x-if="selectedRP?.grind_type">
              <span class="text-xs bg-[#F5F0E8] text-[#2C1A0E] px-2 py-0.5 rounded-full"
                x-text="selectedRP.grind_type"></span>
            </template>
          </div>

          <p class="text-sm text-[#9E8C78] line-clamp-3" x-text="selectedRP?.description"></p>
        </div>

        {{-- Info Grid --}}
        <div class="grid grid-cols-3 gap-3 mt-3">
          <div class="bg-[#F5F0E8] rounded-lg p-2.5">
            <p class="text-xs text-[#9E8C78] mb-0.5">Reseller Price</p>
            <p class="text-sm font-bold text-[#2C1A0E]">
              ₱<span x-text="parseFloat(selectedRP?.reseller_price).toLocaleString('en-PH', {minimumFractionDigits:2})"></span>
              / <span x-text="selectedRP?.unit"></span>
            </p>
          </div>
          <div class="bg-[#F5F0E8] rounded-lg p-2.5">
            <p class="text-xs text-[#9E8C78] mb-0.5">Original Price</p>
            <p class="text-sm font-bold text-[#9E8C78] line-through">
              ₱<span x-text="parseFloat(selectedRP?.original_price).toLocaleString('en-PH', {minimumFractionDigits:2})"></span>
            </p>
          </div>
          <div class="bg-[#F5F0E8] rounded-lg p-2.5">
            <p class="text-xs text-[#9E8C78] mb-0.5">Available Stock</p>
            <p class="text-sm font-bold"
              :class="selectedRP?.stock_quantity > 0 
                ? 'text-green-600' : 'text-red-400'"
              x-text="(selectedRP?.stock_quantity ?? 0) + ' units'">
            </p>
          </div>
          <div class="bg-[#F5F0E8] rounded-lg p-2.5">
            <p class="text-xs text-[#9E8C78] mb-0.5">Reseller</p>
            <p class="text-sm font-semibold text-[#2C1A0E] truncate" 
              x-text="selectedRP?.reseller_name"></p>
          </div>
          <div class="bg-[#F5F0E8] rounded-lg p-2.5">
            <p class="text-xs text-[#9E8C78] mb-0.5">Listed</p>
            <p class="text-sm font-semibold text-[#2C1A0E]" 
              x-text="selectedRP?.created_at"></p>
          </div>
          <template x-if="selectedRP?.establishment">
            <div class="bg-[#F5F0E8] rounded-lg p-2.5">
              <p class="text-xs text-[#9E8C78] mb-0.5">Establishment</p>
              <p class="text-sm font-semibold text-[#2C1A0E]" 
                x-text="selectedRP.establishment"></p>
            </div>
          </template>
          <template x-if="selectedRP?.barangay">
            <div class="bg-[#F5F0E8] rounded-lg p-2.5">
              <p class="text-xs text-[#9E8C78] mb-0.5">Barangay</p>
              <p class="text-sm font-semibold text-[#2C1A0E]" 
                x-text="selectedRP.barangay"></p>
            </div>
          </template>
        </div>

      </div>
    </div>
  </div>
</div>
            </div>

            <!-- Orders Tab -->
            <div x-show="tab === 'orders'" class="mt-6">
                @if($orders->count() > 0)
                    <!-- Filter Row -->
                    <div class="flex items-center justify-between mb-4">
                        <!-- Status Dropdown -->
                        <select x-model="statusFilter" class="text-xs border border-gray-200 rounded-lg px-3 py-1.5 bg-white text-[#2C1A0E] focus:outline-none focus:ring-2 focus:ring-[#2C4A2E]">
                            <option value="all">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <div class="flex items-center gap-3">
                            <!-- Search Input -->
                            <div class="relative">
                                <svg class="w-4 h-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-[#9E8C78]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <input type="text" x-model="orderSearch" placeholder="Search by customer, product..." class="pl-9 pr-3 py-1.5 text-xs bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2C4A2E] focus:border-transparent w-64" />
                            </div>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr style="background-color: #3B2F2F;">
                                    <th class="px-6 py-3 text-left text-sm font-medium uppercase text-white">#</th>
                                    <th class="px-6 py-3 text-left text-sm font-medium uppercase text-white">Customer</th>
                                    <th class="px-6 py-3 text-left text-sm font-medium uppercase text-white">Product</th>
                                    <th class="px-6 py-3 text-left text-sm font-medium uppercase text-white">Qty</th>
                                    <th class="px-6 py-3 text-left text-sm font-medium uppercase text-white">Total</th>
                                    <th class="px-6 py-3 text-left text-sm font-medium uppercase text-white">Status</th>
                                    <th class="px-6 py-3 text-left text-sm font-medium uppercase text-white">Date</th>
                                </tr>
                            </thead>
                            <tbody id="orders-tbody">
                                @foreach($orders as $index => $order)
                                <tr class="order-row border-b border-gray-100 hover:bg-[#FAF7F2] transition-colors"
                                    data-status="{{ strtolower($order->status) }}"
                                    data-customer="{{ strtolower($order->user->name) }}"
                                    data-product="{{ strtolower($order->product->name) }}"
                                    x-show="(statusFilter === 'all' || statusFilter === '{{ $order->status }}') && (orderSearch === '' || '{{ strtolower($order->user->name) }}'.includes(orderSearch.toLowerCase()) || '{{ strtolower($order->product->name) }}'.includes(orderSearch.toLowerCase()))"
                                    style="background-color: {{ $index % 2 === 1 ? '#FAF7F2' : '#FFFFFF' }};">
                                    <td class="px-6 py-4 text-sm font-medium text-[#3A2E22]">{{ $order->id }}</td>
                                    <td class="px-6 py-4 text-sm font-medium text-[#3A2E22]">{{ $order->user->name }}</td>
                                    <td class="px-6 py-4 text-sm text-[#9E8C78]">{{ $order->product->name }}</td>
                                    <td class="px-6 py-4 text-sm text-[#9E8C78]">{{ $order->quantity }}</td>
                                    <td class="px-6 py-4 text-sm text-[#9E8C78]">₱{{ number_format($order->total_price, 2) }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        @if($order->status === 'pending')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Pending</span>
                                        @elseif($order->status === 'confirmed')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Confirmed</span>
                                        @elseif($order->status === 'completed')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Completed</span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">Cancelled</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-[#9E8C78]">{{ $order->created_at->format('M d, Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-6">
                        {{ $orders->links() }}
                    </div>
                @else
                    <div class="py-12 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 text-gray-300 mx-auto mb-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="text-gray-400 font-medium mb-2">No orders yet</h3>
                        <p class="text-gray-400 text-sm text-center">Orders will appear here once customers start purchasing products.</p>
                    </div>
                @endif
            </div>

            <!-- Bulk Orders Tab -->
            <div x-show="tab === 'bulk-orders'" class="mt-6">
                @if($bulkOrders->count() > 0)
                    <!-- Filter Row -->
                    <div class="flex items-center justify-between mb-4">
                        <!-- Status Dropdown -->
                        <select x-model="statusFilter" class="text-xs border border-gray-200 rounded-lg px-3 py-1.5 bg-white text-[#2C1A0E] focus:outline-none focus:ring-2 focus:ring-[#2C4A2E]">
                            <option value="all">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <div class="flex items-center gap-3">
                            <!-- Search Input -->
                            <div class="relative">
                                <svg class="w-4 h-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-[#9E8C78]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <input type="text" x-model="bulkOrderSearch" placeholder="Search by reseller, product..." class="pl-9 pr-3 py-1.5 text-xs bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2C4A2E] focus:border-transparent w-64" />
                            </div>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr style="background-color: #3B2F2F;">
                                    <th class="px-6 py-3 text-left text-sm font-medium uppercase text-white">#</th>
                                    <th class="px-6 py-3 text-left text-sm font-medium uppercase text-white">Reseller</th>
                                    <th class="px-6 py-3 text-left text-sm font-medium uppercase text-white">Product</th>
                                    <th class="px-6 py-3 text-left text-sm font-medium uppercase text-white">Qty (kg)</th>
                                    <th class="px-6 py-3 text-left text-sm font-medium uppercase text-white">Total</th>
                                    <th class="px-6 py-3 text-left text-sm font-medium uppercase text-white">Status</th>
                                    <th class="px-6 py-3 text-left text-sm font-medium uppercase text-white">Delivery Date</th>
                                </tr>
                            </thead>
                            <tbody id="bulk-orders-tbody">
                                @foreach($bulkOrders as $index => $bulkOrder)
                                <tr class="bulk-order-row border-b border-gray-100 hover:bg-[#FAF7F2] transition-colors"
                                    data-status="{{ strtolower($bulkOrder->status) }}"
                                    data-reseller="{{ strtolower($bulkOrder->reseller->name) }}"
                                    data-product="{{ strtolower($bulkOrder->product->name) }}"
                                    x-show="(statusFilter === 'all' || statusFilter === '{{ $bulkOrder->status }}') && (bulkOrderSearch === '' || '{{ strtolower($bulkOrder->reseller->name) }}'.includes(bulkOrderSearch.toLowerCase()) || '{{ strtolower($bulkOrder->product->name) }}'.includes(bulkOrderSearch.toLowerCase()))"
                                    style="background-color: {{ $index % 2 === 1 ? '#FAF7F2' : '#FFFFFF' }};">
                                    <td class="px-6 py-4 text-sm font-medium text-[#3A2E22]">{{ $bulkOrder->id }}</td>
                                    <td class="px-6 py-4 text-sm font-medium text-[#3A2E22]">{{ $bulkOrder->reseller->name }}</td>
                                    <td class="px-6 py-4 text-sm text-[#9E8C78]">{{ $bulkOrder->product->name }}</td>
                                    <td class="px-6 py-4 text-sm text-[#9E8C78]">{{ $bulkOrder->quantity_kg }}</td>
                                    <td class="px-6 py-4 text-sm text-[#9E8C78]">₱{{ number_format($bulkOrder->total_price, 2) }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        @if($bulkOrder->status === 'pending')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Pending</span>
                                        @elseif($bulkOrder->status === 'confirmed')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Confirmed</span>
                                        @elseif($bulkOrder->status === 'completed')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Completed</span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">Cancelled</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-[#9E8C78]">{{ $bulkOrder->delivery_date ? \Carbon\Carbon::parse($bulkOrder->delivery_date)->format('M d, Y') : '—' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-6">
                        {{ $bulkOrders->links() }}
                    </div>
                @else
                    <div class="py-12 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 text-gray-300 mx-auto mb-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <h3 class="text-gray-400 font-medium mb-2">No bulk orders yet</h3>
                        <p class="text-gray-400 text-sm text-center">Bulk orders will appear here once resellers start placing large orders.</p>
                    </div>
                @endif
            </div>
        </div>

<!-- Delete Confirmation Modal -->
<div class="fixed inset-0 z-50 flex items-center justify-center px-4" 
  x-show="deleteIsOpen" 
  @keydown.escape.window="closeDelete()"
  x-transition:enter="transition ease-out duration-200"
  x-transition:enter-start="opacity-0 scale-95"
  x-transition:enter-end="opacity-100 scale-100"
  x-transition:leave="transition ease-in duration-150"
  x-transition:leave-start="opacity-100 scale-100"
  x-transition:leave-end="opacity-0 scale-95"
  @click="closeDelete()"
  style="display: none;">

  <div class="fixed inset-0 bg-black bg-opacity-40 backdrop-blur-sm"
    @click.stop="closeDelete()"></div>

  <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full"
    @click.stop>
    <div class="p-6">
      <h2 class="text-2xl font-display font-bold text-[#3A2E22] mb-4">
        Delete Product?
      </h2>
      <p class="text-[#3A2E22] mb-6">
        Are you sure you want to delete 
        <span class="font-semibold" x-text="deleteItemTitle"></span>? 
        This action cannot be undone.
      </p>
      <div class="flex gap-3">
        <button @click="closeDelete()"
          class="flex-1 px-4 py-2 rounded-lg border border-gray-300 
          text-gray-700 font-medium hover:bg-gray-50 transition-colors">
          Cancel
        </button>
        <button @click="confirmDelete()"
          class="flex-1 px-4 py-2 rounded-lg bg-red-600 text-white 
          font-medium hover:bg-red-700 transition-colors">
          Delete
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Hidden Delete Form -->
<form id="marketplace-delete-form" method="POST" style="display: none;">
  @csrf
  @method('DELETE')
</form>

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

    </main>
</div>

@push('styles')
<style>
  @media (max-width: 767px) {
    .marketplace-details-overlay {
      align-items: center !important;
      justify-content: center !important;
      overflow-y: auto;
      min-height: 100dvh;
      padding: 0.85rem;
    }

    .marketplace-details-modal {
      width: 100% !important;
      max-width: 26rem !important;
      margin: 0 auto !important;
      max-height: none !important;
      border-radius: 14px !important;
    }

    .marketplace-details-content {
      display: block !important;
      min-height: 0 !important;
    }

    .marketplace-details-media {
      width: 100% !important;
      min-height: 0 !important;
    }

    .marketplace-details-image,
    .marketplace-details-media > div {
      min-height: 165px !important;
      height: 165px !important;
    }

    .marketplace-details-body {
      padding: 0.85rem !important;
      gap: 0.5rem;
      overflow: visible;
    }

    .marketplace-details-header {
      margin-bottom: 0.3rem !important;
      gap: 0.5rem;
    }

    .marketplace-details-title {
      font-size: 1rem !important;
      line-height: 1.3rem;
      overflow-wrap: anywhere;
    }

    .marketplace-details-description {
      font-size: 0.78rem !important;
      line-height: 1.2rem;
      overflow-wrap: anywhere;
    }

    .marketplace-details-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
      gap: 0.5rem !important;
      margin-top: 0.55rem !important;
    }

    .marketplace-details-grid > div {
      padding: 0.55rem !important;
      border-radius: 10px;
      min-width: 0;
    }

    .marketplace-details-grid p.text-sm {
      font-size: 0.78rem !important;
      line-height: 1.1rem;
      word-break: break-word;
    }
  }
</style>
@endpush

<script>
function marketplaceState() {
  return {
    tab: 'products',
    statusFilter: 'all',
    categoryFilter: 'all',
    showProductModal: false,
    selectedProduct: null,
    showRPModal: false,
    selectedRP: null,
    deleteIsOpen: false,
    deleteItemId: null,
    deleteItemTitle: '',
    deleteItemType: '',

    openDelete(id, title, type) {
      this.deleteItemId = id;
      this.deleteItemTitle = title;
      this.deleteItemType = type;
      this.deleteIsOpen = true;
    },

    closeDelete() {
      this.deleteIsOpen = false;
      this.deleteItemId = null;
      this.deleteItemTitle = '';
      this.deleteItemType = '';
    },

    confirmDelete() {
      if (!this.deleteItemId) return;
      const routes = {
        'product': '/admin/marketplace/products/',
        'reseller_product': '/admin/marketplace/reseller-products/'
      };
      const form = document.getElementById('marketplace-delete-form');
      form.action = routes[this.deleteItemType] + this.deleteItemId;
      form.submit();
    }
  }
}

  const scrollToTopButton = document.getElementById('scrollToTopButton');
  if (scrollToTopButton) {
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
  }
</script>

@endsection