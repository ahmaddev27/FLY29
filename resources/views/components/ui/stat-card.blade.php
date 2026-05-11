@props([
    'label'    => '',
    'value'    => '',
    'icon'     => null,         // SVG slot
    'trend'    => null,         // up | down | neutral
    'trendValue' => null,       // e.g. '+12%'
    'color'    => 'primary',    // primary | cta | accent | danger | tier-diamond | tier-gold | tier-silver | tier-bronze
])

@php
    $colorMap = [
        'primary'      => ['bg' => 'var(--color-primary-50)',      'fg' => 'var(--color-primary-600)'],
        'cta'          => ['bg' => 'var(--color-cta-50)',          'fg' => 'var(--color-cta-600)'],
        'accent'       => ['bg' => 'var(--color-accent-50)',       'fg' => 'var(--color-accent-600)'],
        'danger'       => ['bg' => 'var(--color-danger-50)',       'fg' => 'var(--color-danger-600)'],
        'tier-diamond' => ['bg' => 'var(--color-tier-diamond-50)', 'fg' => 'var(--color-tier-diamond)'],
        'tier-gold'    => ['bg' => 'var(--color-tier-gold-50)',    'fg' => 'var(--color-tier-gold)'],
        'tier-silver'  => ['bg' => 'var(--color-tier-silver-50)',  'fg' => 'var(--color-tier-silver)'],
        'tier-bronze'  => ['bg' => 'var(--color-tier-bronze-50)',  'fg' => 'var(--color-tier-bronze)'],
    ];

    $c = $colorMap[$color] ?? $colorMap['primary'];

    $trendColors = [
        'up'      => 'text-[var(--color-success-700)] bg-[var(--color-success-50)]',
        'down'    => 'text-[var(--color-danger-700)] bg-[var(--color-danger-50)]',
        'neutral' => 'text-[var(--color-text-secondary)] bg-[var(--color-surface-secondary)]',
    ];

    $trendIcons = [
        'up'      => 'M5 10l7-7m0 0l7 7m-7-7v18',
        'down'    => 'M19 14l-7 7m0 0l-7-7m7 7V3',
        'neutral' => 'M5 12h14',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'bg-white rounded-[var(--radius-md)] border border-[var(--color-surface-border)] p-3 sm:p-5 shadow-[var(--shadow-card)]']) }}>
    <div class="flex items-start justify-between gap-2 sm:gap-3">
        <div class="flex-1 min-w-0">
            <p class="text-xs sm:text-sm text-[var(--color-text-secondary)] mb-1 truncate">{{ $label }}</p>
            <p class="text-lg sm:text-2xl font-bold text-[var(--color-text-primary)] truncate">{{ $value }}</p>

            @if($trend && $trendValue)
                <div class="mt-2 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium {{ $trendColors[$trend] ?? '' }}">
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $trendIcons[$trend] }}" />
                    </svg>
                    <span>{{ $trendValue }}</span>
                </div>
            @endif
        </div>

        @if($icon)
            <div
                class="hidden sm:flex flex-shrink-0 w-12 h-12 rounded-[var(--radius-md)] items-center justify-center"
                style="background-color: {{ $c['bg'] }}; color: {{ $c['fg'] }};"
            >
                {{ $icon }}
            </div>
        @endif
    </div>
</div>
