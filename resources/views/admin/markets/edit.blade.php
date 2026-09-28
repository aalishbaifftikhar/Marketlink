@extends('layouts.admin')

@section('title', 'Edit ' . $market->name)

@section('content')
@php
    $days = ['monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday', 'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday', 'sunday' => 'Sunday'];
    $selectedDays = old('operating_days', $market->operating_days ?? []);
@endphp

<div class="flex items-center gap-2 text-sm">
    <a href="{{ route('admin.markets.index') }}" class="text-stone-500 dark:text-stone-400 hover:text-leaf-700 dark:hover:text-leaf-300 font-medium">← Markets</a>
</div>

<form method="POST" action="{{ route('admin.markets.update', $market) }}" enctype="multipart/form-data" class="mt-3 space-y-5">
    @csrf
    @method('PUT')

    <div class="card p-6">
        <h2 class="font-display text-lg font-semibold text-leaf-950 dark:text-cream-50">Market Details</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="name" class="input-label">Market Name <span class="text-red-500">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name', $market->name) }}" required maxlength="150" class="input">
                @error('name') <p class="input-error">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2">
                <label for="description" class="input-label">Description</label>
                <textarea id="description" name="description" rows="4" maxlength="2000" class="input">{{ old('description', $market->description) }}</textarea>
                @error('description') <p class="input-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="address" class="input-label">Address <span class="text-red-500">*</span></label>
                <input type="text" id="address" name="address" value="{{ old('address', $market->address) }}" required maxlength="255" class="input">
                @error('address') <p class="input-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="city" class="input-label">City <span class="text-red-500">*</span></label>
                <input type="text" id="city" name="city" value="{{ old('city', $market->city) }}" required maxlength="100" class="input">
                @error('city') <p class="input-error">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

    <div class="card p-6">
        <h2 class="font-display text-lg font-semibold text-leaf-950 dark:text-cream-50">Operating Days &amp; Hours</h2>
        <div class="mt-4">
            <p class="input-label">Open days</p>
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-2">
                @foreach ($days as $value => $label)
                    <label class="flex items-center gap-2 rounded-xl border border-stone-200 dark:border-leaf-800 bg-white dark:bg-leaf-900/60 px-3 py-2.5 cursor-pointer text-sm font-medium text-stone-700 dark:text-stone-200 hover:border-leaf-300 dark:hover:border-leaf-600 transition-colors has-[:checked]:border-leaf-500 has-[:checked]:bg-leaf-50 dark:has-[:checked]:bg-leaf-800/50">
                        <input type="checkbox" name="operating_days[]" value="{{ $value }}" @checked(in_array($value, $selectedDays)) class="rounded border-stone-300 text-leaf-600 focus:ring-leaf-400">
                        {{ $label }}
                    </label>
                @endforeach
            </div>
            @error('operating_days') <p class="input-error">{{ $message }}</p> @enderror
        </div>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div>
                <label for="open_time" class="input-label">Opening Time</label>
                <input type="time" id="open_time" name="open_time" value="{{ old('open_time', $market->open_time) }}" class="input">
                @error('open_time') <p class="input-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="close_time" class="input-label">Closing Time</label>
                <input type="time" id="close_time" name="close_time" value="{{ old('close_time', $market->close_time) }}" class="input">
                @error('close_time') <p class="input-error">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

    <div class="card p-6">
        <h2 class="font-display text-lg font-semibold text-leaf-950 dark:text-cream-50">Location on Map</h2>
        <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">Click the map to drop a pin, or drag the pin to fine-tune the market entrance.</p>
        <div class="mt-4 grid gap-4 lg:grid-cols-3">
            <div class="space-y-4">
                <div>
                    <label for="latitude" class="input-label">Latitude</label>
                    <input type="number" id="latitude" name="latitude" value="{{ old('latitude', $market->latitude) }}" step="any" min="-90" max="90" class="input">
                    @error('latitude') <p class="input-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="longitude" class="input-label">Longitude</label>
                    <input type="number" id="longitude" name="longitude" value="{{ old('longitude', $market->longitude) }}" step="any" min="-180" max="180" class="input">
                    @error('longitude') <p class="input-error">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="lg:col-span-2">
                <x-map id="market-map" height="h-72"
                    :draggable="true"
                    lat-input="#latitude"
                    lng-input="#longitude"
                    :center="[(float) ($market->latitude ?: -1.2921), (float) ($market->longitude ?: 36.8219)]"
                    :zoom="12"
                    :draggable-pin="['lat' => (float) ($market->latitude ?: -1.2921), 'lng' => (float) ($market->longitude ?: 36.8219)]"
                    :markers="$market->latitude ? [['id' => $market->id, 'name' => $market->name, 'lat' => (float) $market->latitude, 'lng' => (float) $market->longitude]] : []" />
            </div>
        </div>
    </div>

    <div class="card p-6">
        <h2 class="font-display text-lg font-semibold text-leaf-950 dark:text-cream-50">Image &amp; Visibility</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <x-image-picker :existing="$market->galleryEntries()" label="Market photos" hint="Add 1–6 photos. JPG, PNG or WebP, up to 5 MB each. Remove any photo you no longer want." />
            <div class="flex items-end">
                <label class="flex items-center gap-2.5 rounded-xl border border-stone-200 dark:border-leaf-800 bg-white dark:bg-leaf-900/60 px-4 py-3 cursor-pointer text-sm font-medium text-stone-700 dark:text-stone-200">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $market->is_active)) class="rounded border-stone-300 text-leaf-600 focus:ring-leaf-400">
                    Visible on the site (active)
                </label>
            </div>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-3">
        <button type="submit" class="btn-primary">Save Changes</button>
        <a href="{{ route('admin.markets.index') }}" class="btn-ghost">Cancel</a>
    </div>
</form>
@endsection
