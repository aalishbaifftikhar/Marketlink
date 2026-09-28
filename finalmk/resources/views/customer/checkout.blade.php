@extends('layouts.customer')

@section('title', 'Checkout')

@section('content')
    @php
        $canPlace = collect($groups)->every(fn ($g) => count($g['schedule']) > 0);
    @endphp

    @unless ($canPlace)
        <div class="mb-6 badge-red px-4 py-3 rounded-xl justify-start">
            Some farmers in your basket have no pickup slots in the next 14 days. Remove their items or check back once they publish a schedule.
        </div>
    @endunless

    <form method="POST" action="{{ route('customer.checkout.store') }}" class="grid lg:grid-cols-3 gap-6 items-start">
        @csrf

        {{-- ======================= PICKUP DETAILS PER FARMER ======================= --}}
        <div class="lg:col-span-2 space-y-6">
            @foreach ($groups as $farmerId => $group)
                @php
                    $farmer = $group['farmer'];
                    $markets = $farmer->markets;
                    $schedule = $group['schedule'];
                    $marketMarkers = $markets->filter(fn ($m) => $m->latitude && $m->longitude)->map(fn ($m) => [
                        'id' => $m->id,
                        'name' => $m->name,
                        'sub' => $m->address,
                        'lat' => (float) $m->latitude,
                        'lng' => (float) $m->longitude,
                    ])->values();
                @endphp
                <section class="card overflow-hidden" x-data="checkoutGroup(@js($schedule), @js($marketMarkers), @js('checkout-map-' . $farmerId))">
                    <header class="flex flex-wrap items-center justify-between gap-3 px-5 sm:px-6 py-4 border-b border-stone-200/80 dark:border-leaf-800/80 bg-cream-100/60 dark:bg-leaf-900/40">
                        <div class="flex items-center gap-3">
                            <img src="{{ $farmer->user?->avatarUrl() ?? asset('images/product-placeholder.svg') }}" alt="" class="w-10 h-10 rounded-xl object-cover">
                            <div>
                                <p class="text-sm font-bold text-leaf-950 dark:text-cream-50">{{ $farmer->stall_name }}</p>
                                <p class="text-xs text-stone-500 dark:text-stone-400">{{ count($group['items']) }} {{ Str::plural('item', count($group['items'])) }} · ${{ number_format($group['subtotal'], 2) }}</p>
                            </div>
                        </div>
                        @if ($farmer->order_cutoff_hours)
                            <span class="badge-stone">Orders close {{ $farmer->order_cutoff_hours }}h before pickup</span>
                        @endif
                    </header>

                    <div class="px-5 sm:px-6 py-5 space-y-5">
                        {{-- Items recap --}}
                        <ul class="space-y-2">
                            @foreach ($group['items'] as $line)
                                <li class="flex items-center justify-between gap-3 text-sm">
                                    <span class="text-stone-600 dark:text-stone-300 truncate">
                                        <span class="font-semibold text-stone-800 dark:text-stone-100">{{ $line['qty'] }}×</span>
                                        {{ $line['product']->name }}
                                    </span>
                                    <span class="font-semibold text-stone-800 dark:text-stone-100 whitespace-nowrap">${{ number_format($line['product']->price * $line['qty'], 2) }}</span>
                                </li>
                            @endforeach
                        </ul>

                        @if (count($schedule))
                            <div class="pt-4 border-t border-stone-200/70 dark:border-leaf-800/70 grid sm:grid-cols-2 gap-4">
                                {{-- Pickup market --}}
                                <div class="sm:col-span-2">
                                    <label for="market-{{ $farmerId }}" class="input-label">Pickup market</label>
                                    @if ($markets->count())
                                        <select id="market-{{ $farmerId }}" name="orders[{{ $farmerId }}][market_id]" class="input" @change="focusMarket($event.target.value)">
                                            @foreach ($markets as $market)
                                                <option value="{{ $market->id }}">{{ $market->name }} — {{ $market->city }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <p class="input !bg-stone-50 dark:!bg-leaf-900/40 text-stone-500 dark:text-stone-400 cursor-not-allowed">Set by the farmer at acceptance</p>
                                        <input type="hidden" name="orders[{{ $farmerId }}][market_id]" value="">
                                    @endif
                                    @error("orders.{$farmerId}.market_id") <p class="input-error">{{ $message }}</p> @enderror
                                </div>

                                {{-- Pickup point --}}
                                @if ($marketMarkers->isNotEmpty())
                                    <div class="sm:col-span-2">
                                        <p class="input-label">Pickup point</p>
                                        <x-map id="checkout-map-{{ $farmerId }}" height="h-56"
                                            :center="[$marketMarkers->first()['lat'], $marketMarkers->first()['lng']]" :zoom="14"
                                            :markers="$marketMarkers" />
                                        <p class="mt-1.5 text-xs text-stone-400 dark:text-stone-500">Pick a market above and the pin jumps to it — tap the pin for the full address.</p>
                                    </div>
                                @endif

                                {{-- Pickup date --}}
                                <div>
                                    <label for="date-{{ $farmerId }}" class="input-label">Pickup date</label>
                                    <select id="date-{{ $farmerId }}" name="orders[{{ $farmerId }}][pickup_date]" x-model="date" class="input" required>
                                        @foreach ($schedule as $day)
                                            <option value="{{ $day['date'] }}">{{ $day['label'] }}</option>
                                        @endforeach
                                    </select>
                                    @error("orders.{$farmerId}.pickup_date") <p class="input-error">{{ $message }}</p> @enderror
                                </div>

                                {{-- Pickup slot --}}
                                <div>
                                    <label for="slot-{{ $farmerId }}" class="input-label">Pickup time slot</label>
                                    <select id="slot-{{ $farmerId }}" name="orders[{{ $farmerId }}][slot_id]" class="input" required>
                                        <template x-for="s in slots" :key="s.id">
                                            <option :value="s.id" x-text="s.label"></option>
                                        </template>
                                    </select>
                                    @error("orders.{$farmerId}.slot_id") <p class="input-error">{{ $message }}</p> @enderror
                                </div>

                                {{-- Notes for this farmer --}}
                                <div class="sm:col-span-2">
                                    <label for="notes-{{ $farmerId }}" class="input-label">Notes for {{ $farmer->stall_name }} <span class="font-normal text-stone-400">(optional)</span></label>
                                    <textarea id="notes-{{ $farmerId }}" name="orders[{{ $farmerId }}][notes]" rows="2" maxlength="500"
                                        placeholder="e.g. Pick the ripest ones, I'll arrive early..."
                                        class="input resize-none">{{ old("orders.{$farmerId}.notes") }}</textarea>
                                    @error("orders.{$farmerId}.notes") <p class="input-error">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        @else
                            <div class="pt-4 border-t border-stone-200/70 dark:border-leaf-800/70 badge-red w-full px-4 py-3 rounded-xl justify-start">
                                {{ $farmer->stall_name }} has no active pickup slots in the next 14 days — you can't place this part of the order yet.
                            </div>
                        @endif
                    </div>
                </section>
            @endforeach
        </div>

        {{-- ======================= SUMMARY ======================= --}}
        <aside class="card p-5 sm:p-6 lg:sticky lg:top-24">
            <h3 class="font-display text-lg font-semibold text-leaf-950 dark:text-cream-50">Your pre-order</h3>

            <dl class="mt-4 space-y-2.5 text-sm">
                @foreach ($groups as $group)
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-stone-500 dark:text-stone-400 truncate">{{ $group['farmer']->stall_name }}</dt>
                        <dd class="font-semibold text-stone-800 dark:text-stone-100">${{ number_format($group['subtotal'], 2) }}</dd>
                    </div>
                @endforeach
            </dl>

            <div class="mt-4 pt-4 border-t border-dashed border-stone-200 dark:border-leaf-800 flex items-center justify-between">
                <span class="text-sm font-semibold text-stone-700 dark:text-stone-200">Total</span>
                <span class="font-display text-2xl font-semibold text-leaf-800 dark:text-leaf-200">${{ number_format($grandTotal, 2) }}</span>
            </div>

            <div class="mt-4 flex items-start gap-2.5 rounded-xl bg-leaf-50 dark:bg-leaf-800/40 px-3.5 py-3">
                <svg class="w-4.5 h-4.5 w-5 h-5 shrink-0 text-leaf-700 dark:text-leaf-300" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-2m2-4h-6m0 0l3-3m-3 3l3 3"/></svg>
                <p class="text-xs leading-relaxed text-leaf-900 dark:text-leaf-100">
                    <span class="font-semibold">Pay at pickup.</span> Nothing is charged online — you pay the farmer in person on market day.
                </p>
            </div>

            <button type="submit" class="btn-primary w-full mt-5 !py-3" @unless ($canPlace) disabled @endunless>
                Place pre-order
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </button>

            <a href="{{ route('customer.cart.index') }}" class="btn-ghost w-full mt-2">Back to basket</a>
        </aside>
    </form>
@endsection

@push('scripts')
<script>
function checkoutGroup(schedule, markets, mapId) {
    return {
        schedule: schedule || [],
        markets: markets || [],
        mapId: mapId || null,
        date: schedule && schedule.length ? schedule[0].date : '',
        get slots() {
            const day = this.schedule.find(d => d.date === this.date);
            return day ? day.slots : [];
        },
        focusMarket(marketId) {
            const el = this.mapId ? document.getElementById(this.mapId) : null;
            const handle = el && el._leafletMap;
            const marker = handle && handle.byId[marketId];
            if (!marker) return;
            handle.map.setView(marker.getLatLng(), Math.max(handle.map.getZoom(), 15));
            marker.openPopup();
        },
    };
}
</script>
@endpush
