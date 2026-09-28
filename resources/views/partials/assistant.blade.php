@php
    $suggestions = [
        'What markets are open today?',
        'When are the market timings?',
        'What are the pickup windows?',
        'How does pre-ordering work?',
        'Find fresh tomatoes',
    ];
@endphp
<div x-data="assistant()" x-cloak class="fixed bottom-5 right-5 z-50">
    {{-- FAB --}}
    <button @click="toggle()" aria-label="AI Assistant"
        class="w-14 h-14 rounded-2xl bg-leaf-600 hover:bg-leaf-700 text-white grid place-items-center shadow-lift hover:scale-105 transition-all">
        <svg x-show="!open" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h8M8 14h5M21 12a9 9 0 01-13.2 7.9L3 21l1.1-4.8A9 9 0 1121 12z"/></svg>
        <svg x-show="open" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
    </button>

    {{-- Panel --}}
    <div x-show="open" x-transition
        class="absolute bottom-16 right-0 w-[22rem] max-w-[calc(100vw-2.5rem)] card shadow-lift flex flex-col overflow-hidden"
        style="height: 30rem; display:none">
        <div class="px-4 py-3 bg-leaf-600 text-white flex items-center gap-2.5">
            <span class="w-8 h-8 rounded-lg bg-white/15 grid place-items-center">
                <svg class="w-4.5 h-4.5 w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.8 15.5 3 21l1.2-5.4A8.5 8.5 0 119.8 15.5z"/></svg>
            </span>
            <div>
                <p class="text-sm font-semibold leading-tight">MarketLink Assistant</p>
                <p class="text-[11px] text-leaf-100/80">Ask about markets, pickup, products</p>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-4 space-y-3 bg-cream-50 dark:bg-leaf-950/40" id="assistant-messages" x-ref="messages">
            <template x-for="m in messages" :key="m.id">
                <div>
                    <div :class="m.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                        <div :class="m.role === 'user'
                            ? 'bg-leaf-600 text-white rounded-2xl rounded-br-sm px-3.5 py-2.5 text-sm max-w-[85%]'
                            : 'bg-white dark:bg-leaf-900 border border-stone-200 dark:border-leaf-800 rounded-2xl rounded-bl-sm px-3.5 py-2.5 text-sm max-w-[85%] text-stone-700 dark:text-stone-200'">
                            <p x-html="m.text" class="leading-relaxed"></p>
                        </div>
                    </div>

                    {{-- Product cards --}}
                    <template x-if="m.cards && m.cards.length">
                        <div class="space-y-1.5 mt-2">
                            <template x-for="c in m.cards" :key="c.url">
                                <a :href="c.url" class="card card-hover px-3 py-2 flex items-center justify-between gap-2">
                                    <span class="text-xs font-semibold text-stone-800 dark:text-stone-100 truncate" x-text="c.name"></span>
                                    <span class="text-[11px] text-leaf-700 dark:text-leaf-300 font-bold whitespace-nowrap" x-text="c.meta"></span>
                                </a>
                            </template>
                        </div>
                    </template>

                    {{-- CTA link --}}
                    <template x-if="m.link">
                        <a :href="m.link.url" class="btn-secondary btn-sm mt-2 inline-flex" x-text="m.link.label"></a>
                    </template>

                    {{-- Suggestion chips --}}
                    <template x-if="m.suggestions && m.suggestions.length">
                        <div class="flex flex-wrap gap-1.5 mt-2">
                            <template x-for="s in m.suggestions" :key="s">
                                <button @click="send(s)" class="badge-stone hover:bg-leaf-100 dark:hover:bg-leaf-800 transition-colors" x-text="s"></button>
                            </template>
                        </div>
                    </template>
                </div>
            </template>

            <div x-show="typing" class="flex justify-start">
                <div class="bg-white dark:bg-leaf-900 border border-stone-200 dark:border-leaf-800 rounded-2xl rounded-bl-sm px-4 py-3 flex gap-1">
                    <span class="w-2 h-2 rounded-full bg-stone-400 animate-bounce"></span>
                    <span class="w-2 h-2 rounded-full bg-stone-400 animate-bounce" style="animation-delay:.15s"></span>
                    <span class="w-2 h-2 rounded-full bg-stone-400 animate-bounce" style="animation-delay:.3s"></span>
                </div>
            </div>
        </div>

        <div class="p-3 border-t border-stone-200 dark:border-leaf-800 bg-white dark:bg-leaf-900">
            <div class="flex flex-wrap gap-1.5 mb-2">
                <template x-for="s in starter" :key="s">
                    <button @click="send(s)" class="badge-green !font-medium cursor-pointer" x-text="s"></button>
                </template>
            </div>
            <form @submit.prevent="submit()" class="flex gap-2">
                <input x-model="input" type="text" placeholder="Type a question..." class="input !py-2 !rounded-xl text-sm" autocomplete="off">
                <button type="submit" class="btn-primary !px-3.5 !py-2" :disabled="!input.trim() || loading">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function assistant() {
    return {
        open: false,
        input: '',
        loading: false,
        typing: false,
        starter: @json($suggestions),
        messages: [{ id: 0, role: 'bot', text: "Hi! I'm the MarketLink assistant. I can help you find products, check market timings, pickup windows, or track an order.", suggestions: [], cards: [] }],
        toggle() {
            this.open = !this.open;
            if (this.open) this.$nextTick(() => this.scroll());
        },
        scroll() {
            const el = this.$refs.messages;
            if (el) el.scrollTop = el.scrollHeight;
        },
        submit() {
            if (!this.input.trim() || this.loading) return;
            this.send(this.input.trim());
            this.input = '';
        },
        async send(text) {
            this.messages.push({ id: Date.now(), role: 'user', text: this.escape(text), suggestions: [], cards: [] });
            this.loading = true;
            this.typing = true;
            this.$nextTick(() => this.scroll());
            try {
                const res = await fetch(@json(route('assistant.chat')), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ message: text }),
                });
                const data = await res.json();
                this.messages.push({
                    id: Date.now() + 1,
                    role: 'bot',
                    text: this.escape(data.reply || 'Sorry, I could not process that.'),
                    suggestions: data.suggestions || [],
                    cards: data.cards || [],
                    link: data.link || null,
                });
            } catch (e) {
                this.messages.push({ id: Date.now() + 1, role: 'bot', text: 'Network error — please try again.', suggestions: [], cards: [] });
            }
            this.loading = false;
            this.typing = false;
            this.$nextTick(() => this.scroll());
        },
        escape(s) {
            return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>');
        },
    };
}
</script>
@endpush
