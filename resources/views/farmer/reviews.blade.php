@extends('layouts.farmer')

@section('title', 'Reviews')

@section('content')
    @php
        $reviews->loadMissing(['user', 'product']);
    @endphp

    <div class="space-y-6">

        {{-- Rating summary --}}
        <div class="card flex flex-col items-start justify-between gap-6 p-6 sm:flex-row sm:items-center">
            <div class="flex items-center gap-5">
                <div class="flex h-20 w-20 shrink-0 flex-col items-center justify-center rounded-2xl bg-leaf-50 ring-1 ring-leaf-100 dark:bg-leaf-900/30 dark:ring-leaf-800">
                    <span class="text-3xl font-bold text-leaf-700 dark:text-leaf-300">{{ number_format($average, 1) }}</span>
                    <div class="mt-0.5 flex items-center gap-0.5">
                        @for ($i = 1; $i <= 5; $i++)
                            <svg class="h-3 w-3 {{ $i <= round($average) ? 'text-amber-400' : 'text-stone-300 dark:text-stone-600' }}"
                                viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M10 1.5l2.6 5.3 5.9.9-4.2 4.1 1 5.8L10 14.9l-5.3 2.7 1-5.8L1.5 7.7l5.9-.9L10 1.5z" />
                            </svg>
                        @endfor
                    </div>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-stone-900 dark:text-stone-100">Customer reviews</h2>
                    <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">
                        Based on {{ $total }} {{ Str::plural('review', $total) }} from verified orders.
                    </p>
                    <p class="mt-1 text-xs text-stone-400 dark:text-stone-500">
                        Only reviews for {{ $farmer->stall_name }} are shown here.
                    </p>
                </div>
            </div>
            <a href="{{ route('farmers.show', $farmer) }}" class="btn-secondary btn-sm shrink-0">View public stall</a>
        </div>

        {{-- Review list --}}
        <div class="space-y-4">
            @forelse ($reviews as $review)
                @php
                    $customerName = $review->user->name ?? 'Customer';
                @endphp
                <article class="card card-hover p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <img src="{{ $review->user->avatarUrl() }}" alt="{{ $customerName }}" class="h-11 w-11 rounded-full object-cover ring-2 ring-cream-200 dark:ring-stone-700">
                            <div>
                                <p class="font-semibold text-stone-900 dark:text-stone-100">{{ $customerName }}</p>
                                <div class="mt-0.5 flex items-center gap-1.5">
                                    <div class="flex items-center gap-0.5" aria-label="{{ $review->rating }} out of 5 stars">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <svg class="h-4 w-4 {{ $i <= $review->rating ? 'text-amber-400' : 'text-stone-300 dark:text-stone-600' }}"
                                                viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path d="M10 1.5l2.6 5.3 5.9.9-4.2 4.1 1 5.8L10 14.9l-5.3 2.7 1-5.8L1.5 7.7l5.9-.9L10 1.5z" />
                                            </svg>
                                        @endfor
                                    </div>
                                    <span class="text-xs text-stone-400 dark:text-stone-500">·</span>
                                    <span class="text-xs text-stone-400 dark:text-stone-500">{{ $review->created_at?->format('M j, Y') }}</span>
                                </div>
                            </div>
                        </div>
                        @if ($review->product)
                            <a href="{{ route('farmer.products.edit', $review->product) }}"
                                class="badge badge-cream hidden transition hover:bg-stone-200 dark:hover:bg-stone-600 sm:inline-flex">
                                {{ $review->product->name }}
                            </a>
                        @endif
                    </div>

                    @if ($review->comment)
                        <p class="mt-4 whitespace-pre-line text-sm leading-relaxed text-stone-600 dark:text-stone-300">{{ $review->comment }}</p>
                    @endif

                    @if ($review->product)
                        <p class="mt-2 text-xs text-stone-400 dark:text-stone-500 sm:hidden">
                            Product: <span class="font-medium text-stone-500 dark:text-stone-400">{{ $review->product->name }}</span>
                        </p>
                    @endif

                    {{-- Farmer response --}}
                    @if ($review->farmer_response)
                        <div class="mt-4 rounded-2xl border border-leaf-100 bg-leaf-50 p-4 dark:border-leaf-800 dark:bg-leaf-900/30">
                            <p class="text-xs font-semibold uppercase tracking-wide text-leaf-600 dark:text-leaf-400">
                                Your response{{ $review->responded_at ? ' · ' . $review->responded_at->format('M j, Y') : '' }}
                            </p>
                            <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-stone-600 dark:text-stone-300">{{ $review->farmer_response }}</p>
                        </div>
                    @else
                        <form method="POST" action="{{ route('farmer.reviews.respond', $review) }}" class="mt-4 border-t border-cream-200 pt-4 dark:border-stone-700">
                            @csrf
                            <label for="response-{{ $review->id }}" class="input-label">Respond publicly to this review</label>
                            <textarea id="response-{{ $review->id }}" name="response" rows="3" maxlength="1000"
                                class="input mt-1.5"
                                placeholder="Thank the customer or share a note that appears on your public stall…">{{ old('response') }}</textarea>
                            <x-input-error field="response" />
                            <div class="mt-3 flex items-center justify-end gap-3">
                                <p class="mr-auto hidden text-xs text-stone-400 dark:text-stone-500 sm:block">Your response will be shown publicly on your stall page.</p>
                                <button type="submit" class="btn-primary btn-sm">Post response</button>
                            </div>
                        </form>
                    @endif
                </article>
            @empty
                <div class="card flex flex-col items-center justify-center p-12 text-center">
                    <svg class="h-12 w-12 text-leaf-300 dark:text-leaf-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                    </svg>
                    <h3 class="mt-4 text-lg font-semibold text-stone-900 dark:text-stone-100">No reviews yet</h3>
                    <p class="mt-2 max-w-sm text-sm text-stone-500 dark:text-stone-400">
                        Once customers complete pickups for orders from {{ $farmer->stall_name }}, their reviews will appear here.
                    </p>
                    <a href="{{ route('farmer.dashboard') }}" class="btn-secondary btn-sm mt-6">Back to dashboard</a>
                </div>
            @endforelse
        </div>

        @if ($reviews->hasPages())
            <div>{{ $reviews->appends(request()->query())->links() }}</div>
        @endif
    </div>
@endsection
