@props([
    'name'    => 'modal',
    'title'   => null,
    'size'    => 'md', // sm | md | lg | xl
    'closeOnBackdrop' => true,
])

@php
    $sizes = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-2xl',
        'xl' => 'max-w-4xl',
    ];
@endphp

<div
    x-data="{ open: false }"
    x-on:open-modal.window="if ($event.detail === '{{ $name }}') open = true"
    x-on:close-modal.window="if ($event.detail === '{{ $name }}') open = false"
    x-on:keydown.escape.window="open = false"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="modal-title"
    role="dialog"
    aria-modal="true"
>
    {{-- Backdrop --}}
    <div
        x-show="open"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @if($closeOnBackdrop) x-on:click="open = false" @endif
        class="fixed inset-0 bg-black/50 transition-opacity"
        aria-hidden="true"
    ></div>

    <div class="flex min-h-full items-center justify-center p-4">
        <div
            x-show="open"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            x-on:click.stop
            class="relative bg-white rounded-[var(--radius-lg)] shadow-[var(--shadow-modal)] w-full {{ $sizes[$size] ?? $sizes['md'] }}"
        >
            {{-- Header --}}
            @if($title)
                <div class="flex items-start justify-between gap-4 px-6 pt-5 pb-4 border-b border-[var(--color-surface-divider)]">
                    <h3 id="modal-title" class="text-lg font-semibold text-[var(--color-text-primary)]">{{ $title }}</h3>
                    <button
                        type="button"
                        x-on:click="open = false"
                        class="text-[var(--color-text-muted)] hover:text-[var(--color-text-primary)] transition-base"
                        aria-label="إغلاق"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            @endif

            {{-- Body --}}
            <div class="px-6 py-5">
                {{ $slot }}
            </div>

            {{-- Footer (optional) --}}
            @isset($footer)
                <div class="flex justify-end gap-2 px-6 py-4 bg-[var(--color-surface-tertiary)] border-t border-[var(--color-surface-divider)] rounded-b-[var(--radius-lg)]">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
</div>
