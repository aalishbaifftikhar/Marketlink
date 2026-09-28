@extends('layouts.app')

@section('title', 'Contact')

@section('content')
    <section class="page-hero relative overflow-hidden">
        <div class="hero-overlay absolute inset-0"></div>
        <div class="relative mx-auto max-w-7xl px-4 py-16 text-center sm:px-6 sm:py-20">
            <h1 class="font-display text-4xl font-semibold text-white sm:text-5xl">Get in touch</h1>
            <p class="mx-auto mt-3 max-w-xl text-lg text-cream-100/90">Questions about an order, becoming a vendor, or running a market with us — we'd love to hear from you.</p>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6">
        <div class="grid gap-8 lg:grid-cols-5">
            <div class="space-y-4 lg:col-span-2">
                <div class="card flex items-start gap-4 p-5">
                    <div class="rounded-2xl bg-leaf-50 p-3 text-leaf-700 dark:bg-leaf-900/60 dark:text-leaf-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                    </div>
                    <div>
                        <h2 class="font-semibold text-leaf-950 dark:text-cream-50">Email</h2>
                        <a href="mailto:{{ settings('contact_email') }}" class="mt-0.5 block text-sm text-leaf-700 hover:underline dark:text-leaf-300">{{ settings('contact_email') ?: 'hello@marketlink.local' }}</a>
                        <p class="mt-1 text-xs text-stone-500 dark:text-stone-400">We reply within one business day.</p>
                    </div>
                </div>

                <div class="card flex items-start gap-4 p-5">
                    <div class="rounded-2xl bg-leaf-50 p-3 text-leaf-700 dark:bg-leaf-900/60 dark:text-leaf-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                    </div>
                    <div>
                        <h2 class="font-semibold text-leaf-950 dark:text-cream-50">Phone</h2>
                        <a href="tel:{{ settings('contact_phone') }}" class="mt-0.5 block text-sm text-leaf-700 hover:underline dark:text-leaf-300">{{ settings('contact_phone') ?: '—' }}</a>
                        <p class="mt-1 text-xs text-stone-500 dark:text-stone-400">Mon–Fri, 8am–5pm.</p>
                    </div>
                </div>

                <div class="card flex items-start gap-4 p-5">
                    <div class="rounded-2xl bg-leaf-50 p-3 text-leaf-700 dark:bg-leaf-900/60 dark:text-leaf-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0zM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                    </div>
                    <div>
                        <h2 class="font-semibold text-leaf-950 dark:text-cream-50">Office</h2>
                        <p class="mt-0.5 text-sm text-stone-600 dark:text-stone-300">{{ settings('contact_address') ?: 'Visit any of our markets' }}</p>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-3">
                <div class="card overflow-hidden !p-0">
                    @if(settings('contact_latitude') && settings('contact_longitude'))
                        <x-map id="contact-map" height="h-[26rem]"
                            :center="[(float) settings('contact_latitude'), (float) settings('contact_longitude')]" :zoom="14"
                            :markers="[['id' => 0, 'name' => settings('site_name', 'MarketLink'), 'lat' => (float) settings('contact_latitude'), 'lng' => (float) settings('contact_longitude'), 'url' => null]]" />
                    @else
                        <div class="flex h-[26rem] flex-col items-center justify-center bg-cream-100 px-6 text-center dark:bg-leaf-900">
                            <svg class="h-10 w-10 text-stone-300 dark:text-stone-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0zM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                            <p class="mt-3 text-sm text-stone-500 dark:text-stone-400">A map will appear here once an office location is set by the site administrator.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
