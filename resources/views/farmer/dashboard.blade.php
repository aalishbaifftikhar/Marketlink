@extends('layouts.farmer')

@section('title', 'Dashboard')

@section('content')
@php
    $statusBadge = fn (?string $status) => match ($status) {
        'placed' => 'badge-amber',
        'accepted', 'ready_for_pickup' => 'badge-blue',
        'completed' => 'badge-green',
        'cancelled', 'declined' => 'badge-red',
        default => 'badge-stone',
    };

    $statCards = [
        ['label' => 'Total orders', 'value' => number_format($stats['total_orders']), 'icon' => 'M9 5H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V7a2 2 0 00-2-2h-3M9 3h6v4H9z', 'tone' => 'text-leaf-700 bg-leaf-100 dark:bg-leaf-800 dark:text-leaf-200'],
        ['label' => 'Needs action', 'value' => number_format($stats['pending_orders']), 'icon' => 'M12 8v4m0 4h.01M12 21a9 9 0 100-18 9 9 0 000 18z', 'tone' => 'text-amber-700 bg-amber-100 dark:bg-amber-500/15 dark:text-amber-300'],
        ['label' => 'Active orders', 'value' => number_format($stats['active_orders']), 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'tone' => 'text-sky-700 bg-sky-100 dark:bg-sky-500/15 dark:text-sky-300'],
        ['label' => 'Revenue', 'value' => '$' . number_format($stats['revenue'], 2), 'icon' => 'M12 6v12m-3-2.8c.6.5 1.8.8 3 .8 1.8 0 3-1 3-2.3 0-3.2-6-1.6-6-4.6 0-1.2 1.2-2.2 3-2.2 1.2 0 2.4.3 3 .8M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'tone' => 'text-emerald-700 bg-emerald-100 dark:bg-emerald-500/15 dark:text-emerald-300'],
    ];
@endphp

<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <p class="text-sm text-stone-500 dark:text-stone-400">{{ now()->format('l, F j') }}</p>
        <p class="mt-1 font-display text-xl font-semibold text-leaf-900 dark:text-leaf-100">Welcome back, {{ $farmer->contact_person ?: auth()->user()->name }}.</p>
    </div>
    <a href="{{ route('farmer.products.create') }}" class="btn-primary">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
        Add product
    </a>
</div>

{{-- Stat cards --}}
<div class="mt-6 grid grid-cols-2 gap-4 xl:grid-cols-4">
    @foreach ($statCards as $card)
        <div class="card card-hover p-5">
            <div class="flex items-center justify-between gap-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-stone-500 dark:text-stone-400">{{ $card['label'] }}</p>
                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl {{ $card['tone'] }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}"/></svg>
                </span>
            </div>
            <p class="mt-3 font-display text-2xl font-semibold text-stone-800 dark:text-stone-100 sm:text-3xl">{{ $card['value'] }}</p>
        </div>
    @endforeach
</div>

{{-- Pending orders banner --}}
@if ($stats['pending_orders'] > 0)
    <a href="{{ route('farmer.orders.index', ['status' => 'placed']) }}" class="mt-6 flex items-center gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3.5 transition-colors hover:bg-amber-100 dark:border-amber-500/30 dark:bg-amber-500/10 dark:hover:bg-amber-500/15">
        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
        </span>
        <span class="flex-1 text-sm font-medium text-amber-800 dark:text-amber-200">
            You have {{ $stats['pending_orders'] }} {{ Str::plural('order', $stats['pending_orders']) }} waiting for a response.
            <span class="font-semibold underline underline-offset-2">Review them now</span>
        </span>
        <svg class="h-4 w-4 shrink-0 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    </a>
@endif

<div class="mt-6 grid gap-6 xl:grid-cols-3">
    {{-- Recent orders --}}
    <div class="card xl:col-span-2">
        <div class="flex items-center justify-between gap-3 border-b border-stone-100 px-5 py-4 dark:border-leaf-800/80">
            <h2 class="font-display text-lg font-semibold text-stone-800 dark:text-stone-100">Recent orders</h2>
            <a href="{{ route('farmer.orders.index') }}" class="btn-ghost btn-sm">View all</a>
        </div>

        @if ($recentOrders->count())
            <div class="overflow-x-auto">
                <table class="w-full min-w-[36rem] text-left text-sm">
                    <thead>
                        <tr class="text-xs uppercase tracking-wide text-stone-400 dark:text-stone-500">
                            <th class="px-5 py-3 font-semibold">Customer</th>
                            <th class="px-3 py-3 font-semibold">Items</th>
                            <th class="px-3 py-3 font-semibold">Total</th>
                            <th class="px-3 py-3 font-semibold">Status</th>
                            <th class="px-3 py-3 font-semibold">Placed</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 dark:divide-leaf-800/60">
                        @foreach ($recentOrders as $order)
                            <tr class="transition-colors hover:bg-leaf-50/60 dark:hover:bg-leaf-800/30">
                                <td class="px-5 py-3.5 font-semibold text-stone-800 dark:text-stone-100">{{ $order->customer?->name ?? 'Guest' }}</td>
                                <td class="px-3 py-3.5 text-stone-600 dark:text-stone-300">
                                    {{ $order->relationLoaded('items') ? $order->items->count() : '—' }}
                                </td>
                                <td class="px-3 py-3.5 font-semibold text-stone-800 dark:text-stone-100">${{ number_format($order->total_amount, 2) }}</td>
                                <td class="px-3 py-3.5"><span class="{{ $statusBadge($order->status) }}">{{ $order->statusLabel() }}</span></td>
                                <td class="px-3 py-3.5 whitespace-nowrap text-stone-500 dark:text-stone-400">{{ $order->placed_at?->format('M j, g:i A') }}</td>
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route('farmer.orders.show', $order) }}" class="btn-ghost btn-sm">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-5 py-12 text-center">
                <span class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-leaf-100 text-leaf-600 dark:bg-leaf-800 dark:text-leaf-300">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V7a2 2 0 00-2-2h-3M9 3h6v4H9z"/></svg>
                </span>
                <p class="mt-4 text-sm font-semibold text-stone-700 dark:text-stone-200">No orders yet</p>
                <p class="mx-auto mt-1 max-w-xs text-xs text-stone-500 dark:text-stone-400">When customers pre-order from your stall, their orders will show up here.</p>
            </div>
        @endif
    </div>

    {{-- Best sellers --}}
    <div class="card">
        <div class="flex items-center justify-between gap-3 border-b border-stone-100 px-5 py-4 dark:border-leaf-800/80">
            <h2 class="font-display text-lg font-semibold text-stone-800 dark:text-stone-100">Best sellers</h2>
            <a href="{{ route('farmer.products.index') }}" class="btn-ghost btn-sm">All products</a>
        </div>
        <div class="p-3">
            @forelse ($bestSellers as $product)
                <div class="flex items-center gap-3 rounded-xl px-2 py-2.5 transition-colors hover:bg-leaf-50 dark:hover:bg-leaf-800/40">
                    <img src="{{ $product->imageUrl() }}" alt="" class="h-11 w-11 shrink-0 rounded-xl border border-stone-200 object-cover dark:border-leaf-700">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-stone-800 dark:text-stone-100">{{ $product->name }}</p>
                        <p class="text-xs text-stone-500 dark:text-stone-400">{{ number_format($product->sold_units ?? 0) }} sold · ${{ number_format($product->price, 2) }}/{{ $product->unit }}</p>
                    </div>
                    <span class="badge-green">{{ number_format($product->sold_units ?? 0) }}</span>
                </div>
            @empty
                <p class="px-2 py-10 text-center text-sm text-stone-500 dark:text-stone-400">No sales recorded yet. Your top products will appear here.</p>
            @endforelse
        </div>
    </div>
