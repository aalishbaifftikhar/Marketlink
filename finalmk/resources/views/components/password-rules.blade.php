{{-- Live password checklist; requires the shared Alpine "passwordRules" component. --}}
<div x-cloak {{ $attributes->merge(['class' => 'mt-2 rounded-xl border border-stone-200/80 bg-stone-50 px-3.5 py-2.5 dark:border-leaf-800 dark:bg-leaf-900/40']) }}>
    <p class="text-xs font-medium text-stone-500 dark:text-stone-400">Your password needs:</p>
    <ul class="mt-1.5 grid grid-cols-1 gap-x-4 gap-y-1 sm:grid-cols-2">
        <template x-for="rule in rules" :key="rule.key">
            <li class="flex items-center gap-1.5 text-xs transition-colors"
                :class="checks[rule.key] ? 'text-leaf-700 dark:text-leaf-300' : 'text-stone-400 dark:text-stone-500'">
                <svg x-show="checks[rule.key]" class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <svg x-show="!checks[rule.key]" class="h-3.5 w-3.5 shrink-0 text-stone-300 dark:text-leaf-700" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/></svg>
                <span x-text="rule.label"></span>
            </li>
        </template>
    </ul>
</div>
