@extends('layouts.admin')

@section('title', 'Categories')

@section('content')
<div class="grid gap-5 lg:grid-cols-3">
    {{-- ======================= CREATE ======================= --}}
    <div class="card p-5 h-fit">
        <h2 class="font-display text-lg font-semibold text-leaf-950 dark:text-cream-50">New Category</h2>
        <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">Categories group products across every farmer's catalogue.</p>
        <form method="POST" action="{{ route('admin.categories.store') }}" class="mt-4 space-y-4">
            @csrf
            <div>
                <label for="name" class="input-label">Name <span class="text-red-500">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required maxlength="100" class="input" placeholder="e.g. Leafy Greens">
                @error('name') <p class="input-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="description" class="input-label">Description</label>
                <textarea id="description" name="description" rows="2" maxlength="500" class="input" placeholder="Optional short description">{{ old('description') }}</textarea>
                @error('description') <p class="input-error">{{ $message }}</p> @enderror
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="icon" class="input-label">Icon URL</label>
                    <input type="text" id="icon" name="icon" value="{{ old('icon') }}" maxlength="50" class="input" placeholder="Optional">
                    @error('icon') <p class="input-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="sort_order" class="input-label">Sort Order</label>
                    <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order') }}" min="0" class="input" placeholder="Auto">
                    @error('sort_order') <p class="input-error">{{ $message }}</p> @enderror
                </div>
            </div>
            <button type="submit" class="btn-primary w-full">Add Category</button>
        </form>
    </div>

    {{-- ======================= LIST ======================= --}}
    <div class="lg:col-span-2 card overflow-hidden">
        <div class="px-5 py-4 border-b border-stone-200/70 dark:border-leaf-800 flex items-center justify-between">
            <h2 class="font-display text-lg font-semibold text-leaf-950 dark:text-cream-50">All Categories</h2>
            <span class="text-sm text-stone-500 dark:text-stone-400">{{ $categories->count() }} total</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-stone-400 dark:text-stone-500 bg-stone-50 dark:bg-leaf-900/40 border-b border-stone-200/70 dark:border-leaf-800">
                        <th class="py-3 px-5">Category</th>
                        <th class="py-3 px-5 text-center">Products</th>
                        <th class="py-3 px-5 text-center">Sort</th>
                        <th class="py-3 px-5">Status</th>
                        <th class="py-3 px-5 text-right">Actions</th>
                    </tr>
                </thead>
                @foreach ($categories as $category)
                    <tbody x-data="{ editing: false }">
                        <tr class="hover:bg-leaf-50/60 dark:hover:bg-leaf-900/40 transition-colors">
                            <td class="py-3.5 px-5">
                                <div class="flex items-center gap-3">
                                    <span class="w-9 h-9 rounded-xl bg-leaf-100 dark:bg-leaf-800 text-leaf-700 dark:text-leaf-300 grid place-items-center shrink-0">
                                        @if ($category->icon)
                                            <img src="{{ $category->icon }}" alt="" class="w-5 h-5 object-contain">
                                        @else
                                            <svg class="w-4.5 h-4.5 w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c4 3 6 6.5 6 10a6 6 0 11-12 0c0-3.5 2-7 6-10z"/></svg>
                                        @endif
                                    </span>
                                    <div>
                                        <p class="font-semibold text-stone-800 dark:text-stone-100">{{ $category->name }}</p>
                                        @if ($category->description) <p class="text-xs text-stone-500 dark:text-stone-400">{{ Str::limit($category->description, 60) }}</p> @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-5 text-center text-stone-600 dark:text-stone-300">{{ $category->products_count }}</td>
                            <td class="py-3.5 px-5 text-center text-stone-600 dark:text-stone-300">{{ $category->sort_order }}</td>
                            <td class="py-3.5 px-5">
                                <span class="{{ $category->is_active ? 'badge-green' : 'badge-stone' }}">{{ $category->is_active ? 'Active' : 'Disabled' }}</span>
                            </td>
                            <td class="py-3.5 px-5">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" @click="editing = true" class="btn-secondary btn-sm">Edit</button>
                                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Delete the category {{ $category->name }}? Categories with products cannot be deleted — disable them instead.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger btn-sm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <tr x-show="editing" x-cloak style="display:none" class="bg-leaf-50/70 dark:bg-leaf-900/50">
                            <td colspan="5" class="px-5 py-4">
                                <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="flex flex-wrap items-end gap-3">
                                    @csrf
                                    @method('PUT')
                                    <div class="min-w-[10rem] flex-1">
                                        <label class="input-label">Name</label>
                                        <input type="text" name="name" value="{{ old('name', $category->name) }}" required maxlength="100" class="input">
                                    </div>
                                    <div class="min-w-[12rem] flex-[2]">
                                        <label class="input-label">Description</label>
                                        <input type="text" name="description" value="{{ old('description', $category->description) }}" maxlength="500" class="input">
                                    </div>
                                    <div class="w-28">
                                        <label class="input-label">Icon URL</label>
                                        <input type="text" name="icon" value="{{ old('icon', $category->icon) }}" maxlength="50" class="input">
                                    </div>
                                    <div class="w-24">
                                        <label class="input-label">Sort</label>
                                        <input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order) }}" min="0" class="input">
                                    </div>
                                    <div class="flex gap-2">
                                        <button type="submit" class="btn-primary btn-sm">Save</button>
                                        <button type="button" @click="editing = false" class="btn-ghost btn-sm">Cancel</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    </tbody>
                @endforeach
            </table>
        </div>
    </div>
</div>
@endsection
