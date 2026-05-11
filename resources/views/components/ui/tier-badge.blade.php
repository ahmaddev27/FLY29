@props([
    'tier' => 'bronze', // bronze | silver | gold | diamond
    'size' => 'md',      // sm | md | lg
])

@php
    $tiers = [
        'bronze'  => ['label' => 'برونزي', 'bg' => 'var(--color-tier-bronze-50)',  'fg' => 'var(--color-tier-bronze)',  'icon' => 'M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z'],
        'silver'  => ['label' => 'فضي',   'bg' => 'var(--color-tier-silver-50)',  'fg' => 'var(--color-tier-silver)',  'icon' => 'M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z'],
        'gold'    => ['label' => 'ذهبي',   'bg' => 'var(--color-tier-gold-50)',    'fg' => 'var(--color-tier-gold)',    'icon' => 'M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z'],
        'diamond' => ['label' => 'ماسي',   'bg' => 'var(--color-tier-diamond-50)', 'fg' => 'var(--color-tier-diamond)', 'icon' => 'M6 2l6 7 6-7-3 6-3 14-3-14-3-6z'],
    ];

    $sizes = [
        'sm' => ['padding' => 'px-2.5 py-1', 'text' => 'text-xs', 'icon' => 'h-3 w-3'],
        'md' => ['padding' => 'px-3 py-1.5', 'text' => 'text-sm', 'icon' => 'h-4 w-4'],
        'lg' => ['padding' => 'px-4 py-2',   'text' => 'text-base font-semibold', 'icon' => 'h-5 w-5'],
    ];

    $t = $tiers[$tier] ?? $tiers['bronze'];
    $s = $sizes[$size] ?? $sizes['md'];
@endphp

<span
    {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-full font-medium border {$s['padding']} {$s['text']}"]) }}
    style="background-color: {{ $t['bg'] }}; color: {{ $t['fg'] }}; border-color: {{ $t['fg'] }}33;"
>
    <svg class="{{ $s['icon'] }}" fill="currentColor" viewBox="0 0 24 24">
        <path d="{{ $t['icon'] }}"/>
    </svg>
    <span>{{ $t['label'] }}</span>
</span>
