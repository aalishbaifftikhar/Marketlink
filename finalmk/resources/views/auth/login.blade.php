@extends('layouts.auth')
@section('title', 'Sign In')

@section('content')
<h1 class="font-display text-3xl font-semibold text-leaf-900 dark:text-leaf-100">Welcome back</h1>
<p class="mt-2 text-sm text-stone-500 dark:text-stone-400">Sign in to your {{ settings('site_name', 'MarketLink') }} account.</p>

<a href="{{ route('google.redirect') }}" class="mt-6 btn-secondary w-full !py-3 !justify-center">
    <svg class="w-5 h-5" viewBox="0 0 24 24"><path fill="#4285F4" d="M23.5 12.3c0-.9-.1-1.5-.3-2.2H12v4.3h6.5c-.1 1.1-.8 2.7-2.4 3.8l3.7 2.9c2.3-2.1 3.7-5.2 3.7-8.8z"/><path fill="#34A853" d="M12 24c3.2 0 5.9-1.1 7.9-2.9l-3.7-2.9c-1 .7-2.4 1.2-4.2 1.2-3.2 0-5.9-2.1-6.8-5.1L1.3 17.2C3.3 21.2 7.3 24 12 24z"/><path fill="#FBBC05" d="M5.2 14.3c-.2-.7-.4-1.5-.4-2.3s.1-1.6.4-2.3L1.3 6.8C.5 8.4 0 10.1 0 12s.5 3.6 1.3 5.2l3.9-2.9z"/><path fill="#EA4335" d="M12 4.7c1.8 0 3 .8 3.7 1.4l3.3-3.2C17.9 1.1 15.2 0 12 0 7.3 0 3.3 2.8 1.3 6.8l3.9 2.9c.9-2.9 3.6-5 6.8-5z"/></svg>
    Continue with Google
</a>

<div class="my-6 flex items-center gap-3">
    <span class="h-px flex-1 bg-stone-200 dark:bg-leaf-800"></span>
    <span class="text-xs text-stone-400">or with email</span>
    <span class="h-px flex-1 bg-stone-200 dark:bg-leaf-800"></span>
</div>

<form method="POST" action="{{ route('login') }}" class="space-y-5">
    @csrf
    <div>
        <label for="email" class="input-label">Email Address</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" class="input" placeholder="you@example.com">
        @error('email') <p class="input-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <div class="flex items-center justify-between mb-1.5">
            <label for="password" class="input-label !mb-0">Password</label>
            <a href="{{ route('password.request') }}" class="text-xs font-semibold text-leaf-700 dark:text-leaf-300 hover:underline">Forgot password?</a>
        </div>
        <input id="password" type="password" name="password" required autocomplete="current-password" class="input" placeholder="••••••••">
        @error('password') <p class="input-error">{{ $message }}</p> @enderror
    </div>

    <div class="flex items-center">
        <input id="remember" type="checkbox" name="remember" class="w-4 h-4 rounded border-stone-300 text-leaf-600 focus:ring-leaf-400">
        <label for="remember" class="ml-2 text-sm text-stone-600 dark:text-stone-300">Remember me</label>
    </div>

    <button type="submit" class="btn-primary w-full !py-3">Sign In</button>
</form>

<p class="mt-6 text-center text-sm text-stone-500 dark:text-stone-400">
    New to {{ settings('site_name', 'MarketLink') }}?
    <a href="{{ route('register') }}" class="font-semibold text-leaf-700 dark:text-leaf-300 hover:underline">Create an account</a>
</p>
@endsection
