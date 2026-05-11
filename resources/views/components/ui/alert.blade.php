@props([
    'variant'    => 'info', // success | warning | danger | info
    'title'      => null,
    'dismissible' => false,
])

@php
    $variants = [
        'success' => [
            'bg'      => 'var(--color-success-50)',
            'border'  => 'var(--color-success-500)',
            'text'    => 'var(--color-success-700)',
            'icon'    => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
        'warning' => [
            'bg'      => 'var(--color-warning-50)',
            'border'  => 'var(--color-warning-500)',
            'text'    => 'var(--color-warning-700)',
            'icon'    => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
        ],
        'danger'  => [
            'bg'      => 'var(--color-danger-50)',
            'border'  => 'var(--color-danger-500)',
            'text'    => 'var(--color-danger-700)',
            'icon'    => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
        'info'    => [
            'bg'      => 'var(--color-info-50)',
            'border'  => 'var(--color-info-500)',
            'text'    => 'var(--color-info-700)',
            'icon'    => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
    ];

    $v = $variants[$variant] ?? $variants['info'];
@endphp

<div
    @if($dismissible) x-data="{ show: true }" x-show="show" @endif
    role="alert"
    {{ $attributes->merge(['class' => 'flex items-start gap-3 p-4 border-s-4 rounded-[var(--radius-sm)]']) }}
    style="background-color: {{ $v['bg'] }}; border-color: {{ $v['border'] }}; color: {{ $v['text'] }};"
>
    <svg class="h-5 w-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $v['icon'] }}" />
    </svg>

    <div class="flex-1 min-w-0">
        @if($title)
            <h4 class="font-semibold mb-1">{{ $title }}</h4>
        @endif
        <div class="text-sm">{{ $slot }}</div>
    </div>

    @if($dismissible)
        <button
            type="button"
            x-on:click="show = false"
            class="flex-shrink-0 opacity-60 hover:opacity-100 transition-base"
            aria-label="إغلاق"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    @endif
</div>
