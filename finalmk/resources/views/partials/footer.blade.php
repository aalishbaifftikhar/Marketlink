<footer class="mt-20 bg-leaf-900 dark:bg-leaf-900/60 text-leaf-100 border-t border-leaf-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-4">
            <div>
                <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                    <span class="w-9 h-9 rounded-xl bg-leaf-600 text-white grid place-items-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c4 3 6 6.5 6 10a6 6 0 11-12 0c0-3.5 2-7 6-10z"/></svg>
                    </span>
                    <span class="font-display font-semibold text-lg text-white">{{ settings('site_name', 'MarketLink') }}</span>
                </a>
                <p class="mt-4 text-sm text-leaf-200/80 leading-relaxed">
                    {{ settings('site_tagline', 'Fresh from local farms, straight to your basket') }}. Connecting farmers-market growers with their community.
                </p>
            </div>

            <div>
                <h4 class="font-display font-semibold text-white mb-4">Explore</h4>
                <ul class="space-y-2.5 text-sm">
                    <li><a href="{{ route('markets.index') }}" class="text-leaf-200/80 hover:text-white transition-colors">Markets</a></li>
                    <li><a href="{{ route('farmers.index') }}" class="text-leaf-200/80 hover:text-white transition-colors">Farmers</a></li>
                    <li><a href="{{ route('products.index') }}" class="text-leaf-200/80 hover:text-white transition-colors">Products</a></li>
                    <li><a href="{{ route('about') }}" class="text-leaf-200/80 hover:text-white transition-colors">About Us</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-display font-semibold text-white mb-4">Account</h4>
                <ul class="space-y-2.5 text-sm">
                    @auth
                        <li><a href="{{ auth()->user()->dashboardRoute() }}" class="text-leaf-200/80 hover:text-white transition-colors">Dashboard</a></li>
                        <li><a href="{{ route('profile.edit') }}" class="text-leaf-200/80 hover:text-white transition-colors">Profile Settings</a></li>
                        <li><a href="{{ route('notifications.index') }}" class="text-leaf-200/80 hover:text-white transition-colors">Notifications</a></li>
                    @else
                        <li><a href="{{ route('login') }}" class="text-leaf-200/80 hover:text-white transition-colors">Sign In</a></li>
                        <li><a href="{{ route('register') }}" class="text-leaf-200/80 hover:text-white transition-colors">Create Account</a></li>
                    @endauth
                    <li><a href="{{ route('contact') }}" class="text-leaf-200/80 hover:text-white transition-colors">Contact Us</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-display font-semibold text-white mb-4">Get in Touch</h4>
                <ul class="space-y-2.5 text-sm text-leaf-200/80">
                    <li>{{ settings('contact_email', 'hello@marketlink.local') }}</li>
                    <li>{{ settings('contact_phone', '+1 (555) 010-2030') }}</li>
                    <li class="leading-relaxed">{{ settings('contact_address', '1 Market Square, Greenfield') }}</li>
                </ul>
            </div>
        </div>

        <div class="mt-12 pt-6 border-t border-leaf-800 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-leaf-300/70">
            <p>&copy; {{ date('Y') }} {{ settings('site_name', 'MarketLink') }}. All rights reserved.</p>
            @if (settings('footer_text'))
                <p>{{ settings('footer_text') }}</p>
            @endif
            <p class="flex items-center gap-1.5">Made with <svg class="w-3.5 h-3.5 text-leaf-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21s-7.5-4.9-10-9C.5 8 2.5 4.5 6.5 4.5c2.3 0 3.9 1.3 5.5 3.2 1.6-1.9 3.2-3.2 5.5-3.2 4 0 6 3.5 4.5 7.5-2.5 4.1-10 9-10 9z"/></svg> for local farms</p>
        </div>
    </div>
</footer>
