@props([
    'variant'     => 'primary',   // primary | secondary | cta | danger | ghost | outline
    'size'        => 'md',        // sm | md | lg
    'type'        => 'button',    // button | submit | reset
    'href'        => null,        // إذا تم تمريره، يصبح <a> بدل <button>
    'loading'     => false,       // initial loading state (SSR)
    'disabled'    => false,
    'full'        => false,       // w-full
    'autoLoading' => true,        // ★ افتراضياً: يفعّل loading تلقائياً عند click/submit
    'loadingText' => 'جاري التحميل…',
])

@php
    $base = 'relative inline-flex items-center justify-center gap-2 font-medium rounded-[var(--radius-sm)] '
          . 'transition-base disabled:opacity-50 disabled:cursor-not-allowed select-none';

    $sizes = [
        'sm' => 'px-3 py-1.5 text-sm min-h-[32px]',
        'md' => 'px-4 py-2 text-base min-h-[40px]',
        'lg' => 'px-6 py-3 text-lg min-h-[48px]',
    ];

    $variants = [
        'primary'   => 'bg-[var(--color-primary-500)] text-white hover:bg-[var(--color-primary-600)] active:bg-[var(--color-primary-700)]',
        'secondary' => 'bg-[var(--color-surface-secondary)] text-[var(--color-text-primary)] border border-[var(--color-surface-border)] hover:bg-[var(--color-surface-divider)]',
        'cta'       => 'bg-[var(--color-cta-500)] text-white hover:bg-[var(--color-cta-600)] active:bg-[var(--color-cta-700)]',
        'danger'    => 'bg-[var(--color-danger-500)] text-white hover:bg-[var(--color-danger-600)] active:bg-[var(--color-danger-700)]',
        'ghost'     => 'text-[var(--color-text-primary)] hover:bg-[var(--color-surface-divider)]',
        'outline'   => 'bg-transparent text-[var(--color-primary-500)] border border-[var(--color-primary-500)] hover:bg-[var(--color-primary-50)]',
    ];

    $width   = $full ? 'w-full' : '';
    $classes = collect([$base, $sizes[$size] ?? $sizes['md'], $variants[$variant] ?? $variants['primary'], $width])
        ->filter()->implode(' ');

    $initialLoading  = $loading ? 'true' : 'false';
    $initialDisabled = $disabled ? 'true' : 'false';
    $autoLoadEnabled = $autoLoading ? 'true' : 'false';
@endphp

{{--
    ============================================
    Smart-loading Button
    --------------------------------------------
    Default behavior (autoLoading=true):
      • type=submit  → loading=true تلقائياً عند submit الفورم (بعد ما يمر validation الـ HTML5)
      • <a> link     → loading=true تلقائياً عند click + يمنع double-click
      • Manual       → استدعِ من خارج: $dispatch('button-loading', {target: 'myBtnId', state: true})
                       أو wrap الزر بـ x-ref واستخدم: $refs.myBtn.dispatchEvent(new CustomEvent('set-loading', {detail: true}))

    عند autoLoading=false:
      • يعمل كزر عادي (يدوي بحت). استخدم :loading="$state" من PHP لو محتاج SSR.
    ============================================
--}}

@if($href)
    {{-- ============== Link variant ============== --}}
    <a
        href="{{ $href }}"
        x-data="{
            loading: {{ $initialLoading }},
            disabled: {{ $initialDisabled }},
            click(e) {
                if (this.loading || this.disabled) {
                    e.preventDefault();
                    return;
                }
                {{ $autoLoadEnabled }} && (this.loading = true);
            }
        }"
        x-on:click="click($event)"
        x-on:set-loading.stop="loading = $event.detail"
        :class="(loading || disabled) ? 'opacity-75 cursor-wait pointer-events-none' : ''"
        :aria-busy="loading"
        {{ $attributes->merge(['class' => $classes]) }}
    >
        {{-- Spinner (visible when loading) --}}
        <svg x-show="loading" x-cloak
             class="animate-spin h-4 w-4 flex-shrink-0"
             xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
        </svg>

        {{-- Loading label --}}
        <span x-show="loading" x-cloak>{{ $loadingText }}</span>

        {{-- Default content --}}
        <span x-show="!loading">{{ $slot }}</span>
    </a>

@else
    {{-- ============== Button variant ============== --}}
    <button
        type="{{ $type }}"
        x-data="{
            loading: {{ $initialLoading }},
            disabled: {{ $initialDisabled }},
            click(e) {
                if (this.loading || this.disabled) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    return;
                }
                // Non-submit buttons: enable loading on click
                if (e.target.type !== 'submit' && {{ $autoLoadEnabled }}) {
                    this.loading = true;
                }
            }
        }"
        x-init="
            // Submit button: listen to form's submit event so we only flip after validation passes.
            if ({{ $autoLoadEnabled }} && $el.type === 'submit') {
                const form = $el.closest('form');
                if (form) {
                    form.addEventListener('submit', () => { loading = true; });
                }
            }
        "
        x-on:click="click($event)"
        x-on:set-loading.stop="loading = $event.detail"
        :disabled="loading || disabled"
        :class="loading ? 'cursor-wait' : ''"
        :aria-busy="loading"
        {{ $attributes->merge(['class' => $classes]) }}
    >
        {{-- Spinner (visible when loading) --}}
        <svg x-show="loading" x-cloak
             class="animate-spin h-4 w-4 flex-shrink-0"
             xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
        </svg>

        {{-- Loading label --}}
        <span x-show="loading" x-cloak>{{ $loadingText }}</span>

        {{-- Default content --}}
        <span x-show="!loading">{{ $slot }}</span>
    </button>
@endif
