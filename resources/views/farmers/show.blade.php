@extends(dashboard_layout())

@section('title', $farmer->stall_name)

@section('content')
    <section class="page-hero relative overflow-hidden">
        <div class="hero-overlay absolute inset-0"></div>
        <div class="relative mx-auto max-w-7xl px-4 py-14 sm:px-6 sm:py-18">
            <nav class="mb-4 flex items-center gap-1.5 text-sm text-cream-100/80">
                <a href="{{ route('farmers.index') }}" class="hover:text-white">Farmers</a>
                <span>/</span>
                <span class="text-white">{{ $farmer->stall_name }}</span>
            </nav>
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="flex items-center gap-4">
                    <img src="{{ $farmer->user->avatarUrl() }}" alt="" class="h-20 w-20 rounded-3xl object-cover ring-2 ring-white/30">
                    <div>
                        <h1 class="font-display text-3xl font-semibold text-white sm:text-4xl">{{ $farmer->stall_name }}</h1>
                        <p class="mt-1 flex items-center gap-1.5 text-cream-100/90">
                            <svg class="h-4 w-4 text-amber-400" viewBox="0 0 20 20" fill="currentColor"><path d="M10 1.5l2.6 5.3 5.9.9-4.2 4.1 1 5.8L10 14.9l-5.3 2.7 1-5.8L1.5 7.7l5.9-.9L10 1.5z"/></svg>
                            <span class="font-semibold">{{ number_format($farmer->averageRating(), 1) }}</span>
                            · {{ $farmer->totalReviews() }} {{ Str::plural('review', $farmer->totalReviews()) }}
                        </p>
                    </div>
                </div>
                @auth
                    <button type="button" onclick="toggleFavorite(event, 'farmer', {{ $farmer->id }}, this)"
                        data-fav-type="farmer" data-fav-id="{{ $farmer->id }}" data-favorited="{{ $isFavorited ? '1' : '' }}"
                        class="flex items-center gap-2 rounded-full bg-white/10 px-4 py-2.5 text-sm font-semibold text-white ring-1 ring-white/30 backdrop-blur transition hover:bg-white/20">
                        <svg class="h-4.5 w-4.5 {{ $isFavorited ? 'fill-red-400 text-red-400' : 'fill-none' }}" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                        {{ $isFavorited ? 'Saved' : 'Save farm' }}
                    </button>
                @endauth
            </div>
            <div class="mt-4 flex flex-wrap gap-x-6 gap-y-2 text-sm text-cream-100/90">
                <span>{{ $farmer->markets->pluck('name')->join(' · ') ?: 'Independent grower' }}</span>
                @if($farmer->operatingDaysLabel())
                    <span class="flex items-center gap-1.5">
                        <svg class="h-4 w-4 text-leaf-300" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ $farmer->operatingDaysLabel() }}
                    </span>
                @endif
                @if($farmer->phone)
                    <span class="flex items-center gap-1.5">
                        <svg class="h-4 w-4 text-leaf-300" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                        {{ $farmer->phone }}
                    </span>
                @endif
            </div>
            @if($farmer->description)
                <p class="mt-4 max-w-3xl text-cream-100/90">{{ $farmer->description }}</p>
            @endif
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6">
        @if($productGroups->count())
            @foreach($productGroups as $groupName => $products)
                <div class="mb-10">
                    <h2 class="font-display text-2xl font-semibold text-leaf-950 dark:text-cream-50">{{ $groupName }}</h2>
                    <div class="mt-5 grid grid-cols-2 gap-4 sm:grid-cols-3 sm:gap-5 lg:grid-cols-4">
                        @foreach($products as $product)
                            <x-product-card :product="$product" />
                        @endforeach
                    </div>
                </div>
            @endforeach
        @else
            <div class="card px-6 py-14 text-center">
                <p class="text-stone-500 dark:text-stone-400">No products listed right now — check back closer to market day.</p>
            </div>
        @endif

        {{-- Location --}}
        <div class="mt-10">
            <h2 class="font-display text-2xl font-semibold text-leaf-950 dark:text-cream-50">Location</h2>
            @if($farmer->latitude && $farmer->longitude)
                <div class="card mt-5 overflow-hidden !p-0">
                    <x-map id="farmer-map" height="h-80"
                        :center="[(float) $farmer->latitude, (float) $farmer->longitude]" :zoom="14"
                        :markers="[['id' => $farmer->id, 'name' => $farmer->stall_name, 'days' => $farmer->operatingDaysLabel(), 'lat' => (float) $farmer->latitude, 'lng' => (float) $farmer->longitude]]" />
                </div>
                <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                    <p class="text-sm text-stone-500 dark:text-stone-400">
                        {{ $farmer->address ?: 'Stall position pinned by ' . $farmer->stall_name . '.' }}
                    </p>
                    <x-directions-link :lat="$farmer->latitude" :lng="$farmer->longitude" />
                </div>
            @else
                <div class="card mt-5 px-6 py-10 text-center">
                    <p class="text-sm text-stone-500 dark:text-stone-400">{{ $farmer->stall_name }} hasn't pinned their location yet — you'll find them at their listed markets.</p>
                </div>
            @endif
        </div>

        <div class="mt-6">
            <h2 class="font-display text-2xl font-semibold text-leaf-950 dark:text-cream-50">Reviews</h2>
            @if($reviews->count())
                <div class="mt-5 grid gap-4 lg:grid-cols-2">
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
            @else
                <div class="card mt-5 px-6 py-10 text-center">
                    <p class="text-sm text-stone-500 dark:text-stone-400">No reviews yet.</p>
                </div>
            @endif
        </div>
    </section>
@endsection
