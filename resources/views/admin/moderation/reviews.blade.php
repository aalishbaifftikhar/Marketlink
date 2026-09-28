@extends('layouts.admin')

@section('title', 'Review Moderation')

@section('content')
<div class="flex flex-col gap-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="font-display text-xl font-bold text-stone-900 dark:text-cream-50">Review Moderation</h2>
            <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">Review customer feedback and hide content that violates community guidelines.</p>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.reviews.index') }}" class="card flex flex-col gap-4 p-4 sm:flex-row sm:items-end">
        <div class="flex-1">
            <label class="input-label">Search</label>
            <input type="text" name="q" value="{{ request('q') }}" class="input" placeholder="Customer, farmer, or comment...">
        </div>
        <label class="flex cursor-pointer items-center gap-2 pb-2.5 text-sm font-medium text-stone-600 dark:text-stone-300">
            <input type="checkbox" name="flagged" value="1" @checked(request('flagged')) class="rounded border-stone-300 text-leaf-600 focus:ring-leaf-500 dark:border-leaf-700 dark:bg-leaf-900">
            Flagged only (rating 1–2)
        </label>
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('admin.reviews.index') }}" class="btn btn-ghost">Reset</a>
        </div>
    </form>

    <div class="grid gap-4 lg:grid-cols-2">
        @forelse ($reviews as $review)
            <div class="card p-5">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <img src="{{ $review->user?->avatarUrl() }}" alt="{{ $review->user?->name }}" class="h-10 w-10 rounded-full object-cover">
                        <div>
                            <a href="{{ route('admin.customers.show', $review->user) }}" class="font-semibold text-stone-900 hover:text-leaf-700 dark:text-cream-50 dark:hover:text-leaf-400">
                                {{ $review->user?->name ?? 'Deleted account' }}
                            </a>
                            <div class="mt-0.5 flex items-center gap-0.5">
                                @for ($i = 1; $i <= 5; $i++)
                                    <svg class="h-4 w-4 {{ $i <= $review->rating ? 'fill-amber-400 text-amber-400' : 'fill-stone-200 text-stone-200 dark:fill-leaf-800 dark:text-leaf-800' }}" viewBox="0 0 20 20" aria-hidden="true">
                                        <path d="M9.05 2.93c.3-.92 1.6-.92 1.9 0l1.28 3.94a1 1 0 0 0 .95.69h4.14c.97 0 1.37 1.24.59 1.81l-3.35 2.43a1 1 0 0 0-.36 1.12l1.28 3.94c.3.92-.75 1.69-1.53 1.12l-3.35-2.43a1 1 0 0 0-1.18 0l-3.35 2.43c-.78.57-1.83-.2-1.53-1.12l1.28-3.94a1 1 0 0 0-.36-1.12L2.97 9.37c-.78-.57-.38-1.81.59-1.81h4.14a1 1 0 0 0 .95-.69l1.4-3.94z"/>
                                    </svg>
                                @endfor
                            </div>
                        </div>
                    </div>
                    @if ($review->is_hidden)
                        <span class="badge badge-stone">Hidden</span>
                    @else
                        <span class="badge badge-green">Visible</span>
                    @endif
                </div>

                @if ($review->comment)
                    <p class="mt-4 text-sm leading-relaxed text-stone-600 dark:text-stone-300">"{{ $review->comment }}"</p>
                @endif

                <div class="mt-4 space-y-1 text-xs text-stone-500 dark:text-stone-400">
                    <p>
                        Farmer:
                        <a href="{{ route('admin.farmers.show', $review->farmer) }}" class="font-medium text-leaf-700 hover:underline dark:text-leaf-400">
                            {{ $review->farmer?->stall_name ?? '—' }}
                        </a>
                    </p>
                    <p>
                        Product:
                        @if ($review->product)
                            <a href="{{ route('products.show', $review->product) }}" class="font-medium text-leaf-700 hover:underline dark:text-leaf-400">
                                {{ $review->product->name }}
                            </a>
                        @else
                            <span>General (farmer review)</span>
                        @endif
                    </p>
                    <p>{{ $review->created_at->format('M j, Y') }}</p>
                </div>

                <div class="mt-4 border-t border-stone-100 pt-4 dark:border-leaf-800/60">
                    <form method="POST" action="{{ route('admin.reviews.toggle', $review) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm {{ $review->is_hidden ? 'btn-secondary' : 'btn-danger' }}">
                            {{ $review->is_hidden ? 'Show Review' : 'Hide Review' }}
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="card p-12 text-center text-stone-400 dark:text-stone-500 lg:col-span-2">
                No reviews match the current filters.
            </div>
        @endforelse
    </div>

    @if ($reviews->hasPages())
        {{ $reviews->links() }}
    @endif
</div>
@endsection
