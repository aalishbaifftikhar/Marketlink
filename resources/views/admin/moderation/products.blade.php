@extends('layouts.admin')

@section('title', 'Product Moderation')

@section('content')
@php
    $statuses = ['all' => 'All', 'active' => 'Active', 'hidden' => 'Hidden', 'unavailable' => 'Unavailable'];
@endphp

<div class="flex flex-col gap-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="font-display text-xl font-bold text-stone-900 dark:text-cream-50">Product Moderation</h2>
            <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">Review the product catalogue and remove items that violate platform guidelines.</p>
        </div>
    </div>

    <div class="flex flex-wrap gap-2">
        @foreach ($statuses as $key => $label)
            <a href="{{ route('admin.products.index', array_merge(request()->only(['q']), $key !== 'all' ? ['status' => $key] : [])) }}"
                class="rounded-full px-4 py-1.5 text-sm font-semibold transition {{ (request('status', 'all') === $key) ? 'bg-leaf-600 text-white shadow-sm' : 'bg-white dark:bg-leaf-900/50 text-stone-600 dark:text-stone-300 ring-1 ring-stone-200 dark:ring-leaf-700/60 hover:bg-stone-50 dark:hover:bg-leaf-800' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <form method="GET" action="{{ route('admin.products.index') }}" class="card flex flex-col gap-4 p-4 sm:flex-row sm:items-end">
        @if (request('status') && request('status') !== 'all')
            <input type="hidden" name="status" value="{{ request('status') }}">
        @endif
        <div class="flex-1">
            <label class="input-label">Search</label>
            <input type="text" name="q" value="{{ request('q') }}" class="input" placeholder="Product or farmer name...">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-ghost">Reset</a>
        </div>
    </form>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-stone-100 dark:divide-leaf-800/60">
                <thead class="bg-stone-50 text-xs font-semibold uppercase tracking-wide text-stone-400 dark:bg-leaf-900/40 dark:text-stone-500">
                    <tr>
                        <th class="px-5 py-3 text-left">Product</th>
                        <th class="px-5 py-3 text-left">Farmer</th>
                        <th class="px-5 py-3 text-left">Category</th>
                        <th class="px-5 py-3 text-left">Price</th>
                        <th class="px-5 py-3 text-left">Stock</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 dark:divide-leaf-800/60">
                    @forelse ($products as $product)
                        <tr>
                            <td class="px-5 py-4">
                                <a href="{{ route('products.show', $product) }}" class="flex items-center gap-3">
                                    <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" class="h-10 w-10 rounded-lg object-cover">
                                    <span class="font-semibold text-stone-900 hover:text-leaf-700 dark:text-cream-50 dark:hover:text-leaf-400">{{ $product->name }}</span>
                                </a>
                            </td>
                            <td class="px-5 py-4">
                                <a href="{{ route('admin.farmers.show', $product->farmer) }}" class="font-medium text-leaf-700 hover:underline dark:text-leaf-400">
                                    {{ $product->farmer?->stall_name ?? '—' }}
                                </a>
                            </td>
                            <td class="px-5 py-4 text-stone-500 dark:text-stone-400">{{ $product->category?->name ?? '—' }}</td>
                            <td class="px-5 py-4 font-medium text-stone-900 dark:text-cream-50">${{ number_format($product->price, 2) }}</td>
                            <td class="px-5 py-4 text-stone-500 dark:text-stone-400">{{ $product->stock_quantity }}</td>
                            <td class="px-5 py-4">
                                <div class="flex flex-col gap-1">
                                    @if ($product->is_active)
                                        <span class="badge badge-green">Visible</span>
                                    @else
                                        <span class="badge badge-red">Hidden</span>
                                    @endif
                                    @if ($product->is_available)
                                        <span class="badge badge-green">Available</span>
                                    @else
                                        <span class="badge badge-amber">Unavailable</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Permanently delete this product? This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-stone-400 dark:text-stone-500">No products match the current filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($products->hasPages())
            <div class="border-t border-stone-100 px-5 py-4 dark:border-leaf-800/60">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
