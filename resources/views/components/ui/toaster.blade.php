{{--
    Global toaster — drop this once in the root layout. Trigger from anywhere:

        window.toast({ variant: 'success', message: 'تم الحفظ' });
        // or: window.dispatchEvent(new CustomEvent('toast', { detail: {...} }));

    Variants: success | danger | warning | info  (default: info)
    Options: { variant, message, title?, duration? (ms, default 4000) }
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
                success: { ring: 'ring-[var(--color-success-500)]/30', icon: 'M5 13l4 4L19 7', iconBg: 'var(--color-success-50)',  iconFg: 'var(--color-success-500)' },
                danger:  { ring: 'ring-[var(--color-danger-500)]/30',  icon: 'M6 18L18 6M6 6l12 12', iconBg: 'var(--color-danger-50)', iconFg: 'var(--color-danger-500)' },
                warning: { ring: 'ring-[var(--color-warning-500)]/30', icon: 'M12 9v2m0 4h.01M5.07 19h13.86a2 2 0 001.78-2.92l-6.93-12.05a2 2 0 00-3.56 0L3.29 16.08A2 2 0 005.07 19z', iconBg: 'var(--color-warning-50)', iconFg: 'var(--color-warning-500)' },
                info:    { ring: 'ring-[var(--color-info-500)]/30',    icon: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', iconBg: 'var(--color-info-50)', iconFg: 'var(--color-info-500)' },
            };
            return map[variant] || map.info;
        },
    }"
    @toast.window="add($event.detail)"
    class="fixed top-4 start-4 z-[100] flex flex-col gap-3 pointer-events-none w-full max-w-sm"
    aria-live="polite"
    aria-atomic="true"
>
    <template x-for="t in toasts" :key="t.id">
        <div
            x-show="true"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="pointer-events-auto bg-white rounded-[var(--radius-md)] shadow-[var(--shadow-modal)] ring-1 overflow-hidden flex gap-3 p-3"
            :class="styles(t.variant).ring"
            role="alert"
        >
            {{-- Icon --}}
            <div
                class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center"
                :style="`background-color: ${styles(t.variant).iconBg}; color: ${styles(t.variant).iconFg};`"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" :d="styles(t.variant).icon"/>
                </svg>
            </div>

            {{-- Body --}}
            <div class="flex-1 min-w-0 pt-0.5">
                <p x-show="t.title" x-cloak class="font-semibold text-sm text-[var(--color-text-primary)] mb-0.5" x-text="t.title"></p>
                <p class="text-sm text-[var(--color-text-secondary)]" x-text="t.message"></p>
            </div>

            {{-- Close --}}
            <button
                type="button"
                @click="remove(t.id)"
                class="flex-shrink-0 text-[var(--color-text-muted)] hover:text-[var(--color-text-primary)] transition-base"
                aria-label="إغلاق"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </template>
</div>
