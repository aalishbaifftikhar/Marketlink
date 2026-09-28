@extends('layouts.app')

@section('title', 'Fresh Local Produce, Pre-Ordered from Farmers')

@section('content')
{{-- ======================= HERO ======================= --}}
<section class="hero-bg relative">
    <div class="hero-overlay absolute inset-0"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 sm:py-32 lg:py-40">
        <div class="max-w-2xl">
            <span class="badge bg-white/10 text-leaf-100 border border-white/20 backdrop-blur animate-fade-up">eGreen Basket · Farmers Market Network</span>
            <h1 class="mt-5 font-display text-4xl sm:text-5xl lg:text-6xl font-semibold text-white leading-[1.08] animate-fade-up" style="animation-delay:.08s">
                Fresh from local farms,<br class="hidden sm:block"> straight to your <span class="text-leaf-300">basket</span>.
            </h1>
            <p class="mt-5 text-lg text-leaf-100/85 leading-relaxed max-w-xl animate-fade-up" style="animation-delay:.16s">
                Discover nearby farmers markets, browse this week's harvest, and reserve your pick-up in advance. No more sold-out trips — pay in person on market day.
            </p>
            <div class="mt-8 flex flex-wrap gap-3 animate-fade-up" style="animation-delay:.24s">
                <a href="{{ route('products.index') }}" class="btn-primary !px-7 !py-3.5 !text-base">
                    Browse Fresh Produce
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
                <a href="{{ route('markets.index') }}" class="btn !px-7 !py-3.5 !text-base bg-white/10 text-white border border-white/25 hover:bg-white/20 backdrop-blur">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.1-7.5 11.25-7.5 11.25S4.5 17.6 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                    Find Markets Near You
                </a>
            </div>

            {{-- Stats --}}
            <div class="mt-12 grid grid-cols-3 gap-4 max-w-md animate-fade-up" style="animation-delay:.32s">
                <div><p class="text-2xl sm:text-3xl font-display font-semibold text-white">{{ $stats['farmers'] }}+</p><p class="text-xs text-leaf-100/70 mt-0.5">Local farmers</p></div>
                <div><p class="text-2xl sm:text-3xl font-display font-semibold text-white">{{ $stats['markets'] }}</p><p class="text-xs text-leaf-100/70 mt-0.5">Markets</p></div>
                <div><p class="text-2xl sm:text-3xl font-display font-semibold text-white">{{ $stats['orders'] }}+</p><p class="text-xs text-leaf-100/70 mt-0.5">Pre-orders placed</p></div>
            </div>
        </div>
    </div>
    <div class="hero-overlay absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-cream-50 dark:from-leaf-950 to-transparent" style="background:none;background-image:linear-gradient(to top, rgb(13 31 15 / 0.4), transparent)"></div>
</section>

{{-- ======================= CATEGORY STRIP ======================= --}}
@if ($categories->count())
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 relative z-10">
    <div class="card p-5 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 animate-fade-up">
        @foreach ($categories as $category)
            <a href="{{ route('products.index', ['category' => $category->id]) }}" class="flex flex-col items-center gap-2 rounded-xl px-3 py-4 text-center hover:bg-leaf-50 dark:hover:bg-leaf-800/50 transition-colors group">
                <span class="w-11 h-11 rounded-xl bg-leaf-100 dark:bg-leaf-800 text-leaf-700 dark:text-leaf-300 grid place-items-center group-hover:scale-110 transition-transform">
                    @if ($category->icon)
                        <img src="{{ $category->icon }}" alt="" class="w-6 h-6 object-contain">
                    @else
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v3m0 12v3M3 12h3m12 0h3M7 7l2 2m6 6 2 2M7 17l2-2m6-6 2-2"/><circle cx="12" cy="12" r="3"/></svg>
                    @endif
                </span>
                <span class="text-xs font-semibold text-stone-700 dark:text-stone-200">{{ $category->name }}</span>
            </a>
        @endforeach
    </div>
</section>
@endif

