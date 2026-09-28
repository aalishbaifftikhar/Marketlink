<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', settings('site_name', 'MarketLink')) — {{ settings('site_tagline', 'Fresh from local farms') }}</title>
    <meta name="description" content="@yield('meta_description', settings('site_tagline'))">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    {{-- Session flashes surfaced via JS toasts --}}
    @if (session('success')) <meta name="flash-success" content="{{ e(session('success')) }}"> @endif
    @if (session('error')) <meta name="flash-error" content="{{ e(session('error')) }}"> @endif
    @if (session('info')) <meta name="flash-info" content="{{ e(session('info')) }}"> @endif

    {{-- Hero background image (provided asset; phones get the 512px variant) --}}
    <style>:root {
        --hero-image: url('{{ asset('images/products-hero-bg.png') }}');
        --hero-image-sm: url('{{ asset('images/products-hero-bg-sm.png') }}');
    }</style>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen flex flex-col antialiased">
    @include('partials.header')

    <main class="flex-1">
        {{-- Server-side flash (noscript fallback + instant render) --}}
        @if (session('success') || session('error') || session('info'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
                @if (session('success')) <div class="badge-green px-4 py-2.5 rounded-xl justify-center">{{ session('success') }}</div> @endif
                @if (session('error')) <div class="badge-red px-4 py-2.5 rounded-xl justify-center">{{ session('error') }}</div> @endif
                @if (session('info')) <div class="badge-blue px-4 py-2.5 rounded-xl justify-center">{{ session('info') }}</div> @endif
            </div>
        @endif

        @yield('content')
    </main>

    @include('partials.footer')

    {{-- AI assistant widget (available everywhere for customers & guests) --}}
    @include('partials.assistant')

    @stack('scripts')
</body>
</html>
