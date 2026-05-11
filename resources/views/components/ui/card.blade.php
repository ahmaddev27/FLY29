@props([
    'title'     => null,
    'subtitle'  => null,
    'actions'   => null, // slot for buttons in header
    'padding'   => 'md', // none | sm | md | lg
    'shadow'    => 'card', // none | card | hover
])

@php
    $paddings = [
        'none' => '',
        'sm'   => 'p-4',
        'md'   => 'p-6',
        'lg'   => 'p-8',
    ];

    $shadows = [
        'none'  => '',
        'card'  => 'shadow-[var(--shadow-card)]',
        'hover' => 'shadow-[var(--shadow-card)] hover:shadow-[var(--shadow-card-hover)] transition-base',
    ];

    $classes = collect([
        'bg-white rounded-[var(--radius-md)] border border-[var(--color-surface-border)]',
        $shadows[$shadow] ?? $shadows['card'],
    ])->filter()->implode(' ');
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    @if($title || $subtitle || $actions)
        <div class="flex items-start justify-between gap-4 px-6 pt-6 pb-4 border-b border-[var(--color-surface-divider)]">
            <div>
                @if($title)
                    <h3 class="text-lg font-semibold text-[var(--color-text-primary)]">{{ $title }}</h3>
                @endif
                @if($subtitle)
                    <p class="text-sm text-[var(--color-text-secondary)] mt-1">{{ $subtitle }}</p>
                @endif
            </div>
            @if($actions)
                <div class="flex gap-2">{{ $actions }}</div>
            @endif
        </div>
        <div class="{{ $paddings[$padding] ?? $paddings['md'] }}">
            {{ $slot }}
        </div>
    @else
        <div class="{{ $paddings[$padding] ?? $paddings['md'] }}">
            {{ $slot }}
        </div>
    @endif
</div>
