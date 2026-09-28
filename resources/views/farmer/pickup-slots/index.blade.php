@extends('layouts.farmer')

@section('title', 'Pickup Slots')

@section('content')
@php
    $week = [1, 2, 3, 4, 5, 6, 0]; // Monday-first display
    $today = now()->dayOfWeek; // 0 (Sunday) – 6 (Saturday)
@endphp

<p class="text-sm text-stone-500 dark:text-stone-400">These are the weekly pickup windows customers can choose when pre-ordering from your stall. Disabled windows stay visible but cannot be selected.</p>

{{-- Add slot --}}
<form method="POST" action="{{ route('farmer.slots.store') }}" class="card mt-6 p-5 sm:p-6">
    @csrf
    <h2 class="font-display text-lg font-semibold text-stone-800 dark:text-stone-100">Add a pickup window</h2>
    <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <label for="day_of_week" class="input-label">Day <span class="text-red-500">*</span></label>
            <select id="day_of_week" name="day_of_week" class="input" required>
                @foreach ($days as $number => $label)
                    <option value="{{ $number }}" @selected((string) old('day_of_week') === (string) $number)>{{ $label }}</option>
                @endforeach
            </select>
            <x-input-error field="day_of_week" />
        </div>
        <div>
            <label for="start_time" class="input-label">Starts <span class="text-red-500">*</span></label>
            <input type="time" id="start_time" name="start_time" class="input" required value="{{ old('start_time') }}">
            <x-input-error field="start_time" />
        </div>
        <div>
            <label for="end_time" class="input-label">Ends <span class="text-red-500">*</span></label>
            <input type="time" id="end_time" name="end_time" class="input" required value="{{ old('end_time') }}">
            <x-input-error field="end_time" />
        </div>
        <div class="flex items-end">
            <button type="submit" class="btn-primary w-full sm:w-auto">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                Add window
            </button>
        </div>
    </div>
</form>

{{-- Weekly grid --}}
<div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-7">
    @foreach ($week as $dayNumber)
        @php $daySlots = $slotsByDay->get($dayNumber) ?? collect(); @endphp
        <div class="card flex flex-col {{ $dayNumber === $today ? 'ring-2 ring-leaf-500/60 dark:ring-leaf-400/60' : '' }}">
            <div class="flex items-center justify-between gap-2 border-b border-stone-100 px-4 py-3 dark:border-leaf-800/80">
                <h2 class="font-display text-sm font-semibold text-stone-800 dark:text-stone-100">{{ $days[$dayNumber] ?? 'Day' }}</h2>
                @if ($dayNumber === $today)
                    <span class="badge-green">Today</span>
                @endif
            </div>

            <div class="flex-1 space-y-2 p-3">
                @forelse ($daySlots as $slot)
                    <div class="rounded-xl border border-stone-200 bg-cream-50 p-3 dark:border-leaf-800 dark:bg-leaf-900/40 {{ $slot->is_active ? '' : 'opacity-60' }}">
                        <p class="flex items-center justify-between gap-2 text-sm font-semibold text-stone-800 dark:text-stone-100">
                            <span>{{ $slot->label() }}</span>
                            @if ($slot->is_active)
                                <span class="badge-green">Active</span>
                            @else
                                <span class="badge-stone">Off</span>
                            @endif
                        </p>
                        <div class="mt-2 flex items-center gap-1.5">
                            <form method="POST" action="{{ route('farmer.slots.toggle', $slot) }}">
                                @csrf
                                <button type="submit" class="btn-ghost btn-sm">
                                    @if ($slot->is_active)
                                        Disable
                                    @else
                                        Enable
                                    @endif
                                </button>
                            </form>
                            <form method="POST" action="{{ route('farmer.slots.destroy', $slot) }}"
                                  onsubmit="return confirm('Remove the {{ $slot->label() }} pickup window on {{ $slot->dayName() }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-ghost btn-sm !text-red-600 hover:!bg-red-50 dark:!text-red-400 dark:hover:!bg-red-500/10">Delete</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="rounded-xl border border-dashed border-stone-200 px-3 py-6 text-center text-xs text-stone-400 dark:border-leaf-800 dark:text-stone-500">
                        No pickup windows
                    </p>
                @endforelse
            </div>
        </div>
    @endforeach
</div>

@if ($slotsByDay->isEmpty())
    <div class="card mt-6 px-6 py-12 text-center">
        <span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-leaf-100 text-leaf-600 dark:bg-leaf-800 dark:text-leaf-300">
            <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 100-18 9 9 0 000 18zM12 7v5l3 2"/></svg>
        </span>
        <h3 class="mt-5 font-display text-lg font-semibold text-stone-800 dark:text-stone-100">No pickup windows yet</h3>
        <p class="mx-auto mt-2 max-w-sm text-sm text-stone-500 dark:text-stone-400">Add your first window above so customers know when they can collect their pre-orders from you.</p>
    </div>
@endif
@endsection
