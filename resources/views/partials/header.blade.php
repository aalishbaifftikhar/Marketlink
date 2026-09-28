@php
    $navMarkets = \App\Models\Market::active()->orderBy('name')->take(6)->get(['name', 'slug']);
@endphp
<header x-data="{ open: false }" class="sticky top-0 z-40 backdrop-blur-md bg-cream-50/85 dark:bg-leaf-950/85 border-b border-stone-200/70 dark:border-leaf-800/70">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            {{-- Brand --}}
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
                <span class="w-9 h-9 rounded-xl bg-leaf-600 text-white grid place-items-center shadow-soft group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c4 3 6 6.5 6 10a6 6 0 11-12 0c0-3.5 2-7 6-10z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 21c0-4 1.5-7 4-9"/></svg>
                </span>
                <span class="font-display font-semibold text-lg text-leaf-900 dark:text-leaf-100">{{ settings('site_name', 'MarketLink') }}</span>
            </a>

            {{-- Desktop nav --}}
            <nav class="hidden lg:flex items-center gap-1">
                <a href="{{ route('markets.index') }}" class="nav-link">Markets</a>
                <a href="{{ route('farmers.index') }}" class="nav-link">Farmers</a>
                <a href="{{ route('products.index') }}" class="nav-link">Products</a>
                <a href="{{ route('about') }}" class="nav-link">About</a>
                <a href="{{ route('contact') }}" class="nav-link">Contact</a>
            </nav>

            <div class="hidden lg:flex items-center gap-2">
                <button onclick="toggleTheme()" aria-label="Toggle theme" class="w-10 h-10 grid place-items-center rounded-xl text-stone-600 dark:text-stone-300 hover:bg-leaf-50 dark:hover:bg-leaf-900/70 transition-colors">
                    <svg data-theme-icon-sun class="w-5 h-5 hidden" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 17a5 5 0 100-10 5 5 0 000 10z"/><path stroke-linecap="round" d="M12 2v2m0 16v2M4.9 4.9l1.4 1.4m11.4 11.4 1.4 1.4M2 12h2m16 0h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
                    <svg data-theme-icon-moon class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12.8A9 9 0 1111.2 3 7 7 0 0021 12.8z"/></svg>
                </button>

                @auth
                    @php $unread = auth()->user()->unreadNotifications()->count(); @endphp
                    <a href="{{ route('notifications.index') }}" class="relative w-10 h-10 grid place-items-center rounded-xl text-stone-600 dark:text-stone-300 hover:bg-leaf-50 dark:hover:bg-leaf-900/70 transition-colors" aria-label="Notifications">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        @if ($unread) <span class="absolute top-1.5 right-1.5 min-w-[18px] h-[18px] px-1 rounded-full bg-red-500 text-white text-[10px] font-bold grid place-items-center">{{ $unread > 9 ? '9+' : $unread }}</span> @endif
                    </a>
                    <a href="{{ auth()->user()->dashboardRoute() }}" class="btn-primary btn-sm">Dashboard</a>
                    <div x-data="{ u: false }" class="relative">
                        <button @click="u = !u" class="flex items-center gap-2 pl-1 pr-2 py-1 rounded-xl hover:bg-leaf-50 dark:hover:bg-leaf-900/70 transition-colors">
                            <img src="{{ auth()->user()->avatarUrl() }}" alt="" class="w-8 h-8 rounded-full object-cover ring-2 ring-leaf-200 dark:ring-leaf-700">
                            <svg class="w-4 h-4 text-stone-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 9l6 6 6-6"/></svg>
                        </button>
                        <div x-show="u" x-cloak @click.outside="u = false" @keydown.escape.window="u = false" class="absolute right-0 mt-2 w-56 card p-2 animate-scale-in origin-top-right" style="display:none">
                            <div class="px-3 py-2 border-b border-stone-100 dark:border-leaf-800">
                                <p class="text-sm font-semibold text-stone-800 dark:text-stone-100">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-stone-500 dark:text-stone-400 truncate">{{ auth()->user()->email }}</p>
                            </div>
                            <a href="{{ auth()->user()->dashboardRoute() }}" class="nav-link mt-1">Dashboard</a>
                            <a href="{{ route('profile.edit') }}" class="nav-link">Profile Settings</a>
                            <a href="{{ route('notifications.index') }}" class="nav-link">Notifications</a>
                            <form method="POST" action="{{ route('logout') }}">@csrf
                                <button type="submit" class="nav-link w-full text-left text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10">Sign Out</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn-ghost btn-sm">Sign In</a>
                    <a href="{{ route('register') }}" class="btn-primary btn-sm">Get Started</a>
                @endauth
            </div>

            {{-- Mobile toggle --}}
            <div class="flex lg:hidden items-center gap-1">
                <button onclick="toggleTheme()" aria-label="Toggle theme" class="w-10 h-10 grid place-items-center rounded-xl text-stone-600 dark:text-stone-300">
                    <svg data-theme-icon-sun class="w-5 h-5 hidden" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 17a5 5 0 100-10 5 5 0 000 10zM12 2v2m0 16v2M4.9 4.9l1.4 1.4m11.4 11.4 1.4 1.4M2 12h2m16 0h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
                    <svg data-theme-icon-moon class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12.8A9 9 0 1111.2 3 7 7 0 0021 12.8z"/></svg>
                </button>
                <button @click="open = !open" aria-label="Menu" class="w-10 h-10 grid place-items-center rounded-xl text-stone-600 dark:text-stone-300 hover:bg-leaf-50 dark:hover:bg-leaf-900/70">
                    <svg x-show="!open" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
                    <svg x-show="open" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div x-show="open" x-cloak x-transition class="lg:hidden border-t border-stone-200/70 dark:border-leaf-800/70 bg-cream-50 dark:bg-leaf-950" style="display:none">
        <nav class="px-4 py-4 space-y-1">
            <a href="{{ route('markets.index') }}" @click="open=false" class="nav-link">Markets</a>
            <a href="{{ route('farmers.index') }}" @click="open=false" class="nav-link">Farmers</a>
            <a href="{{ route('products.index') }}" @click="open=false" class="nav-link">Products</a>
            <a href="{{ route('about') }}" @click="open=false" class="nav-link">About</a>
            <a href="{{ route('contact') }}" @click="open=false" class="nav-link">Contact</a>
            <div class="pt-3 mt-2 border-t border-stone-200 dark:border-leaf-800 flex gap-2">
                @auth
                    <a href="{{ auth()->user()->dashboardRoute() }}" class="btn-primary flex-1">Dashboard</a>
                    <form method="POST" action="{{ route('logout') }}">@csrf
                        <button type="submit" class="btn-secondary">Sign Out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn-secondary flex-1">Sign In</a>
                    <a href="{{ route('register') }}" class="btn-primary flex-1">Get Started</a>
                @endauth
            </div>
        </nav>
    </div>
</header>
