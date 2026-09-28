@extends('layouts.farmer')

@section('title', 'Orders')

@section('content')
@php
    $statusBadge = fn (?string $status) => match ($status) {
        'placed' => 'badge-amber',
        'accepted', 'ready_for_pickup' => 'badge-blue',
        'completed' => 'badge-green',
        'cancelled', 'declined' => 'badge-red',
        default => 'badge-stone',
    };
    $statusLabel = fn (string $status) => ucwords(str_replace('_', ' ', $status));
    $currentStatus = $filters['status'] ?? 'all';
@endphp

<div class="flex flex-wrap items-center justify-between gap-4">
    <p class="text-sm text-stone-500 dark:text-stone-400">
        @if ($currentStatus === 'all')
            Showing active orders. Customers pay in person when they pick up.
        @else
            Orders with status “{{ $statusLabel($currentStatus) }}”.
        @endif
    </p>
</div>

<div class="card mt-6 p-4 sm:p-5">
    <form method="GET" action="{{ route('farmer.orders.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <input type="hidden" name="status" value="{{ $currentStatus }}">
        <div class="relative flex-1">
            <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-stone-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/></svg>
            <input type="search" name="q" value="{{ $filters['q'] }}" class="input pl-10" placeholder="Search by order number…">
        </div>
        <div>
            <label class="sr-only" for="date">Pickup date</label>
            <input type="date" id="date" name="date" value="{{ $filters['date'] }}" class="input sm:w-48">
        </div>
        <button type="submit" class="btn-secondary shrink-0">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/></svg>
            Search
        </button>
        @if ($filters['q'] || $filters['date'] || $currentStatus !== 'all')
            <a href="{{ route('farmer.orders.index') }}" class="btn-ghost btn-sm shrink-0">Reset</a>
        @endif
    </form>

    {{-- Status pills --}}
    <div class="mt-4 flex flex-wrap gap-2 border-t border-stone-100 pt-4 dark:border-leaf-800/80">
        <a href="{{ route('farmer.orders.index', array_filter(['q' => $filters['q'], 'date' => $filters['date']])) }}"
           class="{{ $currentStatus === 'all' ? 'btn-primary' : 'btn-secondary' }} btn-sm">
            Active <span class="opacity-75">({{ number_format(array_sum($counts)) }})</span>
        </a>
        @foreach ($statuses as $status)
            <a href="{{ route('farmer.orders.index', array_filter(['status' => $status, 'q' => $filters['q'], 'date' => $filters['date']])) }}"
               class="{{ $currentStatus === $status ? 'btn-primary' : 'btn-secondary' }} btn-sm">
                {{ $statusLabel($status) }} <span class="opacity-75">({{ number_format($counts[$status] ?? 0) }})</span>
            </a>
        @endforeach
    </div>
</div>

<div class="card mt-6">
    @if ($orders->count())
        <div class="overflow-x-auto">
            <table class="w-full min-w-[52rem] text-left text-sm">
                <thead>
                    <tr class="border-b border-stone-100 text-xs uppercase tracking-wide text-stone-400 dark:border-leaf-800/80 dark:text-stone-500">
                        <th class="px-5 py-3.5 font-semibold">Order</th>
                        <th class="px-3 py-3.5 font-semibold">Customer</th>
                        <th class="px-3 py-3.5 font-semibold">Items</th>
                        <th class="px-3 py-3.5 font-semibold">Pickup</th>
                        <th class="px-3 py-3.5 font-semibold">Total</th>
                        <th class="px-3 py-3.5 font-semibold">Status</th>
                        <th class="px-3 py-3.5 font-semibold">Placed</th>
                        <th class="px-5 py-3.5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 dark:divide-leaf-800/60">
                    @foreach ($orders as $order)
                        <tr class="transition-colors hover:bg-leaf-50/60 dark:hover:bg-leaf-800/30">
                            <td class="whitespace-nowrap px-5 py-4 font-semibold text-stone-800 dark:text-stone-100">{{ $order->order_number }}</td>
                            <td class="px-3 py-4 text-stone-700 dark:text-stone-200">{{ $order->customer?->name ?? 'Guest' }}</td>
                            <td class="px-3 py-4 text-stone-600 dark:text-stone-300">{{ $order->items->count() }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-stone-600 dark:text-stone-300">
                                {{ $order->pickup_date?->format('D, M j') }}
                                <span class="block text-xs text-stone-400 dark:text-stone-500">{{ $order->pickup_slot }}</span>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 font-semibold text-stone-800 dark:text-stone-100">${{ number_format($order->total_amount, 2) }}</td>
                            <td class="px-3 py-4"><span class="{{ $statusBadge($order->status) }}">{{ $order->statusLabel() }}</span></td>
                            <td class="whitespace-nowrap px-3 py-4 text-stone-500 dark:text-stone-400">{{ $order->placed_at?->format('M j, g:i A') }}</td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('farmer.orders.show', $order) }}" class="btn-ghost btn-sm">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="px-6 py-16 text-center">
            <span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-leaf-100 text-leaf-600 dark:bg-leaf-800 dark:text-leaf-300">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V7a2 2 0 00-2-2h-3M9 3h6v4H9z"/></svg>
            </span>
            <h2 class="mt-5 font-display text-xl font-semibold text-stone-800 dark:text-stone-100">No orders here</h2>
            <p class="mx-auto mt-2 max-w-sm text-sm text-stone-500 dark:text-stone-400">
                @if ($filters['q'] || $filters['date'] || $currentStatus !== 'all')
                    Nothing matches your current filters.
                @else
                    New customer pre-orders will appear here for you to accept and prepare.
                @endif
            </p>
            @if ($filters['q'] || $filters['date'] || $currentStatus !== 'all')
                <a href="{{ route('farmer.orders.index') }}" class="btn-secondary mt-6">Clear filters</a>
            @endif
        </div>
    @endif
</div>

@if ($orders->hasPages())
    <div class="mt-6">{{ $orders->links() }}</div>
@endif
@endsection
