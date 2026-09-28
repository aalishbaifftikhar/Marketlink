@extends('layouts.admin')

@section('title', 'Farmers')

@section('content')
@php
    $badge = fn ($status) => match ($status) {
        'pending' => 'badge-amber',
        'active', 'approved' => 'badge-green',
        'suspended' => 'badge-red',
        default => 'badge-stone',
    };
@endphp

<div class="flex flex-wrap items-center justify-between gap-3">
    <p class="text-sm text-stone-500 dark:text-stone-400">Review stall applications, approve new farmers and manage existing accounts.</p>
</div>

{{-- Status tabs --}}
<div class="mt-4 flex flex-wrap gap-2">
    @foreach (['all' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'suspended' => 'Suspended'] as $value => $label)
        <a href="{{ route('admin.farmers.index', array_merge(request()->only('q', 'market'), ['status' => $value])) }}"
            class="inline-flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-semibold transition-colors {{ ($filters['status'] ?? 'all') === $value
                ? 'border-leaf-600 bg-leaf-600 text-white shadow-soft'
                : 'border-stone-200 dark:border-leaf-800 bg-white dark:bg-leaf-900/60 text-stone-600 dark:text-stone-300 hover:border-leaf-300 dark:hover:border-leaf-600' }}">
            {{ $label }}
            <span class="min-w-[22px] h-[22px] px-1.5 rounded-full text-[11px] font-bold grid place-items-center {{ ($filters['status'] ?? 'all') === $value ? 'bg-white/20 text-white' : 'bg-stone-100 dark:bg-leaf-800 text-stone-500 dark:text-stone-300' }}">{{ $counts[$value] ?? 0 }}</span>
        </a>
    @endforeach
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('admin.farmers.index') }}" class="mt-4 card p-4">
    <input type="hidden" name="status" value="{{ $filters['status'] ?? 'all' }}">
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <label for="q" class="input-label">Search</label>
            <input type="search" id="q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Stall name, contact or email…" class="input">
        </div>
        <div>
            <label for="market" class="input-label">Market</label>
            <select id="market" name="market" class="input">
                <option value="">All markets</option>
                @foreach ($markets as $market)
                    <option value="{{ $market->id }}" @selected((string) ($filters['market'] ?? '') === (string) $market->id)>{{ $market->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="btn-primary flex-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.3-4.3M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Filter
            </button>
            <a href="{{ route('admin.farmers.index') }}" class="btn-ghost">Reset</a>
        </div>
    </div>
</form>

{{-- Table --}}
<div class="mt-5 card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs font-semibold uppercase tracking-wide text-stone-400 dark:text-stone-500 bg-stone-50 dark:bg-leaf-900/40 border-b border-stone-200/70 dark:border-leaf-800">
                    <th class="py-3 px-5">Stall</th>
                    <th class="py-3 px-5">Contact</th>
                    <th class="py-3 px-5">Markets</th>
                    <th class="py-3 px-5 text-center">Products</th>
                    <th class="py-3 px-5">Status</th>
                    <th class="py-3 px-5">Joined</th>
                    <th class="py-3 px-5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100 dark:divide-leaf-800/70">
                @forelse ($farmers as $farmer)
                    @php $fStatus = $farmer->user?->status ?? 'pending'; @endphp
                    <tr class="hover:bg-leaf-50/60 dark:hover:bg-leaf-900/40 transition-colors">
                        <td class="py-3.5 px-5">
                            <a href="{{ route('admin.farmers.show', $farmer) }}" class="font-semibold text-leaf-800 dark:text-leaf-200 hover:underline">{{ $farmer->stall_name }}</a>
                        </td>
                        <td class="py-3.5 px-5">
                            <p class="font-medium text-stone-800 dark:text-stone-100">{{ $farmer->contact_person ?? $farmer->user?->name ?? '—' }}</p>
                            <p class="text-xs text-stone-500 dark:text-stone-400">{{ $farmer->user?->email }}</p>
                        </td>
                        <td class="py-3.5 px-5 text-stone-600 dark:text-stone-300">
                            {{ $farmer->markets->pluck('name')->join(', ') ?: '—' }}
                        </td>
                        <td class="py-3.5 px-5 text-center text-stone-600 dark:text-stone-300">{{ $farmer->products()->count() }}</td>
                        <td class="py-3.5 px-5">
                            <span class="{{ $badge($fStatus) }}">{{ $fStatus === 'active' ? 'Approved' : ucfirst($fStatus) }}</span>
                        </td>
                        <td class="py-3.5 px-5 text-stone-500 dark:text-stone-400 whitespace-nowrap">{{ $farmer->created_at?->format('M j, Y') }}</td>
                        <td class="py-3.5 px-5">
                            <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                @if ($fStatus === 'pending' || $fStatus === 'suspended')
                                    <form method="POST" action="{{ route('admin.farmers.approve', $farmer) }}">
                                        @csrf
                                        <button type="submit" class="btn-primary btn-sm">Approve</button>
                                    </form>
                                @endif
                                @if ($fStatus === 'active')
                                    <form method="POST" action="{{ route('admin.farmers.suspend', $farmer) }}">
                                        @csrf
                                        <button type="submit" class="btn-secondary btn-sm">Suspend</button>
                                    </form>
                                @endif
                                @if ($fStatus === 'suspended')
                                    <form method="POST" action="{{ route('admin.farmers.restore', $farmer) }}">
                                        @csrf
                                        <button type="submit" class="btn-secondary btn-sm">Restore</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.farmers.destroy', $farmer) }}" onsubmit="return confirm('Permanently delete {{ $farmer->stall_name }} and their account? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger btn-sm">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-stone-500 dark:text-stone-400">No farmers match these filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-5">{{ $farmers->links() }}</div>
@endsection
