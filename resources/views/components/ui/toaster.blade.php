{{--
    Global toaster — top-center stack.
    Trigger:  window.toast({ variant, message, title?, duration? })
    Variants: success | danger | warning | info  (default: info)

    Styling: split layout — fly29 navy strip on the start (sidebar colour)
    carries the icon; clean white body for the message. Soft brand-tinted
    shadow + subtle ring. Animated progress bar shows remaining time.
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
                    accent: 'bg-emerald-500',
                    iconRing: 'ring-emerald-300/50',
                    title:  'text-emerald-700',
                    progress: 'bg-emerald-400',
                    icon:   'M5 13l4 4L19 7',
                },
                danger: {
                    accent: 'bg-rose-500',
                    iconRing: 'ring-rose-300/50',
                    title:  'text-rose-700',
                    progress: 'bg-rose-400',
                    icon:   'M12 9v2m0 4h.01M5.07 19h13.86a2 2 0 001.78-2.92l-6.93-12.05a2 2 0 00-3.56 0L3.29 16.08A2 2 0 005.07 19z',
                },
                warning: {
                    accent: 'bg-amber-500',
                    iconRing: 'ring-amber-300/50',
                    title:  'text-amber-700',
                    progress: 'bg-amber-400',
                    icon:   'M12 9v2m0 4h.01M12 4a8 8 0 100 16 8 8 0 000-16z',
                },
                info: {
                    accent: 'bg-sky-500',
                    iconRing: 'ring-sky-300/50',
                    title:  'text-slate-900',
                    progress: 'bg-sky-400',
                    icon:   'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                },
            };
            return map[variant] || map.info;
        },
    }"
    @toast.window="add($event.detail)"
    class="fixed top-5 inset-x-0 z-[100] flex flex-col items-center gap-3 pointer-events-none px-4"
    aria-live="polite"
    aria-atomic="true"
>
    <template x-for="t in toasts" :key="t.id">
        <div
            x-show="true"
            x-transition:enter="transition ease-out duration-400"
            x-transition:enter-start="opacity-0 -translate-y-6 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-250"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 -translate-y-3 scale-95"
            class="pointer-events-auto w-full max-w-sm relative overflow-hidden rounded-2xl bg-white shadow-[0_20px_50px_-12px] shadow-slate-900/25 ring-1 ring-slate-200/70 flex"
            role="alert"
        >
            {{-- Navy accent strip (matches sidebar) with floating icon --}}
            <div
                class="flex-shrink-0 relative w-14 flex items-center justify-center"
                :style="`background: linear-gradient(160deg, var(--color-primary-700) 0%, var(--color-primary-900) 100%);`"
            >
                {{-- Subtle inner highlight on the strip --}}
                <div class="absolute inset-y-0 start-0 w-px bg-white/15"></div>

                {{-- Icon chip --}}
                <div
                    class="relative w-9 h-9 rounded-full bg-white/10 backdrop-blur flex items-center justify-center ring-2 text-white"
                    :class="styles(t.variant).iconRing"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="styles(t.variant).icon"/>
                    </svg>
                </div>
            </div>

            {{-- Body --}}
            <div class="flex-1 min-w-0 flex items-start gap-3 ps-4 pe-3 py-3.5">
                <div class="flex-1 min-w-0">
                    <p
                        x-show="t.title"
                        x-cloak
                        class="font-bold text-sm mb-0.5 tracking-tight"
                        :class="styles(t.variant).title"
                        x-text="t.title"
                    ></p>
                    <p class="text-[13.5px] text-slate-600 leading-relaxed" x-text="t.message"></p>
                </div>

                {{-- Close --}}
                <button
                    type="button"
                    @click="remove(t.id)"
                    class="flex-shrink-0 w-7 h-7 -mt-0.5 -me-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 flex items-center justify-center transition-base"
                    aria-label="إغلاق"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Progress bar at the bottom --}}
            <div
                x-show="t.duration > 0"
                class="absolute bottom-0 inset-x-0 h-[3px] bg-slate-100 overflow-hidden"
            >
                <div
                    class="h-full origin-end"
                    :class="styles(t.variant).progress"
                    :style="`animation: toast-progress ${t.duration}ms linear forwards;`"
                ></div>
            </div>
        </div>
    </template>
</div>

@once
    @push('styles')
        <style>
            @keyframes toast-progress {
                from { transform: scaleX(1); }
                to   { transform: scaleX(0); }
            }
        </style>
    @endpush
@endonce
