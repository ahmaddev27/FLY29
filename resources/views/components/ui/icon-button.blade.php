@props([
    'icon',                // icon name (see ui/icon component)
    'variant' => 'ghost',  // ghost | primary | danger | success | warning
    'size'    => 'md',     // sm | md | lg
    'href'    => null,
    'type'    => 'button',
    'tooltip' => null,
    'loading' => false,
    'disabled' => false,
])

@php
    $sizes = [
        'sm' => ['btn' => 'w-8 h-8',  'icon' => 'sm'],
        'md' => ['btn' => 'w-9 h-9',  'icon' => 'md'],
        'lg' => ['btn' => 'w-10 h-10','icon' => 'md'],
    ];

    $variants = [
        'ghost'   => 'text-slate-500 hover:bg-slate-100 hover:text-slate-800 focus-visible:ring-slate-200',
        'primary' => 'text-[var(--color-primary-600)] hover:bg-[var(--color-primary-50)] focus-visible:ring-[var(--color-primary-200)]',
        'danger'  => 'text-[var(--color-danger-600)] hover:bg-[var(--color-danger-50)] focus-visible:ring-rose-200',
        'success' => 'text-emerald-600 hover:bg-emerald-50 focus-visible:ring-emerald-200',
        'warning' => 'text-amber-600 hover:bg-amber-50 focus-visible:ring-amber-200',
    ];

    $s = $sizes[$size] ?? $sizes['md'];
    $classes = collect([
        'inline-flex items-center justify-center rounded-lg transition-all duration-150 align-middle',
        'focus:outline-none focus-visible:ring-4',
        'active:scale-90',
        'disabled:opacity-40 disabled:cursor-not-allowed',
        $s['btn'],
        $variants[$variant] ?? $variants['ghost'],
    ])->implode(' ');

    $iconName  = $loading ? 'refresh' : $icon;
    $iconSize  = $s['icon'];
    $iconClass = $loading ? 'animate-spin' : '';
@endphp

<span
    @if($tooltip) x-data="{ show: false }" x-on:mouseenter="show = true" x-on:mouseleave="show = false" @endif
    class="relative inline-block align-middle"
>
    @if($href)
        <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}
           @if($tooltip) x-on:focus="show = true" x-on:blur="show = false" @endif>
            <x-ui.icon :name="$iconName" :size="$iconSize" class="{{ $iconClass }}" />
        </a>
    @else
        <button
            type="{{ $type }}"
            @if($disabled) disabled @endif
            @if($tooltip) x-on:focus="show = true" x-on:blur="show = false" @endif
            {{ $attributes->merge(['class' => $classes]) }}
        >
            <x-ui.icon :name="$iconName" :size="$iconSize" class="{{ $iconClass }}" />
        </button>
    @endif

    @if($tooltip)
        <span
            x-show="show"
            x-cloak
            x-transition.opacity
            class="absolute z-50 px-2 py-1 text-xs font-medium text-white bg-[var(--color-text-primary)] rounded-[var(--radius-sm)] whitespace-nowrap pointer-events-none bottom-full mb-2 start-1/2 -translate-x-1/2"
            role="tooltip"
        >{{ $tooltip }}</span>
    @endif
</span>