{{-- ======================= FEATURES ======================= --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 sm:py-24">
    <div class="text-center max-w-2xl mx-auto">
        <span class="badge-green">Why MarketLink</span>
        <h2 class="mt-4 font-display text-3xl sm:text-4xl font-semibold text-leaf-900 dark:text-leaf-100">A better way to shop the farmers market</h2>
        <p class="mt-4 text-stone-600 dark:text-stone-300">Everything you need to plan your market trip, support local growers, and never miss out on fresh stock again.</p>
    </div>

    <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        @php
            $features = [
                ['title' => 'See Weekly Stock', 'desc' => 'Farmers publish live inventory and prices so you know exactly what is available before you go.', 'icon' => 'M4 7h16M4 12h16M4 17h10'],
                ['title' => 'Reserve for Pickup', 'desc' => 'Pre-order your basket and choose a pickup slot. Your items are set aside until market day.', 'icon' => 'M5 8h14l-1.2 12H6.2L5 8zm3 0V6a4 4 0 118 0v2'],
                ['title' => 'Live Market Map', 'desc' => 'Find nearby markets and stall locations on an interactive map with directions.', 'icon' => 'M15 10.5a3 3 0 11-6 0 3 3 0 016 0zM19.5 10.5c0 7.1-7.5 11.25-7.5 11.25S4.5 17.6 4.5 10.5a7.5 7.5 0 1115 0z'],
                ['title' => 'Pay at Pickup', 'desc' => 'No online payment or fees. Settle in person when you collect your fresh produce.', 'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-2m2-4h-6m0 0l3-3m-3 3l3 3'],
            ];
        @endphp
        @foreach ($features as $i => $f)
            <div class="card card-hover p-6 animate-fade-up" style="animation-delay:{{ $i * 0.08 }}s">
                <span class="w-12 h-12 rounded-2xl bg-leaf-600 text-white grid place-items-center shadow-soft">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $f['icon'] }}"/></svg>
                </span>
                <h3 class="mt-4 font-display text-lg font-semibold text-stone-800 dark:text-stone-100">{{ $f['title'] }}</h3>
                <p class="mt-2 text-sm text-stone-600 dark:text-stone-300 leading-relaxed">{{ $f['desc'] }}</p>
            </div>
        @endforeach
    </div>
</section>

{{-- ======================= HOW IT WORKS ======================= --}}
<section class="bg-leaf-900 dark:bg-leaf-900/40 text-white py-20 sm:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto">
            <span class="badge bg-white/10 text-leaf-100 border border-white/20">How it works</span>
            <h2 class="mt-4 font-display text-3xl sm:text-4xl font-semibold">From field to basket in four steps</h2>
        </div>
        <div class="mt-14 grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
            @php
                $steps = [
                    ['n' => '01', 't' => 'Browse & Discover', 'd' => 'Explore markets, farmers and this week\'s fresh stock near you.'],
                    ['n' => '02', 't' => 'Pre-Order', 'd' => 'Add to your basket and pick a convenient pickup day and time slot.'],
                    ['n' => '03', 't' => 'Farmer Prepares', 'd' => 'The farmer accepts your order and sets your items aside, harvested fresh.'],
                    ['n' => '04', 't' => 'Pick Up & Pay', 'd' => 'Collect at the market stall and pay in person. Modify or cancel anytime before cutoff.'],
                ];
            @endphp
            @foreach ($steps as $s)
                <div class="relative">
                    <p class="font-display text-5xl font-semibold text-leaf-600/60">{{ $s['n'] }}</p>
                    <h3 class="mt-3 font-display text-xl font-semibold">{{ $s['t'] }}</h3>
                    <p class="mt-2 text-sm text-leaf-100/75 leading-relaxed">{{ $s['d'] }}</p>
                </div>
            @endforeach
        </div>
        <div class="mt-12 text-center">
            <a href="{{ route('register') }}" class="btn-primary !px-8 !py-3.5 !text-base">Create a free account</a>
        </div>
    </div>
</section>

{{-- ======================= FEATURED PRODUCTS ======================= --}}
@if ($products->count())
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 sm:py-24">
    <div class="flex items-end justify-between gap-4">
        <div>
            <span class="badge-green">This week's harvest</span>
            <h2 class="mt-4 font-display text-3xl sm:text-4xl font-semibold text-leaf-900 dark:text-leaf-100">Fresh arrivals</h2>
        </div>
        <a href="{{ route('products.index') }}" class="btn-ghost btn-sm shrink-0 hidden sm:inline-flex">View all
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6l6 6-6 6"/></svg>
        </a>
    </div>
    <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($products as $product)
            <x-product-card :product="$product" />
        @endforeach
    </div>
    <div class="mt-8 text-center sm:hidden">
        <a href="{{ route('products.index') }}" class="btn-secondary">View all products</a>
    </div>
</section>
@endif

