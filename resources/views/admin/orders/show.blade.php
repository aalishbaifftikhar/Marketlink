@extends('layouts.admin')

@section('title', 'Order ' . $order->order_number)

@section('content')
@php
    $orderBadge = fn (string $status) => match ($status) {
        'placed' => 'badge-amber',
        'accepted', 'ready_for_pickup' => 'badge-blue',
        'completed' => 'badge-green',
        'cancelled', 'declined' => 'badge-red',
        default => 'badge-stone',
    };
@endphp

<div class="flex flex-col gap-6">
    <a href="{{ route('admin.orders.index') }}" class="inline-flex w-fit items-center gap-1.5 text-sm font-medium text-stone-500 hover:text-leaf-700 dark:text-stone-400 dark:hover:text-leaf-400">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
        </svg>
        Back to all orders
    </a>

    <div class="card flex flex-col gap-3 p-6 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="font-mono font-display text-2xl font-bold text-stone-900 dark:text-cream-50">{{ $order->order_number }}</h2>
                <span class="badge {{ $orderBadge($order->status) }}">{{ $order->statusLabel() }}</span>
            </div>
            <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">
                Placed {{ $order->placed_at?->format('M j, Y g:i A') ?? '—' }}
                @if ($order->cutoff_at)
                    · Cutoff {{ $order->cutoff_at->format('M j, Y g:i A') }}
                @endif
            </p>
        </div>
        <div class="text-left sm:text-right">
            <p class="text-xs font-semibold uppercase tracking-wide text-stone-400 dark:text-stone-500">Order total</p>
            <p class="font-display text-2xl font-bold text-stone-900 dark:text-cream-50">${{ number_format($order->total_amount, 2) }}</p>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
        <div class="card p-5">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-stone-400 dark:text-stone-500">Customer</h3>
            @if ($order->customer)
                <p class="mt-2 font-semibold text-stone-900 dark:text-cream-50">{{ $order->customer->name }}</p>
                <p class="text-sm text-stone-500 dark:text-stone-400">{{ $order->customer->email }}</p>
                @if ($order->customer->phone)
                    <p class="text-sm text-stone-500 dark:text-stone-400">{{ $order->customer->phone }}</p>
                @endif
                <a href="{{ route('admin.customers.show', $order->customer) }}" class="mt-3 inline-block text-sm font-medium text-leaf-700 hover:underline dark:text-leaf-400">View customer</a>
            @else
                <p class="mt-2 text-stone-500 dark:text-stone-400">Account deleted</p>
            @endif
        </div>

        <div class="card p-5">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-stone-400 dark:text-stone-500">Farmer</h3>
            @if ($order->farmer)
                <p class="mt-2 font-semibold text-stone-900 dark:text-cream-50">{{ $order->farmer->stall_name }}</p>
                <p class="text-sm text-stone-500 dark:text-stone-400">{{ $order->farmer->user?->name }}</p>
                <a href="{{ route('admin.farmers.show', $order->farmer) }}" class="mt-3 inline-block text-sm font-medium text-leaf-700 hover:underline dark:text-leaf-400">View farmer</a>
            @else
                <p class="mt-2 text-stone-500 dark:text-stone-400">—</p>
            @endif
        </div>

        <div class="card p-5">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-stone-400 dark:text-stone-500">Pickup</h3>
            <p class="mt-2 font-semibold text-stone-900 dark:text-cream-50">{{ $order->market?->name ?? '—' }}</p>
            <p class="text-sm text-stone-500 dark:text-stone-400">
                {{ $order->pickup_date?->format('M j, Y') }}
                @if ($order->pickup_slot)
                    · {{ $order->pickup_slot }}
                @endif
            </p>
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="border-b border-stone-100 px-5 py-4 dark:border-leaf-800/60">
            <h3 class="font-display text-lg font-bold text-stone-900 dark:text-cream-50">Items</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-stone-100 dark:divide-leaf-800/60">
                <thead class="bg-stone-50 text-xs font-semibold uppercase tracking-wide text-stone-400 dark:bg-leaf-900/40 dark:text-stone-500">
                    <tr>
                        <th class="px-5 py-3 text-left">Product</th>
                        <th class="px-5 py-3 text-left">Unit price</th>
                        <th class="px-5 py-3 text-left">Qty</th>
                        <th class="px-5 py-3 text-right">Line total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 dark:divide-leaf-800/60">
                    @foreach ($order->items as $item)
                        <tr>
                            <td class="px-5 py-4">
                                <p class="font-semibold text-stone-900 dark:text-cream-50">{{ $item->product_name }}</p>
                                @if ($item->unit)
                                    <p class="text-xs text-stone-500 dark:text-stone-400">{{ $item->unit }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-stone-500 dark:text-stone-400">${{ number_format($item->unit_price, 2) }}</td>
                            <td class="px-5 py-4 text-stone-500 dark:text-stone-400">{{ $item->quantity }}</td>
                            <td class="px-5 py-4 text-right font-medium text-stone-900 dark:text-cream-50">${{ number_format($item->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t border-stone-100 bg-stone-50 dark:border-leaf-800/60 dark:bg-leaf-900/40">
                    <tr>
                        <td colspan="3" class="px-5 py-3 text-right text-sm font-medium text-stone-500 dark:text-stone-400">Subtotal</td>
                        <td class="px-5 py-3 text-right text-sm font-semibold text-stone-900 dark:text-cream-50">${{ number_format($order->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="px-5 py-3 text-right text-sm font-semibold text-stone-900 dark:text-cream-50">Total</td>
                        <td class="px-5 py-3 text-right font-display text-lg font-bold text-stone-900 dark:text-cream-50">${{ number_format($order->total_amount, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    @if ($order->customer_notes || $order->farmer_notes)
        <div class="grid gap-4 lg:grid-cols-2">
            @if ($order->customer_notes)
                <div class="card p-5">
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-stone-400 dark:text-stone-500">Customer notes</h3>
                    <p class="mt-2 text-sm leading-relaxed text-stone-600 dark:text-stone-300">{{ $order->customer_notes }}</p>
                </div>
            @endif
            @if ($order->farmer_notes)
                <div class="card p-5">
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-stone-400 dark:text-stone-500">Farmer notes</h3>
                    <p class="mt-2 text-sm leading-relaxed text-stone-600 dark:text-stone-300">{{ $order->farmer_notes }}</p>
                </div>
            @endif
        </div>
    @endif

    <div class="card p-5">
        <h3 class="font-display text-lg font-bold text-stone-900 dark:text-cream-50">Reviews on this order</h3>
        @if ($order->reviews->count())
            <div class="mt-4 space-y-4">
                @foreach ($order->reviews as $review)
                    <div class="flex items-start gap-3 border-b border-stone-100 pb-4 last:border-0 last:pb-0 dark:border-leaf-800/60">
                        <img src="{{ $review->user?->avatarUrl() }}" alt="{{ $review->user?->name }}" class="h-9 w-9 rounded-full object-cover">
                        <div>
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-semibold text-stone-900 dark:text-cream-50">{{ $review->user?->name ?? 'Deleted account' }}</p>
                                <span class="text-sm font-semibold text-amber-500">{{ $review->rating }}★</span>
                                @if ($review->is_hidden)
                                    <span class="badge badge-stone">Hidden</span>
                                @endif
                            </div>
                            @if ($review->comment)
                                <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">"{{ $review->comment }}"</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="mt-3 text-sm text-stone-500 dark:text-stone-400">No reviews were left for this order.</p>
        @endif
    </div>
</div>
@endsection
