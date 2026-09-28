<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ settings('site_name', 'MarketLink') }} Farmer</title>
    <meta name="description" content="Farmer stall dashboard">
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
        $user = auth()->user();
        $approved = $user && $user->status === 'active';
        $publicStall = $user && $user->farmer ? route('farmers.show', $user->farmer) : null;
        $unreadCount = $user ? $user->unreadNotifications()->count() : 0;

        $farmerNav = [
            ['route' => 'farmer.dashboard', 'match' => 'farmer.dashboard', 'label' => 'Dashboard', 'icon' => 'M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z'],
            ['route' => 'farmer.products.index', 'match' => 'farmer.products.*', 'label' => 'Products', 'icon' => 'M12 3l8 4.5v9L12 21l-8-4.5v-9L12 3zM12 12l8-4.5M12 12L4 7.5M12 12v9'],
            ['route' => 'farmer.orders.index', 'match' => 'farmer.orders.*', 'label' => 'Orders', 'icon' => 'M9 5H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V7a2 2 0 00-2-2h-3M9 3h6v4H9zM8 11h8M8 15h5'],
            ['route' => 'farmer.slots.index', 'match' => 'farmer.slots.*', 'label' => 'Pickup Slots', 'icon' => 'M12 21a9 9 0 100-18 9 9 0 000 18zM12 7v5l3 2'],
            ['route' => 'farmer.templates.index', 'match' => 'farmer.templates.*', 'label' => 'Stock Templates', 'icon' => 'M12 3l9 5-9 5-9-5 9-5zM4 12.5l8 4.5 8-4.5'],
            ['route' => 'farmer.insights', 'match' => 'farmer.insights', 'label' => 'Insights', 'icon' => 'M4 20h16M7 16v-5M12 16V8M17 16v-8'],
            ['route' => 'farmer.reviews.index', 'match' => 'farmer.reviews.*', 'label' => 'Reviews', 'icon' => 'M10 1.8l2.5 5.1 5.6.8-4 4 .9 5.6L10 14.7l-5 2.6.9-5.6-4-4 5.6-.8L10 1.8z'],
            ['route' => 'notifications.index', 'match' => 'notifications.index', 'label' => 'Notifications', 'icon' => 'M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'],
            ['route' => 'farmer.profile.edit', 'match' => 'farmer.profile.*', 'label' => 'Profile', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM4.5 20.5a7.5 7.5 0 0115 0'],
        ];
    @endphp

    {{-- Dimmed overlay behind the mobile sidebar --}}
    <div x-show="sidebar" x-cloak x-transition.opacity
         @click="sidebar = false"
         class="fixed inset-0 z-40 bg-stone-900/60 backdrop-blur-sm lg:hidden"
         style="display:none"></div>

    {{-- ======================= SIDEBAR ======================= --}}
    <aside x-bind:class="sidebar ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col border-r border-stone-200/80 bg-white transition-transform duration-300 dark:border-leaf-800/80 dark:bg-leaf-900/70"
           @keydown.escape.window="sidebar = false">
        <div class="flex h-16 shrink-0 items-center justify-between border-b border-stone-200/80 px-5 dark:border-leaf-800/80">
            <a href="{{ $approved ? route('farmer.dashboard') : route('farmer.pending') }}" class="flex items-center gap-2.5">
                <span class="grid h-9 w-9 place-items-center rounded-xl bg-leaf-600 text-white shadow-soft">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c4 3 6 6.5 6 10a6 6 0 11-12 0c0-3.5 2-7 6-10z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 21c0-4 1.5-7 4-9"/></svg>
                </span>
                <span class="font-display text-lg font-semibold text-leaf-900 dark:text-leaf-100">{{ $user?->farmer?->stall_name ?? settings('site_name', 'MarketLink') }}</span>
            </a>
            <button @click="sidebar = false" aria-label="Close menu" class="grid h-9 w-9 place-items-center rounded-xl text-stone-500 hover:bg-leaf-50 hover:text-leaf-700 dark:text-stone-300 dark:hover:bg-leaf-800/60 dark:hover:text-leaf-200 lg:hidden">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>

        @unless ($approved)
            <div class="mx-4 mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-500/30 dark:bg-amber-500/10">
                <p class="flex items-center gap-2 text-sm font-semibold text-amber-800 dark:text-amber-300">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M10.3 3.9L2.6 17a2 2 0 001.7 3h15.4a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/></svg>
                    Approval pending
                </p>
                <p class="mt-1 text-xs leading-relaxed text-amber-700 dark:text-amber-200/80">Your stall is waiting for admin approval. You can finish setting up your profile in the meantime.</p>
            </div>
        @endunless

        <nav class="flex-1 space-y-1 overflow-y-auto p-4">
            @foreach ($farmerNav as $item)
                @if ($approved || $item['route'] === 'farmer.profile.edit')
                    <a href="{{ route($item['route']) }}" @click="sidebar = false"
                       class="{{ request()->routeIs($item['match']) ? 'nav-link-active' : 'nav-link' }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/></svg>
                        {{ $item['label'] }}
                        @if ($item['label'] === 'Notifications' && $unreadCount)
                            <span class="ml-auto min-w-[20px] h-5 px-1.5 rounded-full bg-red-500 text-white text-[10px] font-bold grid place-items-center">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                        @endif
                    </a>
                @endif
            @endforeach
        </nav>

        <div class="shrink-0 space-y-1 border-t border-stone-200/80 p-4 dark:border-leaf-800/80">
            <a href="{{ route('home') }}" @click="sidebar = false" class="nav-link">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l9.75-9 9.75 9M4.5 10.5V21h5.25v-6h4.5v6H19.5V10.5"/></svg>
                Back to website
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-link w-full text-left text-red-600 hover:bg-red-50 hover:text-red-700 dark:text-red-400 dark:hover:bg-red-500/10 dark:hover:text-red-300">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6A2.25 2.25 0 005.25 5.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 12h9m0 0l-3-3m3 3l-3 3"/></svg>
                    Sign out
                </button>
            </form>
        </div>
    </aside>

    {{-- ======================= TOPBAR ======================= --}}
    <header class="sticky top-0 z-30 border-b border-stone-200/70 bg-cream-50/85 backdrop-blur-md dark:border-leaf-800/70 dark:bg-leaf-950/85 lg:pl-72">
        <div class="flex h-16 items-center gap-3 px-4 sm:px-6 lg:px-8">
            <button @click="sidebar = true" aria-label="Open menu" class="grid h-10 w-10 shrink-0 place-items-center rounded-xl text-stone-600 hover:bg-leaf-50 hover:text-leaf-700 dark:text-stone-300 dark:hover:bg-leaf-900/70 lg:hidden">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>

            <h1 class="truncate font-display text-lg font-semibold text-leaf-900 dark:text-leaf-100 sm:text-xl">@yield('title', 'Dashboard')</h1>

            <div class="ml-auto flex items-center gap-1.5 sm:gap-2">
                @if ($publicStall && $approved)
                    <a href="{{ $publicStall }}" class="btn-ghost btn-sm hidden sm:inline-flex">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12z"/><circle cx="12" cy="12" r="3"/></svg>
                        View public stall
                    </a>
                @endif

                <button onclick="toggleTheme()" aria-label="Toggle theme" class="grid h-10 w-10 place-items-center rounded-xl text-stone-600 transition-colors hover:bg-leaf-50 hover:text-leaf-700 dark:text-stone-300 dark:hover:bg-leaf-900/70 dark:hover:text-leaf-200">
                    <svg data-theme-icon-sun class="hidden h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 17a5 5 0 100-10 5 5 0 000 10z"/><path stroke-linecap="round" d="M12 2v2m0 16v2M4.9 4.9l1.4 1.4m11.4 11.4 1.4 1.4M2 12h2m16 0h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
                    <svg data-theme-icon-moon class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12.8A9 9 0 1111.2 3 7 7 0 0021 12.8z"/></svg>
                </button>

                <a href="{{ route('farmer.profile.edit') }}" class="flex items-center gap-2 rounded-xl py-1 pl-1 pr-1.5 transition-colors hover:bg-leaf-50 dark:hover:bg-leaf-900/70 sm:pr-3">
                    <img src="{{ $user->avatarUrl() }}" alt="" class="h-8 w-8 rounded-full object-cover ring-2 ring-leaf-200 dark:ring-leaf-700">
                    <span class="hidden max-w-[10rem] truncate text-sm font-semibold text-stone-700 dark:text-stone-200 md:block">{{ $user->name }}</span>
                </a>
            </div>
        </div>
    </header>

    {{-- ======================= MAIN ======================= --}}
    <main class="lg:pl-72">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            {{-- Server-side flash (noscript fallback + instant render) --}}
            @if (session('success') || session('error') || session('info'))
                @if (session('success')) <div class="badge-green justify-center rounded-xl px-4 py-2.5 mb-6">{{ session('success') }}</div> @endif
                @if (session('error')) <div class="badge-red justify-center rounded-xl px-4 py-2.5 mb-6">{{ session('error') }}</div> @endif
                @if (session('info')) <div class="badge-blue justify-center rounded-xl px-4 py-2.5 mb-6">{{ session('info') }}</div> @endif
            @endif

            @yield('content')
        </div>
    </main>

    <footer class="pb-8 lg:pl-72">
        <p class="text-center text-xs text-stone-400 dark:text-stone-500">&copy; {{ date('Y') }} {{ settings('site_name', 'MarketLink') }} — Farmer dashboard</p>
    </footer>

    @stack('scripts')
</body>
</html>
