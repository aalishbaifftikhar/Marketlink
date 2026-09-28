@extends(dashboard_layout())

@section('title', 'Farmers')

@section('content')
    <section class="page-hero relative overflow-hidden">
        <div class="hero-overlay absolute inset-0"></div>
        <div class="relative mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-20">
            <p class="text-sm font-semibold uppercase tracking-widest text-leaf-200">Meet the growers</p>
            <h1 class="mt-2 font-display text-4xl font-semibold text-white sm:text-5xl">Our farmers</h1>
            <p class="mt-3 max-w-xl text-lg text-cream-100/90">Verified local producers selling at community markets near you.</p>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6">
        <form method="GET" action="{{ route('farmers.index') }}" class="card mb-8 flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:p-5">
            <div class="relative flex-1">
                <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4.5 w-4.5 -translate-y-1/2 text-stone-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/></svg>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search farms and stalls…" class="input w-full !pl-10">
            </div>
            <select name="market" class="input sm:w-52">
                <option value="">All markets</option>
                @foreach($markets as $market)
                    <option value="{{ $market->id }}" @selected(request('market') == $market->id)>{{ $market->name }}</option>
                @endforeach
            </select>
            <select name="day" class="input sm:w-44">
                <option value="">Any day</option>
                @foreach(['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $day)
                    <option value="{{ $day }}" @selected(request('day') === $day)>{{ ucfirst($day) }}s</option>
                @endforeach
            </select>
            <button type="submit" class="btn-primary shrink-0">Search</button>
        </form>

        @if($farmers->count())
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($farmers as $farmer)
                    <a href="{{ route('farmers.show', $farmer) }}" class="card card-hover flex flex-col p-5">
                        <div class="flex items-center gap-4">
                            <img src="{{ $farmer->user->avatarUrl() }}" alt="" class="h-14 w-14 rounded-2xl object-cover">
                            <div class="min-w-0">
                                <h2 class="truncate font-display text-lg font-semibold text-leaf-950 dark:text-cream-50">{{ $farmer->stall_name }}</h2>
                                <p class="truncate text-sm text-stone-500 dark:text-stone-400">{{ $farmer->markets->pluck('name')->join(' · ') ?: 'Independent grower' }}</p>
                            </div>
                        </div>
                        <p class="mt-3 line-clamp-2 flex-1 text-sm text-stone-600 dark:text-stone-300">{{ $farmer->description }}</p>
                        <div class="mt-4 flex items-center justify-between border-t border-stone-100 pt-3 text-sm dark:border-leaf-800">
                            <span class="flex items-center gap-1 text-amber-500">
                                <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20"><path d="M10 1.5l2.6 5.3 5.9.9-4.2 4.1 1 5.8L10 14.9l-5.3 2.7 1-5.8L1.5 7.7l5.9-.9L10 1.5z"/></svg>
                                <span class="font-semibold">{{ number_format($farmer->averageRating(), 1) }}</span>
                                <span class="text-stone-400">({{ $farmer->totalReviews() }})</span>
                            </span>
                            <span class="text-stone-500 dark:text-stone-400">{{ $farmer->products_count }} {{ Str::plural('product', $farmer->products_count) }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
            <div class="mt-10">{{ $farmers->links() }}</div>

            @if($mapFarmers->count())
                <div class="card mt-10 overflow-hidden !p-0">
                    <x-map id="farmers-map" height="h-96" :markers="$mapFarmers" :center="[-1.2921, 36.8219]" :zoom="11" />
                </div>
            @endif
        @else
            <div class="card flex flex-col items-center px-6 py-16 text-center">
                <svg class="h-12 w-12 text-stone-300 dark:text-stone-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v3m0 0a5.25 5.25 0 015.25 5.25c0 2.485-2.12 4.5-5.25 4.5m0-9.5A5.25 5.25 0 006.75 8.25C6.75 10.735 8.87 12.75 12 12.75M12 12.75V21m-3.75-6h7.5"/></svg>
                <h2 class="mt-4 font-display text-xl font-semibold text-leaf-950 dark:text-cream-50">No farmers found</h2>
                <p class="mt-1.5 text-sm text-stone-500 dark:text-stone-400">Try another search term, market or day.</p>
            </div>
        @endif
    </section>
@endsection
