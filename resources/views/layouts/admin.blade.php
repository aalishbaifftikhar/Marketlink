@php
    $pendingFarmersCount = \App\Models\Farmer::whereHas('user', fn ($q) => $q->where('status', 'pending'))->count();
    $adminNav = [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'pattern' => 'admin.dashboard', 'icon' => 'M3 12l9-9 9 9M5 10v10a1 1 0 001 1h4v-6h4v6h4a1 1 0 001-1V10'],
        ['label' => 'Farmers', 'route' => 'admin.farmers.index', 'pattern' => 'admin.farmers.*', 'icon' => 'M12 3c4 3 6 6.5 6 10a6 6 0 11-12 0c0-3.5 2-7 6-10z'],
        ['label' => 'Customers', 'route' => 'admin.customers.index', 'pattern' => 'admin.customers.*', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM4 21a8 8 0 0116 0'],
        ['label' => 'Markets', 'route' => 'admin.markets.index', 'pattern' => 'admin.markets.*', 'icon' => 'M3 9l1.5-5h15L21 9M3 9v10a1 1 0 001 1h16a1 1 0 001-1V9M3 9h18M9 20v-6h6v6'],
        ['label' => 'Categories', 'route' => 'admin.categories.index', 'pattern' => 'admin.categories.*', 'icon' => 'M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h6v6h-6z'],
        ['label' => 'Products', 'route' => 'admin.products.index', 'pattern' => 'admin.products.*', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
        ['label' => 'Reviews', 'route' => 'admin.reviews.index', 'pattern' => 'admin.reviews.*', 'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 01-13.2 7.9L3 21l1.1-4.8A9 9 0 1121 12z'],
        ['label' => 'Orders', 'route' => 'admin.orders.index', 'pattern' => 'admin.orders.*', 'icon' => 'M9 5h6M9 3h6a2 2 0 012 2v0a2 2 0 01-2 2H9a2 2 0 01-2-2v0a2 2 0 012-2zM5 7h14l-1.2 12a2 2 0 01-2 1.8H8.2a2 2 0 01-2-1.8L5 7z'],
        ['label' => 'Reports', 'route' => 'admin.reports.index', 'pattern' => 'admin.reports.*', 'icon' => 'M9 17v-6m4 6V7m4 10v-3M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z'],
        ['label' => 'Announcements', 'route' => 'admin.announcements.index', 'pattern' => 'admin.announcements.*', 'icon' => 'M11 5.9A2 2 0 0113.1 4h1.8A2 2 0 0117 6.1V8h1a2 2 0 012 2v2a2 2 0 01-2 2h-1v1.9a2 2 0 01-2.1 2.1h-1.8A2 2 0 0111 15.9V14H6a2 2 0 01-2-2v-2a2 2 0 012-2h5V5.9zM11 14v4a1 1 0 01-1 1H8'],
        ['label' => 'Settings', 'route' => 'admin.settings.index', 'pattern' => 'admin.settings.*', 'icon' => 'M10.3 4.3a2 2 0 013.4 0l.4.6a2 2 0 002.6.7l.7-.3a2 2 0 012.8 2.8l-.3.7a2 2 0 00.7 2.6l.6.4a2 2 0 010 3.4l-.6.4a2 2 0 00-.7 2.6l.3.7a2 2 0 01-2.8 2.8l-.7-.3a2 2 0 00-2.6.7l-.4.6a2 2 0 01-3.4 0l-.4-.6a2 2 0 00-2.6-.7l-.7.3a2 2 0 01-2.8-2.8l.3-.7a2 2 0 00-.7-2.6l-.6-.4a2 2 0 010-3.4l.6-.4a2 2 0 00.7-2.6l-.3-.7a2 2 0 012.8-2.8l.7.3a2 2 0 002.6-.7l.4-.6zM15 12a3 3 0 11-6 0 3 3 0 016 0z'],
    ];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ settings('site_name', 'MarketLink') }} Admin</title>
    <meta name="description" content="@yield('meta_description', settings('site_tagline'))">
    <meta name="robots" content="noindex, nofollow">
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
<body x-data="{ sidebar: false }" class="min-h-screen antialiased bg-cream-50 dark:bg-leaf-950">

    {{-- Mobile overlay --}}
    <div x-show="sidebar" x-cloak @click="sidebar = false"
        class="fixed inset-0 z-40 bg-leaf-950/60 backdrop-blur-sm lg:hidden" style="display:none"></div>

    {{-- ======================= SIDEBAR (stays dark in both modes) ======================= --}}
    <aside :class="sidebar ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-50 w-64 flex flex-col bg-leaf-950 border-r border-leaf-900 transition-transform duration-300 lg:translate-x-0">
        {{-- Brand --}}
        <a href="{{ route('admin.dashboard') }}" @click="sidebar = false" class="flex items-center gap-2.5 px-5 h-16 border-b border-leaf-900 shrink-0">
            <span class="w-9 h-9 rounded-xl bg-leaf-600 text-white grid place-items-center shadow-soft">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c4 3 6 6.5 6 10a6 6 0 11-12 0c0-3.5 2-7 6-10z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 21c0-4 1.5-7 4-9"/></svg>
            </span>
            <span class="min-w-0">
                <span class="block font-display font-semibold text-white leading-tight">{{ settings('site_name', 'MarketLink') }} Admin</span>
                <span class="block text-[11px] text-leaf-300/80 leading-tight">Control panel</span>
            </span>
        </a>

        {{-- Nav --}}
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
            @foreach ($adminNav as $item)
                @php $active = request()->routeIs($item['pattern']); @endphp
                <a href="{{ route($item['route']) }}" @click="sidebar = false"
                    class="group relative flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-colors {{ $active ? 'bg-leaf-600 text-white shadow-soft' : 'text-leaf-100/70 hover:bg-white/5 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0 {{ $active ? 'text-white' : 'text-leaf-300/70 group-hover:text-leaf-200' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/></svg>
                    <span class="flex-1 truncate">{{ $item['label'] }}</span>
                    @if ($item['label'] === 'Farmers' && $pendingFarmersCount > 0)
                        <span class="relative flex h-2.5 w-2.5 shrink-0" title="{{ $pendingFarmersCount }} pending approval">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-amber-400"></span>
                        </span>
                        <span class="min-w-[20px] h-5 px-1.5 rounded-full bg-amber-400/20 text-amber-300 text-[11px] font-bold grid place-items-center">{{ $pendingFarmersCount }}</span>
                    @endif
                </a>
            @endforeach
        </nav>

        {{-- Back to site --}}
        <div class="px-3 py-4 border-t border-leaf-900 shrink-0">
            <a href="{{ route('home') }}" class="group flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium text-leaf-100/70 hover:bg-white/5 hover:text-white transition-colors">
                <svg class="w-5 h-5 text-leaf-300/70 group-hover:text-leaf-200" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                Back to {{ settings('site_name', 'MarketLink') }}
            </a>
        </div>
    </aside>

    {{-- ======================= MAIN COLUMN ======================= --}}
    <div class="lg:pl-64 flex flex-col min-h-screen">

        {{-- Topbar --}}
        <header class="sticky top-0 z-30 h-16 flex items-center gap-3 px-4 sm:px-6 lg:px-8 bg-cream-50/85 dark:bg-leaf-950/85 backdrop-blur-md border-b border-stone-200/70 dark:border-leaf-800/70">
            <button @click="sidebar = !sidebar" aria-label="Toggle navigation" class="lg:hidden w-10 h-10 grid place-items-center rounded-xl text-stone-600 dark:text-stone-300 hover:bg-leaf-50 dark:hover:bg-leaf-900/70 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>

            <h1 class="font-display text-lg sm:text-xl font-semibold text-leaf-950 dark:text-cream-50 truncate">@yield('title', 'Dashboard')</h1>

            <div class="ml-auto flex items-center gap-2 sm:gap-3">
                {{-- Theme toggle --}}
                <button onclick="toggleTheme()" aria-label="Toggle theme" class="w-10 h-10 grid place-items-center rounded-xl text-stone-600 dark:text-stone-300 hover:bg-leaf-50 dark:hover:bg-leaf-900/70 transition-colors">
                    <svg data-theme-icon-sun class="w-5 h-5 hidden" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 17a5 5 0 100-10 5 5 0 000 10z"/><path stroke-linecap="round" d="M12 2v2m0 16v2M4.9 4.9l1.4 1.4m11.4 11.4 1.4 1.4M2 12h2m16 0h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
                    <svg data-theme-icon-moon class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12.8A9 9 0 1111.2 3 7 7 0 0021 12.8z"/></svg>
                </button>

                {{-- Admin chip --}}
                <div class="hidden sm:flex items-center gap-2.5 pl-1 pr-3 py-1 rounded-xl border border-stone-200/80 dark:border-leaf-800 bg-white dark:bg-leaf-900/60">
                    <img src="{{ auth()->user()->avatarUrl() }}" alt="" class="w-8 h-8 rounded-full object-cover ring-2 ring-leaf-200 dark:ring-leaf-700">
                    <span class="min-w-0">
                        <span class="block text-sm font-semibold text-stone-800 dark:text-stone-100 leading-tight truncate max-w-[10rem]">{{ auth()->user()->name }}</span>
                        <span class="block text-[11px] text-stone-500 dark:text-stone-400 leading-tight">Administrator</span>
                    </span>
                </div>

                {{-- Logout --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-ghost btn-sm" title="Sign out">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 016 0h4a3 3 0 013 3v1"/></svg>
                        <span class="hidden sm:inline">Sign out</span>
                    </button>
                </form>
            </div>
        </header>

        {{-- Content --}}
        <main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-8">
            {{-- Server-side flash (noscript fallback + instant render) --}}
            @if (session('success') || session('error') || session('info'))
                <div class="mb-4 space-y-2">
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
