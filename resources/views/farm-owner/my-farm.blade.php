@extends('farm-owner.layouts.app')

@php
    $title = 'Farm';
@endphp

@section('title', 'Farm - BrewHub')

@section('content')
<div class="farm-profile-header mb-8">
    <h1 class="text-3xl font-display font-bold text-[#3A2E22] mb-1">
        Farm <span class="italic text-[#4A6741]">Profile</span>
    </h1>
    <p class="text-[#9E8C78] text-sm font-medium">Manage your farm details</p>

    @if(($managedFarms ?? collect())->count() > 1)
        <form method="GET" action="{{ route('farm-owner.my-farm') }}" class="mt-4 inline-flex items-center gap-2">
            <label for="farm-switch" class="text-xs font-semibold text-[#6B5B4A]">Switch Farm</label>
            <select id="farm-switch" name="farm_id" onchange="this.form.submit()" class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs text-[#3A2E22] focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]">
                @foreach(($managedFarms ?? collect()) as $farmOption)
                    <option value="{{ $farmOption->id }}" @selected((int) $farmOption->id === (int) ($establishment->id ?? 0))>
                        {{ $farmOption->name }}
                    </option>
                @endforeach
            </select>
        </form>
    @endif
</div>

<style>
    @media (max-width: 767px) {
        .farm-profile-header {
            margin-bottom: 1rem !important;
        }

        .farm-profile-header h1 {
            font-size: 1.7rem !important;
            line-height: 2rem;
        }

        .farm-profile-header form {
            width: 100%;
            flex-wrap: wrap;
        }

        .farm-profile-header form select {
            width: 100%;
        }
    }
</style>

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

<div
    x-data="{
        editMode: false,
        imagePreviewUrl: @js($establishment->image ?? ''),
        initialImagePreviewUrl: @js($establishment->image ?? ''),
        bannerFitX: @js((int) ($establishment->banner_focus_x ?? 50)),
        bannerFitY: @js((int) ($establishment->banner_focus_y ?? 50)),
        profileFitX: @js((int) ($establishment->profile_focus_x ?? 50)),
        profileFitY: @js((int) ($establishment->profile_focus_y ?? 50)),
        initialBannerFitX: @js((int) ($establishment->banner_focus_x ?? 50)),
        initialBannerFitY: @js((int) ($establishment->banner_focus_y ?? 50)),
        initialProfileFitX: @js((int) ($establishment->profile_focus_x ?? 50)),
        initialProfileFitY: @js((int) ($establishment->profile_focus_y ?? 50)),
        imageObjectUrl: null,
        fitDragMoveHandler: null,
        fitDragUpHandler: null,
        handleImageChange(event) {
            const file = event.target.files && event.target.files[0] ? event.target.files[0] : null;
            if (!file) {
                return;
            }

            if (this.imageObjectUrl) {
                URL.revokeObjectURL(this.imageObjectUrl);
            }

            this.imageObjectUrl = URL.createObjectURL(file);
            this.imagePreviewUrl = this.imageObjectUrl;
        },
        clearImageSelection() {
            if (this.imageObjectUrl) {
                URL.revokeObjectURL(this.imageObjectUrl);
                this.imageObjectUrl = null;
            }

            this.imagePreviewUrl = this.initialImagePreviewUrl;
            this.bannerFitX = this.initialBannerFitX;
            this.bannerFitY = this.initialBannerFitY;
            this.profileFitX = this.initialProfileFitX;
            this.profileFitY = this.initialProfileFitY;

            if (this.$refs.profileImageInput) {
                this.$refs.profileImageInput.value = '';
            }
        },
        fitStyle(x, y) {
            return `object-position: ${x}% ${y}%;`;
        },
        beginFitDrag(target, event) {
            if (!this.editMode || !this.imagePreviewUrl) {
                return;
            }

            const rect = event.currentTarget.getBoundingClientRect();
            const startX = event.clientX;
            const startY = event.clientY;
            const startFitX = target === 'banner' ? this.bannerFitX : this.profileFitX;
            const startFitY = target === 'banner' ? this.bannerFitY : this.profileFitY;

            event.preventDefault();

            this.fitDragMoveHandler = (moveEvent) => {
                const deltaX = ((moveEvent.clientX - startX) / Math.max(rect.width, 1)) * 100;
                const deltaY = ((moveEvent.clientY - startY) / Math.max(rect.height, 1)) * 100;
                const nextX = Math.max(0, Math.min(100, startFitX + deltaX));
                const nextY = Math.max(0, Math.min(100, startFitY + deltaY));

                if (target === 'banner') {
                    this.bannerFitX = nextX;
                    this.bannerFitY = nextY;
                } else {
                    this.profileFitX = nextX;
                    this.profileFitY = nextY;
                }
            };

            this.fitDragUpHandler = () => {
                if (this.fitDragMoveHandler) {
                    window.removeEventListener('pointermove', this.fitDragMoveHandler);
                }

                if (this.fitDragUpHandler) {
                    window.removeEventListener('pointerup', this.fitDragUpHandler);
                }

                this.fitDragMoveHandler = null;
                this.fitDragUpHandler = null;
            };

            window.addEventListener('pointermove', this.fitDragMoveHandler);
            window.addEventListener('pointerup', this.fitDragUpHandler);
        }
    }"
    class="space-y-8"
