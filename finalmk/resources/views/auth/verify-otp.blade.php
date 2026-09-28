@extends('layouts.auth')

@section('title', $purpose === \App\Models\Otp::PURPOSE_PASSWORD_RESET ? 'Reset your password' : 'Verify your email')

@section('content')
    <div x-data="otpForm({{ (int) $resendIn }})" x-init="init()">
        <div class="text-center mb-6">
            <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-leaf-100 text-leaf-700 dark:bg-leaf-900/60 dark:text-leaf-300">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                </svg>
            </div>
            <h2 class="font-display text-2xl font-semibold text-leaf-950 dark:text-cream-50">
                {{ $purpose === \App\Models\Otp::PURPOSE_PASSWORD_RESET ? 'Enter your reset code' : 'Verify your email' }}
            </h2>
            <p class="mt-1.5 text-sm text-stone-500 dark:text-stone-400">
                We emailed a 6-digit code to
                <span class="font-semibold text-leaf-800 dark:text-leaf-200">{{ $email }}</span>.
                It expires in {{ \App\Models\Otp::TTL_MINUTES }} minutes.
            </p>
        </div>

        <form x-ref="form" method="POST" action="{{ route('otp.verify') }}" class="space-y-5" novalidate>
            @csrf
            <input type="hidden" name="code" :value="code">

            <div class="flex justify-center gap-2 sm:gap-3" @keydown.left.prevent="focusBox(Math.max(0, active - 1))" @keydown.right.prevent="focusBox(Math.min(5, active + 1))">
                @for ($i = 0; $i < 6; $i++)
                    <input
                        type="text"
                        inputmode="numeric"
                        autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}"
                        maxlength="1"
                        data-otp="{{ $i }}"
                        :value="digits[{{ $i }}]"
                        @input="onInput({{ $i }}, $event)"
                        @focus="active = {{ $i }}"
                        @keydown.backspace="onBackspace({{ $i }}, $event)"
                        @paste.prevent="onPaste($event)"
                        aria-label="Digit {{ $i + 1 }}"
                        class="h-14 w-11 rounded-xl border bg-white text-center text-xl font-semibold text-leaf-950 shadow-soft transition-shadow focus:border-leaf-400 focus:outline-none focus:ring-2 focus:ring-leaf-400/60 sm:h-16 sm:w-12 dark:bg-leaf-900/60 dark:text-cream-50"
                        :class="hasError
                            ? 'border-red-400 dark:border-red-500'
                            : 'border-stone-200 dark:border-leaf-800'"
                    >
                @endfor
            </div>

            @error('code')
                <p class="text-center text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror

            <button type="submit" class="btn-primary w-full !py-3" :disabled="!isComplete || submitting">
                <span x-show="!submitting">Verify code</span>
                <span x-show="submitting">Verifying…</span>
            </button>
        </form>

        <div class="mt-6 text-center text-sm text-stone-500 dark:text-stone-400">
            <p x-show="cooldown > 0" x-cloak>
                Didn't get it? You can request a new code in
                <span class="font-semibold text-leaf-700 dark:text-leaf-300" x-text="cooldown"></span>s
            </p>
            <p x-show="cooldown <= 0" x-cloak>Didn't get the email?</p>
        </div>

        <form x-show="cooldown <= 0" x-cloak method="POST" action="{{ route('otp.resend') }}" class="mt-2 text-center">
            @csrf
            <button type="submit" class="btn-ghost mx-auto">Resend code</button>
        </form>

        <p class="mt-6 text-center text-sm text-stone-500 dark:text-stone-400">
            Wrong address?
            <a href="{{ $purpose === \App\Models\Otp::PURPOSE_PASSWORD_RESET ? route('password.request') : route('register') }}"
                class="font-semibold text-leaf-700 transition hover:text-leaf-800 dark:text-leaf-300 dark:hover:text-leaf-200">
                {{ $purpose === \App\Models\Otp::PURPOSE_PASSWORD_RESET ? 'Start over' : 'Register again' }}
            </a>
        </p>
    </div>
@endsection

@push('scripts')
<script>
    function otpForm(resendIn) {
        return {
            digits: ['', '', '', '', '', ''],
            active: 0,
            cooldown: resendIn || 0,
            timer: null,
            submitting: false,
            hasError: {{ $errors->has('code') ? 'true' : 'false' }},

            init() {
                this.$nextTick(() => this.focusBox(0));

                if (this.cooldown > 0) {
                    this.startTimer();
                }
            },

            get code() {
                return this.digits.join('');
            },

            get isComplete() {
                return this.digits.every((d) => d !== '');
            },

            boxes() {
                return this.$root.querySelectorAll('input[data-otp]');
            },

            focusBox(i) {
                const box = this.boxes()[i];

                if (box) {
                    box.focus();
                    box.select();
                    this.active = i;
                }
            },

            startTimer() {
                clearInterval(this.timer);
                this.timer = setInterval(() => {
                    this.cooldown = Math.max(0, this.cooldown - 1);

                    if (this.cooldown === 0) {
                        clearInterval(this.timer);
                    }
                }, 1000);
            },

            onInput(i, event) {
                const value = event.target.value.replace(/\D/g, '');

                if (value.length > 1) {
                    this.distribute(0, value);

                    return;
                }

                this.digits[i] = value;
                event.target.value = value;

                if (value && i < 5) {
                    this.focusBox(i + 1);
                }

                this.maybeSubmit();
            },

            onBackspace(i, event) {
                event.preventDefault();

                if (this.digits[i]) {
                    this.digits[i] = '';

                    return;
                }

                if (i > 0) {
                    this.digits[i - 1] = '';
                    this.focusBox(i - 1);
                }
            },

            onPaste(event) {
                const text = (event.clipboardData || window.clipboardData).getData('text');

                this.distribute(0, text);
            },

            distribute(start, text) {
                const chars = text.replace(/\D/g, '').slice(0, 6 - start).split('');

                chars.forEach((char, offset) => {
                    this.digits[start + offset] = char;
                });

                this.focusBox(Math.min(5, start + Math.max(chars.length - 1, 0)));
                this.maybeSubmit();
            },

            maybeSubmit() {
                if (this.submitting || !this.isComplete) {
                    return;
                }

                this.submitting = true;
                this.$nextTick(() => this.$refs.form.requestSubmit());
            },
        };
    }
</script>
@endpush
