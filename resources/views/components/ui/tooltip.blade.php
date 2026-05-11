@props([
    'text'      => '',
    'position'  => 'top', // top | bottom | start | end
])

@php
    $positions = [
        'top'    => 'bottom-full mb-2 start-1/2 -translate-x-1/2',
        'bottom' => 'top-full mt-2 start-1/2 -translate-x-1/2',
        'start'  => 'end-full me-2 top-1/2 -translate-y-1/2',
        'end'    => 'start-full ms-2 top-1/2 -translate-y-1/2',
    ];
@endphp

<span
    x-data="{ show: false }"
    x-on:mouseenter="show = true"
    x-on:mouseleave="show = false"
    x-on:focus="show = true"
    x-on:blur="show = false"
    class="relative inline-block"
>
    {{ $slot }}

    <span
        x-show="show"
        x-cloak
        x-transition.opacity
        class="absolute z-50 px-2 py-1 text-xs font-medium text-white bg-[var(--color-text-primary)] rounded-[var(--radius-sm)] whitespace-nowrap pointer-events-none {{ $positions[$position] ?? $positions['top'] }}"
        role="tooltip"
    >
        {{ $text }}
    </span>
</span>
