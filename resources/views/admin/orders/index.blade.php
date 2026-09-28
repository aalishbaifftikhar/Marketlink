@extends('layouts.admin')

@section('title', 'All Orders')

@section('content')
@php
    $orderBadge = fn (string $status) => match ($status) {
        'placed' => 'badge-amber',
        'accepted', 'ready_for_pickup' => 'badge-blue',
        'completed' => 'badge-green',
        'cancelled', 'declined' => 'badge-red',
        default => 'badge-stone',
    };
    $statusLabel = fn (string $status) => ucwords(str_replace('_', ' ', $status));
@endphp

<div class="flex flex-col gap-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="font-display text-xl font-bold text-stone-900 dark:text-cream-50">All Orders</h2>
            <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">Monitor every pre-order placed across the platform.</p>
        </div>
    </div>

    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.orders.index', array_merge(request()->only(['q', 'date_from', 'date_to', 'market']))) }}"
            class="rounded-full px-4 py-1.5 text-sm font-semibold transition {{ !request('status') ? 'bg-leaf-600 text-white shadow-sm' : 'bg-white dark:bg-leaf-900/50 text-stone-600 dark:text-stone-300 ring-1 ring-stone-200 dark:ring-leaf-700/60 hover:bg-stone-50 dark:hover:bg-leaf-800' }}">
            All <span class="ml-1 text-xs opacity-70">({{ $counts['all'] ?? 0 }})</span>
        </a>
        @foreach ($statuses as $status)
            <a href="{{ route('admin.orders.index', array_merge(request()->only(['q', 'date_from', 'date_to', 'market']), ['status' => $status])) }}"
                class="rounded-full px-4 py-1.5 text-sm font-semibold transition {{ request('status') === $status ? 'bg-leaf-600 text-white shadow-sm' : 'bg-white dark:bg-leaf-900/50 text-stone-600 dark:text-stone-300 ring-1 ring-stone-200 dark:ring-leaf-700/60 hover:bg-stone-50 dark:hover:bg-leaf-800' }}">
                {{ $statusLabel($status) }} <span class="ml-1 text-xs opacity-70">({{ $counts[$status] ?? 0 }})</span>
            </a>
        @endforeach
    </div>

    <form method="GET" action="{{ route('admin.orders.index') }}" class="card grid gap-4 p-4 sm:grid-cols-2 lg:grid-cols-5 lg:items-end">
        @if (request('status'))
            <input type="hidden" name="status" value="{{ request('status') }}">
        @endif
        <div>
            <label class="input-label">Search</label>
            <input type="text" name="q" value="{{ request('q') }}" class="input" placeholder="Order number...">
        </div>
        <div>
            <label class="input-label">From</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="input">
        </div>
        <div>
            <label class="input-label">To</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="input">
        </div>
        <div>
            <label class="input-label">Market</label>
            <select name="market" class="input">
                <option value="">All markets</option>
                @foreach ($markets as $market)
                    <option value="{{ $market->id }}" @selected(request('market') == $market->id)>{{ $market->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-ghost">Reset</a>
        </div>
    </form>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-stone-100 dark:divide-leaf-800/60">
                <thead class="bg-stone-50 text-xs font-semibold uppercase tracking-wide text-stone-400 dark:bg-leaf-900/40 dark:text-stone-500">
                    <tr>
                        <th class="px-5 py-3 text-left">Order</th>
                        <th class="px-5 py-3 text-left">Customer</th>
                        <th class="px-5 py-3 text-left">Farmer</th>
                        <th class="px-5 py-3 text-left">Market</th>
                        <th class="px-5 py-3 text-left">Pickup</th>
                        <th class="px-5 py-3 text-left">Total</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">Placed</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 dark:divide-leaf-800/60">
                    @forelse ($orders as $order)
                        <tr>
                            <td class="px-5 py-4">
                                <a href="{{ route('admin.orders.show', $order) }}" class="font-mono font-semibold text-leaf-700 hover:underline dark:text-leaf-400">
                                    {{ $order->order_number }}
                                </a>
                            </td>
                            <td class="px-5 py-4 text-stone-900 dark:text-cream-50">{{ $order->customer?->name ?? '—' }}</td>
                            <td class="px-5 py-4 text-stone-500 dark:text-stone-400">{{ $order->farmer?->stall_name ?? '—' }}</td>
                            <td class="px-5 py-4 text-stone-500 dark:text-stone-400">{{ $order->market?->name ?? '—' }}</td>
                            <td class="px-5 py-4 text-stone-500 dark:text-stone-400">
                                {{ $order->pickup_date?->format('M j, Y') }}
                                <span class="block text-xs">{{ $order->pickup_slot }}</span>
                            </td>
                            <td class="px-5 py-4 font-medium text-stone-900 dark:text-cream-50">${{ number_format($order->total_amount, 2) }}</td>
                            <td class="px-5 py-4">
                                <span class="badge {{ $orderBadge($order->status) }}">{{ $order->statusLabel() }}</span>
                            </td>
                            <td class="px-5 py-4 text-stone-500 dark:text-stone-400">{{ $order->placed_at?->diffForHumans() ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-stone-400 dark:text-stone-500">No orders match the current filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($orders->hasPages())
            <div class="border-t border-stone-100 px-5 py-4 dark:border-leaf-800/60">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
