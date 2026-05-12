@props([
    'variant'     => 'primary',   // primary | secondary | cta | danger | ghost | outline
    'size'        => 'md',        // sm | md | lg
    'type'        => 'button',    // button | submit | reset
    'href'        => null,        // إذا تم تمريره، يصبح <a> بدل <button>
    'loading'     => false,       // initial loading state (SSR)
    'disabled'    => false,
    'full'        => false,       // w-full
    'autoLoading' => true,        // ★ افتراضياً: يفعّل loading تلقائياً
    'loadingText' => 'جاري التحميل…',
])

@php
    $base = 'group/btn relative inline-flex items-center justify-center gap-2 font-semibold rounded-xl '
          . 'transition-all duration-150 ease-out select-none whitespace-nowrap '
          . 'focus:outline-none focus-visible:ring-4 focus-visible:ring-offset-0 '
          . 'disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:!translate-y-0 disabled:hover:!shadow-none '
          . 'active:translate-y-0 active:scale-[0.98]';

    $sizes = [
        'sm' => 'px-3.5 py-1.5 text-sm min-h-[34px]',
        'md' => 'px-4 py-2 text-sm min-h-[40px]',
        'lg' => 'px-5 py-2.5 text-base min-h-[46px]',
    ];

    // Each variant: solid colour with a subtle inset highlight + glow that
    // grows on hover. Hover lifts the button by 1px. Active resets the lift
    // and applies a slight squash via active:scale on the base.
    $variants = [
        'primary'   => 'bg-[var(--color-primary-500)] text-white '
                     . 'shadow-[0_2px_4px_-1px_rgba(0,102,204,0.25),inset_0_1px_0_rgba(255,255,255,0.12)] '
                     . 'hover:bg-[var(--color-primary-600)] hover:-translate-y-0.5 '
                     . 'hover:shadow-[0_8px_20px_-4px_rgba(0,102,204,0.4),inset_0_1px_0_rgba(255,255,255,0.12)] '
                     . 'focus-visible:ring-[var(--color-primary-200)]',

        'cta'       => 'bg-[var(--color-cta-500)] text-white '
                     . 'shadow-[0_2px_4px_-1px_rgba(16,185,129,0.25),inset_0_1px_0_rgba(255,255,255,0.12)] '
                     . 'hover:bg-[var(--color-cta-600)] hover:-translate-y-0.5 '
                     . 'hover:shadow-[0_8px_20px_-4px_rgba(16,185,129,0.4),inset_0_1px_0_rgba(255,255,255,0.12)] '
                     . 'focus-visible:ring-emerald-200',

        'danger'    => 'bg-[var(--color-danger-500)] text-white '
                     . 'shadow-[0_2px_4px_-1px_rgba(239,68,68,0.25),inset_0_1px_0_rgba(255,255,255,0.12)] '
                     . 'hover:bg-[var(--color-danger-600)] hover:-translate-y-0.5 '
                     . 'hover:shadow-[0_8px_20px_-4px_rgba(239,68,68,0.4),inset_0_1px_0_rgba(255,255,255,0.12)] '
                     . 'focus-visible:ring-rose-200',

        'secondary' => 'bg-white text-slate-700 ring-1 ring-slate-200 '
                     . 'shadow-[0_1px_2px_rgba(0,0,0,0.05)] '
                     . 'hover:bg-slate-50 hover:ring-slate-300 hover:-translate-y-0.5 '
                     . 'hover:shadow-[0_4px_12px_-2px_rgba(0,0,0,0.08)] '
                     . 'focus-visible:ring-slate-200',

        'outline'   => 'bg-transparent text-[var(--color-primary-600)] ring-1.5 ring-[var(--color-primary-500)]/40 '
                     . 'hover:bg-[var(--color-primary-50)] hover:ring-[var(--color-primary-500)] '
                     . 'focus-visible:ring-[var(--color-primary-200)]',

        'ghost'     => 'bg-transparent text-slate-600 '
                     . 'hover:bg-slate-100 hover:text-slate-900 '
                     . 'focus-visible:ring-slate-200',
    ];

    $width   = $full ? 'w-full' : '';
    $classes = collect([$base, $sizes[$size] ?? $sizes['md'], $variants[$variant] ?? $variants['primary'], $width])
        ->filter()->implode(' ');

    $initialLoading  = $loading ? 'true' : 'false';
    $initialDisabled = $disabled ? 'true' : 'false';
    $autoForm        = $autoLoading && $type === 'submit';
    $autoClick       = $autoLoading && $type !== 'submit';
@endphp

{{--
    ============================================
    Smart-loading Button
    --------------------------------------------
    autoLoading=true (default):
      • type=submit  → intercepts form submit, sets loading=true, waits 2
                       animation frames so the spinner paints, THEN re-submits
                       the form programmatically (browser-native validation
                       still runs because submit event only fires after it).
      • <a> link     → sets loading=true on click + blocks double-click.
      • other        → sets loading=true on click (caller usually resets it
                       via $dispatch('set-loading', false)).

    Reset loading from outside (AJAX flows):
      $el.dispatchEvent(new CustomEvent('set-loading', { detail: false }))
    ============================================
--}}

@if($href)
    {{-- ============== Link variant ============== --}}
    <a
        href="{{ $href }}"
        x-data="{ loading: {{ $initialLoading }}, disabled: {{ $initialDisabled }} }"
        x-on:click="
            if (loading || disabled) { $event.preventDefault(); return; }
            {{ $autoLoading ? 'loading = true;' : '' }}
        "
        x-on:set-loading.stop="loading = $event.detail"
        :class="(loading || disabled) ? 'opacity-75 cursor-wait pointer-events-none' : ''"
        :aria-busy="loading"
        {{ $attributes->merge(['class' => $classes]) }}
    >
        <svg x-show="loading" x-cloak
             class="animate-spin h-4 w-4 flex-shrink-0"
             xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
        </svg>
        <span x-show="loading" x-cloak>{{ $loadingText }}</span>
        <span x-show="!loading">{{ $slot }}</span>
    </a>

@else
    {{-- ============== Button variant ============== --}}
    <button
        type="{{ $type }}"
        x-data="{ loading: {{ $initialLoading }}, disabled: {{ $initialDisabled }} }"
        @if($autoForm)
        x-init="
            const form = $el.closest('form');
            if (form) {
                let submitting = false;
                form.addEventListener('submit', (e) => {
                    if (submitting) return;
                    submitting = true;
                    e.preventDefault();
                    loading = true;
                    // wait two animation frames so the spinner actually paints
                    // before the browser navigates away.
                    requestAnimationFrame(() =>
                        requestAnimationFrame(() => form.submit())
                    );
                });
            }
        "
        @endif
        x-on:click="
            if (loading || disabled) {
                $event.preventDefault();
                $event.stopImmediatePropagation();
                return;
            }
            {{ $autoClick ? 'loading = true;' : '' }}
        "
        x-on:set-loading.stop="loading = $event.detail"
        :disabled="loading || disabled"
        :class="loading ? 'cursor-wait' : ''"
        :aria-busy="loading"
        {{ $attributes->merge(['class' => $classes]) }}
    >
        <svg x-show="loading" x-cloak
             class="animate-spin h-4 w-4 flex-shrink-0"
             xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
        </svg>
        <span x-show="loading" x-cloak>{{ $loadingText }}</span>
        <span x-show="!loading">{{ $slot }}</span>
    </button>
@endif