{{-- ======================= FEATURED MARKETS ======================= --}}
@if ($markets->count())
<section class="bg-cream-100 dark:bg-leaf-900/30 py-20 sm:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto">
            <span class="badge-green">Visit a market</span>
            <h2 class="mt-4 font-display text-3xl sm:text-4xl font-semibold text-leaf-900 dark:text-leaf-100">Markets near you</h2>
            <p class="mt-4 text-stone-600 dark:text-stone-300">Find opening days, hours and the farmers at each location.</p>
        </div>
        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($markets as $market)
                <a href="{{ route('markets.show', $market) }}" class="card card-hover overflow-hidden group block">
                    <div class="aspect-[16/9] bg-leaf-100 dark:bg-leaf-800 relative overflow-hidden">
                        @if ($market->image)
                            <img src="{{ asset('storage/' . $market->image) }}" alt="{{ $market->name }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @else
                            <div class="w-full h-full grid place-items-center text-leaf-400">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9l9-6 9 6v11a1 1 0 01-1 1H4a1 1 0 01-1-1V9z"/></svg>
                            </div>
                        @endif
                        @if ($market->isOpenToday())
                            <span class="absolute top-3 left-3 badge-green bg-leaf-600 !text-white">Open today</span>
                        @endif
                    </div>
                    <div class="p-5">
                        <h3 class="font-display text-lg font-semibold text-stone-800 dark:text-stone-100 group-hover:text-leaf-700 dark:group-hover:text-leaf-300 transition-colors">{{ $market->name }}</h3>
                        <p class="mt-1 text-sm text-stone-500 dark:text-stone-400 flex items-center gap-1.5">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.1-7.5 11.25-7.5 11.25S4.5 17.6 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                            {{ $market->city }}
                        </p>
                        <div class="mt-3 flex items-center justify-between text-xs">
                            <span class="text-stone-500 dark:text-stone-400">{{ $market->operatingDaysLabel() ?: 'Days vary' }}</span>
                            <span class="badge-stone">{{ $market->farmers_count }} farmers</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-10 text-center">
            <a href="{{ route('markets.index') }}" class="btn-primary">Explore all markets</a>
        </div>
    </div>
</section>
@endif

{{-- ======================= TOP FARMERS ======================= --}}
@if ($farmers->count())
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 sm:py-24">
    <div class="text-center max-w-2xl mx-auto">
        <span class="badge-green">Meet the growers</span>
        <h2 class="mt-4 font-display text-3xl sm:text-4xl font-semibold text-leaf-900 dark:text-leaf-100">Top-rated farmers</h2>
    </div>
    <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($farmers as $farmer)
            <a href="{{ route('farmers.show', $farmer) }}" class="card card-hover p-6 text-center group block">
                <img src="{{ $farmer->user?->avatarUrl() ?? asset('images/product-placeholder.svg') }}" alt="{{ $farmer->stall_name }}" class="w-20 h-20 rounded-full object-cover mx-auto ring-4 ring-leaf-100 dark:ring-leaf-800 group-hover:ring-leaf-300 transition-all">
                <h3 class="mt-4 font-display text-lg font-semibold text-stone-800 dark:text-stone-100 group-hover:text-leaf-700 dark:group-hover:text-leaf-300 transition-colors">{{ $farmer->stall_name }}</h3>
                <p class="text-xs text-stone-500 dark:text-stone-400 mt-0.5">{{ $farmer->operatingDaysLabel() }}</p>
                <div class="mt-3 flex items-center justify-center gap-1 text-sm">
                    <span class="inline-flex items-center gap-0.5 font-semibold text-amber-600 dark:text-amber-400">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 2l2.9 6.3 6.9.8-5.1 4.7 1.4 6.8L12 17.2 5.9 20.6l1.4-6.8L2.2 9.1l6.9-.8L12 2z"/></svg>
                        {{ number_format($farmer->averageRating(), 1) }}
                    </span>
                    <span class="text-stone-400">·</span>
                    <span class="text-stone-500 dark:text-stone-400 text-xs">{{ $farmer->totalReviews() }} reviews</span>
                </div>
                <p class="mt-2 badge-stone">{{ $farmer->products_count }} products</p>
            </a>
        @endforeach
    </div>
</section>
@endif

{{-- ======================= TESTIMONIAL / CTA ======================= --}}
<section class="hero-bg relative">
    <div class="hero-overlay absolute inset-0"></div>
    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-20 sm:py-28 text-center">
        <svg class="w-10 h-10 mx-auto text-leaf-300" fill="currentColor" viewBox="0 0 24 24"><path d="M10 7L8 11h3v6H5v-6l2-4h3zm9 0l-2 4h3v6h-6v-6l2-4h3z"/></svg>
        <blockquote class="mt-6 font-display text-2xl sm:text-3xl text-white font-medium leading-relaxed">
            "I used to drive to the market hoping my favorite eggs weren't sold out. Now I pre-order on MarketLink and they're waiting for me every Saturday."
        </blockquote>
        <p class="mt-6 text-leaf-100/80 text-sm font-semibold">— Maria T., happy customer</p>
        <div class="mt-10 flex flex-wrap justify-center gap-3">
            <a href="{{ route('register') }}" class="btn-primary !px-8 !py-3.5 !text-base">Start Shopping</a>
            <a href="{{ route('register') }}?role=farmer" class="btn !px-8 !py-3.5 !text-base bg-white/10 text-white border border-white/25 hover:bg-white/20 backdrop-blur">Sell at a Market</a>
        </div>
    </div>
</section>
@endsection
