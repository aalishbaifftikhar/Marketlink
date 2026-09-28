@extends('layouts.admin')

@section('title', $customer->name)

@section('content')
@php
    $badge = fn ($status) => match ($status) {
        'pending' => 'badge-amber',
        'active' => 'badge-green',
        'suspended' => 'badge-red',
        default => 'badge-stone',
    };
    $orderBadge = fn ($status) => match ($status) {
        'placed' => 'badge-amber',
        'accepted', 'ready_for_pickup' => 'badge-blue',
        'completed' => 'badge-green',
        'cancelled', 'declined' => 'badge-red',
        default => 'badge-stone',
    };
    $statusLabel = fn ($status) => match ($status) {
        'ready_for_pickup' => 'Ready for Pickup',
        default => ucfirst(str_replace('_', ' ', (string) $status)),
    };
@endphp

<div class="flex items-center gap-2 text-sm">
    <a href="{{ route('admin.customers.index') }}" class="text-stone-500 dark:text-stone-400 hover:text-leaf-700 dark:hover:text-leaf-300 font-medium">← Customers</a>
</div>

{{-- ======================= USER HEADER ======================= --}}
<div class="mt-3 card p-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="flex items-start gap-4 min-w-0">
            <img src="{{ $customer->avatarUrl() }}" alt="" class="w-16 h-16 rounded-2xl object-cover ring-2 ring-leaf-100 dark:ring-leaf-800 shrink-0">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2.5">
                    <h2 class="font-display text-2xl font-semibold text-leaf-950 dark:text-cream-50">{{ $customer->name }}</h2>
                    <span class="{{ $badge($customer->status) }}">{{ ucfirst($customer->status) }}</span>
                </div>
                <p class="mt-1 text-sm text-stone-600 dark:text-stone-300">
                    <a href="mailto:{{ $customer->email }}" class="text-leaf-700 dark:text-leaf-300 hover:underline">{{ $customer->email }}</a>
                    @if ($customer->phone) · <a href="tel:{{ $customer->phone }}" class="text-leaf-700 dark:text-leaf-300 hover:underline">{{ $customer->phone }}</a> @endif
                </p>
                <p class="mt-1 text-xs text-stone-500 dark:text-stone-400">Customer since {{ $customer->created_at?->format('M j, Y') }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.customers.toggle', $customer) }}" class="shrink-0">
            @csrf
            @if ($customer->isActive())
                <button type="submit" class="btn-secondary" onclick="return confirm('Suspend {{ $customer->name }}? They will lose access to their account.');">Suspend Account</button>
            @else
                <button type="submit" class="btn-primary">Activate Account</button>
            @endif
        </form>
    </div>
</div>

{{-- ======================= STATS ======================= --}}
<div class="mt-5 grid gap-4 grid-cols-2 sm:grid-cols-3 xl:grid-cols-5">
    <div class="card p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-stone-400 dark:text-stone-500">Total Orders</p>
        <p class="mt-1 font-display text-2xl font-semibold text-leaf-950 dark:text-cream-50">{{ $stats['orders'] }}</p>
    </div>
    <div class="card p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-stone-400 dark:text-stone-500">Completed</p>
        <p class="mt-1 font-display text-2xl font-semibold text-leaf-950 dark:text-cream-50">{{ $stats['completed'] }}</p>
    </div>
    <div class="card p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-stone-400 dark:text-stone-500">Total Spent</p>
        <p class="mt-1 font-display text-2xl font-semibold text-leaf-950 dark:text-cream-50">${{ number_format((float) $stats['spent'], 2) }}</p>
    </div>
    <div class="card p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-stone-400 dark:text-stone-500">Favorites</p>
        <p class="mt-1 font-display text-2xl font-semibold text-leaf-950 dark:text-cream-50">{{ $stats['favorites'] }}</p>
    </div>
    <div class="card p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-stone-400 dark:text-stone-500">Reviews Written</p>
        <p class="mt-1 font-display text-2xl font-semibold text-leaf-950 dark:text-cream-50">{{ $stats['reviews'] }}</p>
    </div>
</div>

{{-- ======================= ORDERS ======================= --}}
<div class="mt-6 card p-5">
    <h2 class="font-display text-lg font-semibold text-leaf-950 dark:text-cream-50">Order History</h2>
    <div class="mt-3 overflow-x-auto -mx-5 px-5">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs font-semibold uppercase tracking-wide text-stone-400 dark:text-stone-500 border-b border-stone-100 dark:border-leaf-800">
                    <th class="py-2 pr-4">Order</th>
                    <th class="py-2 pr-4">Farmer</th>
                    <th class="py-2 pr-4 text-center">Items</th>
                    <th class="py-2 pr-4 text-right">Total</th>
                    <th class="py-2 pr-4">Status</th>
                    <th class="py-2">Placed</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100 dark:divide-leaf-800/70">
                @forelse ($orders as $order)
                    <tr>
                        <td class="py-2.5 pr-4"><a href="{{ route('admin.orders.show', $order) }}" class="font-semibold text-leaf-800 dark:text-leaf-200 hover:underline">{{ $order->order_number }}</a></td>
                        <td class="py-2.5 pr-4 text-stone-600 dark:text-stone-300">{{ $order->farmer?->stall_name ?? '—' }}</td>
                        <td class="py-2.5 pr-4 text-center text-stone-600 dark:text-stone-300">{{ $order->items->sum('quantity') }}</td>
                        <td class="py-2.5 pr-4 text-right font-semibold text-stone-800 dark:text-stone-100">${{ number_format((float) $order->total_amount, 2) }}</td>
                        <td class="py-2.5 pr-4"><span class="{{ $orderBadge($order->status) }}">{{ $statusLabel($order->status) }}</span></td>
                        <td class="py-2.5 text-stone-500 dark:text-stone-400 whitespace-nowrap">{{ $order->placed_at?->format('M j, Y g:ia') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-8 text-center text-stone-500 dark:text-stone-400">This customer has not placed any orders yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $orders->links() }}</div>
</div>
@endsection
