@extends('layouts.auth')

@section('title', 'Finish setting up your account')

@section('content')
    <div x-data="{ role: '{{ old('role', 'customer') }}' }">
        <div class="text-center mb-6">
            <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center overflow-hidden rounded-2xl bg-cream-100 ring-2 ring-cream-200 dark:bg-leaf-800 dark:ring-leaf-700">
                @if(!empty($google['avatar']))
                    <img src="{{ $google['avatar'] }}" alt="" class="h-full w-full object-cover">
                @else
                    <span class="font-display text-xl font-semibold text-leaf-700 dark:text-leaf-300">{{ strtoupper(substr($google['name'], 0, 1)) }}</span>
                @endif
            </div>
            <h2 class="font-display text-2xl font-semibold text-leaf-950 dark:text-cream-50">Welcome, {{ explode(' ', $google['name'])[0] }}</h2>
            <p class="mt-1.5 text-sm text-stone-500 dark:text-stone-400">Signed in as <span class="font-semibold text-leaf-800 dark:text-leaf-200">{{ $google['email'] }}</span> via Google. Tell us how you'll use MarketLink.</p>
        </div>

        <form method="POST" action="{{ route('google.complete.store') }}" class="space-y-4" novalidate x-data="passwordRules">
            @csrf

            <div class="grid grid-cols-2 gap-3">
                <label class="relative flex cursor-pointer rounded-2xl border p-3.5 text-left transition-all duration-200"
                    :class="role === 'customer'
                        ? 'border-leaf-500 bg-leaf-50 ring-2 ring-leaf-500/40 dark:bg-leaf-900/30'
                        : 'border-stone-200 hover:border-leaf-300 dark:border-leaf-800'">
                    <input type="radio" name="role" value="customer" x-model="role" class="sr-only">
                    <span class="w-full">
                        <span class="block font-semibold text-sm text-leaf-950 dark:text-cream-50">Shopper</span>
                        <span class="mt-0.5 block text-xs text-stone-500 dark:text-stone-400">Browse markets and pre-order</span>
                    </span>
                </label>
                <label class="relative flex cursor-pointer rounded-2xl border p-3.5 text-left transition-all duration-200"
                    :class="role === 'farmer'
                        ? 'border-leaf-500 bg-leaf-50 ring-2 ring-leaf-500/40 dark:bg-leaf-900/30'
                        : 'border-stone-200 hover:border-leaf-300 dark:border-leaf-800'">
                    <input type="radio" name="role" value="farmer" x-model="role" class="sr-only">
                    <span class="w-full">
                        <span class="block font-semibold text-sm text-leaf-950 dark:text-cream-50">Farmer</span>
                        <span class="mt-0.5 block text-xs text-stone-500 dark:text-stone-400">Sell at verified markets</span>
                    </span>
                </label>
            </div>
            <x-input-error field="role" />

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="phone" class="input-label">Phone number <span class="font-normal text-stone-400">(optional)</span></label>
                    <input id="phone" name="phone" type="tel" value="{{ old('phone') }}"
                        class="input @error('phone') !border-red-400 dark:!border-red-500 @enderror" placeholder="0712345678"
                        inputmode="numeric" maxlength="15" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    <p class="mt-1 text-xs text-stone-400 dark:text-stone-500">Numbers only, 10–15 digits.</p>
                    <x-input-error field="phone" />
                </div>
                <div>
                    <label for="address" class="input-label">Address <span class="font-normal text-stone-400">(optional)</span></label>
                    <input id="address" name="address" type="text" value="{{ old('address') }}"
                        class="input" placeholder="Estate, street, town">
                </div>
            </div>

            <div x-show="role === 'farmer'" x-transition class="space-y-4 rounded-2xl border border-leaf-200 bg-leaf-50/60 p-4 dark:border-leaf-800 dark:bg-leaf-900/20" style="display:none;">
                <p class="text-xs font-semibold uppercase tracking-wider text-leaf-700 dark:text-leaf-300">Farm / stall details</p>
                <div>
                    <label for="stall_name" class="input-label">Farm / stall name</label>
                    <input id="stall_name" name="stall_name" type="text" value="{{ old('stall_name') }}"
                        class="input @error('stall_name') !border-red-400 dark:!border-red-500 @enderror" placeholder="Green Valley Organics">
                    <x-input-error field="stall_name" />
                </div>
                <div>
                    <label for="contact_person" class="input-label">Contact person</label>
                    <input id="contact_person" name="contact_person" type="text" value="{{ old('contact_person', $google['name']) }}"
                        class="input" placeholder="Name of the person we should call">
                    <x-input-error field="contact_person" />
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="password" class="input-label">Password <span class="font-normal text-stone-400">(optional)</span></label>
                    <input id="password" name="password" type="password" x-model="password" autocomplete="new-password"
                        class="input @error('password') !border-red-400 dark:!border-red-500 @enderror" placeholder="••••••••">
                    <p class="mt-1 text-xs text-stone-400 dark:text-stone-500">Set one if you also want to sign in with email. Leave blank to use Google only.</p>
                    <x-input-error field="password" />
                </div>
                <div>
                    <label for="password_confirmation" class="input-label">Confirm password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" x-model="confirmation" autocomplete="new-password" class="input" placeholder="••••••••">
                    <p class="mt-1 text-xs" x-cloak x-show="confirmation !== ''"
                        :class="confirmationMatches ? 'text-leaf-700 dark:text-leaf-300' : 'text-red-600 dark:text-red-400'"
                        x-text="confirmationMatches ? 'Passwords match' : 'Passwords do not match yet'"></p>
                </div>
            </div>

            <x-password-rules x-show="password.length > 0" />

            <button type="submit" class="btn-primary w-full !py-3" x-bind:disabled="password.length > 0 && !ready">Finish setup</button>
        </form>
    </div>
@endsection
