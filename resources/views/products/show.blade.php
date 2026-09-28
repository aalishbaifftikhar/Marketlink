@extends(dashboard_layout())

@section('title', $product->name)

@section('content')
    @php
        $farmer = $product->farmer;
        $avg = $product->averageRating();
        $totalReviews = $product->totalReviews();
    @endphp

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
        <nav class="mb-6 flex items-center gap-1.5 text-sm text-stone-500 dark:text-stone-400">
            <a href="{{ route('home') }}" class="hover:text-leaf-700 dark:hover:text-leaf-300">Home</a>
            <span>/</span>
            <a href="{{ route('products.index') }}" class="hover:text-leaf-700 dark:hover:text-leaf-300">Products</a>
            <span>/</span>
            <span class="font-medium text-leaf-900 dark:text-cream-100">{{ $product->name }}</span>
        </nav>

        <div class="grid gap-8 lg:grid-cols-2 lg:gap-12">
            @php $gallery = $product->galleryImages(); @endphp
            @if(count($gallery))
                <x-gallery-slider :images="$gallery" :alt="$product->name" class="shrink-0">
                    @if(!$product->inStock())
                        <span class="absolute left-4 top-4 badge-red">Sold out</span>
                    @elseif($product->created_at->greaterThan(now()->subDays(7)))
                        <span class="absolute left-4 top-4 badge-green">New this week</span>
                    @endif
                </x-gallery-slider>
            @else
                <div class="relative overflow-hidden rounded-3xl bg-cream-100 ring-1 ring-stone-200/60 dark:bg-leaf-900 dark:ring-leaf-800">
                    <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" class="aspect-square w-full object-cover">
                    @if(!$product->inStock())
                        <span class="absolute left-4 top-4 badge-red">Sold out</span>
                    @elseif($product->created_at->greaterThan(now()->subDays(7)))
                        <span class="absolute left-4 top-4 badge-green">New this week</span>
                    @endif
                </div>
            @endif

            <div>
                @if($product->category)
                    <a href="{{ route('products.index', ['category' => $product->category_id]) }}" class="badge-stone">{{ $product->category->name }}</a>
                @endif
                <h1 class="mt-3 font-display text-3xl font-semibold text-leaf-950 dark:text-cream-50 sm:text-4xl">{{ $product->name }}</h1>

                <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm">
                    <span class="flex items-center gap-1 font-semibold text-leaf-800 dark:text-leaf-200">
                        <svg class="h-4 w-4 text-amber-500" viewBox="0 0 20 20" fill="currentColor"><path d="M10 1.5l2.6 5.3 5.9.9-4.2 4.1 1 5.8L10 14.9l-5.3 2.7 1-5.8L1.5 7.7l5.9-.9L10 1.5z"/></svg>
                        {{ number_format($avg, 1) }}
                        <span class="font-normal text-stone-500 dark:text-stone-400">({{ $totalReviews }} {{ Str::plural('review', $totalReviews) }})</span>
                    </span>
                    <span class="text-stone-300 dark:text-stone-600">|</span>
                    <span class="text-stone-500 dark:text-stone-400">{{ $product->stock_quantity }} {{ Str::plural($product->unit, $product->stock_quantity) }} available</span>
                </div>

                <p class="mt-5 font-display text-3xl font-semibold text-leaf-800 dark:text-leaf-200">
                    {{ number_format($product->price, 2) }} <span class="text-lg font-normal text-stone-500 dark:text-stone-400">/ {{ $product->unit }}</span>
                </p>

                @if($product->description)
                    <p class="mt-5 leading-relaxed text-stone-600 dark:text-stone-300">{{ $product->description }}</p>
                @endif

                <div class="mt-8 flex flex-wrap items-center gap-3">
                    @if($product->inStock())
                        <form method="POST" action="{{ route('customer.cart.add') }}" class="flex items-center gap-3">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <div class="flex items-center rounded-full border border-stone-200 dark:border-leaf-700">
                                <button type="button" onclick="this.nextElementSibling.stepDown()" class="px-3 py-2.5 text-stone-500 hover:text-leaf-700 dark:hover:text-leaf-300" aria-label="Decrease quantity">&minus;</button>
                                <input type="number" name="quantity" value="1" min="1" max="{{ $product->stock_quantity }}" class="w-14 border-0 bg-transparent text-center text-sm font-semibold focus:ring-0">
                                <button type="button" onclick="this.previousElementSibling.stepUp()" class="px-3 py-2.5 text-stone-500 hover:text-leaf-700 dark:hover:text-leaf-300" aria-label="Increase quantity">+</button>
                            </div>
                            <button type="submit" class="btn-primary">
                                <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/></svg>
                                Add to basket
                            </button>
                        </form>
                    @else
                        <span class="badge-red !px-4 !py-2 !text-sm">Sold out — check back next market day</span>
                    @endif
                    @auth
                        <button type="button" onclick="toggleFavorite(event, 'product', {{ $product->id }}, this)"
                            data-fav-type="product" data-fav-id="{{ $product->id }}" data-favorited="{{ $isFavorited ? '1' : '' }}"
                            class="rounded-full border border-stone-200 p-3 text-stone-400 transition hover:border-red-300 hover:text-red-500 dark:border-leaf-700 dark:hover:border-red-400"
                            aria-label="Save to favorites">
                            <svg class="h-5 w-5 {{ $isFavorited ? 'fill-red-500 text-red-500' : 'fill-none' }}" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                        </button>
                    @else
                        <a href="{{ route('login') }}" class="rounded-full border border-stone-200 p-3 text-stone-400 transition hover:border-red-300 hover:text-red-500 dark:border-leaf-700" aria-label="Sign in to save">
                            <svg class="h-5 w-5 fill-none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                        </a>
                    @endauth
                </div>

                <a href="{{ route('farmers.show', $farmer) }}" class="card card-hover mt-8 flex items-center gap-4 p-4">
                    <img src="{{ $farmer->user->avatarUrl() }}" alt="" class="h-14 w-14 rounded-2xl object-cover">
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-leaf-950 dark:text-cream-50">{{ $farmer->stall_name }}</p>
                        <p class="truncate text-sm text-stone-500 dark:text-stone-400">
                            {{ $farmer->markets->pluck('name')->join(' · ') ?: 'Independent grower' }}
                        </p>
                    </div>
                    <span class="flex items-center gap-1 text-sm font-semibold text-leaf-800 dark:text-leaf-200">
                        <svg class="h-4 w-4 text-amber-500" viewBox="0 0 20 20" fill="currentColor"><path d="M10 1.5l2.6 5.3 5.9.9-4.2 4.1 1 5.8L10 14.9l-5.3 2.7 1-5.8L1.5 7.7l5.9-.9L10 1.5z"/></svg>
                        {{ number_format($farmer->averageRating(), 1) }}
                    </span>
                </a>
            </div>
        </div>

        <section class="mt-16">
            <h2 class="font-display text-2xl font-semibold text-leaf-950 dark:text-cream-50">Reviews</h2>
            @if($reviews->count())
                <div class="mt-5 space-y-4">
                    @foreach($reviews as $review)
                        <article class="card p-5">
                            <div class="flex items-center gap-3">
                                <img src="{{ $review->user->avatarUrl() }}" alt="" class="h-10 w-10 rounded-full object-cover">
                                <div>
                                    <p class="text-sm font-semibold text-leaf-950 dark:text-cream-50">{{ $review->user->name }}</p>
                                    <div class="flex items-center gap-0.5 text-amber-500">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg class="h-3.5 w-3.5 {{ $i <= $review->rating ? 'fill-current' : 'fill-stone-200 dark:fill-stone-700' }}" viewBox="0 0 20 20"><path d="M10 1.5l2.6 5.3 5.9.9-4.2 4.1 1 5.8L10 14.9l-5.3 2.7 1-5.8L1.5 7.7l5.9-.9L10 1.5z"/></svg>
                                        @endfor
                                    </div>
                                </div>
                                <span class="ml-auto text-xs text-stone-400 dark:text-stone-500">{{ $review->created_at->diffForHumans() }}</span>
                            </div>
                            @if($review->comment)
                                <p class="mt-3 text-sm leading-relaxed text-stone-600 dark:text-stone-300">{{ $review->comment }}</p>
                            @endif
                            @if($review->farmer_response)
                                <div class="mt-3 rounded-2xl bg-leaf-50 p-4 dark:bg-leaf-900/40">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-leaf-700 dark:text-leaf-300">Response from {{ $farmer->stall_name }}</p>
                                    <p class="mt-1.5 text-sm leading-relaxed text-stone-600 dark:text-stone-300">{{ $review->farmer_response }}</p>
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
                <div class="mt-6">{{ $reviews->links() }}</div>
            @else
                <div class="card mt-5 px-6 py-10 text-center">
                    <p class="text-sm text-stone-500 dark:text-stone-400">No reviews yet. Reviews appear after customers complete a pickup.</p>
                </div>
            @endif
        </section>

        @if($related->count())
            <section class="mt-16">
                <div class="flex items-end justify-between">
                    <h2 class="font-display text-2xl font-semibold text-leaf-950 dark:text-cream-50">You might also like</h2>
                    <a href="{{ route('products.index', ['category' => $product->category_id]) }}" class="text-sm font-semibold text-leaf-700 hover:text-leaf-800 dark:text-leaf-300">View all</a>
                </div>
                <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4 sm:gap-5">
                    @foreach($related as $item)
                        <x-product-card :product="$item" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
