@extends(dashboard_layout())

@section('title', 'Fresh produce')

@section('content')
    <section class="page-hero relative overflow-hidden">
        <div class="hero-overlay absolute inset-0"></div>
        <div class="relative mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-20">
            <p class="text-sm font-semibold uppercase tracking-widest text-leaf-200">Marketplace</p>
            <h1 class="mt-2 font-display text-4xl font-semibold text-white sm:text-5xl">Fresh produce</h1>
            <p class="mt-3 max-w-xl text-lg text-cream-100/90">Search what's in season this week and pre-order directly from local growers.</p>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6">
        <form method="GET" action="{{ route('products.index') }}" class="card mb-8 space-y-4 p-4 sm:p-5">
            <div class="flex flex-col gap-3 sm:flex-row">
                <div class="relative flex-1">
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4.5 w-4.5 -translate-y-1/2 text-stone-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/></svg>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search tomatoes, honey, eggs…"
                        class="input w-full !pl-10">
                </div>
                <select name="category" class="input sm:w-52">
                    <option value="">All categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <select name="market" class="input sm:w-52">
                    <option value="">All markets</option>
                    @foreach($markets as $market)
                        <option value="{{ $market->id }}" @selected(request('market') == $market->id)>{{ $market->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary shrink-0">Filter</button>
            </div>
            <div class="flex flex-wrap items-center gap-3 border-t border-stone-100 pt-4 dark:border-leaf-800">
                <div class="flex items-center gap-2 text-sm">
                    <span class="text-stone-500 dark:text-stone-400">Price</span>
                    <input type="number" name="min_price" value="{{ request('min_price') }}" min="0" step="0.01" placeholder="Min" class="input w-24 !py-1.5">
                    <span class="text-stone-400">–</span>
                    <input type="number" name="max_price" value="{{ request('max_price') }}" min="0" step="0.01" placeholder="Max" class="input w-24 !py-1.5">
                </div>
                <select name="day" class="input !w-auto !py-1.5 text-sm">
                    <option value="">Any market day</option>
                    @foreach(['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $day)
                        <option value="{{ $day }}" @selected(request('day') === $day)>{{ ucfirst($day) }}s</option>
                    @endforeach
                </select>
                <select name="sort" class="input !w-auto !py-1.5 text-sm">
                    <option value="newest" @selected(request('sort', 'newest') === 'newest')>Newest first</option>
                    <option value="price_asc" @selected(request('sort') === 'price_asc')>Price: low to high</option>
                    <option value="price_desc" @selected(request('sort') === 'price_desc')>Price: high to low</option>
                    <option value="oldest" @selected(request('sort') === 'oldest')>Oldest first</option>
                </select>
                @if(request()->hasAny(['q','category','market','min_price','max_price','day','sort']))
                    <a href="{{ route('products.index') }}" class="text-sm font-semibold text-leaf-700 hover:text-leaf-800 dark:text-leaf-300">Clear filters</a>
                @endif
            </div>
        </form>

        @if($products->count())
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 sm:gap-5 lg:grid-cols-4">
                @foreach($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
            <div class="mt-10">{{ $products->links() }}</div>
        @else
            <div class="card flex flex-col items-center px-6 py-16 text-center">
                <svg class="h-12 w-12 text-stone-300 dark:text-stone-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/></svg>
                <h2 class="mt-4 font-display text-xl font-semibold text-leaf-950 dark:text-cream-50">No produce found</h2>
                <p class="mt-1.5 max-w-sm text-sm text-stone-500 dark:text-stone-400">Try a different search term, or clear the filters to see everything that's fresh this week.</p>
                <a href="{{ route('products.index') }}" class="btn-secondary mt-6">Clear filters</a>
            </div>
        @endif
    </section>
@endsection
