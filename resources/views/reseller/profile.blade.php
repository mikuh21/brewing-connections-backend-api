@extends('reseller.layouts.app')

@php
    $title = 'My Profile';
    $resellerRecord = $resellerRecord ?? (object) [];
    $resellerColumns = $resellerColumns ?? [];
    $userLocationColumns = $userLocationColumns ?? [];
    $allVarieties = $allVarieties ?? collect();
    $selectedVarietyIds = $selectedVarietyIds ?? [];
    $primaryVarietyId = $primaryVarietyId ?? null;
    $resellerFieldMeta = $resellerFieldMeta ?? [];
    $userPhotoColumn = $userPhotoColumn ?? null;
    $currentProfilePhoto = $currentProfilePhoto ?? null;
    $canAdjustProfilePhoto = $canAdjustProfilePhoto ?? false;
    $profileFocusX = isset($profileFocusX) ? (int) $profileFocusX : 50;
    $profileFocusY = isset($profileFocusY) ? (int) $profileFocusY : 50;
@endphp

@section('title', 'Reseller Profile - BrewHub')

@section('content')
<div class="reseller-profile-page">
<div class="mb-8">
    <h1 class="text-3xl font-display font-bold text-[#3A2E22] mb-1">
        Reseller <span class="italic text-[#4A6741]">Profile</span>
    </h1>
    <p class="text-[#9E8C78] text-sm font-medium">Manage your reseller information and account details</p>
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

<div
    x-data="{
        editMode: false,
        profilePreviewUrl: @js($currentProfilePhoto ?? ''),
        initialProfilePreviewUrl: @js($currentProfilePhoto ?? ''),
        profileFitX: @js($profileFocusX),
        profileFitY: @js($profileFocusY),
        initialProfileFitX: @js($profileFocusX),
        initialProfileFitY: @js($profileFocusY),
        profileObjectUrl: null,
        fitDragMoveHandler: null,
        fitDragUpHandler: null,
        handleProfilePhotoChange(event) {
            const file = event.target.files && event.target.files[0] ? event.target.files[0] : null;
            if (!file) {
                return;
            }

            if (this.profileObjectUrl) {
                URL.revokeObjectURL(this.profileObjectUrl);
            }

            this.profileObjectUrl = URL.createObjectURL(file);
            this.profilePreviewUrl = this.profileObjectUrl;
            this.profileFitX = 50;
            this.profileFitY = 50;
        },
        fitStyle(x, y) {
            return `object-position: ${x}% ${y}%;`;
        },
        beginProfileFitDrag(event) {
            if (!this.editMode || !this.profilePreviewUrl) {
                return;
            }

            const rect = event.currentTarget.getBoundingClientRect();
            const startX = event.clientX;
            const startY = event.clientY;
            const startFitX = this.profileFitX;
            const startFitY = this.profileFitY;

            event.preventDefault();

            this.fitDragMoveHandler = (moveEvent) => {
                const deltaX = ((moveEvent.clientX - startX) / Math.max(rect.width, 1)) * 100;
                const deltaY = ((moveEvent.clientY - startY) / Math.max(rect.height, 1)) * 100;
                this.profileFitX = Math.max(0, Math.min(100, startFitX + deltaX));
                this.profileFitY = Math.max(0, Math.min(100, startFitY + deltaY));
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
        },
        clearProfilePhotoSelection() {
            if (this.profileObjectUrl) {
                URL.revokeObjectURL(this.profileObjectUrl);
                this.profileObjectUrl = null;
            }

            this.profilePreviewUrl = this.initialProfilePreviewUrl;
            this.profileFitX = this.initialProfileFitX;
            this.profileFitY = this.initialProfileFitY;

            if (this.$refs.profilePhotoInput) {
                this.$refs.profilePhotoInput.value = '';
            }
        }
    }"
    class="space-y-8"
