@extends('layouts.customer')

@section('title', 'Edit Order #' . $order->order_number)

@section('content')
    @php
        $farmer = $order->farmer;
        $statusBadge = match ($order->status) {
            'placed' => 'badge-amber',
            'accepted', 'ready_for_pickup' => 'badge-blue',
            'completed' => 'badge-green',
            default => 'badge-red',
        };
    @endphp

    <div class="flex flex-wrap items-center gap-3">
        <a href="{{ route('customer.orders.show', $order) }}" class="btn-ghost btn-sm !px-2.5" aria-label="Back to order">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h2 class="font-display text-2xl font-semibold text-leaf-950 dark:text-cream-50">Edit order</h2>
        <span class="{{ $statusBadge }}">{{ $order->statusLabel() }}</span>
    </div>

    @if ($order->cutoff_at)
        <div class="mt-5 badge-amber px-5 py-3.5 rounded-2xl justify-start w-full">
            <svg class="w-4.5 h-4.5 w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/></svg>
            Changes are allowed until {{ $order->cutoff_at->format('D, j M \a\t g:i A') }}
            ({{ $order->cutoff_at->diffForHumans() }}). Pickup stays
            {{ $order->pickup_date?->format('D, j M') }} · {{ $order->pickup_slot }}.
        </div>
    @endif

    <form method="POST" action="{{ route('customer.orders.update', $order) }}" class="mt-6 grid lg:grid-cols-3 gap-6 items-start">
        @csrf
        @method('PUT')

        {{-- ======================= QUANTITIES ======================= --}}
        <div class="lg:col-span-2 card overflow-hidden">
            <header class="px-5 sm:px-6 py-4 border-b border-stone-200/80 dark:border-leaf-800/80">
                <h3 class="font-display text-lg font-semibold text-leaf-950 dark:text-cream-50">Items from {{ $farmer?->stall_name }}</h3>
                <p class="mt-0.5 text-xs text-stone-500 dark:text-stone-400">Adjust quantities — set one to 0 to remove it from the order.</p>
            </header>

            <ul class="divide-y divide-stone-200/70 dark:divide-leaf-800/70">
                @foreach ($order->items as $item)
                    @php $product = $item->product; @endphp
                    <li class="flex flex-col sm:flex-row sm:items-center gap-4 px-5 sm:px-6 py-4">
                        <div class="flex items-center gap-4 min-w-0 flex-1">
                            @if ($product)
                                <a href="{{ route('products.show', $product) }}" class="shrink-0">
                                    <img src="{{ $product->imageUrl() }}" alt="{{ $item->product_name }}" class="w-14 h-14 rounded-xl object-cover bg-cream-100 dark:bg-leaf-800">
                                </a>
                            @endif
                            <div class="min-w-0">
                                <p class="font-semibold text-stone-800 dark:text-stone-100 truncate">{{ $item->product_name }}</p>
                                <p class="mt-0.5 text-xs text-stone-500 dark:text-stone-400">
                                    ${{ number_format($item->unit_price, 2) }} / {{ $item->unit }}
                                    @if ($product)
                                        · {{ $product->stock_quantity }} {{ Str::plural($item->unit, $product->stock_quantity) }} in stock
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between sm:justify-end gap-4">
                            <div class="flex items-center rounded-xl border border-stone-200 dark:border-leaf-800 bg-white dark:bg-leaf-900/60">
                                <button type="button" onclick="this.nextElementSibling.stepDown()"
                                    class="px-3 py-2 text-stone-500 hover:text-leaf-700 dark:hover:text-leaf-300 transition-colors" aria-label="Decrease quantity">&minus;</button>
                                <input type="number" name="quantities[{{ $item->id }}]" value="{{ old("quantities.{$item->id}", $item->quantity) }}"
                                    min="0" max="99" required
                                    class="w-14 border-0 bg-transparent text-center text-sm font-semibold focus:ring-0" aria-label="Quantity for {{ $item->product_name }}">
                                <button type="button" onclick="this.previousElementSibling.stepUp()"
                                    class="px-3 py-2 text-stone-500 hover:text-leaf-700 dark:hover:text-leaf-300 transition-colors" aria-label="Increase quantity">+</button>
                            </div>
                            <p class="text-sm font-semibold text-stone-800 dark:text-stone-100 w-20 text-right shrink-0">
                                ${{ number_format($item->line_total, 2) }}
                            </p>
                        </div>
                    </li>
                    @error("quantities.{$item->id}") <li class="px-5 sm:px-6 py-2 bg-red-50 dark:bg-red-500/5"><p class="input-error">{{ $message }}</p></li> @enderror
                @endforeach
            </ul>
        </div>

        {{-- ======================= SUMMARY ======================= --}}
        <aside class="card p-5 sm:p-6 lg:sticky lg:top-24 space-y-5">
            <div>
                <h3 class="font-display text-lg font-semibold text-leaf-950 dark:text-cream-50">Order notes</h3>
                <label for="notes" class="input-label mt-3">Message for {{ $farmer?->stall_name ?? 'the farmer' }} <span class="font-normal text-stone-400">(optional)</span></label>
                <textarea id="notes" name="notes" rows="4" maxlength="500" placeholder="e.g. Swap the kale for chard if it looks better..."
                    class="input resize-none">{{ old('notes', $order->customer_notes) }}</textarea>
                @error('notes') <p class="input-error">{{ $message }}</p> @enderror
            </div>

            <div class="pt-4 border-t border-dashed border-stone-200 dark:border-leaf-800">
                <div class="flex items-center justify-between text-sm">
                    <span class="font-semibold text-stone-700 dark:text-stone-200">Current total</span>
                    <span class="font-display text-xl font-semibold text-leaf-800 dark:text-leaf-200">${{ number_format($order->total_amount, 2) }}</span>
                </div>
                <p class="mt-2 text-xs leading-relaxed text-stone-500 dark:text-stone-400">
                    Totals are recalculated when you save. If every item is removed, the order is cancelled and stock is released.
                </p>
            </div>

            <button type="submit" class="btn-primary w-full !py-3">Save changes</button>
            <a href="{{ route('customer.orders.show', $order) }}" class="btn-ghost w-full">Discard changes</a>
        </aside>
    </form>
@endsection
