@props(['product'])
@php
    $farmer = $product->farmer;
@endphp
<div class="card card-hover overflow-hidden group flex flex-col">
    <a href="{{ route('products.show', $product) }}" class="relative block aspect-[4/3] overflow-hidden bg-cream-100 dark:bg-leaf-900">
        <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" loading="lazy"
            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
        @if (! $product->inStock())
            <span class="absolute top-2.5 left-2.5 badge-red">Sold out</span>
        @elseif ($product->created_at?->gt(now()->subDays(7)))
            <span class="absolute top-2.5 left-2.5 badge-green">New</span>
        @endif
        @auth
            @php $fav = auth()->user()->hasFavorited('product', $product->id); @endphp
            <button type="button" onclick="toggleFavorite(event, 'product', {{ $product->id }}, this)"
                class="absolute top-2.5 right-2.5 w-8 h-8 rounded-full bg-white/90 dark:bg-leaf-900/90 backdrop-blur grid place-items-center shadow-soft hover:scale-110 transition-transform"
                data-fav-type="product" data-fav-id="{{ $product->id }}" data-favorited="{{ $fav ? '1' : '0' }}" aria-label="Favorite">
                <svg class="w-4 h-4 {{ $fav ? 'text-red-500 fill-current' : 'text-stone-500' }}" fill="{{ $fav ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.3 6.3A5 5 0 0112 6a5 5 0 017.7.3c1.9 1.9 2 4.9.3 7L12 20.5l-8-7.2a5.3 5.3 0 01.3-7z"/></svg>
            </button>
        @endauth
    </a>
    <div class="p-4 flex flex-col flex-1">
        <div class="flex items-start justify-between gap-2">
            <a href="{{ route('products.show', $product) }}" class="font-semibold text-stone-800 dark:text-stone-100 hover:text-leaf-700 dark:hover:text-leaf-300 transition-colors leading-snug">{{ $product->name }}</a>
            @if ($product->averageRating() > 0)
                <span class="inline-flex items-center gap-0.5 text-xs font-semibold text-amber-600 dark:text-amber-400 shrink-0">
                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12 2l2.9 6.3 6.9.8-5.1 4.7 1.4 6.8L12 17.2 5.9 20.6l1.4-6.8L2.2 9.1l6.9-.8L12 2z"/></svg>
                    {{ number_format($product->averageRating(), 1) }}
                </span>
            @endif
        </div>
        <p class="mt-1 text-xs text-stone-500 dark:text-stone-400 truncate">
            <a href="{{ $farmer ? route('farmers.show', $farmer) : '#' }}" class="hover:text-leaf-700 dark:hover:text-leaf-300">{{ $farmer?->stall_name ?? 'Local farm' }}</a>
        </p>
        <div class="mt-auto pt-3 flex items-center justify-between">
            <p class="text-lg font-display font-semibold text-leaf-700 dark:text-leaf-300">
                ${{ number_format($product->price, 2) }}<span class="text-xs font-sans font-medium text-stone-400"> / {{ $product->unit }}</span>
            </p>
            @if ($product->inStock())
                <form method="POST" action="{{ route('customer.cart.add') }}">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="btn-primary btn-sm !px-3" aria-label="Add to basket">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
                    </button>
                </form>
            @else
                <span class="badge-stone">Unavailable</span>
            @endif
        </div>
    </div>
</div>