>
    <form method="POST" action="{{ route('farm-owner.my-farm') }}" enctype="multipart/form-data" class="space-y-8">
        @csrf
        @method('PATCH')

        <input type="hidden" name="farm_id" value="{{ $establishment->id ?? '' }}">
        <input type="hidden" name="latitude" value="{{ old('latitude', $establishment->latitude) }}">
        <input type="hidden" name="longitude" value="{{ old('longitude', $establishment->longitude) }}">

        <input type="hidden" name="banner_focus_x" :value="Math.round(bannerFitX)">
        <input type="hidden" name="banner_focus_y" :value="Math.round(bannerFitY)">
        <input type="hidden" name="profile_focus_x" :value="Math.round(profileFitX)">
        <input type="hidden" name="profile_focus_y" :value="Math.round(profileFitY)">

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div
                class="h-48 w-full bg-gradient-to-r from-[#4A6741] via-[#6B3A2A] to-[#3A2E22]"
                :class="editMode && imagePreviewUrl ? 'cursor-grab active:cursor-grabbing' : ''"
                @pointerdown="beginFitDrag('banner', $event)"
            >
                <template x-if="imagePreviewUrl">
                    <img :src="imagePreviewUrl" alt="Farm cover" class="h-full w-full object-cover" :style="fitStyle(bannerFitX, bannerFitY)" />
                </template>
            </div>

            <div class="px-8 pb-8 pt-4">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                    <div class="flex items-start gap-4">
                        <div
                            class="-mt-12 ml-6 relative w-24 h-24 rounded-full bg-[#F5F0E8] ring-4 ring-white shadow-sm overflow-hidden flex items-center justify-center text-[#4A6741] font-semibold text-xs text-center px-2 shrink-0"
                            :class="editMode && imagePreviewUrl ? 'cursor-grab active:cursor-grabbing' : ''"
                            @pointerdown="beginFitDrag('profile', $event)"
                        >
                            <template x-if="imagePreviewUrl">
                                <img :src="imagePreviewUrl" alt="Farm" class="absolute inset-0 w-full h-full max-w-none object-cover" :style="fitStyle(profileFitX, profileFitY)" />
                            </template>
                            <template x-if="!imagePreviewUrl">
                                Farm Image
                            </template>
                        </div>
                        <div class="pt-4">
                            <template x-if="!editMode">
                                <div>
                                    <h2 class="text-3xl font-display font-bold text-[#3A2E22]">{{ $establishment->name ?: 'Unnamed Farm' }}</h2>
                                    <p class="text-sm text-[#9E8C78] mt-1">{{ $establishment->address ?: 'No address yet' }}{{ $establishment->barangay ? ', ' . $establishment->barangay : '' }}</p>
                                </div>
                            </template>
                            <template x-if="editMode">
                                <div class="space-y-3">
                                    <input name="name" value="{{ old('name', $establishment->name) }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]" placeholder="Farm name" required />
                                    <input name="address" value="{{ old('address', $establishment->address) }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]" placeholder="Address" />
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 pt-4">
                        <button
                            type="button"
                            @click="editMode = !editMode"
                            class="px-4 py-2 rounded-lg bg-[#3A2E22] text-white text-sm font-semibold hover:bg-[#2A2119] transition-colors"
                            x-text="editMode ? 'Cancel' : 'Edit Farm'"
                        ></button>
                        <button type="submit" class="px-5 py-2 rounded-lg bg-[#4A6741] text-white text-sm font-semibold hover:bg-[#3A2E22] transition-colors" x-show="editMode" x-cloak>
                            Save Farm
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-sm p-8">
                    <h3 class="text-2xl font-display font-bold text-[#3A2E22] mb-1">About</h3>
                    <p class="text-[#9E8C78] text-sm mb-4">Farm identity and story</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-[#3A2E22]">Type</label>
                            <template x-if="!editMode">
                                <p class="mt-1 text-sm text-[#6B5B4A]">{{ $establishment->type ?: 'N/A' }}</p>
                            </template>
                            <input x-show="editMode" x-cloak name="type" value="{{ old('type', $establishment->type) }}" disabled class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-2 text-[#6B5B4A] shadow-sm cursor-not-allowed" placeholder="farm/cafe/roaster" />
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-[#3A2E22]">Image</label>
                            <template x-if="!editMode">
                                <p class="mt-1 text-sm text-[#6B5B4A]">{{ $establishment->image ? 'Image uploaded' : 'N/A' }}</p>
                            </template>
                            <input x-show="editMode" x-cloak x-ref="profileImageInput" type="file" name="image" accept="image/*" @change="handleImageChange($event)" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm shadow-sm file:mr-3 file:rounded-md file:border-0 file:bg-[#4A6741] file:px-2.5 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-[#3A2E22] focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]" />

                            <div x-show="editMode && imagePreviewUrl" x-cloak class="mt-3 rounded-lg border border-gray-200 bg-[#F5F0E8] p-3 space-y-3">
                                <div class="flex items-center justify-between">
                                    <p class="text-[11px] text-[#6B5B4A] font-semibold">Drag the banner and profile photo above to adjust fit</p>
                                    <button type="button" @click="clearImageSelection()" class="text-[11px] text-[#4A6741] hover:text-[#3A2E22] font-semibold">Reset Selection</button>
                                </div>
                                <p class="text-[11px] text-[#9E8C78]">Tip: drag left/right/up/down directly on each photo area.</p>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-[#3A2E22]">Description</label>
                            <template x-if="!editMode">
                                <p class="mt-1 text-sm text-[#6B5B4A] leading-relaxed">{{ $establishment->description ?: 'No description provided yet.' }}</p>
                            </template>
                            <textarea x-show="editMode" x-cloak name="description" rows="4" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]" placeholder="Tell customers about your farm...">{{ old('description', $establishment->description) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-8">
                    <h3 class="text-2xl font-display font-bold text-[#3A2E22] mb-1">Location</h3>
                    <p class="text-[#9E8C78] text-sm mb-4">Address and map coordinates</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-[#3A2E22]">Address</label>
                            <template x-if="!editMode">
                                <p class="mt-1 text-sm text-[#6B5B4A]">{{ $establishment->address ?: 'N/A' }}</p>
                            </template>
                            <input x-show="editMode" x-cloak name="address" value="{{ old('address', $establishment->address) }}" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]" />
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-[#3A2E22]">Barangay</label>
                            <template x-if="!editMode">
                                <p class="mt-1 text-sm text-[#6B5B4A]">{{ $establishment->barangay ?: 'N/A' }}</p>
                            </template>
                            <input x-show="editMode" x-cloak name="barangay" value="{{ old('barangay', $establishment->barangay) }}" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]" />
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-[#3A2E22]">Latitude</label>
                            <template x-if="!editMode">
                                <p class="mt-1 text-sm text-[#6B5B4A]">{{ $establishment->latitude ?: 'N/A' }}</p>
                            </template>
                            <input x-show="editMode" x-cloak name="latitude" value="{{ old('latitude', $establishment->latitude) }}" disabled class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-2 text-[#6B5B4A] shadow-sm cursor-not-allowed" />
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-[#3A2E22]">Longitude</label>
                            <template x-if="!editMode">
                                <p class="mt-1 text-sm text-[#6B5B4A]">{{ $establishment->longitude ?: 'N/A' }}</p>
                            </template>
                            <input x-show="editMode" x-cloak name="longitude" value="{{ old('longitude', $establishment->longitude) }}" disabled class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-2 text-[#6B5B4A] shadow-sm cursor-not-allowed" />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-8">
                    <h3 class="text-2xl font-display font-bold text-[#3A2E22] mb-1">Contact</h3>
                    <p class="text-[#9E8C78] text-sm mb-4">Contact channels for buyers and visitors</p>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-[#3A2E22]">Contact Number</label>
                            <template x-if="!editMode">
                                <p class="mt-1 text-sm text-[#6B5B4A]">{{ $establishment->contact_number ?: 'N/A' }}</p>
                            </template>
                            <input x-show="editMode" x-cloak name="contact_number" value="{{ old('contact_number', $establishment->contact_number) }}" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]" />
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-[#3A2E22]">Email</label>
                            <template x-if="!editMode">
                                <p class="mt-1 text-sm text-[#6B5B4A]">{{ $establishment->email ?: 'N/A' }}</p>
                            </template>
                            <input x-show="editMode" x-cloak type="email" name="email" value="{{ old('email', $establishment->email) }}" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]" />
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-[#3A2E22]">Website</label>
                            <template x-if="!editMode">
                                <p class="mt-1 text-sm text-[#6B5B4A] break-all">{{ $establishment->website ?: 'N/A' }}</p>
                            </template>
                            <input x-show="editMode" x-cloak type="url" name="website" value="{{ old('website', $establishment->website) }}" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]" />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-8">
                    <h3 class="text-2xl font-display font-bold text-[#3A2E22] mb-1">Operating Hours</h3>
                    <p class="text-[#9E8C78] text-sm mb-4">When your farm is open and available activities</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-[#3A2E22]">Visit Hours</label>
                            <template x-if="!editMode">
                                <p class="mt-1 text-sm text-[#6B5B4A]">{{ $establishment->visit_hours ?: 'N/A' }}</p>
                            </template>
                            <input x-show="editMode" x-cloak name="visit_hours" value="{{ old('visit_hours', $establishment->visit_hours) }}" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]" placeholder="Mon-Sat, 8:00 AM - 5:00 PM" />
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-[#3A2E22]">Activities</label>
                            <template x-if="!editMode">
                                <p class="mt-1 text-sm text-[#6B5B4A]">{{ $establishment->activities ?: 'N/A' }}</p>
                            </template>
                            <input x-show="editMode" x-cloak name="activities" value="{{ old('activities', $establishment->activities) }}" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]" placeholder="Farm tour, Cupping" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm p-8">
                    <h3 class="text-2xl font-display font-bold text-[#3A2E22] mb-1">Coffee Varieties</h3>
                    <p class="text-[#9E8C78] text-sm mb-4">Varieties offered by your farm</p>

                    <div class="space-y-3">
                        @foreach($allVarieties as $variety)
                            @php
                                $selected = in_array($variety->id, old('varieties', $selectedVarietyIds), true);
                                $oldPrimary = old('primary_variety', $primaryVarietyId);
                            @endphp

                            <div class="flex items-center gap-2 rounded-lg border border-gray-200 bg-[#F5F0E8] px-3 py-2">
                                <input
                                    type="checkbox"
                                    name="varieties[]"
                                    value="{{ $variety->id }}"
                                    @checked($selected)
                                    :disabled="!editMode"
                                    class="rounded border-gray-300 text-[#4A6741] focus:ring-[#4A6741]"
                                />
                                <span class="w-3 h-3 rounded-full" style="background: {{ $variety->color ?: '#4A6741' }}"></span>
                                <span class="flex-1 text-sm font-medium text-[#3A2E22]">{{ $variety->name }}</span>
                                <label class="text-xs text-[#6B5B4A] flex items-center gap-1">
                                    <input
                                        type="radio"
                                        name="primary_variety"
                                        value="{{ $variety->id }}"
                                        @checked((int) $oldPrimary === (int) $variety->id)
                                        :disabled="!editMode"
                                        class="text-[#4A6741] focus:ring-[#4A6741]"
                                    />
                                    Primary
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-8">
                    <h3 class="text-xl font-display font-bold text-[#3A2E22] mb-2">Profile Metadata</h3>
                    <div class="space-y-2 text-sm text-[#6B5B4A]">
                        <p><span class="font-semibold text-[#3A2E22]">ID:</span> {{ $establishment->id }}</p>
                        <p><span class="font-semibold text-[#3A2E22]">Owner ID:</span> {{ $establishment->owner_id }}</p>
                        <p><span class="font-semibold text-[#3A2E22]">Created:</span> {{ optional($establishment->created_at)->format('M d, Y h:i A') ?: 'N/A' }}</p>
                        <p><span class="font-semibold text-[#3A2E22]">Updated:</span> {{ optional($establishment->updated_at)->format('M d, Y h:i A') ?: 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
