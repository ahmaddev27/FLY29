{{--
    Global toaster — top-center stack.
    Trigger:  window.toast({ variant, message, title?, duration? })
    Variants: success | danger | warning | info  (default: info)
--}}

<div
    x-data="{
        toasts: [],
        nextId: 1,
        add(detail) {
            const id = this.nextId++;
            const toast = {
                id,
                variant: detail.variant || 'info',
                title:   detail.title   || null,
                message: detail.message || '',
                duration: detail.duration ?? 4000,
            };
            this.toasts.push(toast);
            if (toast.duration > 0) {
                setTimeout(() => this.remove(id), toast.duration);
            }
        },
        remove(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        },
        styles(variant) {
            const map = {
                success: {
                    bar:    'bg-emerald-500',
                    iconBg: 'bg-emerald-50',
                    iconFg: 'text-emerald-600',
                    icon:   'M5 13l4 4L19 7',
                },
                danger: {
                    bar:    'bg-rose-500',
                    iconBg: 'bg-rose-50',
                    iconFg: 'text-rose-600',
                    icon:   'M12 9v2m0 4h.01M5.07 19h13.86a2 2 0 001.78-2.92l-6.93-12.05a2 2 0 00-3.56 0L3.29 16.08A2 2 0 005.07 19z',
                },
                warning: {
                    bar:    'bg-amber-500',
                    iconBg: 'bg-amber-50',
                    iconFg: 'text-amber-600',
                    icon:   'M12 9v2m0 4h.01M12 4a8 8 0 100 16 8 8 0 000-16z',
                },
                info: {
                    bar:    'bg-sky-500',
                    iconBg: 'bg-sky-50',
                    iconFg: 'text-sky-600',
                    icon:   'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                },
            };
            return map[variant] || map.info;
        },
    }"
    @toast.window="add($event.detail)"
    class="fixed top-4 inset-x-0 z-[100] flex flex-col items-center gap-2 pointer-events-none px-4"
    aria-live="polite"
    aria-atomic="true"
>
    <template x-for="t in toasts" :key="t.id">
        <div
            x-show="true"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="pointer-events-auto w-full max-w-md bg-white rounded-xl shadow-2xl shadow-slate-900/10 ring-1 ring-slate-200 overflow-hidden flex"
            role="alert"
        >
            {{-- Coloured left bar (visual-right in RTL) --}}
            <div :class="styles(t.variant).bar" class="w-1 flex-shrink-0"></div>

            <div class="flex-1 flex items-start gap-3 p-4">
                {{-- Icon --}}
                <div
                    class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center"
                    :class="`${styles(t.variant).iconBg} ${styles(t.variant).iconFg}`"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="styles(t.variant).icon"/>
                    </svg>
                </div>

                {{-- Body --}}
                <div class="flex-1 min-w-0 pt-1">
                    <p x-show="t.title" x-cloak class="font-semibold text-sm text-slate-900 mb-0.5" x-text="t.title"></p>
                    <p class="text-sm text-slate-600 leading-relaxed" x-text="t.message"></p>
                </div>

                {{-- Close --}}
                <button
                    type="button"
                    @click="remove(t.id)"
                    class="flex-shrink-0 -mt-1 -me-1 p-1 rounded-md text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-base"
                    aria-label="إغلاق"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </template>
</div>
