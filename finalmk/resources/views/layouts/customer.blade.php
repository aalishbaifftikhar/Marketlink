<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · {{ settings('site_name', 'MarketLink') }}</title>
    <meta name="description" content="@yield('meta_description', settings('site_tagline', 'Fresh from local farms'))">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    {{-- Session flashes surfaced via JS toasts --}}
    @if (session('success')) <meta name="flash-success" content="{{ e(session('success')) }}"> @endif
    @if (session('error')) <meta name="flash-error" content="{{ e(session('error')) }}"> @endif
    @if (session('info')) <meta name="flash-info" content="{{ e(session('info')) }}"> @endif

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen antialiased" x-data="{ sidebar: false }">
    @php
        $customerNav = [
            ['label' => 'Dashboard', 'route' => 'customer.dashboard', 'match' => 'customer.dashboard', 'icon' => 'M3 12l9-9 9 9M5 10v10a1 1 0 001 1h4v-6h4v6h4a1 1 0 001-1V10'],
            ['label' => 'Browse Products', 'route' => 'products.index', 'match' => 'products.index', 'icon' => 'M5 8h14l-1.2 12H6.2L5 8zm3 0V6a4 4 0 118 0v2'],
            ['label' => 'My Orders', 'route' => 'customer.orders.index', 'match' => 'customer.orders.*', 'icon' => 'M9 4h6v2h3a1 1 0 011 1v13a1 1 0 01-1 1H7a1 1 0 01-1-1V7a1 1 0 011-1h3V4zM8 12h8m-8 4h5'],
            ['label' => 'Favorites', 'route' => 'customer.favorites', 'match' => 'customer.favorites', 'icon' => 'M4.3 6.3A5 5 0 0112 6a5 5 0 017.7.3c1.9 1.9 2 4.9.3 7L12 20.5l-8-7.2a5.3 5.3 0 01.3-7z'],
            ['label' => 'Notifications', 'route' => 'notifications.index', 'match' => 'notifications.index', 'icon' => 'M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'],
            ['label' => 'AI Assistant', 'route' => 'customer.assistant', 'match' => 'customer.assistant', 'icon' => 'M8 10h8M8 14h5M21 12a9 9 0 01-13.2 7.9L3 21l1.1-4.8A9 9 0 1121 12z'],
            ['label' => 'Profile', 'route' => 'profile.edit', 'match' => 'profile.edit', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM4.5 20a7.5 7.5 0 0115 0'],
        ];

        $user = auth()->user();
        $unreadCount = $user->unreadNotifications()->count();
        $cartGroups = \App\Http\Controllers\CartController::grouped(request());
        $cartCount = collect($cartGroups)->sum(fn ($g) => count($g['items']));
    @endphp

    {{-- ======================= DESKTOP SIDEBAR ======================= --}}
    <aside class="hidden lg:flex fixed inset-y-0 left-0 w-64 flex-col bg-white dark:bg-leaf-900/70 border-r border-stone-200/80 dark:border-leaf-800/80 z-40">
        <div class="h-16 flex items-center gap-2.5 px-5 border-b border-stone-200/80 dark:border-leaf-800/80 shrink-0">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
                <span class="w-9 h-9 rounded-xl bg-leaf-600 text-white grid place-items-center shadow-soft group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c4 3 6 6.5 6 10a6 6 0 11-12 0c0-3.5 2-7 6-10z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 21c0-4 1.5-7 4-9"/></svg>
                </span>
                <span class="font-display font-semibold text-lg text-leaf-900 dark:text-leaf-100">{{ settings('site_name', 'MarketLink') }}</span>
            </a>
        </div>

        <nav class="flex-1 overflow-y-auto p-3 space-y-1">
            @foreach ($customerNav as $item)
                <a href="{{ route($item['route']) }}"
                    class="{{ request()->routeIs($item['match']) ? 'nav-link-active' : 'nav-link' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/></svg>
                    <span class="flex-1">{{ $item['label'] }}</span>
                    @if ($item['label'] === 'Notifications' && $unreadCount)
                        <span class="min-w-[20px] h-5 px-1.5 rounded-full bg-red-500 text-white text-[10px] font-bold grid place-items-center">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                    @endif
                </a>
            @endforeach
        </nav>

        <div class="p-3 border-t border-stone-200/80 dark:border-leaf-800/80 shrink-0">
            <div class="flex items-center gap-3 px-2 py-2">
                <img src="{{ $user->avatarUrl() }}" alt="" class="w-9 h-9 rounded-full object-cover ring-2 ring-leaf-200 dark:ring-leaf-700">
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-stone-800 dark:text-stone-100 truncate">{{ $user->name }}</p>
                    <p class="text-xs text-stone-500 dark:text-stone-400 truncate">Customer account</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" aria-label="Sign out" class="w-9 h-9 grid place-items-center rounded-xl text-stone-500 dark:text-stone-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/10 dark:hover:text-red-400 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 4h3a1 1 0 011 1v14a1 1 0 01-1 1h-3M10 17l5-5-5-5M15 12H3"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- ======================= MOBILE SLIDE-IN SIDEBAR ======================= --}}
    <div x-show="sidebar" x-cloak @click="sidebar = false" @keydown.escape.window="sidebar = false"
        class="fixed inset-0 z-40 bg-leaf-950/60 backdrop-blur-sm lg:hidden" style="display:none" aria-hidden="true"></div>

    <aside x-show="sidebar" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
        class="fixed inset-y-0 left-0 z-50 w-72 max-w-[85vw] flex flex-col bg-white dark:bg-leaf-900 border-r border-stone-200 dark:border-leaf-800 shadow-lift lg:hidden"
        style="display:none" aria-label="Menu">
        <div class="h-16 flex items-center justify-between px-5 border-b border-stone-200/80 dark:border-leaf-800/80 shrink-0">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                <span class="w-9 h-9 rounded-xl bg-leaf-600 text-white grid place-items-center shadow-soft">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c4 3 6 6.5 6 10a6 6 0 11-12 0c0-3.5 2-7 6-10z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 21c0-4 1.5-7 4-9"/></svg>
                </span>
                <span class="font-display font-semibold text-lg text-leaf-900 dark:text-leaf-100">{{ settings('site_name', 'MarketLink') }}</span>
            </a>
            <button @click="sidebar = false" aria-label="Close menu" class="w-10 h-10 grid place-items-center rounded-xl text-stone-500 dark:text-stone-400 hover:bg-leaf-50 dark:hover:bg-leaf-800/60">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto p-3 space-y-1">
            @foreach ($customerNav as $item)
                <a href="{{ route($item['route']) }}" @click="sidebar = false"
                    class="{{ request()->routeIs($item['match']) ? 'nav-link-active' : 'nav-link' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/></svg>
                    <span class="flex-1">{{ $item['label'] }}</span>
                    @if ($item['label'] === 'Notifications' && $unreadCount)
                        <span class="min-w-[20px] h-5 px-1.5 rounded-full bg-red-500 text-white text-[10px] font-bold grid place-items-center">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                    @endif
                </a>
            @endforeach
        </nav>

        <div class="p-3 border-t border-stone-200/80 dark:border-leaf-800/80 shrink-0">
            <a href="{{ route('profile.edit') }}" @click="sidebar = false" class="flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-leaf-50 dark:hover:bg-leaf-800/60 transition-colors">
                <img src="{{ $user->avatarUrl() }}" alt="" class="w-9 h-9 rounded-full object-cover ring-2 ring-leaf-200 dark:ring-leaf-700">
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-stone-800 dark:text-stone-100 truncate">{{ $user->name }}</p>
                    <p class="text-xs text-stone-500 dark:text-stone-400 truncate">{{ $user->email }}</p>
                </div>
            </a>
            <form method="POST" action="{{ route('logout') }}" class="mt-1">@csrf
                <button type="submit" class="nav-link w-full text-left text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 4h3a1 1 0 011 1v14a1 1 0 01-1 1h-3M10 17l5-5-5-5M15 12H3"/></svg>
                    Sign Out
                </button>
            </form>
        </div>
    </aside>

    {{-- ======================= MAIN COLUMN ======================= --}}
    <div class="lg:pl-64 flex flex-col min-h-screen">

        {{-- Topbar --}}
        <header class="sticky top-0 z-30 backdrop-blur-md bg-cream-50/85 dark:bg-leaf-950/85 border-b border-stone-200/70 dark:border-leaf-800/70">
            <div class="flex items-center gap-3 h-16 px-4 sm:px-6 lg:px-8">
                <button @click="sidebar = true" aria-label="Open menu" class="lg:hidden w-10 h-10 grid place-items-center rounded-xl text-stone-600 dark:text-stone-300 hover:bg-leaf-50 dark:hover:bg-leaf-900/70 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
                </button>

                <h1 class="font-display text-xl font-semibold text-leaf-950 dark:text-cream-50 truncate">@yield('title')</h1>

                <div class="ml-auto flex items-center gap-1.5 sm:gap-2">
                    <button onclick="toggleTheme()" aria-label="Toggle theme" class="w-10 h-10 grid place-items-center rounded-xl text-stone-600 dark:text-stone-300 hover:bg-leaf-50 dark:hover:bg-leaf-900/70 transition-colors">
                        <svg data-theme-icon-sun class="w-5 h-5 hidden" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 17a5 5 0 100-10 5 5 0 000 10z"/><path stroke-linecap="round" d="M12 2v2m0 16v2M4.9 4.9l1.4 1.4m11.4 11.4 1.4 1.4M2 12h2m16 0h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
                        <svg data-theme-icon-moon class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12.8A9 9 0 1111.2 3 7 7 0 0021 12.8z"/></svg>
                    </button>

                    <a href="{{ route('customer.cart.index') }}" aria-label="Basket" class="relative w-10 h-10 grid place-items-center rounded-xl text-stone-600 dark:text-stone-300 hover:bg-leaf-50 dark:hover:bg-leaf-900/70 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/></svg>
                        @if ($cartCount)
                            <span class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 rounded-full bg-leaf-600 text-white text-[10px] font-bold grid place-items-center">{{ $cartCount > 9 ? '9+' : $cartCount }}</span>
                        @endif
                    </a>

                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 pl-1.5 pr-2.5 py-1.5 rounded-xl hover:bg-leaf-50 dark:hover:bg-leaf-900/70 transition-colors">
                        <img src="{{ $user->avatarUrl() }}" alt="" class="w-8 h-8 rounded-full object-cover ring-2 ring-leaf-200 dark:ring-leaf-700">
                        <span class="hidden md:block text-sm font-semibold text-stone-700 dark:text-stone-200 max-w-[10rem] truncate">{{ $user->name }}</span>
                    </a>
                </div>
            </div>
        </header>

        <main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
            {{-- Server-side flash (noscript fallback + instant render) --}}
            @if (session('success') || session('error') || session('info'))
                <div class="mb-4">
                    @if (session('success')) <div class="badge-green px-4 py-2.5 rounded-xl justify-center">{{ session('success') }}</div> @endif
                    @if (session('error')) <div class="badge-red px-4 py-2.5 rounded-xl justify-center">{{ session('error') }}</div> @endif
                    @if (session('info')) <div class="badge-blue px-4 py-2.5 rounded-xl justify-center">{{ session('info') }}</div> @endif
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
