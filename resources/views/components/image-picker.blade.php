@props([
    'existing' => [],
    'max' => 6,
    'label' => 'Photos',
    'hint' => 'JPG, PNG or WebP, up to 5 MB each.',
])

<div x-data="imagePicker({ existing: @json($existing), max: {{ (int) $max }} })" class="sm:col-span-2">
    <span class="input-label">
        {{ $label }} <span class="text-red-500">*</span>
        <span class="font-normal text-stone-400 dark:text-stone-500">(1–{{ $max }} images)</span>
    </span>

    <div class="flex flex-wrap gap-3">
        <template x-for="thumb in thumbs" :key="thumb.key">
            <div class="relative">
                <img :src="thumb.url" alt="" class="h-24 w-24 rounded-xl border border-stone-200 object-cover dark:border-leaf-700">
                <button type="button" @click="remove(thumb)" aria-label="Remove image"
                        class="absolute -right-1.5 -top-1.5 grid h-6 w-6 place-items-center rounded-full bg-red-600 text-white shadow-soft transition hover:bg-red-700">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
                </button>
            </div>
        </template>

        <button type="button" @click="$refs.input.click()" x-show="count < max"
                class="grid h-24 w-24 place-items-center rounded-xl border-2 border-dashed border-stone-300 text-stone-400 transition hover:border-leaf-400 hover:text-leaf-600 dark:border-leaf-700 dark:text-stone-500 dark:hover:border-leaf-500 dark:hover:text-leaf-300"
                aria-label="Add image">
            <span class="flex flex-col items-center gap-1">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                <span class="text-[11px] font-semibold">Add photo</span>
            </span>
        </button>
    </div>

    <input type="file" name="images[]" x-ref="input" multiple
           accept="image/jpeg,image/png,image/webp" class="sr-only" @change="onChange">

    <template x-for="path in removed" :key="path">
        <input type="hidden" name="remove_images[]" :value="path">
    </template>

    <p x-cloak x-show="error" x-text="error" class="input-error"></p>
    @error('images') <p class="input-error">{{ $message }}</p> @enderror
    <p class="mt-1.5 text-xs text-stone-400 dark:text-stone-500">{{ $hint }}</p>
</div>
