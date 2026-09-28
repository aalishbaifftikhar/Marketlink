@extends('layouts.farmer')

@section('title', 'Order ' . $order->order_number)

@section('content')
@php
    $statusBadge = fn (?string $status) => match ($status) {
        'placed' => 'badge-amber',
        'accepted', 'ready_for_pickup' => 'badge-blue',
        'completed' => 'badge-green',
        'cancelled', 'declined' => 'badge-red',
        default => 'badge-stone',
    };

    $actions = match ($order->status) {
        'placed' => [
            ['status' => 'accepted', 'label' => 'Accept order', 'class' => 'btn-primary', 'confirm' => null],
            ['status' => 'declined', 'label' => 'Decline order', 'class' => 'btn-danger', 'confirm' => 'Decline this order? The reserved stock will be returned and the customer will be notified.'],
        ],
        'accepted' => [
            ['status' => 'ready_for_pickup', 'label' => 'Mark ready for pickup', 'class' => 'btn-primary', 'confirm' => null],
            ['status' => 'completed', 'label' => 'Mark completed', 'class' => 'btn-secondary', 'confirm' => null],
        ],
        'ready_for_pickup' => [
            ['status' => 'completed', 'label' => 'Mark completed', 'class' => 'btn-primary', 'confirm' => null],
        ],
        default => [],
    };
@endphp

<a href="{{ route('farmer.orders.index') }}" class="btn-ghost btn-sm">
    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    Back to orders
</a>

<div class="card mt-4 flex flex-wrap items-center justify-between gap-4 p-5 sm:p-6">
    <div>
        <div class="flex flex-wrap items-center gap-3">
            <h2 class="font-display text-xl font-semibold text-stone-800 dark:text-stone-100">{{ $order->order_number }}</h2>
            <span class="{{ $statusBadge($order->status) }}">{{ $order->statusLabel() }}</span>
        </div>
        <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">Placed {{ $order->placed_at?->format('l, F j, Y \a\t g:i A') }}</p>
    </div>
    @if ($actions)
        <div class="flex flex-wrap items-center gap-2">
            @foreach ($actions as $action)
                <form method="POST" action="{{ route('farmer.orders.status', $order) }}"
                      @if ($action['confirm']) onsubmit="return confirm('{{ $action['confirm'] }}');" @endif>
                    @csrf
                    <input type="hidden" name="status" value="{{ $action['status'] }}">
                    <button type="submit" class="{{ $action['class'] }} btn-sm">{{ $action['label'] }}</button>
                </form>
            @endforeach
        </div>
    @endif
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-3">
    {{-- Customer --}}
    <div class="card p-5">
        <h3 class="font-display text-base font-semibold text-stone-800 dark:text-stone-100">Customer</h3>
        <div class="mt-4 flex items-center gap-3">
            <img src="{{ $order->customer?->avatarUrl() ?? '' }}" alt="" class="h-11 w-11 rounded-full object-cover ring-2 ring-leaf-200 dark:ring-leaf-700">
            <div class="min-w-0">
                <p class="truncate text-sm font-bold text-stone-800 dark:text-stone-100">{{ $order->customer?->name ?? 'Guest' }}</p>
                <p class="truncate text-xs text-stone-500 dark:text-stone-400">{{ $order->customer?->email }}</p>
            </div>
        </div>
        @if ($order->customer?->phone)
            <p class="mt-4 flex items-center gap-2 text-sm text-stone-600 dark:text-stone-300">
                <svg class="h-4 w-4 shrink-0 text-stone-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                {{ $order->customer->phone }}
            </p>
        @endif
    </div>

    {{-- Pickup details --}}
    <div class="card p-5">
        <h3 class="font-display text-base font-semibold text-stone-800 dark:text-stone-100">Pickup</h3>
        <ul class="mt-4 space-y-3 text-sm">
            <li class="flex items-start gap-2.5 text-stone-600 dark:text-stone-300">
                <svg class="mt-0.5 h-4 w-4 shrink-0 text-leaf-600 dark:text-leaf-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.1-7.5 11.25-7.5 11.25S4.5 17.6 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                <span><strong class="font-semibold text-stone-800 dark:text-stone-100">{{ $order->market?->name ?? 'Market TBC' }}</strong><br>{{ $order->market?->address }}</span>
            </li>
            <li class="flex items-start gap-2.5 text-stone-600 dark:text-stone-300">
                <svg class="mt-0.5 h-4 w-4 shrink-0 text-leaf-600 dark:text-leaf-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                <span>{{ $order->pickup_date?->format('l, F j, Y') }}<br><strong class="font-semibold text-stone-800 dark:text-stone-100">{{ $order->pickup_slot }}</strong></span>
            </li>
            @if ($order->cutoff_at)
                <li class="flex items-start gap-2.5 text-stone-600 dark:text-stone-300">
                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-leaf-600 dark:text-leaf-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Customer can modify until<br><strong class="font-semibold text-stone-800 dark:text-stone-100">{{ $order->cutoff_at->format('M j, g:i A') }}</strong></span>
                </li>
            @endif
            @if ($order->completed_at)
                <li class="flex items-start gap-2.5 text-stone-600 dark:text-stone-300">
                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-leaf-600 dark:text-leaf-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    <span>Completed {{ $order->completed_at->format('M j, g:i A') }}</span>
                </li>
            @endif
        </ul>
    </div>

    {{-- Notes --}}
    <div class="card p-5">
        <h3 class="font-display text-base font-semibold text-stone-800 dark:text-stone-100">Notes</h3>
        @if ($order->customer_notes)
            <div class="mt-4 rounded-xl bg-cream-100 p-3.5 dark:bg-leaf-800/50">
                <p class="text-xs font-semibold uppercase tracking-wide text-stone-400 dark:text-stone-500">From the customer</p>
                <p class="mt-1.5 text-sm leading-relaxed text-stone-700 dark:text-stone-200">{{ $order->customer_notes }}</p>
            </div>
        @endif
        @if ($order->farmer_notes)
            <div class="mt-4 rounded-xl bg-leaf-50 p-3.5 dark:bg-leaf-800/50">
                <p class="text-xs font-semibold uppercase tracking-wide text-leaf-700 dark:text-leaf-300">Your notes</p>
                <p class="mt-1.5 text-sm leading-relaxed text-stone-700 dark:text-stone-200">{{ $order->farmer_notes }}</p>
            </div>
        @endif
        @if (! $order->customer_notes && ! $order->farmer_notes)
            <p class="mt-4 text-sm text-stone-400 dark:text-stone-500">No notes on this order.</p>
        @endif
    </div>
