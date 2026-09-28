@extends('layouts.app')

@section('title', 'About us')

@section('content')
    <section class="page-hero relative overflow-hidden">
        <div class="hero-overlay absolute inset-0"></div>
        <div class="relative mx-auto max-w-7xl px-4 py-16 text-center sm:px-6 sm:py-24">
            <p class="text-sm font-semibold uppercase tracking-widest text-leaf-200">Our story</p>
            <h1 class="mx-auto mt-3 max-w-3xl font-display text-4xl font-semibold leading-tight text-white sm:text-5xl">
                Better food starts with knowing your farmer
            </h1>
            <p class="mx-auto mt-5 max-w-2xl text-lg text-cream-100/90">
                {{ settings('site_name', 'MarketLink') }} connects local growers with the people who eat what they grow —
                pre-ordered online, picked up fresh at your community market.
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6">
        <div class="grid gap-10 lg:grid-cols-2 lg:items-center">
            <div>
                <h2 class="font-display text-3xl font-semibold text-leaf-950 dark:text-cream-50">Why we exist</h2>
                <p class="mt-4 leading-relaxed text-stone-600 dark:text-stone-300">
                    Farmers lose too much to middlemen and guesswork: they harvest without knowing what will sell, and good
                    produce goes to waste. Shoppers, meanwhile, want fresh, traceable food but don't know who grows it or
                    what will be at the market on Saturday.
                </p>
                <p class="mt-4 leading-relaxed text-stone-600 dark:text-stone-300">
                    We fix both sides. Farmers list what's ready and set weekly availability so they harvest to order.
                    Customers pre-order in a few clicks, then pick everything up at one verified market stall. Less waste,
                    fairer prices, fresher food.
                </p>
                <div class="mt-8 grid grid-cols-3 gap-4">
                    <div class="card p-4 text-center">
                        <p class="font-display text-3xl font-semibold text-leaf-700 dark:text-leaf-300">{{ \App\Models\Farmer::whereHas('user', fn ($q) => $q->where('status', 'active'))->count() }}</p>
                        <p class="mt-1 text-xs font-medium uppercase tracking-wider text-stone-500 dark:text-stone-400">Local farmers</p>
                    </div>
                    <div class="card p-4 text-center">
                        <p class="font-display text-3xl font-semibold text-leaf-700 dark:text-leaf-300">{{ \App\Models\Market::active()->count() }}</p>
                        <p class="mt-1 text-xs font-medium uppercase tracking-wider text-stone-500 dark:text-stone-400">Markets</p>
                    </div>
                    <div class="card p-4 text-center">
                        <p class="font-display text-3xl font-semibold text-leaf-700 dark:text-leaf-300">{{ \App\Models\Product::available()->count() }}</p>
                        <p class="mt-1 text-xs font-medium uppercase tracking-wider text-stone-500 dark:text-stone-400">Products listed</p>
                    </div>
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="card p-6">
                    <svg class="h-8 w-8 text-leaf-600 dark:text-leaf-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v3m0 0a5.25 5.25 0 015.25 5.25c0 2.485-2.12 4.5-5.25 4.5m0-9.5A5.25 5.25 0 006.75 8.25C6.75 10.735 8.87 12.75 12 12.75M12 12.75V21m-3.75-6h7.5"/></svg>
                    <h3 class="mt-4 font-semibold text-leaf-950 dark:text-cream-50">Harvest to order</h3>
                    <p class="mt-2 text-sm leading-relaxed text-stone-600 dark:text-stone-300">Farmers only pick what has already been sold, so nothing is wasted and everything is at its peak.</p>
                </div>
                <div class="card p-6">
                    <svg class="h-8 w-8 text-leaf-600 dark:text-leaf-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                    <h3 class="mt-4 font-semibold text-leaf-950 dark:text-cream-50">Verified growers</h3>
                    <p class="mt-2 text-sm leading-relaxed text-stone-600 dark:text-stone-300">Every farmer is approved by our team and sells at a verified market, so you always know where your food comes from.</p>
                </div>
                <div class="card p-6">
                    <svg class="h-8 w-8 text-leaf-600 dark:text-leaf-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <h3 class="mt-4 font-semibold text-leaf-950 dark:text-cream-50">One pickup trip</h3>
                    <p class="mt-2 text-sm leading-relaxed text-stone-600 dark:text-stone-300">Order from many farms in a single basket, then collect everything at your chosen market time slot.</p>
                </div>
                <div class="card p-6">
                    <svg class="h-8 w-8 text-leaf-600 dark:text-leaf-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                    <h3 class="mt-4 font-semibold text-leaf-950 dark:text-cream-50">Truly local</h3>
                    <p class="mt-2 text-sm leading-relaxed text-stone-600 dark:text-stone-300">Markets and pickup points are mapped so you can choose the one closest to home.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-leaf-950 py-16 dark:bg-black/40">
        <div class="mx-auto max-w-7xl px-4 text-center sm:px-6">
            <h2 class="mx-auto max-w-2xl font-display text-3xl font-semibold text-cream-50">Ready to taste the difference?</h2>
            <div class="mt-7 flex flex-wrap justify-center gap-3">
                <a href="{{ route('register') }}" class="btn-primary">Create a free account</a>
                <a href="{{ route('markets.index') }}" class="btn-secondary !border-cream-100/30 !bg-white/5 !text-cream-50 hover:!bg-white/10">Browse markets</a>
            </div>
        </div>
    </section>
@endsection
