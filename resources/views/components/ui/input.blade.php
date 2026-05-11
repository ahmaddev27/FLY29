@props([
    'type'        => 'text',
    'name'        => null,
    'id'          => null,
    'value'       => null,
    'placeholder' => null,
    'iconStart'   => null, // SVG name from heroicons (optional)
    'iconEnd'     => null,
    'error'       => null,
    'size'        => 'md', // sm | md | lg
])

@php
    $id = $id ?? $name;
    $sizes = [
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2 text-base',
        'lg' => 'px-5 py-3 text-lg',
    ];

    $base = 'block w-full bg-white border rounded-[var(--radius-sm)] transition-base '
          . 'focus:outline-none focus:ring-2 focus:ring-offset-0 disabled:bg-[var(--color-surface-secondary)] disabled:cursor-not-allowed';

    $stateClasses = $error
        ? 'border-[var(--color-danger-500)] focus:border-[var(--color-danger-500)] focus:ring-[var(--color-danger-100)]'
        : 'border-[var(--color-surface-border)] focus:border-[var(--color-primary-500)] focus:ring-[var(--color-primary-100)]';

    $padding = '';
    if ($iconStart) { $padding .= ' ps-10'; }
    if ($iconEnd)   { $padding .= ' pe-10'; }

    $classes = collect([$base, $sizes[$size] ?? $sizes['md'], $stateClasses, $padding])->filter()->implode(' ');
@endphp

<div class="relative">
    @if($iconStart)
        <div class="absolute inset-y-0 start-0 ps-3 flex items-center pointer-events-none text-[var(--color-text-muted)]">
            {{ $iconStart }}
        </div>
    @endif

    <input
        type="{{ $type }}"
        @if($name) name="{{ $name }}" @endif
        @if($id) id="{{ $id }}" @endif
        @if($value !== null) value="{{ $value }}" @endif
        @if($placeholder) placeholder="{{ $placeholder }}" @endif
        {{ $attributes->merge(['class' => $classes]) }}
    />

    @if($iconEnd)
        <div class="absolute inset-y-0 end-0 pe-3 flex items-center pointer-events-none text-[var(--color-text-muted)]">
            {{ $iconEnd }}
        </div>
    @endif
</div>

@if($error)
    <p class="mt-1 text-sm text-[var(--color-danger-600)]">{{ $error }}</p>
@endif
