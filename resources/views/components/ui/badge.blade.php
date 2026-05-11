@props([
    'variant' => 'neutral', // neutral | primary | success | warning | danger | info
    'size'    => 'md',      // sm | md
    'dot'     => false,
])

@php
    $sizes = [
        'sm' => 'px-2 py-0.5 text-xs',
        'md' => 'px-2.5 py-1 text-sm',
    ];

    $variants = [
        'neutral' => 'bg-[var(--color-surface-secondary)] text-[var(--color-text-secondary)] border-[var(--color-surface-border)]',
        'primary' => 'bg-[var(--color-primary-50)] text-[var(--color-primary-700)] border-[var(--color-primary-100)]',
        'success' => 'bg-[var(--color-success-50)] text-[var(--color-success-700)] border-[var(--color-success-50)]',
        'warning' => 'bg-[var(--color-warning-50)] text-[var(--color-warning-700)] border-[var(--color-warning-50)]',
        'danger'  => 'bg-[var(--color-danger-50)] text-[var(--color-danger-700)] border-[var(--color-danger-100)]',
        'info'    => 'bg-[var(--color-info-50)] text-[var(--color-info-700)] border-[var(--color-info-50)]',
    ];

    $dotColors = [
        'neutral' => 'bg-[var(--color-text-muted)]',
        'primary' => 'bg-[var(--color-primary-500)]',
        'success' => 'bg-[var(--color-success-500)]',
        'warning' => 'bg-[var(--color-warning-500)]',
        'danger'  => 'bg-[var(--color-danger-500)]',
        'info'    => 'bg-[var(--color-info-500)]',
    ];

    $classes = collect([
        'inline-flex items-center gap-1.5 rounded-full font-medium border',
        $sizes[$size] ?? $sizes['md'],
        $variants[$variant] ?? $variants['neutral'],
    ])->implode(' ');
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full {{ $dotColors[$variant] ?? $dotColors['neutral'] }}"></span>
    @endif
    {{ $slot }}
</span>
