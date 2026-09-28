@props(['images', 'alt' => '', 'aspect' => 'aspect-square'])

@php
    $images = array_values((array) $images);
    $imagesJson = json_encode($images, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP);
@endphp

@if (count($images) === 1)
    <div class="relative overflow-hidden rounded-3xl bg-cream-100 ring-1 ring-stone-200/60 dark:bg-leaf-900 dark:ring-leaf-800" {{ $attributes }}>
        <img src="{{ $images[0] }}" alt="{{ $alt }}" class="{{ $aspect }} w-full object-cover">
        {{ $slot }}
    </div>
@elseif (count($images) > 1)
    <div x-data="{ images: {{ $imagesJson }}, active: 0, next() { this.active = (this.active + 1) % this.images.length }, prev() { this.active = (this.active - 1 + this.images.length) % this.images.length } }"
         class="relative overflow-hidden rounded-3xl bg-cream-100 ring-1 ring-stone-200/60 dark:bg-leaf-900 dark:ring-leaf-800" {{ $attributes }}>
        <img :src="images[active]" src="{{ $images[0] }}" alt="{{ $alt }}" class="{{ $aspect }} w-full object-cover">
        {{ $slot }}

        <button type="button" @click="prev()" aria-label="Previous image"
                class="absolute left-3 top-1/2 grid h-9 w-9 -translate-y-1/2 place-items-center rounded-full bg-white/90 text-stone-700 shadow-soft transition hover:bg-white dark:bg-leaf-900/90 dark:text-stone-200">
            <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <button type="button" @click="next()" aria-label="Next image"
                class="absolute right-3 top-1/2 grid h-9 w-9 -translate-y-1/2 place-items-center rounded-full bg-white/90 text-stone-700 shadow-soft transition hover:bg-white dark:bg-leaf-900/90 dark:text-stone-200">
            <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </button>

        <div class="absolute bottom-3 left-1/2 flex -translate-x-1/2 gap-1.5">
            <template x-for="(img, i) in images" :key="i">
                <button type="button" @click="active = i" aria-label="Go to image"
                        class="h-2 rounded-full transition-all"
                        :class="active === i ? 'w-6 bg-white' : 'w-2 bg-white/60 hover:bg-white/80'"></button>
            </template>
        </div>
    </div>
@endif
