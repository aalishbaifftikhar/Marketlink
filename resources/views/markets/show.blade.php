@extends(dashboard_layout())

@section('title', $market->name)

@section('content')
    <section class="page-hero relative overflow-hidden">
        <div class="hero-overlay absolute inset-0"></div>
        <div class="relative mx-auto max-w-7xl px-4 py-14 sm:px-6 sm:py-18">
            <nav class="mb-3 flex items-center gap-1.5 text-sm text-cream-100/80">
                <a href="{{ route('markets.index') }}" class="hover:text-white">Markets</a>
                <span>/</span>
                <span class="text-white">{{ $market->name }}</span>
            </nav>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="font-display text-4xl font-semibold text-white sm:text-5xl">{{ $market->name }}</h1>
                @if($isOpenToday)
                    <span class="badge-green !text-sm">Open today</span>
                @endif
            </div>
            <p class="mt-3 max-w-2xl text-lg text-cream-100/90">{{ $market->description }}</p>
            <div class="mt-4 flex flex-wrap gap-x-6 gap-y-2 text-sm text-cream-100/90">
                <span class="flex items-center gap-1.5">
                    <svg class="h-4 w-4 text-leaf-300" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0zM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                    {{ $market->address }}{{ $market->city ? ', ' . $market->city : '' }}
                </span>
                <span class="flex items-center gap-1.5">
                    <svg class="h-4 w-4 text-leaf-300" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ $market->operatingDaysLabel() }}
                    @if($market->open_time) · {{ substr($market->open_time, 0, 5) }}–{{ substr($market->close_time, 0, 5) }} @endif
                </span>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6">
        <div class="grid gap-8 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <h2 class="font-display text-2xl font-semibold text-leaf-950 dark:text-cream-50">
                    Farmers at this market <span class="text-base font-normal text-stone-400">({{ $farmers->count() }})</span>
                </h2>
                @if($farmers->count())
                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        @foreach($farmers as $farmer)
                            <a href="{{ route('farmers.show', $farmer) }}" class="card card-hover flex items-center gap-4 p-4">
                                <img src="{{ $farmer->user->avatarUrl() }}" alt="" class="h-14 w-14 rounded-2xl object-cover">
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-leaf-950 dark:text-cream-50">{{ $farmer->stall_name }}</p>
                                    <p class="text-xs text-stone-500 dark:text-stone-400">{{ $farmer->products_count }} {{ Str::plural('product', $farmer->products_count) }} listed</p>
                                    <p class="mt-1 flex items-center gap-0.5 text-xs text-amber-500">
                                        <svg class="h-3.5 w-3.5 fill-current" viewBox="0 0 20 20"><path d="M10 1.5l2.6 5.3 5.9.9-4.2 4.1 1 5.8L10 14.9l-5.3 2.7 1-5.8L1.5 7.7l5.9-.9L10 1.5z"/></svg>
                                        <span class="font-semibold">{{ number_format($farmer->averageRating(), 1) }}</span>
                                        <span class="text-stone-400">({{ $farmer->totalReviews() }})</span>
                                    </p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="card mt-5 px-6 py-10 text-center">
                        <p class="text-sm text-stone-500 dark:text-stone-400">No farmers are selling at this market yet.</p>
                    </div>
                @endif
            </div>

            <div>
                @php $gallery = $market->galleryImages(); @endphp
                @if(count($gallery))
                    <h2 class="font-display text-2xl font-semibold text-leaf-950 dark:text-cream-50">Photos</h2>
                    <x-gallery-slider :images="$gallery" :alt="$market->name" aspect="aspect-[4/3]" class="mt-5" />
                @endif

                <h2 class="font-display text-2xl font-semibold text-leaf-950 dark:text-cream-50 {{ count($gallery) ? 'mt-8' : '' }}">Location</h2>
                @if($market->latitude && $market->longitude)
                    <div class="card mt-5 overflow-hidden !p-0">
                        <x-map id="market-map" height="h-80"
                            :center="[(float) $market->latitude, (float) $market->longitude]" :zoom="15"
                            :markers="[['id' => $market->id, 'name' => $market->name, 'days' => $market->operatingDaysLabel(), 'lat' => (float) $market->latitude, 'lng' => (float) $market->longitude]]" />
                    </div>
                    <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                        <p class="text-sm text-stone-500 dark:text-stone-400">{{ $market->address }}</p>
                        <x-directions-link :lat="$market->latitude" :lng="$market->longitude" />
                    </div>
                @else
                    <div class="card mt-5 px-6 py-10 text-center">
                        <p class="text-sm text-stone-500 dark:text-stone-400">This market's exact location hasn't been pinned yet — check the address in the header or contact the organisers.</p>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
