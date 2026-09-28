@extends('layouts.farmer')

@section('title', 'Approval Pending')

@section('content')
@php
    $user = auth()->user();
@endphp

<div class="page-hero relative overflow-hidden rounded-3xl">
    <div class="hero-overlay absolute inset-0"></div>
    <div class="relative max-w-3xl mx-auto px-6 py-16 sm:py-20 text-center">
        <span class="badge bg-white/10 text-amber-200 border border-amber-200/30 backdrop-blur animate-fade-up">Application under review</span>
        <h2 class="mt-5 font-display text-3xl sm:text-4xl font-semibold text-white leading-tight animate-fade-up" style="animation-delay:.08s">
            Thanks, {{ $user->name }} — your stall is almost ready.
        </h2>
        <p class="mt-4 text-leaf-100/85 leading-relaxed max-w-xl mx-auto animate-fade-up" style="animation-delay:.16s">
            Your farmer account <strong class="text-white">{{ $user->farmer?->stall_name ?? '' }}</strong> is currently
            <strong class="text-amber-200">pending admin approval</strong>. Our team reviews every stall so customers can
            trust the farmers on {{ settings('site_name', 'MarketLink') }}. You will be able to list products and receive
            orders as soon as you are approved.
        </p>
    </div>
</div>

<div class="mt-10 max-w-3xl mx-auto">
    <div class="grid gap-4 sm:grid-cols-3">
        @php
            $steps = [
                ['n' => '1', 't' => 'Application received', 'd' => 'Your stall details and markets were submitted successfully.', 'done' => true],
                ['n' => '2', 't' => 'Admin review', 'd' => 'We are verifying your stall information. This usually takes a short while.', 'done' => false],
                ['n' => '3', 't' => 'Go live', 'd' => 'Once approved, your stall appears publicly and you can list your weekly stock.', 'done' => false],
            ];
        @endphp
        @foreach ($steps as $s)
            <div class="card card-hover p-5">
                <span class="grid h-9 w-9 place-items-center rounded-xl text-sm font-bold {{ $s['done'] ? 'bg-leaf-600 text-white' : 'bg-leaf-100 text-leaf-700 dark:bg-leaf-800 dark:text-leaf-300' }}">{{ $s['n'] }}</span>
                <h3 class="mt-3 font-display text-base font-semibold text-stone-800 dark:text-stone-100">{{ $s['t'] }}</h3>
                <p class="mt-1.5 text-xs leading-relaxed text-stone-600 dark:text-stone-300">{{ $s['d'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="card mt-8 p-6 sm:p-8">
        <div class="flex flex-col items-start gap-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="font-display text-lg font-semibold text-stone-800 dark:text-stone-100">While you wait</h3>
                <p class="mt-1 text-sm text-stone-600 dark:text-stone-300">
                    You can keep setting up your
                    <a href="{{ route('farmer.profile.edit') }}" class="font-semibold text-leaf-700 underline decoration-leaf-300 underline-offset-2 hover:text-leaf-800 dark:text-leaf-300 dark:hover:text-leaf-200">stall profile</a>
                    (description, markets, pickup windows and location), or head back to the
                    <a href="{{ route('home') }}" class="font-semibold text-leaf-700 underline decoration-leaf-300 underline-offset-2 hover:text-leaf-800 dark:text-leaf-300 dark:hover:text-leaf-200">public site</a>.
                </p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-secondary shrink-0">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6A2.25 2.25 0 005.25 5.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 12h9m0 0l-3-3m3 3l-3 3"/></svg>
                    Sign out
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