</div>

<div class="mt-6 grid gap-6 xl:grid-cols-3">
    {{-- Low stock --}}
    <div class="card xl:col-span-2">
        <div class="flex items-center justify-between gap-3 border-b border-stone-100 px-5 py-4 dark:border-leaf-800/80">
            <h2 class="font-display text-lg font-semibold text-stone-800 dark:text-stone-100">Low stock alerts</h2>
            <span class="text-xs font-medium text-stone-400 dark:text-stone-500">5 units or fewer</span>
        </div>
        @if ($lowStock->count())
            <div class="overflow-x-auto">
                <table class="w-full min-w-[30rem] text-left text-sm">
                    <tbody class="divide-y divide-stone-100 dark:divide-leaf-800/60">
                        @foreach ($lowStock as $product)
                            <tr class="transition-colors hover:bg-leaf-50/60 dark:hover:bg-leaf-800/30">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $product->imageUrl() }}" alt="" class="h-10 w-10 rounded-lg border border-stone-200 object-cover dark:border-leaf-700">
                                        <span class="font-semibold text-stone-800 dark:text-stone-100">{{ $product->name }}</span>
                                    </div>
                                </td>
                                <td class="px-3 py-3">
                                    <span class="{{ $product->stock_quantity <= 0 ? 'badge-red' : 'badge-amber' }}">{{ $product->stock_quantity <= 0 ? 'Out of stock' : $product->stock_quantity . ' left' }}</span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <a href="{{ route('farmer.products.edit', $product) }}" class="btn-ghost btn-sm">Restock</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="px-5 py-10 text-center text-sm text-stone-500 dark:text-stone-400">All of your products are well stocked.</p>
        @endif
    </div>

    {{-- Announcements --}}
    <div class="card">
        <div class="border-b border-stone-100 px-5 py-4 dark:border-leaf-800/80">
            <h2 class="font-display text-lg font-semibold text-stone-800 dark:text-stone-100">Announcements</h2>
        </div>
        <div class="space-y-3 p-5">
            @forelse ($announcements as $announcement)
                <article class="rounded-2xl border border-stone-100 bg-cream-50 p-4 dark:border-leaf-800 dark:bg-leaf-900/40">
                    <p class="flex items-center justify-between gap-2 text-xs text-stone-400 dark:text-stone-500">
                        <span class="font-semibold uppercase tracking-wide text-leaf-700 dark:text-leaf-300">From the team</span>
                        {{ $announcement->published_at?->format('M j, Y') }}
                    </p>
                    <h3 class="mt-2 text-sm font-bold text-stone-800 dark:text-stone-100">{{ $announcement->title }}</h3>
                    <p class="mt-1 text-xs leading-relaxed text-stone-600 dark:text-stone-300">{{ Str::limit($announcement->body, 160) }}</p>
                </article>
            @empty
                <p class="py-8 text-center text-sm text-stone-500 dark:text-stone-400">No announcements right now. Check back soon.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