>
    <form method="POST" action="{{ route('reseller.profile') }}" enctype="multipart/form-data" class="space-y-8">
        @csrf

        @if($canAdjustProfilePhoto)
            <input type="hidden" name="profile_focus_x" :value="Math.round(profileFitX)">
            <input type="hidden" name="profile_focus_y" :value="Math.round(profileFitY)">
        @endif

        <div class="reseller-profile-hero bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="h-48 w-full bg-gradient-to-r from-[#4A6741] via-[#6B3A2A] to-[#3A2E22]"></div>

            <div class="reseller-profile-hero-body px-8 pb-8 pt-4">
                <div class="reseller-profile-hero-top flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                    <div class="reseller-profile-hero-identity flex items-start gap-4">
                        <div
                            class="-mt-12 ml-6 relative w-24 h-24 rounded-full bg-[#F5F0E8] ring-4 ring-white shadow-sm overflow-hidden flex items-center justify-center text-[#4A6741] font-semibold text-xs text-center px-2 shrink-0"
                            :class="editMode && profilePreviewUrl ? 'cursor-grab active:cursor-grabbing' : ''"
                            @pointerdown="beginProfileFitDrag($event)"
                        >
                            <template x-if="profilePreviewUrl">
                                <img :src="profilePreviewUrl" alt="Profile" class="absolute inset-0 w-full h-full object-cover" :style="fitStyle(profileFitX, profileFitY)" />
                            </template>
                            <template x-if="!profilePreviewUrl">
                                <span>{{ strtoupper(substr($user->name ?? 'R', 0, 1)) }}</span>
                            </template>
                        </div>
                        <div class="pt-4">
                            <template x-if="!editMode">
                                <div>
                                    <h2 class="text-3xl font-display font-bold text-[#3A2E22]">{{ old('business_name', data_get($resellerRecord, 'business_name', $user->name ?? 'Reseller')) }}</h2>
                                    <p class="text-sm text-[#9E8C78] mt-1">{{ old('address', data_get($resellerRecord, 'address', $user->address ?? 'No address yet')) }}</p>
                                </div>
                            </template>
                            <div class="space-y-3" x-show="editMode" x-cloak>
                                <input
                                    name="name"
                                    value="{{ old('name', $user->name) }}"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]"
                                    placeholder="Reseller name"
                                    required
                                />
                                <input
                                    type="email"
                                    name="email"
                                    value="{{ old('email', $user->email) }}"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]"
                                    placeholder="Email"
                                    required
                                />
                            </div>
                        </div>
                    </div>

                    <div class="reseller-profile-actions flex items-center gap-3 pt-4">
                        <button
                            type="button"
                            @click="editMode = !editMode"
                            class="px-4 py-2 rounded-lg bg-[#3A2E22] text-white text-sm font-semibold hover:bg-[#2A2119] transition-colors"
                            x-text="editMode ? 'Cancel' : 'Edit Profile'"
                        ></button>
                        <button type="submit" class="px-5 py-2 rounded-lg bg-[#4A6741] text-white text-sm font-semibold hover:bg-[#3A2E22] transition-colors" x-show="editMode" x-cloak>
                            Save Profile
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-sm p-8">
                    <h3 class="text-2xl font-display font-bold text-[#3A2E22] mb-1">Account</h3>
                    <p class="text-[#9E8C78] text-sm mb-4">Personal account details</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-[#3A2E22]">Name</label>
                            <template x-if="!editMode">
                                <p class="mt-1 text-sm text-[#6B5B4A]">{{ old('name', $user->name) }}</p>
                            </template>
                            <input
                                x-show="editMode"
                                x-cloak
                                name="name"
                                value="{{ old('name', $user->name) }}"
                                class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]"
                                required
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-[#3A2E22]">Email</label>
                            <template x-if="!editMode">
                                <p class="mt-1 text-sm text-[#6B5B4A]">{{ old('email', $user->email) }}</p>
                            </template>
                            <input
                                x-show="editMode"
                                x-cloak
                                type="email"
                                name="email"
                                value="{{ old('email', $user->email) }}"
                                class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]"
                                required
                            />
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-[#3A2E22]">Profile Photo</label>
                            <template x-if="!editMode">
                                <p class="mt-1 text-sm text-[#6B5B4A]" x-text="profilePreviewUrl ? 'Image uploaded' : 'N/A'"></p>
                            </template>
                            <input
                                x-show="editMode"
                                x-cloak
                                x-ref="profilePhotoInput"
                                type="file"
                                name="profile_photo"
                                accept="image/*"
                                @change="handleProfilePhotoChange($event)"
                                class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm shadow-sm file:mr-3 file:rounded-md file:border-0 file:bg-[#4A6741] file:px-2.5 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-[#3A2E22] focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]"
                            />

                            <div x-show="editMode && profilePreviewUrl" x-cloak class="mt-3 rounded-lg border border-gray-200 bg-[#F5F0E8] p-3 space-y-3">
                                <div class="flex items-center justify-between">
                                    <p class="text-[11px] text-[#6B5B4A] font-semibold">Drag the circular profile photo above to adjust fit</p>
                                    <button type="button" @click="clearProfilePhotoSelection()" class="text-[11px] text-[#4A6741] hover:text-[#3A2E22] font-semibold">Reset Selection</button>
                                </div>
                                <p class="text-[11px] text-[#9E8C78]">Tip: drag left/right/up/down directly on the avatar circle.</p>
                            </div>

                            @if(!$userPhotoColumn)
                                <p class="mt-2 text-xs text-[#9E8C78]">Profile photo column is not available in the users table on this environment.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-8">
                    <h3 class="text-2xl font-display font-bold text-[#3A2E22] mb-1">Location</h3>
                    <p class="text-[#9E8C78] text-sm mb-4">Address and map coordinates</p>

                    @if(empty($userLocationColumns))
                        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                            Location columns are not available in the users table yet. Run the migration to enable reseller mapping.
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @if(in_array('address', $userLocationColumns, true))
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-semibold text-[#3A2E22]">Address</label>
                                    <template x-if="!editMode">
                                        <p class="mt-1 text-sm text-[#6B5B4A]">{{ old('address', $user->address) ?: 'N/A' }}</p>
                                    </template>
                                    <input
                                        x-show="editMode"
                                        x-cloak
                                        name="address"
                                        value="{{ old('address', $user->address) }}"
                                        class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]"
                                        placeholder="Street, purok, or landmark"
                                    />
                                </div>
                            @endif

                            @if(in_array('barangay', $userLocationColumns, true))
                                <div>
                                    <label class="block text-sm font-semibold text-[#3A2E22]">Barangay</label>
                                    <template x-if="!editMode">
                                        <p class="mt-1 text-sm text-[#6B5B4A]">{{ old('barangay', $user->barangay) ?: 'N/A' }}</p>
                                    </template>
                                    <input
                                        x-show="editMode"
                                        x-cloak
                                        name="barangay"
                                        value="{{ old('barangay', $user->barangay) }}"
                                        class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]"
                                        placeholder="Barangay"
                                    />
                                </div>
                            @endif

                            @if(in_array('latitude', $userLocationColumns, true))
                                <div>
                                    <label class="block text-sm font-semibold text-[#3A2E22]">Latitude</label>
                                    <template x-if="!editMode">
                                        <p class="mt-1 text-sm text-[#6B5B4A]">{{ old('latitude', $user->latitude) ?: 'N/A' }}</p>
                                    </template>
                                    <input
                                        x-show="editMode"
                                        x-cloak
                                        type="number"
                                        step="any"
                                        name="latitude"
                                        value="{{ old('latitude', $user->latitude) }}"
                                        class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-100 text-gray-500 px-3 py-2 shadow-sm cursor-not-allowed"
                                        placeholder="e.g. 13.139062"
                                        readonly
                                        disabled
                                    />
                                </div>
                            @endif

                            @if(in_array('longitude', $userLocationColumns, true))
                                <div>
                                    <label class="block text-sm font-semibold text-[#3A2E22]">Longitude</label>
                                    <template x-if="!editMode">
                                        <p class="mt-1 text-sm text-[#6B5B4A]">{{ old('longitude', $user->longitude) ?: 'N/A' }}</p>
                                    </template>
                                    <input
                                        x-show="editMode"
                                        x-cloak
                                        type="number"
                                        step="any"
                                        name="longitude"
                                        value="{{ old('longitude', $user->longitude) }}"
                                        class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-100 text-gray-500 px-3 py-2 shadow-sm cursor-not-allowed"
                                        placeholder="e.g. 121.183776"
                                        readonly
                                        disabled
                                    />
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="bg-white rounded-xl shadow-sm p-8">
                    <h3 class="text-2xl font-display font-bold text-[#3A2E22] mb-1">Contact</h3>
                    <p class="text-[#9E8C78] text-sm mb-4">Reseller contact and business details</p>

                    @if(in_array('contact_number', $userLocationColumns, true))
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-[#3A2E22]">Contact Number</label>
                            <template x-if="!editMode">
                                <p class="mt-1 text-sm text-[#6B5B4A]">{{ old('contact_number', $user->contact_number) ?: 'N/A' }}</p>
                            </template>
                            <input
                                x-show="editMode"
                                x-cloak
                                name="contact_number"
                                value="{{ old('contact_number', $user->contact_number) }}"
                                class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]"
                                placeholder="e.g. 09XXXXXXXXX"
                            />
                        </div>
                    @endif

                    @if(!empty($resellerColumns))
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($resellerColumns as $column)
                                @continue($column === 'contact_number')

                                @php
                                    $meta = $resellerFieldMeta[$column] ?? ['label' => str($column)->replace('_', ' ')->title()->toString(), 'type' => 'string', 'readonly' => false];
                                    $value = old($column, data_get($resellerRecord, $column));
                                    $display = filled($value) ? $value : 'N/A';
                                    $isReadonly = (bool) ($meta['readonly'] ?? false);
                                    $isLongText = in_array($meta['type'] ?? '', ['text', 'mediumtext', 'longtext'], true);
                                @endphp

                                <div class="{{ $isLongText ? 'md:col-span-2' : '' }}">
                                    <label class="block text-sm font-semibold text-[#3A2E22]">{{ $meta['label'] }}</label>

                                    <template x-if="!editMode">
                                        <p class="mt-1 text-sm text-[#6B5B4A] break-words">{{ is_bool($display) ? ($display ? 'Yes' : 'No') : $display }}</p>
                                    </template>

                                    @if($isLongText)
                                        <textarea
                                            x-show="editMode"
                                            x-cloak
                                            name="{{ $column }}"
                                            rows="3"
                                            {{ $isReadonly ? 'readonly' : '' }}
                                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm {{ $isReadonly ? 'bg-gray-100 text-[#6B5B4A] cursor-not-allowed' : 'focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]' }}"
                                        >{{ $value }}</textarea>
                                    @else
                                        <input
                                            x-show="editMode"
                                            x-cloak
                                            name="{{ $column }}"
                                            value="{{ $value }}"
                                            {{ $isReadonly ? 'readonly' : '' }}
                                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm {{ $isReadonly ? 'bg-gray-100 text-[#6B5B4A] cursor-not-allowed' : 'focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4A6741]' }}"
                                        />
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm p-8">
                    <h3 class="text-2xl font-display font-bold text-[#3A2E22] mb-1">Coffee Varieties</h3>
                    <p class="text-[#9E8C78] text-sm mb-4">Varieties linked to your reseller account</p>

                    @if($allVarieties->count() > 0)
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
                    @else
                        <p class="text-sm text-[#9E8C78]">No coffee varieties available.</p>
                    @endif
                </div>

                <div class="bg-white rounded-xl shadow-sm p-8">
                    <h3 class="text-xl font-display font-bold text-[#3A2E22] mb-2">Profile Metadata</h3>
                    <div class="space-y-2 text-sm text-[#6B5B4A]">
                        <p><span class="font-semibold text-[#3A2E22]">User ID:</span> {{ $user->id ?? 'N/A' }}</p>
                        <p><span class="font-semibold text-[#3A2E22]">Role:</span> {{ ucfirst((string) ($user->role ?? 'reseller')) }}</p>
                        <p><span class="font-semibold text-[#3A2E22]">Created:</span> {{ optional($user->created_at)->format('M d, Y h:i A') ?: 'N/A' }}</p>
                        <p><span class="font-semibold text-[#3A2E22]">Updated:</span> {{ optional($user->updated_at)->format('M d, Y h:i A') ?: 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    @media (max-width: 767px) {
        .reseller-profile-page > .mb-8 {
            margin-bottom: 1rem !important;
        }

        .reseller-profile-page .reseller-profile-hero-body,
        .reseller-profile-page .bg-white.rounded-xl.shadow-sm.p-8 {
            padding: 1.25rem !important;
        }

        .reseller-profile-page .reseller-profile-hero-top,
        .reseller-profile-page .reseller-profile-hero-identity {
            gap: 1rem !important;
        }

        .reseller-profile-page .reseller-profile-hero-identity {
            flex-direction: column;
            align-items: flex-start;
        }

        .reseller-profile-page .reseller-profile-hero-identity > div:first-child {
            margin-top: -3rem;
            margin-left: 0 !important;
        }

        .reseller-profile-page .reseller-profile-actions {
            width: 100%;
            flex-direction: column;
            align-items: stretch;
            padding-top: 0;
        }

        .reseller-profile-page .reseller-profile-actions > * {
            width: 100%;
            justify-content: center;
        }

        .reseller-profile-page .grid.grid-cols-1.lg\:grid-cols-3 {
            gap: 1rem !important;
        }
    }
</style>
</div>
@endsection
