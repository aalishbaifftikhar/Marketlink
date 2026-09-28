@extends('layouts.customer')

@section('title', 'AI Assistant')

@section('content')
    <div x-data="assistantPage()" class="mx-auto max-w-3xl">
        <div class="card overflow-hidden flex flex-col" style="height: calc(100vh - 11.5rem); min-height: 30rem;">

            {{-- Header --}}
            <div class="px-5 py-4 bg-leaf-600 text-white flex items-center gap-3 shrink-0">
                <span class="w-10 h-10 rounded-xl bg-white/15 grid place-items-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.8 15.5 3 21l1.2-5.4A8.5 8.5 0 119.8 15.5z"/></svg>
                </span>
                <div class="min-w-0">
                    <p class="font-display text-lg font-semibold leading-tight">MarketLink Assistant</p>
                    <p class="text-xs text-leaf-100/80">Ask about markets, pickup slots, products or your orders</p>
                </div>
            </div>

            {{-- Messages --}}
            <div class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-4 bg-cream-100/60 dark:bg-leaf-950/40" x-ref="messages">
                <template x-for="m in messages" :key="m.id">
                    <div>
                        <div :class="m.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                            <div :class="m.role === 'user'
                                ? 'bg-leaf-600 text-white rounded-2xl rounded-br-sm px-4 py-3 text-sm max-w-[85%] shadow-soft'
                                : 'bg-white dark:bg-leaf-900 border border-stone-200 dark:border-leaf-800 rounded-2xl rounded-bl-sm px-4 py-3 text-sm max-w-[85%] text-stone-700 dark:text-stone-200 shadow-soft'">
                                <p x-html="m.text" class="leading-relaxed"></p>
                            </div>
                        </div>

                        {{-- Result cards --}}
                        <template x-if="m.cards && m.cards.length">
                            <div class="space-y-2 mt-2 sm:pl-2">
                                <template x-for="c in m.cards" :key="c.url">
                                    <a :href="c.url" class="card card-hover px-4 py-3 flex items-center justify-between gap-3 max-w-md">
                                        <span class="text-sm font-semibold text-stone-800 dark:text-stone-100 truncate" x-text="c.name"></span>
                                        <span class="text-xs text-leaf-700 dark:text-leaf-300 font-bold whitespace-nowrap" x-text="c.meta"></span>
                                    </a>
                                </template>
                            </div>
                        </template>

                        {{-- CTA link --}}
                        <template x-if="m.link">
                            <div class="mt-2 sm:pl-2">
                                <a :href="m.link.url" class="btn-secondary btn-sm inline-flex" x-text="m.link.label"></a>
                            </div>
                        </template>

                        {{-- Follow-up suggestion chips --}}
                        <template x-if="m.suggestions && m.suggestions.length">
                            <div class="flex flex-wrap gap-1.5 mt-2 sm:pl-2">
                                <template x-for="s in m.suggestions" :key="s">
                                    <button type="button" @click="send(s)" class="badge-stone hover:bg-leaf-100 dark:hover:bg-leaf-800 transition-colors" x-text="s"></button>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>

                {{-- Typing indicator --}}
                <div x-show="typing" class="flex justify-start">
                    <div class="bg-white dark:bg-leaf-900 border border-stone-200 dark:border-leaf-800 rounded-2xl rounded-bl-sm px-4 py-3.5 flex gap-1.5 shadow-soft">
                        <span class="w-2 h-2 rounded-full bg-stone-400 animate-bounce"></span>
                        <span class="w-2 h-2 rounded-full bg-stone-400 animate-bounce" style="animation-delay:.15s"></span>
                        <span class="w-2 h-2 rounded-full bg-stone-400 animate-bounce" style="animation-delay:.3s"></span>
                    </div>
                </div>
            </div>

            {{-- Input --}}
            <div class="p-3 sm:p-4 border-t border-stone-200 dark:border-leaf-800 bg-white dark:bg-leaf-900 shrink-0">
                <div class="flex flex-wrap gap-1.5 mb-3">
                    <template x-for="s in starter" :key="s">
                        <button type="button" @click="send(s)" class="badge-green !font-medium cursor-pointer" x-text="s"></button>
                    </template>
                </div>
                <form @submit.prevent="submit()" class="flex gap-2">
                    <input x-model="input" type="text" placeholder="Type a question..." class="input !py-3" autocomplete="off">
                    <button type="submit" class="btn-primary !px-5" :disabled="!input.trim() || loading" aria-label="Send">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function assistantPage() {
    return {
        input: '',
        loading: false,
        typing: false,
        starter: ["What's fresh this week?", 'Which markets are open on Saturday?', 'Show me organic vegetables'],
        messages: [{ id: 0, role: 'bot', text: "Hi! I'm the MarketLink assistant. I can help you find fresh produce, check market timings and pickup windows, or track your pre-orders.", suggestions: [], cards: [] }],
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
