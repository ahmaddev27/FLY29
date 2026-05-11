@props([
    'variant' => 'primary', // primary | secondary | cta | danger | ghost | outline
    'size'    => 'md',      // sm | md | lg
    'type'    => 'button',
    'href'    => null,
    'icon'    => null,      // optional icon slot name
    'loading' => false,
    'disabled' => false,
    'full'    => false,     // full-width
])

@php
    $base = 'inline-flex items-center justify-center gap-2 font-medium rounded-[var(--radius-sm)] '
          . 'transition-base disabled:opacity-50 disabled:cursor-not-allowed';

    $sizes = [
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2 text-base',
        'lg' => 'px-6 py-3 text-lg',
    ];

    $variants = [
        'primary'   => 'bg-[var(--color-primary-500)] text-white hover:bg-[var(--color-primary-600)] active:bg-[var(--color-primary-700)]',
        'secondary' => 'bg-[var(--color-surface-secondary)] text-[var(--color-text-primary)] border border-[var(--color-surface-border)] hover:bg-[var(--color-surface-divider)]',
        'cta'       => 'bg-[var(--color-cta-500)] text-white hover:bg-[var(--color-cta-600)] active:bg-[var(--color-cta-700)]',
        'danger'    => 'bg-[var(--color-danger-500)] text-white hover:bg-[var(--color-danger-600)] active:bg-[var(--color-danger-700)]',
        'ghost'     => 'text-[var(--color-text-primary)] hover:bg-[var(--color-surface-divider)]',
        'outline'   => 'bg-transparent text-[var(--color-primary-500)] border border-[var(--color-primary-500)] hover:bg-[var(--color-primary-50)]',
    ];

    $width = $full ? 'w-full' : '';

    $classes = collect([$base, $sizes[$size] ?? $sizes['md'], $variants[$variant] ?? $variants['primary'], $width])
        ->filter()->implode(' ');
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($loading)
            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
            </svg>
        @endif
        {{ $slot }}
    </a>
@else
    <button
        type="{{ $type }}"
        @if($disabled || $loading) disabled @endif
        {{ $attributes->merge(['class' => $classes]) }}
    >
        @if($loading)
            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
            </svg>
        @endif
        {{ $slot }}
    </button>
@endif