</div>

{{-- Items --}}
<div class="card mt-6">
    <div class="border-b border-stone-100 px-5 py-4 dark:border-leaf-800/80">
        <h3 class="font-display text-lg font-semibold text-stone-800 dark:text-stone-100">Items</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[34rem] text-left text-sm">
            <thead>
                <tr class="text-xs uppercase tracking-wide text-stone-400 dark:text-stone-500">
                    <th class="px-5 py-3 font-semibold">Product</th>
                    <th class="px-3 py-3 font-semibold">Unit price</th>
                    <th class="px-3 py-3 font-semibold">Qty</th>
                    <th class="px-5 py-3 text-right font-semibold">Line total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100 dark:divide-leaf-800/60">
                @foreach ($order->items as $item)
                    <tr>
                        <td class="px-5 py-3.5">
                            <p class="font-semibold text-stone-800 dark:text-stone-100">{{ $item->product_name }}</p>
                            <p class="text-xs text-stone-400 dark:text-stone-500">per {{ $item->unit }}</p>
                        </td>
                        <td class="whitespace-nowrap px-3 py-3.5 text-stone-600 dark:text-stone-300">${{ number_format($item->unit_price, 2) }}</td>
                        <td class="px-3 py-3.5 text-stone-600 dark:text-stone-300">× {{ $item->quantity }}</td>
                        <td class="whitespace-nowrap px-5 py-3.5 text-right font-semibold text-stone-800 dark:text-stone-100">${{ number_format($item->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="border-t border-stone-100 dark:border-leaf-800/80">
                <tr>
                    <td colspan="3" class="px-5 py-3 text-right text-sm text-stone-500 dark:text-stone-400">Subtotal</td>
                    <td class="whitespace-nowrap px-5 py-3 text-right text-sm font-semibold text-stone-700 dark:text-stone-200">${{ number_format($order->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="px-5 pb-4 text-right font-display text-base font-semibold text-stone-800 dark:text-stone-100">Total due at pickup</td>
                    <td class="whitespace-nowrap px-5 pb-4 text-right font-display text-base font-semibold text-leaf-700 dark:text-leaf-300">${{ number_format($order->total_amount, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
