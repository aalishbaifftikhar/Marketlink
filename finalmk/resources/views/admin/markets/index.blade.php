@extends('layouts.admin')

@section('title', 'Markets')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3">
    <p class="text-sm text-stone-500 dark:text-stone-400">Manage the market locations where farmers sell and customers pick up orders.</p>
    <a href="{{ route('admin.markets.create') }}" class="btn-primary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
        Add Market
    </a>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('admin.markets.index') }}" class="mt-4 card p-4">
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <label for="q" class="input-label">Search</label>
            <input type="search" id="q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Name, city or address…" class="input">
        </div>
        <div>
            <label for="status" class="input-label">Status</label>
            <select id="status" name="status" class="input">
                @foreach (['all' => 'All', 'active' => 'Active', 'inactive' => 'Hidden'] as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['status'] ?? 'all') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="btn-primary flex-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.3-4.3M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Filter
            </button>
            <a href="{{ route('admin.markets.index') }}" class="btn-ghost">Reset</a>
        </div>
    </div>
</form>

{{-- Table --}}
<div class="mt-5 card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs font-semibold uppercase tracking-wide text-stone-400 dark:text-stone-500 bg-stone-50 dark:bg-leaf-900/40 border-b border-stone-200/70 dark:border-leaf-800">
                    <th class="py-3 px-5">Market</th>
                    <th class="py-3 px-5">Days</th>
                    <th class="py-3 px-5">Hours</th>
                    <th class="py-3 px-5 text-center">Farmers</th>
                    <th class="py-3 px-5">Status</th>
                    <th class="py-3 px-5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100 dark:divide-leaf-800/70">
                @forelse ($markets as $market)
                    <tr class="hover:bg-leaf-50/60 dark:hover:bg-leaf-900/40 transition-colors">
                        <td class="py-3.5 px-5">
                            <div class="flex items-center gap-3">
                                @if ($market->image)
                                    <img src="{{ asset('storage/' . $market->image) }}" alt="" class="w-12 h-12 rounded-xl object-cover shrink-0">
                                @else
                                    <span class="w-12 h-12 rounded-xl bg-leaf-100 dark:bg-leaf-800 text-leaf-700 dark:text-leaf-300 grid place-items-center shrink-0">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9l1.5-5h15L21 9M3 9v10a1 1 0 001 1h16a1 1 0 001-1V9M3 9h18M9 20v-6h6v6"/></svg>
                                    </span>
                                @endif
                                <div class="min-w-0">
                                    <a href="{{ route('markets.show', $market) }}" class="font-semibold text-leaf-800 dark:text-leaf-200 hover:underline">{{ $market->name }}</a>
                                    <p class="text-xs text-stone-500 dark:text-stone-400 truncate max-w-[16rem]">{{ $market->city }} · {{ $market->address }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-5 text-stone-600 dark:text-stone-300 whitespace-nowrap">{{ $market->operatingDaysLabel() }}</td>
                        <td class="py-3.5 px-5 text-stone-600 dark:text-stone-300 whitespace-nowrap">
                            {{ ($market->open_time && $market->close_time) ? $market->open_time . ' – ' . $market->close_time : '—' }}
                        </td>
                        <td class="py-3.5 px-5 text-center text-stone-600 dark:text-stone-300">{{ $market->farmers_count }}</td>
                        <td class="py-3.5 px-5">
                            <span class="{{ $market->is_active ? 'badge-green' : 'badge-stone' }}">{{ $market->is_active ? 'Active' : 'Hidden' }}</span>
                        </td>
                        <td class="py-3.5 px-5">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('admin.markets.edit', $market) }}" class="btn-secondary btn-sm">Edit</a>
                                <form method="POST" action="{{ route('admin.markets.destroy', $market) }}" onsubmit="return confirm('Delete the market {{ $market->name }}? Markets with farmers assigned cannot be deleted.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger btn-sm">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-stone-500 dark:text-stone-400">No markets found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-5">{{ $markets->links() }}</div>
@endsection
