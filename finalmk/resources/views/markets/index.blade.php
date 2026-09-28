@extends(dashboard_layout())

@section('title', 'Markets')

@section('content')
    <section class="page-hero relative overflow-hidden">
        <div class="hero-overlay absolute inset-0"></div>
        <div class="relative mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-20">
            <p class="text-sm font-semibold uppercase tracking-widest text-leaf-200">Where it all happens</p>
            <h1 class="mt-2 font-display text-4xl font-semibold text-white sm:text-5xl">Farmers markets</h1>
            <p class="mt-3 max-w-xl text-lg text-cream-100/90">Find your nearest market, see who'll be there, and plan your pickup.</p>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6">
        <form method="GET" action="{{ route('markets.index') }}" class="card mb-8 flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:p-5">
            <div class="relative flex-1">
                <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4.5 w-4.5 -translate-y-1/2 text-stone-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/></svg>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by name, city or area…" class="input w-full !pl-10">
            </div>
            <select name="day" class="input sm:w-48">
                <option value="">Any market day</option>
                @foreach(['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $day)
                    <option value="{{ $day }}" @selected(request('day') === $day)>{{ ucfirst($day) }}s</option>
                @endforeach
            </select>
            <button type="submit" class="btn-primary shrink-0">Search</button>
            <a href="{{ route('markets.nearby') }}" class="btn-secondary shrink-0">
                <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                Nearby markets
            </a>
        </form>

        @if($markets->count())
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($markets as $market)
                    <a href="{{ route('markets.show', $market) }}" class="card card-hover group overflow-hidden !p-0">
                        <div class="relative h-44 overflow-hidden bg-cream-100 dark:bg-leaf-900">
                            @if($market->image)
                                <img src="{{ asset('storage/' . $market->image) }}" alt="{{ $market->name }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                            @else
                                <div class="page-hero h-full w-full"></div>
                            @endif
                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                            @if($market->isOpenToday())
                                <span class="absolute left-3 top-3 badge-green">Open today</span>
                            @endif
                            <h2 class="absolute bottom-3 left-4 right-4 font-display text-xl font-semibold text-white">{{ $market->name }}</h2>
                        </div>
                        <div class="p-4">
                            <p class="flex items-start gap-1.5 text-sm text-stone-500 dark:text-stone-400">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-leaf-600 dark:text-leaf-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                                {{ $market->address }}{{ $market->city ? ', ' . $market->city : '' }}
                            </p>
                            <p class="mt-2 flex items-center gap-1.5 text-sm text-stone-500 dark:text-stone-400">
                                <svg class="h-4 w-4 text-leaf-600 dark:text-leaf-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ $market->operatingDaysLabel() }}
                                @if($market->open_time)
                                    · {{ substr($market->open_time, 0, 5) }}–{{ substr($market->close_time, 0, 5) }}
                                @endif
                            </p>
                            <p class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-leaf-50 px-2.5 py-1 text-xs font-semibold text-leaf-700 dark:bg-leaf-900/60 dark:text-leaf-300">
                                {{ $market->farmers_count }} {{ Str::plural('farmer', $market->farmers_count) }} selling
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>
            <div class="mt-10">{{ $markets->links() }}</div>

            <div class="card mt-10 overflow-hidden !p-0">
                <x-map id="markets-map" height="h-96" :markers="$mapMarkets" :center="[-1.2921, 36.8219]" :zoom="11" />
            </div>
        @else
            <div class="card flex flex-col items-center px-6 py-16 text-center">
                <svg class="h-12 w-12 text-stone-300 dark:text-stone-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0zM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                <h2 class="mt-4 font-display text-xl font-semibold text-leaf-950 dark:text-cream-50">No markets found</h2>
                <p class="mt-1.5 text-sm text-stone-500 dark:text-stone-400">Try another search term or day.</p>
            </div>
        @endif
    </section>
@endsection
