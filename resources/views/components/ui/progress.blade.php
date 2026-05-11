@props([
    'value' => 0,
    'max'   => 100,
    'color' => 'primary', // primary | cta | accent | danger | tier-diamond | tier-gold | tier-silver | tier-bronze
    'showLabel' => true,
    'size'  => 'md', // sm | md | lg
    'label' => null,
])

@php
    $pct = $max > 0 ? min(100, max(0, ($value / $max) * 100)) : 0;

    $colors = [
        'primary'      => 'var(--color-primary-500)',
        'cta'          => 'var(--color-cta-500)',
        'accent'       => 'var(--color-accent-500)',
        'danger'       => 'var(--color-danger-500)',
        'tier-diamond' => 'var(--color-tier-diamond)',
        'tier-gold'    => 'var(--color-tier-gold)',
        'tier-silver'  => 'var(--color-tier-silver)',
        'tier-bronze'  => 'var(--color-tier-bronze)',
    ];

    $heights = ['sm' => 'h-1.5', 'md' => 'h-2.5', 'lg' => 'h-4'];

    $fill = $colors[$color] ?? $colors['primary'];
@endphp

<div {{ $attributes }}>
    @if($showLabel)
        <div class="flex items-center justify-between mb-1.5 text-sm">
            <span class="text-[var(--color-text-secondary)]">{{ $label ?? '' }}</span>
            <span class="font-semibold text-[var(--color-text-primary)]">{{ $value }} / {{ $max }}</span>
        </div>
    @endif

    <div class="w-full bg-[var(--color-surface-divider)] rounded-full overflow-hidden {{ $heights[$size] ?? $heights['md'] }}"
         role="progressbar" aria-valuemin="0" aria-valuemax="{{ $max }}" aria-valuenow="{{ $value }}">
        <div
            class="h-full rounded-full transition-all duration-500"
            style="width: {{ $pct }}%; background-color: {{ $fill }};"
        ></div>
    </div>
</div>
