@extends('layouts.auth')

@section('title', 'Create your account')

@section('content')
    <div x-data="{ role: '{{ old('role', $role ?? 'customer') }}' }">
        <div class="text-center mb-6">
            <h2 class="font-display text-2xl font-semibold text-leaf-950 dark:text-cream-50">Create your account</h2>
            <p class="mt-1.5 text-sm text-stone-500 dark:text-stone-400">Join your community market — shop fresh or sell your harvest.</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-4" novalidate x-data="passwordRules">
            @csrf

            <div class="grid grid-cols-2 gap-3">
                <label class="relative flex cursor-pointer rounded-2xl border p-3.5 text-left transition-all duration-200"
                    :class="role === 'customer'
                        ? 'border-leaf-500 bg-leaf-50 ring-2 ring-leaf-500/40 dark:bg-leaf-900/30'
                        : 'border-stone-200 hover:border-leaf-300 dark:border-leaf-800'">
                    <input type="radio" name="role" value="customer" x-model="role" class="sr-only">
                    <span class="w-full">
                        <span class="flex items-center gap-2 font-semibold text-sm text-leaf-950 dark:text-cream-50">
                            <svg class="h-4.5 w-4.5 text-leaf-600 dark:text-leaf-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/></svg>
                            Shopper
                        </span>
                        <span class="mt-0.5 block text-xs text-stone-500 dark:text-stone-400">Browse markets and pre-order</span>
                    </span>
                </label>
                <label class="relative flex cursor-pointer rounded-2xl border p-3.5 text-left transition-all duration-200"
                    :class="role === 'farmer'
                        ? 'border-leaf-500 bg-leaf-50 ring-2 ring-leaf-500/40 dark:bg-leaf-900/30'
                        : 'border-stone-200 hover:border-leaf-300 dark:border-leaf-800'">
                    <input type="radio" name="role" value="farmer" x-model="role" class="sr-only">
                    <span class="w-full">
                        <span class="flex items-center gap-2 font-semibold text-sm text-leaf-950 dark:text-cream-50">
                            <svg class="h-4.5 w-4.5 text-leaf-600 dark:text-leaf-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v3m0 0a5.25 5.25 0 015.25 5.25c0 2.485-2.12 4.5-5.25 4.5m0-9.5A5.25 5.25 0 006.75 8.25C6.75 10.735 8.87 12.75 12 12.75M12 12.75V21m-3.75-6h7.5"/></svg>
                            Farmer
                        </span>
                        <span class="mt-0.5 block text-xs text-stone-500 dark:text-stone-400">Sell at verified markets</span>
                    </span>
                </label>
            </div>
            <x-input-error field="role" class="mt-1" />

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="name" class="input-label">Full name</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus
                        class="input @error('name') input-error @enderror" placeholder="Jane Otieno">
                    <x-input-error field="name" />
                </div>

                <div class="sm:col-span-2">
                    <label for="email" class="input-label">Email address</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required
                        class="input @error('email') input-error @enderror" placeholder="you@example.com">
                    <x-input-error field="email" />
                </div>

                <div>
                    <label for="phone" class="input-label">Phone number</label>
                    <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required
                        class="input @error('phone') input-error @enderror" placeholder="0712345678"
                        inputmode="numeric" maxlength="15" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    <p class="mt-1 text-xs text-stone-400 dark:text-stone-500">Numbers only, 10–15 digits.</p>
                    <x-input-error field="phone" />
                </div>

                <div>
                    <label for="address" class="input-label">Address</label>
                    <input id="address" name="address" type="text" value="{{ old('address') }}" required
                        class="input @error('address') input-error @enderror" placeholder="Estate, street, town">
                    <x-input-error field="address" />
                </div>
            </div>

            <div x-show="role === 'farmer'" x-transition class="space-y-4 rounded-2xl border border-leaf-200 bg-leaf-50/60 p-4 dark:border-leaf-800 dark:bg-leaf-900/20" style="display:none;">
                <p class="text-xs font-semibold uppercase tracking-wider text-leaf-700 dark:text-leaf-300">Farm / stall details</p>
                <div>
                    <label for="stall_name" class="input-label">Farm / stall name</label>
                    <input id="stall_name" name="stall_name" type="text" value="{{ old('stall_name') }}"
                        class="input @error('stall_name') input-error @enderror" placeholder="Green Valley Organics">
                    <x-input-error field="stall_name" />
                </div>
                <div>
                    <label for="contact_person" class="input-label">Contact person</label>
                    <input id="contact_person" name="contact_person" type="text" value="{{ old('contact_person') }}"
                        class="input @error('contact_person') input-error @enderror" placeholder="Name of the person we should call">
                    <x-input-error field="contact_person" />
                </div>
                <p class="text-xs text-stone-500 dark:text-stone-400">Market assignment and pickup point are confirmed when an administrator approves your account.</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="password" class="input-label">Password</label>
                    <input id="password" name="password" type="password" required x-model="password" autocomplete="new-password"
                        class="input @error('password') input-error @enderror" placeholder="••••••••">
                    <x-input-error field="password" />
                </div>
                <div>
                    <label for="password_confirmation" class="input-label">Confirm password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required x-model="confirmation" autocomplete="new-password"
                        class="input" placeholder="••••••••">
                    <p class="mt-1 text-xs" x-cloak x-show="confirmation !== ''"
                        :class="confirmationMatches ? 'text-leaf-700 dark:text-leaf-300' : 'text-red-600 dark:text-red-400'"
                        x-text="confirmationMatches ? 'Passwords match' : 'Passwords do not match yet'"></p>
                </div>
            </div>

            <x-password-rules />

            <button type="submit" class="btn-primary w-full !py-3" x-bind:disabled="!ready">Create account</button>
        </form>

        <div class="my-5 flex items-center gap-3 text-xs text-stone-400 dark:text-stone-500">
            <span class="h-px flex-1 bg-stone-200 dark:bg-leaf-800"></span>or<span class="h-px flex-1 bg-stone-200 dark:bg-leaf-800"></span>
        </div>

        <a href="{{ route('google.redirect') }}" class="btn-secondary w-full !py-3">
            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" aria-hidden="true">
                <path fill="#4285F4" d="M23.5 12.27c0-.85-.08-1.66-.22-2.45H12v4.64h6.45a5.52 5.52 0 01-2.39 3.62v3h3.86c2.26-2.09 3.58-5.17 3.58-8.81z"/>
                <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.86-3c-1.08.72-2.45 1.15-4.07 1.15-3.13 0-5.78-2.11-6.73-4.96H1.29v3.09A12 12 0 0012 24z"/>
                <path fill="#FBBC05" d="M5.27 14.28A7.2 7.2 0 014.89 12c0-.79.14-1.56.38-2.28V6.63H1.29a12 12 0 000 10.74l3.98-3.09z"/>
                <path fill="#EA4335" d="M12 4.77c1.76 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0A12 12 0 001.29 6.63l3.98 3.09C6.22 6.87 8.87 4.77 12 4.77z"/>
            </svg>
            Continue with Google
        </a>

        <p class="mt-6 text-center text-sm text-stone-500 dark:text-stone-400">
            Already have an account?
            <a href="{{ route('login') }}" class="font-semibold text-leaf-700 transition hover:text-leaf-800 dark:text-leaf-300 dark:hover:text-leaf-200">Sign in</a>
        </p>
    </div>
@endsection
