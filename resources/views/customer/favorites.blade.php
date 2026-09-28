@extends('layouts.customer')

@section('title', 'Favorites')

@section('content')
    @php
        $farmers = $farmers->filter();
        $products = $products->filter();
        $markets = $markets->filter();
        $heartSvg = 'M4.3 6.3A5 5 0 0112 6a5 5 0 017.7.3c1.9 1.9 2 4.9.3 7L12 20.5l-8-7.2a5.3 5.3 0 01.3-7z';
        $starPath = 'M10 1.5l2.6 5.3 5.9.9-4.2 4.1 1 5.8L10 14.9l-5.3 2.7 1-5.8L1.5 7.7l5.9-.9L10 1.5z';
    @endphp

    {{-- ======================= FAVORITE FARMERS ======================= --}}
    <section>
        <div class="flex items-end justify-between gap-4">
            <div>
                <span class="badge-green">Growers you follow</span>
                <h2 class="mt-2 font-display text-2xl font-semibold text-leaf-950 dark:text-cream-50">Favorite farmers</h2>
            </div>
            <a href="{{ route('farmers.index') }}" class="btn-ghost btn-sm shrink-0 hidden sm:inline-flex">Discover more</a>
        </div>

        @if ($farmers->count())
            <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($farmers as $farmer)
                    <article class="card card-hover p-6 text-center relative">
                        <button type="button" onclick="toggleFavorite(event, 'farmer', {{ $farmer->id }}, this)"
                            data-fav-type="farmer" data-fav-id="{{ $farmer->id }}" data-favorited="1"
                            class="absolute top-4 right-4 w-9 h-9 rounded-full grid place-items-center bg-red-50 dark:bg-red-500/10 text-red-500 hover:scale-110 transition-transform"
                            aria-label="Remove from favorites">
                            <svg class="w-5 h-5 text-red-500 fill-current" fill="currentColor" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $heartSvg }}"/></svg>
                        </button>

                        <a href="{{ route('farmers.show', $farmer) }}" class="group block">
                            <img src="{{ $farmer->user?->avatarUrl() ?? asset('images/product-placeholder.svg') }}" alt="{{ $farmer->stall_name }}"
                                class="w-20 h-20 rounded-full object-cover mx-auto ring-4 ring-leaf-100 dark:ring-leaf-800 group-hover:ring-leaf-300 transition-all">
                            <h3 class="mt-4 font-display text-lg font-semibold text-stone-800 dark:text-stone-100 group-hover:text-leaf-700 dark:group-hover:text-leaf-300 transition-colors">{{ $farmer->stall_name }}</h3>
                            <p class="mt-1 text-xs text-stone-500 dark:text-stone-400 truncate">{{ $farmer->markets->pluck('name')->join(' · ') ?: 'Independent grower' }}</p>
                            <div class="mt-3 flex items-center justify-center gap-1 text-sm">
                                <span class="inline-flex items-center gap-0.5 font-semibold text-amber-600 dark:text-amber-400">
                                    <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="{{ $starPath }}"/></svg>
                                    {{ number_format($farmer->averageRating(), 1) }}
                                </span>
                                <span class="text-stone-400">·</span>
                                <span class="text-stone-500 dark:text-stone-400 text-xs">{{ $farmer->totalReviews() }} {{ Str::plural('review', $farmer->totalReviews()) }}</span>
                            </div>
                            <p class="mt-2 badge-stone">{{ $farmer->operatingDaysLabel() }}</p>
                        </a>
                    </article>
                @endforeach
            </div>
        @else
            <div class="card mt-5 px-6 py-10 text-center">
                <span class="mx-auto w-12 h-12 rounded-2xl bg-leaf-100 dark:bg-leaf-800 text-leaf-600 dark:text-leaf-300 grid place-items-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $heartSvg }}"/></svg>
                </span>
                <p class="mt-4 text-sm font-semibold text-stone-800 dark:text-stone-100">No favorite farmers yet</p>
                <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">Tap the heart on any farmer's stall to follow their weekly stock here.</p>
                <a href="{{ route('farmers.index') }}" class="btn-secondary btn-sm mt-4">Meet the farmers</a>
            </div>
        @endif
    </section>

    {{-- ======================= FAVORITE PRODUCTS ======================= --}}
    <section class="mt-10">
        <div class="flex items-end justify-between gap-4">
            <div>
                <span class="badge-green">Saved for later</span>
                <h2 class="mt-2 font-display text-2xl font-semibold text-leaf-950 dark:text-cream-50">Favorite products</h2>
            </div>
            <a href="{{ route('products.index') }}" class="btn-ghost btn-sm shrink-0 hidden sm:inline-flex">Browse products</a>
        </div>

        @if ($products->count())
            <div class="mt-5 grid gap-5 grid-cols-2 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        @else
            <div class="card mt-5 px-6 py-10 text-center">
                <span class="mx-auto w-12 h-12 rounded-2xl bg-leaf-100 dark:bg-leaf-800 text-leaf-600 dark:text-leaf-300 grid place-items-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14l-1.2 12H6.2L5 8zm3 0V6a4 4 0 118 0v2"/></svg>
                </span>
                <p class="mt-4 text-sm font-semibold text-stone-800 dark:text-stone-100">No saved products yet</p>
                <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">Save products you buy often and they'll be waiting here — quick to add to your basket.</p>
                <a href="{{ route('products.index') }}" class="btn-secondary btn-sm mt-4">Find something fresh</a>
            </div>
        @endif
    </section>

    {{-- ======================= FAVORITE MARKETS ======================= --}}
    @if ($markets->count())
        <section class="mt-10">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <span class="badge-green">Places you visit</span>
                    <h2 class="mt-2 font-display text-2xl font-semibold text-leaf-950 dark:text-cream-50">Favorite markets</h2>
                </div>
            </div>
            <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($markets as $market)
                    <article class="card card-hover relative">
                        <a href="{{ route('markets.show', $market) }}" class="flex items-center gap-4 p-5 group">
                            <span class="w-12 h-12 rounded-2xl bg-leaf-100 dark:bg-leaf-800 text-leaf-700 dark:text-leaf-300 grid place-items-center shrink-0 group-hover:scale-105 transition-transform">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.1-7.5 11.25-7.5 11.25S4.5 17.6 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-stone-800 dark:text-stone-100 group-hover:text-leaf-700 dark:group-hover:text-leaf-300 transition-colors truncate">{{ $market->name }}</p>
                                <p class="mt-0.5 text-xs text-stone-500 dark:text-stone-400 truncate">{{ $market->city }} · {{ $market->operatingDaysLabel() }}</p>
                            </div>
                        </a>
                        <button type="button" onclick="toggleFavorite(event, 'market', {{ $market->id }}, this)"
                            data-fav-type="market" data-fav-id="{{ $market->id }}" data-favorited="1"
                            class="absolute top-4 right-4 w-9 h-9 rounded-full grid place-items-center bg-red-50 dark:bg-red-500/10 text-red-500 hover:scale-110 transition-transform"
                            aria-label="Remove from favorites">
                            <svg class="w-5 h-5 text-red-500 fill-current" fill="currentColor" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $heartSvg }}"/></svg>
                        </button>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
@endsection
