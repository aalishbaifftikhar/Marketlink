@extends('layouts.customer')

@section('title', 'My Basket')

@section('content')
    @if (empty($groups))
        {{-- Empty basket --}}
        <div class="card px-6 py-16 text-center max-w-2xl mx-auto">
            <span class="mx-auto w-14 h-14 rounded-2xl bg-leaf-100 dark:bg-leaf-800 text-leaf-600 dark:text-leaf-300 grid place-items-center">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/></svg>
            </span>
            <h2 class="mt-5 font-display text-2xl font-semibold text-leaf-950 dark:text-cream-50">Your basket is empty</h2>
            <p class="mt-2 text-sm text-stone-500 dark:text-stone-400 max-w-md mx-auto leading-relaxed">
                Fill it with this week's harvest — reserve ahead and pay in person when you pick it up at the market.
            </p>
            <a href="{{ route('products.index') }}" class="btn-primary mt-6">
                Browse fresh produce
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
        </div>
    @else
        <div class="grid lg:grid-cols-3 gap-6 items-start">
            {{-- ======================= FARMER GROUPS ======================= --}}
            <div class="lg:col-span-2 space-y-6">
                @foreach ($groups as $group)
                    @php $farmer = $group['farmer']; @endphp
                    <section class="card overflow-hidden">
                        <header class="flex flex-wrap items-center justify-between gap-3 px-5 sm:px-6 py-4 border-b border-stone-200/80 dark:border-leaf-800/80 bg-cream-100/60 dark:bg-leaf-900/40">
                            <a href="{{ route('farmers.show', $farmer) }}" class="flex items-center gap-3 group">
                                <img src="{{ $farmer->user?->avatarUrl() ?? asset('images/product-placeholder.svg') }}" alt="" class="w-10 h-10 rounded-xl object-cover">
                                <div>
                                    <p class="text-sm font-bold text-leaf-950 dark:text-cream-50 group-hover:text-leaf-700 dark:group-hover:text-leaf-300 transition-colors">{{ $farmer->stall_name }}</p>
                                    <p class="text-xs text-stone-500 dark:text-stone-400">{{ $farmer->markets->pluck('name')->join(' · ') ?: 'Independent grower' }}</p>
                                </div>
                            </a>
                            <span class="badge-stone">Group subtotal · ${{ number_format($group['subtotal'], 2) }}</span>
                        </header>

                        <ul class="divide-y divide-stone-200/70 dark:divide-leaf-800/70">
                            @foreach ($group['items'] as $line)
                                @php $product = $line['product']; $qty = $line['qty']; @endphp
                                <li class="flex flex-col sm:flex-row sm:items-center gap-4 px-5 sm:px-6 py-4">
                                    <a href="{{ route('products.show', $product) }}" class="flex items-center gap-4 min-w-0 flex-1 group">
                                        <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" class="w-16 h-16 rounded-xl object-cover bg-cream-100 dark:bg-leaf-800 shrink-0">
                                        <div class="min-w-0">
                                            <p class="font-semibold text-stone-800 dark:text-stone-100 group-hover:text-leaf-700 dark:group-hover:text-leaf-300 transition-colors truncate">{{ $product->name }}</p>
                                            <p class="mt-0.5 text-xs text-stone-500 dark:text-stone-400">
                                                ${{ number_format($product->price, 2) }} / {{ $product->unit }}
                                                @if ($qty >= $product->stock_quantity)
                                                    <span class="text-amber-600 dark:text-amber-400 font-semibold">· max available</span>
                                                @endif
                                            </p>
                                        </div>
                                    </a>

                                    <div class="flex items-center justify-between sm:justify-end gap-4">
                                        {{-- Quantity stepper (auto-submits the PATCH form) --}}
                                        <form method="POST" action="{{ route('customer.cart.update') }}" class="flex items-center">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                                            <div class="flex items-center rounded-xl border border-stone-200 dark:border-leaf-800 bg-white dark:bg-leaf-900/60">
                                                <button type="button" onclick="this.nextElementSibling.stepDown(); this.closest('form').requestSubmit()"
                                                    class="px-3 py-2 text-stone-500 hover:text-leaf-700 dark:hover:text-leaf-300 transition-colors" aria-label="Decrease quantity">&minus;</button>
                                                <input type="number" name="quantity" value="{{ $qty }}" min="0" max="{{ $product->stock_quantity }}"
                                                    onchange="this.form.requestSubmit()"
                                                    class="w-12 border-0 bg-transparent text-center text-sm font-semibold focus:ring-0" aria-label="Quantity">
                                                <button type="button" onclick="this.previousElementSibling.stepUp(); this.closest('form').requestSubmit()"
                                                    class="px-3 py-2 text-stone-500 hover:text-leaf-700 dark:hover:text-leaf-300 transition-colors" aria-label="Increase quantity">+</button>
                                            </div>
                                        </form>

                                        <p class="font-display font-semibold text-leaf-800 dark:text-leaf-200 w-20 text-right shrink-0">
                                            ${{ number_format($product->price * $qty, 2) }}
                                        </p>

                                        {{-- Remove --}}
                                        <form method="POST" action="{{ route('customer.cart.remove') }}">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                                            <button type="submit" aria-label="Remove item"
                                                class="w-9 h-9 grid place-items-center rounded-xl text-stone-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.9 12.1A2 2 0 0116.1 21H7.9a2 2 0 01-2-1.9L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endforeach
            </div>

            {{-- ======================= SUMMARY ======================= --}}
            <aside class="card p-5 sm:p-6 lg:sticky lg:top-24">
                <h3 class="font-display text-lg font-semibold text-leaf-950 dark:text-cream-50">Order summary</h3>

                <dl class="mt-4 space-y-2.5 text-sm">
                    @foreach ($groups as $group)
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-stone-500 dark:text-stone-400 truncate">{{ $group['farmer']->stall_name }}</dt>
                            <dd class="font-semibold text-stone-800 dark:text-stone-100">${{ number_format($group['subtotal'], 2) }}</dd>
                        </div>
                    @endforeach
                </dl>

                <div class="mt-4 pt-4 border-t border-dashed border-stone-200 dark:border-leaf-800 flex items-center justify-between">
                    <span class="text-sm font-semibold text-stone-700 dark:text-stone-200">Total to pay at pickup</span>
                    <span class="font-display text-2xl font-semibold text-leaf-800 dark:text-leaf-200">${{ number_format($grandTotal, 2) }}</span>
                </div>

                <p class="mt-3 text-xs leading-relaxed text-stone-500 dark:text-stone-400">
                    No online payment — you settle in person when you collect your order at the market.
                </p>

                <a href="{{ route('customer.checkout') }}" class="btn-primary w-full mt-5 !py-3">
                    Proceed to checkout
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>

                <form method="POST" action="{{ route('customer.cart.clear') }}" class="mt-2"
                    onsubmit="return confirm('Remove every item from your basket?')">
                    @csrf
                    <button type="submit" class="btn-ghost w-full">Clear basket</button>
                </form>
            </aside>
        </div>
    @endif
@endsection
