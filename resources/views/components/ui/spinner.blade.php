@props([
    'size'  => 'md',  // sm | md | lg
    'color' => 'primary', // primary | white | muted
])

@php
    $sizes  = ['sm' => 'h-4 w-4', 'md' => 'h-6 w-6', 'lg' => 'h-10 w-10'];
    $colors = [
        'primary' => 'text-[var(--color-primary-500)]',
        'white'   => 'text-white',
        'muted'   => 'text-[var(--color-text-muted)]',
    ];
@endphp

<svg
    {{ $attributes->merge(['class' => "animate-spin {$sizes[$size]} {$colors[$color]}"]) }}
    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
    role="status" aria-label="جاري التحميل"
>
    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
</svg>
