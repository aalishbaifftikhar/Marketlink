@extends('layouts.customer')

@section('title', 'My Orders')

@section('content')
    @php
        $activeCount = auth()->user()->orders()->active()->count();
        $historyCount = auth()->user()->orders()->history()->count();

        $tabs = [
            'active' => ['label' => 'Active', 'count' => $activeCount],
            'history' => ['label' => 'History', 'count' => $historyCount],
        ];

        $statusBadge = fn ($status) => match ($status) {
            'placed' => 'badge-amber',
            'accepted', 'ready_for_pickup' => 'badge-blue',
            'completed' => 'badge-green',
            default => 'badge-red',
        };
    @endphp

    {{-- ======================= TABS ======================= --}}
    <div class="flex flex-wrap gap-2">
        @foreach ($tabs as $key => $t)
            <a href="{{ route('customer.orders.index', ['tab' => $key]) }}"
                class="{{ $tab === $key ? 'nav-link-active' : 'nav-link' }} !gap-2.5">
                {{ $t['label'] }}
                <span class="{{ $tab === $key ? 'bg-white/20' : 'bg-stone-200/70 dark:bg-leaf-800' }} min-w-[22px] h-[22px] px-1.5 rounded-full text-[11px] font-bold grid place-items-center">{{ $t['count'] }}</span>
            </a>
        @endforeach
    </div>

    {{-- ======================= ORDER LIST ======================= --}}
    @if ($orders->count())
        <div class="mt-6 space-y-4">
            @foreach ($orders as $order)
                <article class="card card-hover p-5 sm:p-6">
                    <div class="flex flex-wrap items-center gap-x-5 gap-y-3">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1.5">
                                <a href="{{ route('customer.orders.show', $order) }}" class="font-display font-semibold text-leaf-950 dark:text-cream-50 hover:text-leaf-700 dark:hover:text-leaf-300 transition-colors">
                                    #{{ $order->order_number }}
                                </a>
                                <span class="{{ $statusBadge($order->status) }}">{{ $order->statusLabel() }}</span>
                            </div>
                            <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">
                                {{ $order->farmer?->stall_name ?? 'Local farm' }}
                                <span class="text-stone-300 dark:text-stone-600">·</span>
                                Placed {{ $order->placed_at?->diffForHumans() }}
                            </p>
                        </div>

                        <div class="text-sm">
                            <p class="font-semibold text-stone-800 dark:text-stone-100">
                                {{ $order->pickup_date?->format('D, j M') }} · {{ $order->pickup_slot }}
                            </p>
                            <p class="text-xs text-stone-500 dark:text-stone-400 text-right">Pickup</p>
                        </div>

                        <p class="font-display text-lg font-semibold text-leaf-800 dark:text-leaf-200 w-24 text-right">
                            ${{ number_format($order->total_amount, 2) }}
                        </p>
                    </div>

                    <div class="mt-4 pt-4 border-t border-stone-200/70 dark:border-leaf-800/70 flex flex-wrap items-center gap-2">
                        <a href="{{ route('customer.orders.show', $order) }}" class="btn-secondary btn-sm">View details</a>
                        @if ($order->canBeCancelled())
                            <form method="POST" action="{{ route('customer.orders.cancel', $order) }}"
                                onsubmit="return confirm('Cancel order #{{ $order->order_number }}? The reserved stock will be released.')">
                                @csrf
                                <button type="submit" class="btn-danger btn-sm">Cancel order</button>
                            </form>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-6">{{ $orders->links() }}</div>
    @else
        {{-- ======================= EMPTY STATES ======================= --}}
        <div class="card mt-6 px-6 py-16 text-center max-w-2xl mx-auto">
            <span class="mx-auto w-14 h-14 rounded-2xl bg-leaf-100 dark:bg-leaf-800 text-leaf-600 dark:text-leaf-300 grid place-items-center">
                @if ($tab === 'active')
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 8l-8-5-8 5v8l8 5 8-5V8zM4 8l8 5 8-5m-8 5v8"/></svg>
                @else
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l2.5 2.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                @endif
            </span>
            @if ($tab === 'active')
                <h2 class="mt-5 font-display text-2xl font-semibold text-leaf-950 dark:text-cream-50">No active orders</h2>
                <p class="mt-2 text-sm text-stone-500 dark:text-stone-400 max-w-md mx-auto leading-relaxed">
                    You don't have any pre-orders waiting for pickup right now. Reserve this week's harvest and it'll show up here.
                </p>
            @else
                <h2 class="mt-5 font-display text-2xl font-semibold text-leaf-950 dark:text-cream-50">No past orders</h2>
                <p class="mt-2 text-sm text-stone-500 dark:text-stone-400 max-w-md mx-auto leading-relaxed">
                    Completed, cancelled and declined orders will appear in your history once you've placed your first pre-order.
                </p>
            @endif
            <a href="{{ route('products.index') }}" class="btn-primary mt-6">Browse fresh produce</a>
        </div>
    @endif
@endsection
