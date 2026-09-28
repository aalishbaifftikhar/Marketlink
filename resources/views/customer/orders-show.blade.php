@extends('layouts.customer')

@section('title', 'Order #' . $order->order_number)

@section('content')
    @php
        $farmer = $order->farmer;
        $statusBadge = match ($order->status) {
            'placed' => 'badge-amber',
            'accepted', 'ready_for_pickup' => 'badge-blue',
            'completed' => 'badge-green',
            default => 'badge-red',
        };

        $steps = ['placed' => 'Placed', 'accepted' => 'Accepted', 'ready_for_pickup' => 'Ready for Pickup', 'completed' => 'Completed'];
        $stepKeys = array_keys($steps);
        $currentIndex = array_search($order->status, $stepKeys);
        $terminated = in_array($order->status, ['cancelled', 'declined']);

        $starPath = 'M10 1.5l2.6 5.3 5.9.9-4.2 4.1 1 5.8L10 14.9l-5.3 2.7 1-5.8L1.5 7.7l5.9-.9L10 1.5z';
        $farmerReview = $reviews->firstWhere('product_id', null);
    @endphp

    {{-- ======================= HEADER ======================= --}}
    <div class="flex flex-wrap items-center gap-3">
        <a href="{{ route('customer.orders.index') }}" class="btn-ghost btn-sm !px-2.5" aria-label="Back to orders">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h2 class="font-display text-2xl font-semibold text-leaf-950 dark:text-cream-50">#{{ $order->order_number }}</h2>
        <span class="{{ $statusBadge }}">{{ $order->statusLabel() }}</span>
        <span class="ml-auto text-sm text-stone-500 dark:text-stone-400">Placed {{ $order->placed_at?->format('D, j M Y \a\t g:i A') }}</span>
    </div>

    @if ($terminated)
        <div class="mt-5 badge-red px-5 py-4 rounded-2xl justify-start w-full">
            This order was {{ $order->statusLabel() }}@if($order->farmer_notes) — {{ $order->farmer_notes }}@endif. Any reserved stock has been released.
        </div>
    @endif

    <div class="mt-6 grid lg:grid-cols-3 gap-6 items-start">
        {{-- ======================= LEFT: ITEMS ======================= --}}
        <div class="lg:col-span-2 space-y-6">
            <section class="card overflow-hidden">
                <header class="px-5 sm:px-6 py-4 border-b border-stone-200/80 dark:border-leaf-800/80">
                    <h3 class="font-display text-lg font-semibold text-leaf-950 dark:text-cream-50">Items</h3>
                </header>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wider text-stone-400 dark:text-stone-500 border-b border-stone-200/70 dark:border-leaf-800/70">
                                <th class="px-5 sm:px-6 py-3 font-semibold">Product</th>
                                <th class="px-4 py-3 font-semibold text-right">Unit price</th>
                                <th class="px-4 py-3 font-semibold text-center">Qty</th>
                                <th class="px-5 sm:px-6 py-3 font-semibold text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-200/70 dark:divide-leaf-800/70">
                            @foreach ($order->items as $item)
                                <tr>
                                    <td class="px-5 sm:px-6 py-4">
                                        <p class="font-semibold text-stone-800 dark:text-stone-100">{{ $item->product_name }}</p>
                                        @if ($item->unit)
                                            <p class="text-xs text-stone-500 dark:text-stone-400">per {{ $item->unit }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-right text-stone-600 dark:text-stone-300">${{ number_format($item->unit_price, 2) }}</td>
                                    <td class="px-4 py-4 text-center font-semibold text-stone-800 dark:text-stone-100">{{ $item->quantity }}</td>
                                    <td class="px-5 sm:px-6 py-4 text-right font-semibold text-stone-800 dark:text-stone-100">${{ number_format($item->line_total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t border-stone-200/80 dark:border-leaf-800/80">
                                <td colspan="3" class="px-5 sm:px-6 py-4 text-right text-sm font-semibold text-stone-500 dark:text-stone-400">Subtotal</td>
                                <td class="px-5 sm:px-6 py-4 text-right font-semibold text-stone-800 dark:text-stone-100">${{ number_format($order->subtotal, 2) }}</td>
                            </tr>
                            <tr class="bg-cream-100/60 dark:bg-leaf-900/40">
                                <td colspan="3" class="px-5 sm:px-6 py-4 text-right text-sm font-bold text-leaf-950 dark:text-cream-50">Total to pay at pickup</td>
                                <td class="px-5 sm:px-6 py-4 text-right font-display text-lg font-semibold text-leaf-800 dark:text-leaf-200">${{ number_format($order->total_amount, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>

            @if ($order->customer_notes)
                <section class="card p-5 sm:p-6">
                    <h3 class="font-display text-lg font-semibold text-leaf-950 dark:text-cream-50">Your notes</h3>
                    <p class="mt-2 text-sm leading-relaxed text-stone-600 dark:text-stone-300">{{ $order->customer_notes }}</p>
                </section>
            @endif

            @if ($farmer?->description)
                <section class="card p-5 sm:p-6">
                    <h3 class="font-display text-lg font-semibold text-leaf-950 dark:text-cream-50">About {{ $farmer->stall_name }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-stone-600 dark:text-stone-300">{{ $farmer->description }}</p>
                </section>
            @endif

            {{-- ======================= REVIEW ======================= --}}
            @if ($order->status === 'completed')
                <section class="card p-5 sm:p-6" id="review">
                    <h3 class="font-display text-lg font-semibold text-leaf-950 dark:text-cream-50">Rate your experience</h3>

                    @if ($canReview)
                        <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">Share feedback on the farmer and any products you'd recommend.</p>

                        <form method="POST" action="{{ route('customer.reviews.store') }}" class="mt-5 space-y-6">
                            @csrf
                            <input type="hidden" name="order_id" value="{{ $order->id }}">

                            {{-- Farmer rating --}}
                            <div>
                                <p class="input-label">How was {{ $farmer?->stall_name ?? 'the farmer' }}?</p>
                                <div class="flex items-center gap-1">
                                    @for ($i = 5; $i >= 1; $i--)
                                        <label class="cursor-pointer">
                                            <input type="radio" name="farmer_rating" value="{{ $i }}" class="peer sr-only" required>
                                            <svg class="h-7 w-7 fill-stone-200 text-stone-300 transition peer-checked:fill-amber-400 peer-checked:text-amber-400 dark:fill-stone-700 dark:text-stone-600" viewBox="0 0 20 20"><path d="{{ $starPath }}"/></svg>
                                        </label>
                                    @endfor
                                </div>
                                @error('farmer_rating') <p class="input-error">{{ $message }}</p> @enderror
                                <textarea name="farmer_comment" rows="2" maxlength="1000" placeholder="Tell others about the stall, service, freshness..."
                                    class="input mt-3 resize-none">{{ old('farmer_comment') }}</textarea>
                                @error('farmer_comment') <p class="input-error">{{ $message }}</p> @enderror
                            </div>

                            {{-- Per-product ratings --}}
                            <div class="space-y-4 pt-5 border-t border-stone-200/70 dark:border-leaf-800/70">
                                <p class="input-label !mb-0">Rate individual products <span class="font-normal text-stone-400">(optional)</span></p>
                                @foreach ($order->items as $item)
                                    <div class="rounded-xl border border-stone-200 dark:border-leaf-800 p-4">
                                        <p class="text-sm font-semibold text-stone-800 dark:text-stone-100">{{ $item->product_name }}</p>
                                        <div class="mt-2 flex items-center gap-1">
                                            @for ($i = 5; $i >= 1; $i--)
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="products[{{ $item->product_id }}][rating]" value="{{ $i }}" class="peer sr-only">
                                                    <svg class="h-6 w-6 fill-stone-200 text-stone-300 transition peer-checked:fill-amber-400 peer-checked:text-amber-400 dark:fill-stone-700 dark:text-stone-600" viewBox="0 0 20 20"><path d="{{ $starPath }}"/></svg>
                                                </label>
                                            @endfor
                                        </div>
                                        <textarea name="products[{{ $item->product_id }}][comment]" rows="2" maxlength="1000" placeholder="How was it? (optional)"
                                            class="input mt-3 !py-2 resize-none">{{ old("products.{$item->product_id}.comment") }}</textarea>
                                    </div>
                                @endforeach
                            </div>

                            <button type="submit" class="btn-primary">Publish review</button>
                        </form>
                    @else
                        <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">You've already shared your feedback for this order — thank you!</p>
                        @if ($farmerReview)
                            <div class="mt-4 rounded-xl bg-cream-100/70 dark:bg-leaf-800/30 px-4 py-3">
                                <div class="flex items-center gap-1 text-amber-500">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <svg class="h-4 w-4 {{ $i <= $farmerReview->rating ? 'fill-current' : 'fill-stone-200 dark:fill-stone-700' }}" viewBox="0 0 20 20"><path d="{{ $starPath }}"/></svg>
                                    @endfor
                                    <span class="ml-2 text-xs font-semibold text-stone-500 dark:text-stone-400">Your farmer review</span>
                                </div>
                                @if ($farmerReview->comment)
                                    <p class="mt-2 text-sm leading-relaxed text-stone-600 dark:text-stone-300">{{ $farmerReview->comment }}</p>
                                @endif
                            </div>
                        @endif
                    @endif
                </section>
            @endif
        </div>

        {{-- ======================= RIGHT: DETAILS ======================= --}}
        <div class="space-y-6">
            {{-- Status stepper --}}
            <section class="card p-5 sm:p-6">
                <h3 class="font-display text-base font-semibold text-leaf-950 dark:text-cream-50">Order progress</h3>
                @if ($terminated)
                    <p class="mt-3 text-sm text-stone-500 dark:text-stone-400">This order is closed.</p>
                @else
                    <ol class="mt-5 space-y-0">
                        @foreach ($stepKeys as $i => $key)
                            @php
                                $done = $currentIndex !== false && $i < $currentIndex;
                                $current = $currentIndex !== false && $i === $currentIndex;
                            @endphp
                            <li class="flex gap-3">
                                <div class="flex flex-col items-center">
                                    <span class="w-8 h-8 rounded-full grid place-items-center text-xs font-bold shrink-0
                                        {{ $done || $current ? 'bg-leaf-600 text-white' : 'bg-stone-200 dark:bg-leaf-800 text-stone-500 dark:text-stone-400' }}">
                                        @if ($done)
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        @else
                                            {{ $i + 1 }}
                                        @endif
                                    </span>
                                    @if (! $loop->last)
                                        <span class="w-0.5 flex-1 min-h-[1.25rem] {{ $done ? 'bg-leaf-600' : 'bg-stone-200 dark:bg-leaf-800' }}"></span>
                                    @endif
                                </div>
                                <div class="pb-5">
                                    <p class="text-sm font-semibold {{ $done || $current ? 'text-leaf-950 dark:text-cream-50' : 'text-stone-400 dark:text-stone-500' }}">{{ $steps[$key] }}</p>
                                    @if ($current)
                                        <p class="text-xs text-stone-500 dark:text-stone-400">Current status</p>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </section>

            {{-- Pickup details --}}
            <section class="card p-5 sm:p-6">
                <h3 class="font-display text-base font-semibold text-leaf-950 dark:text-cream-50">Pickup</h3>
                <dl class="mt-4 space-y-3.5 text-sm">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 shrink-0 text-leaf-600 dark:text-leaf-400 mt-0.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.1-7.5 11.25-7.5 11.25S4.5 17.6 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                        <div>
                            <dt class="text-stone-500 dark:text-stone-400">Market</dt>
                            <dd class="font-semibold text-stone-800 dark:text-stone-100">{{ $order->market?->name ?? 'Set by farmer at acceptance' }}</dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 shrink-0 text-leaf-600 dark:text-leaf-400 mt-0.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 5h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z"/></svg>
                        <div>
                            <dt class="text-stone-500 dark:text-stone-400">Pickup date</dt>
                            <dd class="font-semibold text-stone-800 dark:text-stone-100">{{ $order->pickup_date?->format('l, j F Y') }}</dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 shrink-0 text-leaf-600 dark:text-leaf-400 mt-0.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l2.5 2.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <dt class="text-stone-500 dark:text-stone-400">Time slot</dt>
                            <dd class="font-semibold text-stone-800 dark:text-stone-100">{{ $order->pickup_slot }}</dd>
                        </div>
                    </div>
                </dl>

                @if ($order->cutoff_at)
                    <div class="mt-4 rounded-xl {{ $order->canBeModified() ? 'bg-leaf-50 dark:bg-leaf-800/40' : 'bg-stone-100 dark:bg-leaf-900/40' }} px-3.5 py-3">
                        <p class="text-xs leading-relaxed {{ $order->canBeModified() ? 'text-leaf-900 dark:text-leaf-100' : 'text-stone-500 dark:text-stone-400' }}">
                            @if ($order->canBeModified())
                                <span class="font-semibold">You can modify or cancel until {{ $order->cutoff_at->format('D, j M \a\t g:i A') }}</span>
                                ({{ $order->cutoff_at->diffForHumans() }}).
                            @else
                                The modification window closed {{ $order->cutoff_at->diffForHumans() }}.
                            @endif
                        </p>
                    </div>
                @endif
            </section>

            {{-- Farmer card --}}
            @if ($farmer)
                <a href="{{ route('farmers.show', $farmer) }}" class="card card-hover p-5 flex items-center gap-4 group">
                    <img src="{{ $farmer->user?->avatarUrl() ?? asset('images/product-placeholder.svg') }}" alt="" class="w-14 h-14 rounded-2xl object-cover">
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-leaf-950 dark:text-cream-50 group-hover:text-leaf-700 dark:group-hover:text-leaf-300 transition-colors">{{ $farmer->stall_name }}</p>
                        <p class="truncate text-sm text-stone-500 dark:text-stone-400">
                            {{ $farmer->markets->pluck('name')->join(' · ') ?: 'Independent grower' }}
                        </p>
                    </div>
                    <span class="flex items-center gap-1 text-sm font-semibold text-leaf-800 dark:text-leaf-200">
                        <svg class="h-4 w-4 text-amber-500" viewBox="0 0 20 20" fill="currentColor"><path d="{{ $starPath }}"/></svg>
                        {{ number_format($farmer->averageRating(), 1) }}
                    </span>
                </a>
            @endif

            {{-- Actions --}}
            <section class="card p-5 sm:p-6 space-y-2">
                <h3 class="font-display text-base font-semibold text-leaf-950 dark:text-cream-50 mb-3">Actions</h3>
                @if ($order->canBeModified())
                    <a href="{{ route('customer.orders.edit', $order) }}" class="btn-secondary w-full">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.9 4.6a2.1 2.1 0 013 3L8 19.5 4 20.5l1-4L16.9 4.6z"/></svg>
                        Edit order
                    </a>
                @endif
                <form method="POST" action="{{ route('customer.orders.reorder', $order) }}">
                    @csrf
                    <button type="submit" class="btn-secondary w-full">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 9a8 8 0 0114.9-2M20 15a8 8 0 01-14.9 2M4 4v5h5M20 20v-5h-5"/></svg>
                        Order again
                    </button>
                </form>
                @if ($order->canBeCancelled())
                    <form method="POST" action="{{ route('customer.orders.cancel', $order) }}"
                        onsubmit="return confirm('Cancel order #{{ $order->order_number }}? The reserved stock will be released.')">
                        @csrf
                        <button type="submit" class="btn-danger w-full">Cancel order</button>
                    </form>
                @endif
            </section>
        </div>
    </div>
@endsection
