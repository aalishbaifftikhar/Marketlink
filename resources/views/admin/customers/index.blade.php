@extends('layouts.admin')

@section('title', 'Customers')

@section('content')
@php
    $badge = fn ($status) => match ($status) {
        'pending' => 'badge-amber',
        'active' => 'badge-green',
        'suspended' => 'badge-red',
        default => 'badge-stone',
    };
@endphp

{{-- Status tabs --}}
<div class="flex flex-wrap gap-2">
    @foreach (['all' => 'All', 'active' => 'Active', 'suspended' => 'Suspended'] as $value => $label)
        <a href="{{ route('admin.customers.index', array_merge(request()->only('q'), ['status' => $value])) }}"
            class="inline-flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-semibold transition-colors {{ ($filters['status'] ?? 'all') === $value
                ? 'border-leaf-600 bg-leaf-600 text-white shadow-soft'
                : 'border-stone-200 dark:border-leaf-800 bg-white dark:bg-leaf-900/60 text-stone-600 dark:text-stone-300 hover:border-leaf-300 dark:hover:border-leaf-600' }}">
            {{ $label }}
            <span class="min-w-[22px] h-[22px] px-1.5 rounded-full text-[11px] font-bold grid place-items-center {{ ($filters['status'] ?? 'all') === $value ? 'bg-white/20 text-white' : 'bg-stone-100 dark:bg-leaf-800 text-stone-500 dark:text-stone-300' }}">{{ $counts[$value] ?? 0 }}</span>
        </a>
    @endforeach
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('admin.customers.index') }}" class="mt-4 card p-4">
    <input type="hidden" name="status" value="{{ $filters['status'] ?? 'all' }}">
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        <div>
            <label for="q" class="input-label">Search</label>
            <input type="search" id="q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Name or email…" class="input">
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="btn-primary flex-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.3-4.3M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Filter
            </button>
            <a href="{{ route('admin.customers.index') }}" class="btn-ghost">Reset</a>
        </div>
    </div>
</form>

{{-- Table --}}
<div class="mt-5 card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs font-semibold uppercase tracking-wide text-stone-400 dark:text-stone-500 bg-stone-50 dark:bg-leaf-900/40 border-b border-stone-200/70 dark:border-leaf-800">
                    <th class="py-3 px-5">Customer</th>
                    <th class="py-3 px-5">Phone</th>
                    <th class="py-3 px-5 text-center">Orders</th>
                    <th class="py-3 px-5">Status</th>
                    <th class="py-3 px-5">Joined</th>
                    <th class="py-3 px-5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100 dark:divide-leaf-800/70">
                @forelse ($customers as $customer)
                    <tr class="hover:bg-leaf-50/60 dark:hover:bg-leaf-900/40 transition-colors">
                        <td class="py-3.5 px-5">
                            <div class="flex items-center gap-3">
                                <img src="{{ $customer->avatarUrl() }}" alt="" class="w-9 h-9 rounded-full object-cover ring-2 ring-leaf-100 dark:ring-leaf-800 shrink-0">
                                <div class="min-w-0">
                                    <a href="{{ route('admin.customers.show', $customer) }}" class="font-semibold text-leaf-800 dark:text-leaf-200 hover:underline">{{ $customer->name }}</a>
                                    <p class="text-xs text-stone-500 dark:text-stone-400">{{ $customer->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-5 text-stone-600 dark:text-stone-300">{{ $customer->phone ?? '—' }}</td>
                        <td class="py-3.5 px-5 text-center text-stone-600 dark:text-stone-300">{{ $customer->orders_count }}</td>
                        <td class="py-3.5 px-5"><span class="{{ $badge($customer->status) }}">{{ ucfirst($customer->status) }}</span></td>
                        <td class="py-3.5 px-5 text-stone-500 dark:text-stone-400 whitespace-nowrap">{{ $customer->created_at?->format('M j, Y') }}</td>
                        <td class="py-3.5 px-5 text-right">
                            <form method="POST" action="{{ route('admin.customers.toggle', $customer) }}">
                                @csrf
                                @if ($customer->isActive())
                                    <button type="submit" class="btn-secondary btn-sm" onclick="return confirm('Suspend {{ $customer->name }}? They will lose access to their account.');">Suspend</button>
                                @else
                                    <button type="submit" class="btn-primary btn-sm">Activate</button>
                                @endif
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-stone-500 dark:text-stone-400">No customers match these filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-5">{{ $customers->links() }}</div>
@endsection
