@extends('layouts.customer')

@section('title', 'Dashboard')

@section('content')
    @php
        $hour = now()->hour;
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
        $firstName = explode(' ', trim(auth()->user()->name))[0];

        $statCards = [
            ['label' => 'Active orders', 'value' => $stats['active'], 'href' => route('customer.orders.index', ['tab' => 'active']), 'icon' => 'M9 4h6v2h3a1 1 0 011 1v13a1 1 0 01-1 1H7a1 1 0 01-1-1V7a1 1 0 011-1h3V4zM8 12h8m-8 4h5'],
            ['label' => 'Completed pickups', 'value' => $stats['completed'], 'href' => route('customer.orders.index', ['tab' => 'history']), 'icon' => 'M5 13l4 4L19 7'],
            ['label' => 'Favorite farmers', 'value' => $stats['favorite_farmers'], 'href' => route('customer.favorites'), 'icon' => 'M4.3 6.3A5 5 0 0112 6a5 5 0 017.7.3c1.9 1.9 2 4.9.3 7L12 20.5l-8-7.2a5.3 5.3 0 01.3-7z'],
            ['label' => 'Saved products', 'value' => $stats['favorite_products'], 'href' => route('customer.favorites'), 'icon' => 'M5 8h14l-1.2 12H6.2L5 8zm3 0V6a4 4 0 118 0v2'],
        ];

        $statusBadge = fn ($status) => match ($status) {
            'placed' => 'badge-amber',
            'accepted', 'ready_for_pickup' => 'badge-blue',
            'completed' => 'badge-green',
            default => 'badge-red',
        };
    @endphp

    {{-- ======================= GREETING HERO ======================= --}}
    <section class="page-hero relative overflow-hidden rounded-3xl">
        <div class="hero-overlay absolute inset-0"></div>
        <div class="relative px-6 sm:px-10 py-10 sm:py-12">
            <span class="badge bg-white/10 text-leaf-100 border border-white/20 backdrop-blur">Your market, planned ahead</span>
            <h2 class="mt-4 font-display text-3xl sm:text-4xl font-semibold text-white">{{ $greeting }}, {{ $firstName }}.</h2>
            <p class="mt-2 text-leaf-100/85 max-w-xl leading-relaxed">
                Reserve this week's harvest before it sells out, then collect it at your chosen pickup slot.
            </p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('products.index') }}" class="btn-primary">
                    Browse fresh produce
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
                <a href="{{ route('customer.cart.index') }}" class="btn bg-white/10 text-white border border-white/25 hover:bg-white/20 backdrop-blur">View basket</a>
            </div>
        </div>
    </section>

    {{-- ======================= STATS ======================= --}}
    <section class="mt-6 grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ($statCards as $card)
            <a href="{{ $card['href'] }}" class="card card-hover p-5 group">
                <span class="w-10 h-10 rounded-xl bg-leaf-100 dark:bg-leaf-800 text-leaf-700 dark:text-leaf-300 grid place-items-center group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}"/></svg>
                </span>
                <p class="mt-3 font-display text-2xl font-semibold text-leaf-950 dark:text-cream-50">{{ $card['value'] }}</p>
                <p class="text-xs font-medium text-stone-500 dark:text-stone-400">{{ $card['label'] }}</p>
            </a>
        @endforeach
    </section>

    <div class="mt-6 grid lg:grid-cols-3 gap-6 items-start">
        {{-- ======================= LEFT COLUMN ======================= --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Upcoming pickups --}}
            <section class="card">
                <header class="flex items-center justify-between px-5 sm:px-6 py-4 border-b border-stone-200/80 dark:border-leaf-800/80">
                    <h3 class="font-display text-lg font-semibold text-leaf-950 dark:text-cream-50">Upcoming pickups</h3>
                    <a href="{{ route('customer.orders.index', ['tab' => 'active']) }}" class="text-sm font-semibold text-leaf-700 dark:text-leaf-300 hover:text-leaf-800 dark:hover:text-leaf-200">View all</a>
                </header>
                @if ($upcomingOrders->count())
                    <ul class="divide-y divide-stone-200/70 dark:divide-leaf-800/70">
                        @foreach ($upcomingOrders as $order)
                            <li>
                                <a href="{{ route('customer.orders.show', $order) }}" class="flex flex-wrap items-center gap-x-4 gap-y-2 px-5 sm:px-6 py-4 hover:bg-leaf-50/60 dark:hover:bg-leaf-800/30 transition-colors group">
                                    <div class="min-w-0 flex-1">
                                        <p class="font-semibold text-stone-800 dark:text-stone-100 group-hover:text-leaf-700 dark:group-hover:text-leaf-300 transition-colors">
                                            #{{ $order->order_number }}
                                            <span class="font-normal text-stone-400 dark:text-stone-500">·</span>
                                            <span class="font-normal">{{ $order->farmer?->stall_name ?? 'Local farm' }}</span>
                                        </p>
                                        <p class="mt-0.5 text-xs text-stone-500 dark:text-stone-400">
                                            {{ $order->pickup_date->format('l, j M') }} · {{ $order->pickup_slot }}
                                        </p>
                                    </div>
                                    <span class="{{ $statusBadge($order->status) }}">{{ $order->statusLabel() }}</span>
                                    <span class="font-display font-semibold text-leaf-800 dark:text-leaf-200">${{ number_format($order->total_amount, 2) }}</span>
                                    <svg class="w-4 h-4 text-stone-400 group-hover:text-leaf-600 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6l6 6-6 6"/></svg>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="px-6 py-10 text-center">
                        <span class="mx-auto w-12 h-12 rounded-2xl bg-leaf-100 dark:bg-leaf-800 text-leaf-600 dark:text-leaf-300 grid place-items-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 8l-8-5-8 5v8l8 5 8-5V8zM4 8l8 5 8-5m-8 5v8"/></svg>
                        </span>
                        <p class="mt-4 text-sm font-semibold text-stone-800 dark:text-stone-100">No upcoming pickups</p>
                        <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">Pre-order from your favorite farmers and your pickup schedule will appear here.</p>
                        <a href="{{ route('products.index') }}" class="btn-primary btn-sm mt-4">Start browsing</a>
                    </div>
                @endif
            </section>

            {{-- Recent orders --}}
            <section class="card">
                <header class="flex items-center justify-between px-5 sm:px-6 py-4 border-b border-stone-200/80 dark:border-leaf-800/80">
                    <h3 class="font-display text-lg font-semibold text-leaf-950 dark:text-cream-50">Recent orders</h3>
                    <a href="{{ route('customer.orders.index', ['tab' => 'history']) }}" class="text-sm font-semibold text-leaf-700 dark:text-leaf-300 hover:text-leaf-800 dark:hover:text-leaf-200">Order history</a>
                </header>
                @if ($recentOrders->count())
                    <ul class="divide-y divide-stone-200/70 dark:divide-leaf-800/70">
                        @foreach ($recentOrders as $order)
                            <li>
                                <a href="{{ route('customer.orders.show', $order) }}" class="flex flex-wrap items-center gap-x-4 gap-y-2 px-5 sm:px-6 py-4 hover:bg-leaf-50/60 dark:hover:bg-leaf-800/30 transition-colors group">
                                    <div class="min-w-0 flex-1">
                                        <p class="font-semibold text-stone-800 dark:text-stone-100 group-hover:text-leaf-700 dark:group-hover:text-leaf-300 transition-colors">
                                            #{{ $order->order_number }}
                                            <span class="font-normal text-stone-400 dark:text-stone-500">·</span>
                                            <span class="font-normal">{{ $order->farmer?->stall_name ?? 'Local farm' }}</span>
                                        </p>
                                        <p class="mt-0.5 text-xs text-stone-500 dark:text-stone-400">Placed {{ $order->placed_at?->diffForHumans() }}</p>
                                    </div>
                                    <span class="{{ $statusBadge($order->status) }}">{{ $order->statusLabel() }}</span>
                                    <span class="font-display font-semibold text-leaf-800 dark:text-leaf-200">${{ number_format($order->total_amount, 2) }}</span>
                                    <svg class="w-4 h-4 text-stone-400 group-hover:text-leaf-600 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6l6 6-6 6"/></svg>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="px-6 py-10 text-center">
                        <span class="mx-auto w-12 h-12 rounded-2xl bg-leaf-100 dark:bg-leaf-800 text-leaf-600 dark:text-leaf-300 grid place-items-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 4h6v2h3a1 1 0 011 1v13a1 1 0 01-1 1H7a1 1 0 01-1-1V7a1 1 0 011-1h3V4z"/></svg>
                        </span>
                        <p class="mt-4 text-sm font-semibold text-stone-800 dark:text-stone-100">No orders yet</p>
                        <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">Your completed and past orders will be listed here once you start shopping.</p>
                        <a href="{{ route('products.index') }}" class="btn-primary btn-sm mt-4">Browse products</a>
                    </div>
                @endif
            </section>
        </div>

        {{-- ======================= RIGHT COLUMN ======================= --}}
        <div class="space-y-6">
            {{-- Announcements --}}
            <section class="card">
                <header class="px-5 sm:px-6 py-4 border-b border-stone-200/80 dark:border-leaf-800/80">
                    <h3 class="font-display text-lg font-semibold text-leaf-950 dark:text-cream-50">Announcements</h3>
                </header>
                @if ($announcements->count())
                    <ul class="divide-y divide-stone-200/70 dark:divide-leaf-800/70">
                        @foreach ($announcements as $announcement)
                            <li class="px-5 sm:px-6 py-4">
                                <p class="text-sm font-semibold text-stone-800 dark:text-stone-100">{{ $announcement->title }}</p>
                                <p class="mt-1 text-sm leading-relaxed text-stone-600 dark:text-stone-300">{{ $announcement->body }}</p>
                                <p class="mt-2 text-xs text-stone-400 dark:text-stone-500">{{ $announcement->published_at?->diffForHumans() }}</p>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="px-6 py-10 text-center">
                        <span class="mx-auto w-12 h-12 rounded-2xl bg-leaf-100 dark:bg-leaf-800 text-leaf-600 dark:text-leaf-300 grid place-items-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.9a2 2 0 012 0l7 4a2 2 0 010 3.5l-7 4a2 2 0 01-2 0l-7-4a2 2 0 010-3.5l7-4zM3 11v5a2 2 0 001 1.7l7 4a2 2 0 002 0l7-4a2 2 0 001-1.7v-5"/></svg>
                        </span>
                        <p class="mt-4 text-sm font-semibold text-stone-800 dark:text-stone-100">Nothing new right now</p>
                        <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">Market news and updates from farmers will show up here.</p>
                    </div>
                @endif
            </section>

            {{-- Quick actions --}}
            <section class="card p-5 sm:p-6">
                <h3 class="font-display text-lg font-semibold text-leaf-950 dark:text-cream-50">Quick actions</h3>
                <div class="mt-4 grid gap-2">
                    <a href="{{ route('customer.assistant') }}" class="nav-link">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h8M8 14h5M21 12a9 9 0 01-13.2 7.9L3 21l1.1-4.8A9 9 0 1121 12z"/></svg>
                        Ask the AI assistant
                    </a>
                    <a href="{{ route('customer.favorites') }}" class="nav-link">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.3 6.3A5 5 0 0112 6a5 5 0 017.7.3c1.9 1.9 2 4.9.3 7L12 20.5l-8-7.2a5.3 5.3 0 01.3-7z"/></svg>
                        Review your favorites
                    </a>
                    <a href="{{ route('notifications.index') }}" class="nav-link">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        Check notifications
                    </a>
                </div>
            </section>
        </div>
    </div>

    {{-- ======================= SUGGESTIONS ======================= --}}
    <section class="mt-6">
        <div class="flex items-end justify-between gap-4">
            <div>
                <span class="badge-green">For you</span>
                <h3 class="mt-2 font-display text-2xl font-semibold text-leaf-950 dark:text-cream-50">Picked for you</h3>
            </div>
            <a href="{{ route('products.index') }}" class="btn-ghost btn-sm shrink-0 hidden sm:inline-flex">View all
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6l6 6-6 6"/></svg>
            </a>
        </div>

        @if ($suggestions->count())
            <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($suggestions as $p)
                    <x-product-card :product="$p" />
                @endforeach
            </div>
        @else
            <div class="card mt-5 px-6 py-12 text-center">
                <span class="mx-auto w-12 h-12 rounded-2xl bg-leaf-100 dark:bg-leaf-800 text-leaf-600 dark:text-leaf-300 grid place-items-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c4 3 6 6.5 6 10a6 6 0 11-12 0c0-3.5 2-7 6-10z"/></svg>
                </span>
                <p class="mt-4 text-sm font-semibold text-stone-800 dark:text-stone-100">No suggestions yet</p>
                <p class="mt-1 text-sm text-stone-500 dark:text-stone-400 max-w-md mx-auto">Favorite farmers and products you love, and we'll surface their freshest seasonal stock here each week.</p>
                <a href="{{ route('farmers.index') }}" class="btn-secondary btn-sm mt-4">Meet the farmers</a>
            </div>
        @endif
    </section>
@endsection
