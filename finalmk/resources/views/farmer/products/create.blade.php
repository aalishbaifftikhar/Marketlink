@extends('layouts.farmer')

@section('title', 'Add Product')

@section('content')
<div class="max-w-3xl">
    <a href="{{ route('farmer.products.index') }}" class="btn-ghost btn-sm">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Back to products
    </a>

    <form method="POST" action="{{ route('farmer.products.store') }}" enctype="multipart/form-data" class="card mt-4 p-5 sm:p-7">
        @csrf

        <h2 class="font-display text-lg font-semibold text-stone-800 dark:text-stone-100">New product</h2>
        <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">It will appear on your public stall as soon as it is saved.</p>

        <div class="mt-6 grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="name" class="input-label">Product name <span class="text-red-500">*</span></label>
                <input type="text" id="name" name="name" class="input" required maxlength="150" value="{{ old('name') }}" placeholder="e.g. Heirloom Tomatoes">
                <x-input-error field="name" />
            </div>

            <div>
                <label for="category_id" class="input-label">Category</label>
                <select id="category_id" name="category_id" class="input">
                    <option value="">Uncategorised</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) old('category_id') === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <x-input-error field="category_id" />
            </div>

            <div>
                <label for="unit" class="input-label">Unit <span class="text-red-500">*</span></label>
                <input type="text" id="unit" name="unit" class="input" required maxlength="20" list="unit-suggestions"
                       value="{{ old('unit') }}" placeholder="e.g. kg, bunch, dozen">
                <datalist id="unit-suggestions">
                    <option value="kg"></option>
                    <option value="g"></option>
                    <option value="bunch"></option>
                    <option value="piece"></option>
                    <option value="dozen"></option>
                    <option value="litre"></option>
                    <option value="punnet"></option>
                    <option value="bag"></option>
                    <option value="box"></option>
                    <option value="tray"></option>
                </datalist>
                <x-input-error field="unit" />
            </div>

            <div>
                <label for="price" class="input-label">Price per unit <span class="text-red-500">*</span></label>
                <div class="relative">
                    <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm font-semibold text-stone-400">$</span>
                    <input type="number" id="price" name="price" class="input pl-8" required min="0" max="999999" step="0.01" value="{{ old('price') }}" placeholder="0.00">
                </div>
                <x-input-error field="price" />
            </div>

            <div>
                <label for="stock_quantity" class="input-label">Stock quantity <span class="text-red-500">*</span></label>
                <input type="number" id="stock_quantity" name="stock_quantity" class="input" required min="0" max="1000000" step="1" value="{{ old('stock_quantity') }}" placeholder="0">
                <x-input-error field="stock_quantity" />
            </div>

            <div class="sm:col-span-2">
                <label for="description" class="input-label">Description</label>
                <textarea id="description" name="description" rows="4" maxlength="2000" class="input" placeholder="Variety, ripeness, growing method — anything that helps customers choose.">{{ old('description') }}</textarea>
                <x-input-error field="description" />
            </div>

            <x-image-picker label="Product photos" hint="Add 1–6 photos. JPG, PNG or WebP, up to 5 MB each." />

            <div class="flex flex-wrap gap-3 sm:col-span-2">
                <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-stone-200 px-4 py-3 text-sm font-medium text-stone-700 transition-colors hover:border-leaf-300 hover:bg-leaf-50 has-[:checked]:border-leaf-500 has-[:checked]:bg-leaf-50 dark:border-leaf-800 dark:text-stone-200 dark:hover:border-leaf-600 dark:hover:bg-leaf-800/50 dark:has-[:checked]:border-leaf-500 dark:has-[:checked]:bg-leaf-800/60">
                    <input type="checkbox" name="is_available" value="1" @checked(old('is_available', true)) class="h-4 w-4 rounded border-stone-300 text-leaf-600 focus:ring-leaf-500 dark:border-leaf-700 dark:bg-leaf-900">
                    Available for ordering
                </label>
                <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-stone-200 px-4 py-3 text-sm font-medium text-stone-700 transition-colors hover:border-leaf-300 hover:bg-leaf-50 has-[:checked]:border-leaf-500 has-[:checked]:bg-leaf-50 dark:border-leaf-800 dark:text-stone-200 dark:hover:border-leaf-600 dark:hover:bg-leaf-800/50 dark:has-[:checked]:border-leaf-500 dark:has-[:checked]:bg-leaf-800/60">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true)) class="h-4 w-4 rounded border-stone-300 text-leaf-600 focus:ring-leaf-500 dark:border-leaf-700 dark:bg-leaf-900">
                    Visible on my public stall
                </label>
            </div>
            <x-input-error field="is_available" />
            <x-input-error field="is_active" />
        </div>

        <div class="mt-7 flex flex-wrap items-center gap-3">
            <button type="submit" class="btn-primary">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                Add product
            </button>
            <a href="{{ route('farmer.products.index') }}" class="btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
