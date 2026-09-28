@extends('layouts.farmer')

@section('title', 'Products')

@section('content')
@php
    $availabilityTabs = [
        'all' => 'All products',
        'in_stock' => 'In stock',
        'out_of_stock' => 'Out of stock',
        'hidden' => 'Hidden',
    ];
    $currentAvailability = $filters['availability'] ?? 'all';
@endphp
<div class="flex flex-wrap items-center justify-between gap-4">
    <p class="text-sm text-stone-500 dark:text-stone-400">Manage the stock customers can pre-order from your stall.</p>
    <a href="{{ route('farmer.products.create') }}" class="btn-primary">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
        Add product
    </a>
</div>

<div class="card mt-6 p-4 sm:p-5">
    <form method="GET" action="{{ route('farmer.products.index') }}" class="flex flex-col gap-3 lg:flex-row lg:items-center">
        <input type="hidden" name="availability" value="{{ $currentAvailability }}">
        <div class="relative flex-1">
            <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-stone-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/></svg>
            <input type="search" name="q" value="{{ $filters['q'] }}" class="input pl-10" placeholder="Search products by name…">
        </div>
        <select name="category" class="input lg:w-56">
            <option value="">All categories</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) $filters['category'] === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-secondary shrink-0">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/></svg>
            Filter
        </button>
        @if ($filters['q'] || $filters['category'] || $currentAvailability !== 'all')
            <a href="{{ route('farmer.products.index') }}" class="btn-ghost btn-sm shrink-0">Reset</a>
        @endif
    </form>

    {{-- Availability tabs --}}
    <div class="mt-4 flex flex-wrap gap-2 border-t border-stone-100 pt-4 dark:border-leaf-800/80">
        @foreach ($availabilityTabs as $key => $label)
            <a href="{{ route('farmer.products.index', array_filter(['q' => $filters['q'], 'category' => $filters['category'], 'availability' => $key === 'all' ? null : $key])) }}"
               class="{{ $currentAvailability === $key ? 'btn-primary' : 'btn-secondary' }} btn-sm">
                {{ $label }}
                @isset ($counts)
                    <span class="opacity-75">({{ number_format($counts[$key] ?? 0) }})</span>
                @endisset
            </a>
        @endforeach
    </div>
</div>

{{-- Product list --}}
<div class="mt-6 space-y-3">
    @forelse ($products as $product)
        <div class="card card-hover flex flex-col gap-4 p-4 sm:flex-row sm:items-center sm:p-5">
            <div class="flex min-w-0 flex-1 items-center gap-4">
                <img src="{{ $product->imageUrl() }}" alt="" class="h-16 w-16 shrink-0 rounded-2xl border border-stone-200 object-cover dark:border-leaf-700">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="truncate font-display text-base font-semibold text-stone-800 dark:text-stone-100">{{ $product->name }}</h2>
                        @if ($product->is_available && $product->stock_quantity > 0)
                            <span class="badge-green">In stock</span>
                        @elseif ($product->stock_quantity <= 0)
                            <span class="badge-red">Out of stock</span>
                        @else
                            <span class="badge-amber">Unavailable</span>
                        @endif
                        @if ($product->is_active)
                            <span class="badge-stone">Live</span>
                        @else
                            <span class="badge-red">Hidden</span>
                        @endif
                    </div>
                    <p class="mt-0.5 truncate text-xs text-stone-500 dark:text-stone-400">
                        {{ $product->category?->name ?? 'Uncategorised' }}
                        · <span class="font-semibold text-stone-700 dark:text-stone-200">${{ number_format($product->price, 2) }}</span>/{{ $product->unit }}
                        · {{ number_format($product->stock_quantity) }} in stock
                    </p>
                </div>
            </div>

            <div class="flex shrink-0 flex-wrap items-center gap-2">
                <form method="POST" action="{{ route('farmer.products.toggle', $product) }}">
                    @csrf
                    @if ($product->is_available)
                        <button type="submit" class="btn-secondary btn-sm" title="Hide from customers until restocked">Mark sold out</button>
                    @else
                        <button type="submit" class="btn-secondary btn-sm" title="Make available for ordering again">Mark available</button>
                    @endif
                </form>
                <a href="{{ route('farmer.products.edit', $product) }}" class="btn-ghost btn-sm">Edit</a>
                <form method="POST" action="{{ route('farmer.products.destroy', $product) }}"
                      onsubmit="return confirm('Delete &quot;{{ $product->name }}&quot;? This cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger btn-sm">Delete</button>
                </form>
            </div>
        </div>
    @empty
        <div class="card px-6 py-16 text-center">
            <span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-leaf-100 text-leaf-600 dark:bg-leaf-800 dark:text-leaf-300">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3l8 4.5v9L12 21l-8-4.5v-9L12 3zM12 12l8-4.5M12 12L4 7.5M12 12v9"/></svg>
            </span>
            <h2 class="mt-5 font-display text-xl font-semibold text-stone-800 dark:text-stone-100">No products found</h2>
            <p class="mx-auto mt-2 max-w-sm text-sm text-stone-500 dark:text-stone-400">
                @if ($filters['q'] || $filters['category'] || $currentAvailability !== 'all')
                    Nothing matches your current filters. Try widening your search.
                @else
                    Add your first product so customers can start pre-ordering from your stall.
                @endif
            </p>
            <div class="mt-6">
                @if ($filters['q'] || $filters['category'] || $currentAvailability !== 'all')
                    <a href="{{ route('farmer.products.index') }}" class="btn-secondary">Clear filters</a>
                @else
                    <a href="{{ route('farmer.products.create') }}" class="btn-primary">Add your first product</a>
                @endif
            </div>
        </div>
    @endforelse
</div>

@if ($products->hasPages())
    <div class="mt-6">{{ $products->links() }}</div>
@endif
@endsection
