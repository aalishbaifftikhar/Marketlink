<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Authentication') — {{ settings('site_name', 'MarketLink') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @if (session('success')) <meta name="flash-success" content="{{ e(session('success')) }}"> @endif
    @if (session('error')) <meta name="flash-error" content="{{ e(session('error')) }}"> @endif
    @if (session('status')) <meta name="flash-info" content="{{ e(session('status')) }}"> @endif
    <style>:root { --hero-image: url('{{ asset('images/products-hero-bg.png') }}'); }</style>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen antialiased">
    <div class="min-h-screen lg:grid lg:grid-cols-2">
        {{-- Brand / image panel --}}
        <div class="hero-bg relative hidden lg:flex flex-col justify-between p-12">
            <div class="hero-overlay absolute inset-0"></div>
            <a href="{{ route('home') }}" class="relative flex items-center gap-2.5">
                <span class="w-10 h-10 rounded-xl bg-white/15 backdrop-blur text-white grid place-items-center border border-white/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c4 3 6 6.5 6 10a6 6 0 11-12 0c0-3.5 2-7 6-10z"/></svg>
                </span>
                <span class="font-display font-semibold text-xl text-white">{{ settings('site_name', 'MarketLink') }}</span>
            </a>
            <div class="relative">
                <h2 class="font-display text-4xl font-semibold text-white leading-tight max-w-md">Fresh local produce, pre-ordered from the people who grow it.</h2>
                <p class="mt-4 text-leaf-100/80 max-w-sm">Join your community of local farmers and food lovers. Browse weekly stock, reserve for pickup, and pay in person.</p>
            </div>
            <p class="relative text-leaf-100/60 text-xs">&copy; {{ date('Y') }} {{ settings('site_name', 'MarketLink') }}</p>
        </div>

        {{-- Form panel --}}
        <div class="flex flex-col min-h-screen lg:min-h-0 bg-cream-50 dark:bg-leaf-950">
            <div class="flex items-center justify-between p-6">
                <a href="{{ route('home') }}" class="lg:hidden flex items-center gap-2">
                    <span class="w-9 h-9 rounded-xl bg-leaf-600 text-white grid place-items-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c4 3 6 6.5 6 10a6 6 0 11-12 0c0-3.5 2-7 6-10z"/></svg>
                    </span>
                    <span class="font-display font-semibold text-lg text-leaf-900 dark:text-leaf-100">{{ settings('site_name', 'MarketLink') }}</span>
                </a>
                <div class="ml-auto">
                    <button onclick="toggleTheme()" aria-label="Toggle theme" class="w-10 h-10 grid place-items-center rounded-xl text-stone-600 dark:text-stone-300 hover:bg-leaf-50 dark:hover:bg-leaf-900/70">
                        <svg data-theme-icon-sun class="w-5 h-5 hidden" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 17a5 5 0 100-10 5 5 0 000 10zM12 2v2m0 16v2M4.9 4.9l1.4 1.4m11.4 11.4 1.4 1.4M2 12h2m16 0h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
                        <svg data-theme-icon-moon class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12.8A9 9 0 1111.2 3 7 7 0 0021 12.8z"/></svg>
                    </button>
                </div>
            </div>

            <div class="flex-1 flex items-center justify-center px-6 pb-12">
                <div class="w-full max-w-md">
                    @if (session('status'))
                        <div class="badge-green px-4 py-3 rounded-xl justify-center mb-6 text-center">{{ session('status') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="badge-red px-4 py-3 rounded-xl mb-6 text-center">{{ $errors->first() }}</div>
                    @endif
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
