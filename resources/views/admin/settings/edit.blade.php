@extends('layouts.admin')

@section('title', 'Platform Settings')

@section('content')
@php
    $generalKeys = ['site_name', 'site_tagline', 'footer_text', 'announcement_banner'];
    $contactKeys = ['contact_email', 'contact_phone', 'contact_address', 'contact_latitude', 'contact_longitude'];
@endphp

<div class="flex flex-col gap-6">
    <div>
        <h2 class="font-display text-xl font-bold text-stone-900 dark:text-cream-50">Platform Settings</h2>
        <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">Configure site identity, contact details, and the public announcement banner.</p>
    </div>

    <form method="POST" action="{{ route('admin.settings.update') }}" class="flex flex-col gap-6">
        @csrf
        @method('PUT')

        <div class="card p-5">
            <h3 class="font-display text-lg font-bold text-stone-900 dark:text-cream-50">General</h3>
            <div class="mt-4 grid gap-4 lg:grid-cols-2">
                @foreach ($generalKeys as $key)
                    @php
                        $meta = $keys[$key] ?? ['label' => ucwords(str_replace('_', ' ', $key)), 'type' => 'string'];
                    @endphp
                    <div>
                        <label for="{{ $key }}" class="input-label">{{ $meta['label'] }}</label>
                        <input type="text" id="{{ $key }}" name="{{ $key }}" value="{{ old($key, $values[$key] ?? $meta['default'] ?? '') }}" class="input">
                        @error($key)
                            <p class="input-error">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card p-5">
            <h3 class="font-display text-lg font-bold text-stone-900 dark:text-cream-50">Contact</h3>
            <div class="mt-4 grid gap-4 lg:grid-cols-2">
                @foreach ($contactKeys as $key)
                    @php
                        $meta = $keys[$key] ?? ['label' => ucwords(str_replace('_', ' ', $key)), 'type' => 'string'];
                        $isNumber = ($meta['type'] ?? 'string') === 'number';
                    @endphp
                    <div>
                        <label for="{{ $key }}" class="input-label">{{ $meta['label'] }}</label>
                        <input
                            type="{{ $isNumber ? 'number' : ($key === 'contact_email' ? 'email' : 'text') }}"
                            id="{{ $key }}"
                            name="{{ $key }}"
                            value="{{ old($key, $values[$key] ?? $meta['default'] ?? '') }}"
                            @if ($isNumber) step="any" @endif
                            class="input">
                        @error($key)
                            <p class="input-error">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn btn-primary">Save Settings</button>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
