@extends('layouts.farmer')

@section('title', 'Stall Profile')

@section('content')
@php
    $oldMarkets = old('markets', $farmer->markets->pluck('id')->all());
    $oldDays = old('operating_days', $farmer->operating_days ?? []);
    $lat = old('latitude', $farmer->latitude);
    $lng = old('longitude', $farmer->longitude);
    $hasPin = $lat !== null && $lng !== null;
    $dayLabels = ['monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday', 'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday', 'sunday' => 'Sunday'];
@endphp

<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <p class="text-sm text-stone-500 dark:text-stone-400">How customers see your stall on the marketplace.</p>
    </div>
    <a href="{{ route('profile.edit') }}" class="btn-ghost btn-sm">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
        Account settings &amp; password
    </a>
</div>

<form method="POST" action="{{ route('farmer.profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
    @csrf
    @method('PUT')

    {{-- Stall details --}}
    <section class="card p-5 sm:p-7">
        <h2 class="font-display text-lg font-semibold text-stone-800 dark:text-stone-100">Stall details</h2>

        <div class="mt-5 grid gap-5 sm:grid-cols-2">
            <div>
                <label for="stall_name" class="input-label">Stall name <span class="text-red-500">*</span></label>
                <input type="text" id="stall_name" name="stall_name" class="input" required maxlength="255"
                       value="{{ old('stall_name', $farmer->stall_name) }}" placeholder="e.g. Green Valley Orchard">
                <x-input-error field="stall_name" />
            </div>
            <div>
                <label for="contact_person" class="input-label">Contact person <span class="text-red-500">*</span></label>
                <input type="text" id="contact_person" name="contact_person" class="input" required maxlength="255"
                       value="{{ old('contact_person', $farmer->contact_person) }}" placeholder="Who handles orders?">
                <x-input-error field="contact_person" />
            </div>
            <div>
                <label for="phone" class="input-label">Phone <span class="text-red-500">*</span></label>
                <input type="tel" id="phone" name="phone" class="input" required maxlength="15"
                       value="{{ old('phone', $farmer->phone) }}" placeholder="0712345678"
                       inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                <p class="mt-1 text-xs text-stone-400 dark:text-stone-500">Numbers only, 10–15 digits.</p>
                <x-input-error field="phone" />
            </div>
            <div>
                <label for="order_cutoff_hours" class="input-label">Order cutoff (hours before pickup) <span class="text-red-500">*</span></label>
                <input type="number" id="order_cutoff_hours" name="order_cutoff_hours" class="input" required min="0" max="168" step="1"
                       value="{{ old('order_cutoff_hours', $farmer->order_cutoff_hours) }}">
                <p class="mt-1 text-xs text-stone-400 dark:text-stone-500">Customers can no longer place or change orders this many hours before their pickup slot.</p>
                <x-input-error field="order_cutoff_hours" />
            </div>
            <div class="sm:col-span-2">
                <label for="description" class="input-label">About your stall</label>
                <textarea id="description" name="description" rows="4" maxlength="1000" class="input" placeholder="Tell customers your story — what you grow, how you farm, what makes your produce special.">{{ old('description', $farmer->description) }}</textarea>
                <x-input-error field="description" />
            </div>
            <div class="sm:col-span-2">
                <label for="address" class="input-label">Stall address <span class="text-red-500">*</span></label>
                <input type="text" id="address" name="address" class="input" required maxlength="500"
                       value="{{ old('address', $farmer->address) }}" placeholder="Where your stall can be found at the market">
                <x-input-error field="address" />
            </div>
        </div>
    </section>

    {{-- Markets & operating days --}}
    <section class="card p-5 sm:p-7">
        <h2 class="font-display text-lg font-semibold text-stone-800 dark:text-stone-100">Markets &amp; opening days</h2>

        <div class="mt-5 grid gap-6 lg:grid-cols-2">
            <div>
                <p class="input-label">Markets you attend</p>
                @if ($markets->count())
                    <div class="grid gap-2 sm:grid-cols-2">
                        @foreach ($markets as $market)
                            <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-stone-200 px-3.5 py-2.5 text-sm font-medium text-stone-700 transition-colors hover:border-leaf-300 hover:bg-leaf-50 has-[:checked]:border-leaf-500 has-[:checked]:bg-leaf-50 dark:border-leaf-800 dark:text-stone-200 dark:hover:border-leaf-600 dark:hover:bg-leaf-800/50 dark:has-[:checked]:border-leaf-500 dark:has-[:checked]:bg-leaf-800/60">
                                <input type="checkbox" name="markets[]" value="{{ $market->id }}"
                                       @checked(in_array($market->id, $oldMarkets))
                                       class="h-4 w-4 rounded border-stone-300 text-leaf-600 focus:ring-leaf-500 dark:border-leaf-700 dark:bg-leaf-900">
                                <span class="truncate">{{ $market->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    <x-input-error field="markets" />
                @else
                    <p class="text-sm text-stone-500 dark:text-stone-400">No active markets are available at the moment.</p>
                @endif
            </div>

            <div>
                <p class="input-label">Operating days</p>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                    @foreach ($days as $day)
                        <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-stone-200 px-3 py-2 text-sm font-medium text-stone-700 transition-colors hover:border-leaf-300 hover:bg-leaf-50 has-[:checked]:border-leaf-500 has-[:checked]:bg-leaf-50 dark:border-leaf-800 dark:text-stone-200 dark:hover:border-leaf-600 dark:hover:bg-leaf-800/50 dark:has-[:checked]:border-leaf-500 dark:has-[:checked]:bg-leaf-800/60">
                            <input type="checkbox" name="operating_days[]" value="{{ $day }}"
                                   @checked(in_array($day, $oldDays))
                                   class="h-4 w-4 rounded border-stone-300 text-leaf-600 focus:ring-leaf-500 dark:border-leaf-700 dark:bg-leaf-900">
                            {{ $dayLabels[$day] ?? ucfirst($day) }}
                        </label>
                    @endforeach
                </div>
                <x-input-error field="operating_days" />
            </div>
        </div>
    </section>

    {{-- Location --}}
    <section class="card p-5 sm:p-7">
        <h2 class="font-display text-lg font-semibold text-stone-800 dark:text-stone-100">Stall location</h2>
        <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">Drag the pin or click the map to place your stall exactly where customers can find you.</p>

        <div class="mt-5 grid gap-5 lg:grid-cols-5">
            <div class="space-y-5 lg:col-span-2">
                <div>
                    <label for="latitude" class="input-label">Latitude</label>
                    <input type="number" id="latitude" name="latitude" class="input" step="any" min="-90" max="90"
                           value="{{ $lat }}" placeholder="e.g. 51.50721">
                    <x-input-error field="latitude" />
                </div>
                <div>
                    <label for="longitude" class="input-label">Longitude</label>
                    <input type="number" id="longitude" name="longitude" class="input" step="any" min="-180" max="180"
                           value="{{ $lng }}" placeholder="e.g. -0.12758">
                    <x-input-error field="longitude" />
                </div>
                <p class="flex items-start gap-2 rounded-xl bg-leaf-50 px-3.5 py-3 text-xs leading-relaxed text-leaf-800 dark:bg-leaf-800/50 dark:text-leaf-200">
                    <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.1-7.5 11.25-7.5 11.25S4.5 17.6 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                    Moving the pin on the map updates these fields automatically.
                </p>
            </div>
            <div class="lg:col-span-3">
                <x-map height="h-80"
                       :center="$hasPin ? [(float) $lat, (float) $lng] : [40.7128, -74.0060]"
                       :zoom="$hasPin ? 15 : 12"
                       :draggable-pin="$hasPin ? ['lat' => (float) $lat, 'lng' => (float) $lng] : null"
                       draggable
                       lat-input="#latitude"
                       lng-input="#longitude" />
            </div>
        </div>
    </section>

    {{-- Cover image --}}
    <section class="card p-5 sm:p-7">
        <h2 class="font-display text-lg font-semibold text-stone-800 dark:text-stone-100">Cover photo</h2>

        <div class="mt-5 flex flex-col gap-5 sm:flex-row sm:items-start">
            @if ($farmer->cover_image)
                <img src="{{ asset('storage/' . $farmer->cover_image) }}" alt="Current cover" class="h-32 w-full max-w-xs rounded-2xl border border-stone-200 object-cover dark:border-leaf-700">
            @endif
            <div class="flex-1">
                <label for="cover_image" class="input-label">{{ $farmer->cover_image ? 'Replace cover photo' : 'Upload a cover photo' }}</label>
                <input type="file" id="cover_image" name="cover_image" accept="image/*" class="input file:mr-3 file:rounded-lg file:border-0 file:bg-leaf-100 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-leaf-700 hover:file:bg-leaf-200 dark:file:bg-leaf-800 dark:file:text-leaf-200">
                <p class="mt-1 text-xs text-stone-400 dark:text-stone-500">JPG, PNG or WebP up to 4 MB. Leave empty to keep your current photo.</p>
                <x-input-error field="cover_image" />
            </div>
        </div>
    </section>

    <div class="flex flex-wrap items-center gap-3">
        <button type="submit" class="btn-primary">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
            Save stall profile
        </button>
        <a href="{{ route('farmer.dashboard') }}" class="btn-ghost">Cancel</a>
    </div>
</form>
@endsection
