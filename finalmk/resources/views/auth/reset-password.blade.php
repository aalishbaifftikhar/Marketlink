@extends('layouts.auth')

@section('title', 'Reset password')

@section('content')
    <div class="text-center mb-6">
        <h2 class="font-display text-2xl font-semibold text-leaf-950 dark:text-cream-50">Choose a new password</h2>
        <p class="mt-1.5 text-sm text-stone-500 dark:text-stone-400">Resetting the password for <span class="font-semibold text-leaf-800 dark:text-leaf-200">{{ $request->email }}</span>.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4" novalidate x-data="passwordRules">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <input type="hidden" name="email" value="{{ $request->email }}">

        <div>
            <label for="password" class="input-label">New password</label>
            <input id="password" name="password" type="password" required autofocus x-model="password" autocomplete="new-password"
                class="input @error('password') !border-red-400 dark:!border-red-500 @enderror" placeholder="••••••••">
            @error('password') <p class="input-error">{{ $message }}</p> @enderror
        </div>

        <x-password-rules />

        <div>
            <label for="password_confirmation" class="input-label">Confirm new password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required x-model="confirmation" autocomplete="new-password"
                class="input" placeholder="••••••••">
            <p class="mt-1 text-xs" x-cloak x-show="confirmation !== ''"
                :class="confirmationMatches ? 'text-leaf-700 dark:text-leaf-300' : 'text-red-600 dark:text-red-400'"
                x-text="confirmationMatches ? 'Passwords match' : 'Passwords do not match yet'"></p>
        </div>

        <button type="submit" class="btn-primary w-full !py-3" x-bind:disabled="!ready">Reset password</button>
    </form>

    <p class="mt-6 text-center text-sm text-stone-500 dark:text-stone-400">
        <a href="{{ route('login') }}" class="font-semibold text-leaf-700 transition hover:text-leaf-800 dark:text-leaf-300 dark:hover:text-leaf-200">Back to sign in</a>
    </p>
@endsection
