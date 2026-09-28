@extends(dashboard_layout())

@section('title', 'Nearby Markets')

@section('content')
    <section class="page-hero relative overflow-hidden">
        <div class="hero-overlay absolute inset-0"></div>
        <div class="relative mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-20">
            <p class="text-sm font-semibold uppercase tracking-widest text-leaf-200">Right around the corner</p>
            <h1 class="mt-2 font-display text-4xl font-semibold text-white sm:text-5xl">Nearby markets</h1>
            <p class="mt-3 max-w-xl text-lg text-cream-100/90">Share your location and we'll line up the closest markets first.</p>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6" x-data="nearbyLocator()">
        <div class="card mb-8 flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:p-5">
            <div class="flex flex-1 items-center gap-3">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-leaf-50 text-leaf-700 dark:bg-leaf-900/60 dark:text-leaf-300">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                </span>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-leaf-950 dark:text-cream-50">
                        @if($hasLocation)
                            Showing markets within {{ $radius }} km of you
                        @else
                            Find markets closest to you
                        @endif
                    </p>
                    <p class="truncate text-xs text-stone-500 dark:text-stone-400">
                        @if($hasLocation)
                            Location: {{ number_format($lat, 4) }}, {{ number_format($lng, 4) }}
                        @else
                            Your location is never stored — it's only used to sort this page.
                        @endif
                    </p>
                </div>
            </div>

            <form method="GET" action="{{ route('markets.nearby') }}" class="flex items-center gap-2" @submit="keepLocation($event)">
                @if($hasLocation)
                    <input type="hidden" name="lat" value="{{ $lat }}">
                    <input type="hidden" name="lng" value="{{ $lng }}">
                @endif
                <select name="radius" class="input sm:w-44" aria-label="Search radius">
                    @foreach([5, 10, 25, 50] as $r)
                        <option value="{{ $r }}" @selected($radius === $r)>Within {{ $r }} km</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-secondary shrink-0">Apply</button>
            </form>

            <button type="button" @click="locate()" :disabled="loading"
                    class="btn-primary shrink-0" data-nearby-locate>
                <svg class="h-4.5 w-4.5" :class="loading && 'animate-spin'" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m0-3.75V21m-9-9h2.25m14.25 0A9 9 0 1112 3a9 9 0 019 9z"/></svg>
                <span x-text="loading ? 'Locating…' : (@json($hasLocation) ? 'Refresh my location' : 'Use my location')"></span>
            </button>
        </div>

        {{-- Loading state --}}
        <div x-cloak x-show="loading" class="card flex flex-col items-center px-6 py-16 text-center">
            <svg class="h-10 w-10 animate-spin text-leaf-600 dark:text-leaf-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
            <h2 class="mt-4 font-display text-xl font-semibold text-leaf-950 dark:text-cream-50">Waiting for your location…</h2>
            <p class="mt-1.5 text-sm text-stone-500 dark:text-stone-400">Your browser may ask for permission — choose “Allow” to continue.</p>
        </div>

        {{-- Geolocation error state --}}
        <div x-cloak x-show="error" class="card flex flex-col items-center px-6 py-16 text-center">
            <svg class="h-12 w-12 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
            <h2 class="mt-4 font-display text-xl font-semibold text-leaf-950 dark:text-cream-50" x-text="errorTitle"></h2>
            <p class="mt-1.5 max-w-md text-sm text-stone-500 dark:text-stone-400" x-text="error"></p>
        </div>

        @unless($hasLocation)
            <div x-show="!loading && !error" class="card flex flex-col items-center px-6 py-16 text-center">
                <svg class="h-12 w-12 text-leaf-600 dark:text-leaf-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                <h2 class="mt-4 font-display text-xl font-semibold text-leaf-950 dark:text-cream-50">Ready when you are</h2>
                <p class="mt-1.5 max-w-md text-sm text-stone-500 dark:text-stone-400">Tap “Use my location” above. We only use your position to sort the markets — nothing is saved.</p>
            </div>
        @endunless

        @if($hasLocation)
            @if($markets->count())
                <p class="mb-4 text-sm text-stone-500 dark:text-stone-400">
                    {{ $markets->count() }} {{ Str::plural('market', $markets->count()) }} found, nearest first.
                    @if($noCoordsCount)
                        {{ $noCoordsCount }} other {{ Str::plural('market', $noCoordsCount) }} couldn't be checked — no map coordinates set yet.
                    @endif
                </p>
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
                                <span class="absolute left-3 top-3 badge-green">{{ number_format($market->distance_km, 1) }} km away</span>
                                @if($market->isOpenToday())
                                    <span class="absolute right-3 top-3 badge bg-white/90 !text-leaf-800 shadow-soft">Open today</span>
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
            @else
                <div class="card flex flex-col items-center px-6 py-16 text-center">
                    <svg class="h-12 w-12 text-stone-300 dark:text-stone-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 100-18 9 9 0 000 18zm0 0v3m0-3c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3 7.5 7.03 7.5 12s2.015 9 4.5 9z"/></svg>
                    <h2 class="mt-4 font-display text-xl font-semibold text-leaf-950 dark:text-cream-50">No markets within {{ $radius }} km</h2>
                    <p class="mt-1.5 max-w-md text-sm text-stone-500 dark:text-stone-400">Try widening the radius above, or browse <a href="{{ route('markets.index') }}" class="font-semibold text-leaf-700 hover:underline dark:text-leaf-300">all markets</a>.</p>
                </div>
            @endif
        @endif
    </section>
@endsection

@push('scripts')
<script>
function nearbyLocator() {
    return {
        loading: false,
        error: '',
        errorTitle: '',
        locate() {
            this.error = '';
            this.loading = true;
            if (!navigator.geolocation) {
                this.fail('Location is not supported by this browser.', 'Location unavailable');
                return;
            }
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    const url = new URL(window.location.href);
                    url.searchParams.set('lat', pos.coords.latitude.toFixed(7));
                    url.searchParams.set('lng', pos.coords.longitude.toFixed(7));
                    window.location.href = url.toString();
                },
                (err) => {
                    if (err.code === err.PERMISSION_DENIED) {
                        this.fail('Location permission was denied. Enable it in your browser settings (usually the lock icon in the address bar), then try again — or browse all markets instead.', 'Permission denied');
                    } else if (err.code === err.POSITION_UNAVAILABLE) {
                        this.fail("We couldn't detect your location right now. Check that location services are on and try again.", 'Location unavailable');
                    } else {
                        this.fail('Getting your location took too long. Please try again.', 'Something went wrong');
                    }
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 }
            );
        },
        keepLocation(event) {
            if (this.loading) event.preventDefault();
        },
        fail(message, title) {
            this.loading = false;
            this.error = message;
            this.errorTitle = title;
        },
    };
}
</script>
@endpush
