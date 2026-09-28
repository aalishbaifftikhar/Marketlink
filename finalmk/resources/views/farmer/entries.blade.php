@extends('layouts.farmer')

@section('title', 'Profit & Loss')

@section('content')
@php
    $monthCarbon = \Illuminate\Support\Carbon::create($year, $month, 1);
    $prevMonth = $monthCarbon->copy()->subMonth();
    $nextMonth = $monthCarbon->copy()->addMonth();
    $baseQuery = ['mode' => 'month', 'month' => $month, 'year' => $year];
    $isProfit = $totals['profit'] >= 0;
    $profitClass = $isProfit ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400';
@endphp

<div class="flex flex-wrap items-center gap-2">
    <a href="{{ route('farmer.insights') }}" class="{{ request()->routeIs('farmer.entries.*') ? 'btn-ghost btn-sm' : 'btn-primary btn-sm' }}">Sales overview</a>
    <a href="{{ route('farmer.entries.index') }}" class="{{ request()->routeIs('farmer.entries.*') ? 'btn-primary btn-sm' : 'btn-ghost btn-sm' }}">Profit &amp; Loss</a>
</div>

<p class="mt-4 text-sm text-stone-500 dark:text-stone-400">Track what you spend and earn per product — add a record for each batch you buy and sell.</p>

{{-- Summary cards --}}
<div class="mt-6 grid grid-cols-2 gap-4 xl:grid-cols-4">
    <div class="card card-hover p-5">
        <p class="text-xs font-semibold uppercase tracking-wide text-stone-500 dark:text-stone-400">Total cost</p>
        <p class="mt-3 font-display text-2xl font-semibold text-stone-800 dark:text-stone-100 sm:text-3xl">${{ number_format($totals['cost'], 2) }}</p>
    </div>
    <div class="card card-hover p-5">
        <p class="text-xs font-semibold uppercase tracking-wide text-stone-500 dark:text-stone-400">Total sales</p>
        <p class="mt-3 font-display text-2xl font-semibold text-stone-800 dark:text-stone-100 sm:text-3xl">${{ number_format($totals['sales'], 2) }}</p>
    </div>
    <div class="card card-hover p-5">
        <p class="text-xs font-semibold uppercase tracking-wide text-stone-500 dark:text-stone-400">{{ $isProfit ? 'Net profit' : 'Net loss' }}</p>
        <p class="mt-3 font-display text-2xl font-semibold {{ $profitClass }} sm:text-3xl">
            {{ $isProfit ? '+' : '−' }}${{ number_format(abs($totals['profit']), 2) }}
        </p>
    </div>
    <div class="card card-hover p-5">
        <p class="text-xs font-semibold uppercase tracking-wide text-stone-500 dark:text-stone-400">Records</p>
        <p class="mt-3 font-display text-2xl font-semibold text-stone-800 dark:text-stone-100 sm:text-3xl">{{ $totals['count'] }}</p>
        <p class="mt-1 text-xs text-stone-400 dark:text-stone-500">{{ $periodLabel }}</p>
    </div>
</div>

