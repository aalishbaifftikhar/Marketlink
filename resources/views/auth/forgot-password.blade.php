@extends('layouts.auth')

@section('title', 'Forgot password')

@section('content')
    <div class="text-center mb-6">
        <h2 class="font-display text-2xl font-semibold text-leaf-950 dark:text-cream-50">Forgot your password?</h2>
        <p class="mt-1.5 text-sm text-stone-500 dark:text-stone-400">Enter your email and we'll send you a 6-digit reset code.</p>
    </div>

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4" novalidate>
        @csrf
        <div>
            <label for="email" class="input-label">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                class="input @error('email') !border-red-400 dark:!border-red-500 @enderror" placeholder="you@example.com">
            @error('email') <p class="input-error">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="btn-primary w-full !py-3">Email me a reset code</button>
    </form>

    <p class="mt-6 text-center text-sm text-stone-500 dark:text-stone-400">
        Remembered it after all?
        <a href="{{ route('login') }}" class="font-semibold text-leaf-700 transition hover:text-leaf-800 dark:text-leaf-300 dark:hover:text-leaf-200">Back to sign in</a>
    </p>
@endsection