{{-- Add record + filters --}}
<div class="mt-6 grid gap-6 lg:grid-cols-5">
    <div class="card p-5 lg:col-span-2">
        <h2 class="font-display text-lg font-semibold text-stone-800 dark:text-stone-100">Add a record</h2>
        <form method="POST" action="{{ route('farmer.entries.store') }}" class="mt-4 space-y-4"
              x-data="{ q: '', cp: '', sp: '' }">
            @csrf
            <div>
                <label for="product_name" class="input-label">Product name <span class="text-red-500">*</span></label>
                <input type="text" id="product_name" name="product_name" required maxlength="150" class="input"
                       value="{{ old('product_name') }}" placeholder="e.g. Tomatoes">
                @error('product_name') <p class="input-error">{{ $message }}</p> @enderror
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label for="quantity" class="input-label">Quantity <span class="text-red-500">*</span></label>
                    <input type="number" id="quantity" name="quantity" required min="0.01" step="0.01" max="999999" class="input"
                           value="{{ old('quantity') }}" x-model="q" placeholder="0">
                    @error('quantity') <p class="input-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="cost_price" class="input-label">Cost price <span class="text-stone-400">(kharida)</span> <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm font-semibold text-stone-400">$</span>
                        <input type="number" id="cost_price" name="cost_price" required min="0" step="0.01" max="999999" class="input pl-8"
                               value="{{ old('cost_price') }}" x-model="cp" placeholder="0.00">
                    </div>
                    @error('cost_price') <p class="input-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="selling_price" class="input-label">Selling price <span class="text-stone-400">(becha)</span> <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm font-semibold text-stone-400">$</span>
                        <input type="number" id="selling_price" name="selling_price" required min="0" step="0.01" max="999999" class="input pl-8"
                               value="{{ old('selling_price') }}" x-model="sp" placeholder="0.00">
                    </div>
                    @error('selling_price') <p class="input-error">{{ $message }}</p> @enderror
                </div>
            </div>
            <div>
                <label for="entry_date" class="input-label">Date <span class="text-red-500">*</span></label>
                <input type="date" id="entry_date" name="entry_date" required max="{{ now()->toDateString() }}" class="input sm:w-56"
                       value="{{ old('entry_date', $selectedDate ?? now()->toDateString()) }}">
                @error('entry_date') <p class="input-error">{{ $message }}</p> @enderror
            </div>

            <div class="rounded-xl bg-leaf-50 p-4 text-sm dark:bg-leaf-900/50" x-show="q && cp && sp" x-cloak>
                <div class="flex items-center justify-between gap-2 text-stone-600 dark:text-stone-300">
                    <span>Total cost</span><span class="font-semibold">$<span x-text="(Number(cp) * Number(q)).toFixed(2)"></span></span>
                </div>
                <div class="mt-1 flex items-center justify-between gap-2 text-stone-600 dark:text-stone-300">
                    <span>Total sales</span><span class="font-semibold">$<span x-text="(Number(sp) * Number(q)).toFixed(2)"></span></span>
                </div>
                <div class="mt-1 flex items-center justify-between gap-2 font-semibold"
                     :class="Number(sp) * Number(q) - Number(cp) * Number(q) >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                    <span x-text="Number(sp) * Number(q) - Number(cp) * Number(q) >= 0 ? 'Profit' : 'Loss'"></span>
                    <span>$<span x-text="Math.abs(Number(sp) * Number(q) - Number(cp) * Number(q)).toFixed(2)"></span></span>
                </div>
            </div>

            <button type="submit" class="btn-primary w-full sm:w-auto">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                Add record
            </button>
        </form>
    </div>

    <div class="card p-5 lg:col-span-3">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="font-display text-lg font-semibold text-stone-800 dark:text-stone-100">Period</h2>
            <div class="flex gap-1.5">
                <a href="{{ route('farmer.entries.index', ['mode' => 'month']) }}"
                   class="rounded-lg px-3 py-1.5 text-xs font-semibold transition {{ $mode === 'month' ? 'bg-leaf-600 text-white' : 'bg-stone-100 text-stone-600 hover:bg-leaf-50 dark:bg-leaf-800/60 dark:text-stone-300' }}">Month</a>
                <a href="{{ route('farmer.entries.index', ['mode' => 'range']) }}"
                   class="rounded-lg px-3 py-1.5 text-xs font-semibold transition {{ $mode === 'range' ? 'bg-leaf-600 text-white' : 'bg-stone-100 text-stone-600 hover:bg-leaf-50 dark:bg-leaf-800/60 dark:text-stone-300' }}">Custom range</a>
            </div>
        </div>

        @if($mode === 'range')
            <form method="GET" action="{{ route('farmer.entries.index') }}" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
                <input type="hidden" name="mode" value="range">
                <div class="flex-1">
                    <label for="from" class="input-label">From</label>
                    <input type="date" id="from" name="from" value="{{ $from }}" max="{{ now()->toDateString() }}" class="input">
                </div>
                <div class="flex-1">
                    <label for="to" class="input-label">To</label>
                    <input type="date" id="to" name="to" value="{{ $to }}" max="{{ now()->toDateString() }}" class="input">
                </div>
                <button type="submit" class="btn-secondary shrink-0">Apply range</button>
            </form>
            <p class="mt-3 text-xs text-stone-400 dark:text-stone-500">Showing records between <span class="font-semibold">{{ $periodLabel }}</span>.</p>
        @else
            <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
                <form method="GET" action="{{ route('farmer.entries.index') }}" class="flex flex-1 flex-col gap-3 sm:flex-row sm:items-end">
                    <input type="hidden" name="mode" value="month">
                    <div class="flex-1">
                        <label for="month" class="input-label">Month</label>
                        <select id="month" name="month" class="input">
                            @foreach([1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'] as $m => $label)
                                <option value="{{ $m }}" @selected($month === $m)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1">
                        <label for="year" class="input-label">Year</label>
                        <select id="year" name="year" class="input">
                            @for($y = (int) now()->format('Y') + 1; $y >= (int) now()->format('Y') - 6; $y--)
                                <option value="{{ $y }}" @selected($year === $y)>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <button type="submit" class="btn-secondary shrink-0">Apply</button>
                </form>
            </div>

            {{-- Calendar --}}
            <div class="mt-5">
                <div class="flex items-center justify-between gap-2">
                    <a href="{{ route('farmer.entries.index', ['mode' => 'month', 'month' => $prevMonth->month, 'year' => $prevMonth->year]) }}" aria-label="Previous month" class="btn-ghost btn-sm">←</a>
                    <p class="font-display text-lg font-semibold text-leaf-950 dark:text-cream-50">{{ $monthCarbon->format('F Y') }}</p>
                    <a href="{{ route('farmer.entries.index', ['mode' => 'month', 'month' => $nextMonth->month, 'year' => $nextMonth->year]) }}" aria-label="Next month" class="btn-ghost btn-sm">→</a>
                </div>

                <div class="mt-3 grid grid-cols-7 gap-1 text-center text-[11px] font-semibold uppercase tracking-wide text-stone-400 dark:text-stone-500">
                    @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $dow)
                        <span>{{ $dow }}</span>
                    @endforeach
                </div>
                <div class="mt-1 grid grid-cols-7 gap-1">
                    @foreach($weeks as $week)
                        @foreach($week as $day)
                            @php
                                $dayKey = $day['date']->toDateString();
                                $isSelected = $selectedDate === $dayKey;
                                $isToday = $dayKey === now()->toDateString();
                                $dayProfit = $day['profit'];
                            @endphp
                            <a href="{{ route('farmer.entries.index', $isSelected ? $baseQuery : $baseQuery + ['date' => $dayKey]) }}"
                               class="relative flex min-h-[3.4rem] flex-col items-center justify-start rounded-lg border p-1 text-center transition {{ $day['in_month'] ? '' : 'opacity-40' }} {{ $isSelected ? 'border-leaf-500 ring-2 ring-leaf-400/50' : 'border-stone-200/70 hover:border-leaf-300 dark:border-leaf-800/70 dark:hover:border-leaf-600' }} {{ $isToday && !$isSelected ? '!border-leaf-400' : '' }}">
                                <span class="text-[11px] font-semibold text-stone-500 dark:text-stone-400">{{ $day['date']->day }}</span>
                                @if($dayProfit !== null)
                                    <span class="mt-0.5 text-[10px] font-bold leading-tight {{ $dayProfit >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                        {{ $dayProfit >= 0 ? '+' : '−' }}${{ number_format(abs($dayProfit), 0) }}
                                    </span>
                                @endif
                            </a>
                        @endforeach
                    @endforeach
                </div>
                <p class="mt-2 text-xs text-stone-400 dark:text-stone-500">Days show net profit (green) or loss (red). Click a day to see its records.</p>
            </div>
        @endif
    </div>
</div>

{{-- Records table --}}
<div class="card mt-6">
    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-stone-100 px-5 py-4 dark:border-leaf-800/80">
        <h2 class="font-display text-lg font-semibold text-stone-800 dark:text-stone-100">Records — {{ $periodLabel }}</h2>
        @if($selectedDate)
            <a href="{{ route('farmer.entries.index', $mode === 'month' ? $baseQuery : ['mode' => 'range', 'from' => $from, 'to' => $to]) }}"
               class="badge-amber">Showing {{ \Illuminate\Support\Carbon::parse($selectedDate)->format('j M Y') }} only — clear ✕</a>
        @endif
    </div>
    @if($records->count())
        <div class="overflow-x-auto">
            <table class="w-full min-w-[46rem] text-left text-sm">
                <thead>
                    <tr class="text-xs uppercase tracking-wide text-stone-400 dark:text-stone-500">
                        <th class="px-5 py-3 font-semibold">Date</th>
                        <th class="px-3 py-3 font-semibold">Product</th>
                        <th class="px-3 py-3 text-right font-semibold">Qty</th>
                        <th class="px-3 py-3 text-right font-semibold">Cost / unit</th>
                        <th class="px-3 py-3 text-right font-semibold">Sold / unit</th>
                        <th class="px-3 py-3 text-right font-semibold">Total cost</th>
                        <th class="px-3 py-3 text-right font-semibold">Total sales</th>
                        <th class="px-5 py-3 text-right font-semibold">Profit / Loss</th>
                        <th class="px-3 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 dark:divide-leaf-800/60">
                    @foreach($records as $entry)
                        @php $entryProfit = $entry->profit(); @endphp
                        <tr class="transition-colors hover:bg-leaf-50/60 dark:hover:bg-leaf-800/30">
                            <td class="whitespace-nowrap px-5 py-3.5 text-stone-600 dark:text-stone-300">{{ $entry->entry_date->format('j M Y') }}</td>
                            <td class="px-3 py-3.5 font-semibold text-stone-800 dark:text-stone-100">{{ $entry->product_name }}</td>
                            <td class="px-3 py-3.5 text-right text-stone-600 dark:text-stone-300">{{ number_format($entry->quantity, 2) }}</td>
                            <td class="px-3 py-3.5 text-right text-stone-600 dark:text-stone-300">${{ number_format($entry->cost_price, 2) }}</td>
                            <td class="px-3 py-3.5 text-right text-stone-600 dark:text-stone-300">${{ number_format($entry->selling_price, 2) }}</td>
                            <td class="px-3 py-3.5 text-right text-stone-600 dark:text-stone-300">${{ number_format($entry->totalCost(), 2) }}</td>
                            <td class="px-3 py-3.5 text-right text-stone-600 dark:text-stone-300">${{ number_format($entry->totalSales(), 2) }}</td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-right font-semibold {{ $entryProfit >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ $entryProfit >= 0 ? '+' : '−' }}${{ number_format(abs($entryProfit), 2) }}
                            </td>
                            <td class="px-3 py-3.5 text-right">
                                <form method="POST" action="{{ route('farmer.entries.destroy', $entry) }}"
                                      onsubmit="return confirm('Delete this record?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" aria-label="Delete record" class="grid h-8 w-8 place-items-center rounded-lg text-stone-400 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/10 dark:hover:text-red-400">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 7.5v12a1.5 1.5 0 001.5 1.5h9a1.5 1.5 0 001.5-1.5v-12M4.5 7.5h15M9.75 7.5V4.875A1.125 1.125 0 0110.875 3.75h2.25a1.125 1.125 0 011.125 1.125V7.5M10.5 11v6m3-6v6"/></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t border-stone-200 dark:border-leaf-800">
                        <td colspan="5" class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wide text-stone-400 dark:text-stone-500">Period totals</td>
                        <td class="px-3 py-3.5 text-right font-semibold text-stone-800 dark:text-stone-100">${{ number_format($totals['cost'], 2) }}</td>
                        <td class="px-3 py-3.5 text-right font-semibold text-stone-800 dark:text-stone-100">${{ number_format($totals['sales'], 2) }}</td>
                        <td class="whitespace-nowrap px-5 py-3.5 text-right font-bold {{ $profitClass }}">{{ $isProfit ? '+' : '−' }}${{ number_format(abs($totals['profit']), 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @else
        <div class="px-6 py-14 text-center">
            <svg class="mx-auto h-10 w-10 text-stone-300 dark:text-stone-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p class="mt-3 text-sm font-semibold text-leaf-950 dark:text-cream-50">No records for this period</p>
            <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">Add your first record with the form above to start tracking profit and loss.</p>
        </div>
    @endif
</div>
@endsection
