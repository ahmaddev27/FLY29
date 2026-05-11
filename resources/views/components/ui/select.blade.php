@props([
    'name'    => null,
    'id'      => null,
    'options' => [],   // ['value' => 'label']
    'selected' => null,
    'placeholder' => null,
    'error'   => null,
    'size'    => 'md',
])

@php
    $id = $id ?? $name;
    $sizes = [
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2 text-base',
        'lg' => 'px-5 py-3 text-lg',
    ];

    $base = 'block w-full bg-white border rounded-[var(--radius-sm)] transition-base '
          . 'focus:outline-none focus:ring-2 disabled:bg-[var(--color-surface-secondary)] disabled:cursor-not-allowed appearance-none pe-10';

    $stateClasses = $error
        ? 'border-[var(--color-danger-500)] focus:border-[var(--color-danger-500)] focus:ring-[var(--color-danger-100)]'
        : 'border-[var(--color-surface-border)] focus:border-[var(--color-primary-500)] focus:ring-[var(--color-primary-100)]';

    $classes = collect([$base, $sizes[$size] ?? $sizes['md'], $stateClasses])->filter()->implode(' ');
@endphp

<div class="relative">
    <select
        @if($name) name="{{ $name }}" @endif
        @if($id) id="{{ $id }}" @endif
        {{ $attributes->merge(['class' => $classes]) }}
    >
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach($options as $value => $label)
            <option value="{{ $value }}" @selected((string) $value === (string) $selected)>{{ $label }}</option>
        @endforeach
        {{ $slot }}
    </select>

    {{-- Chevron icon (logical position: end) --}}
    <div class="absolute inset-y-0 end-0 pe-3 flex items-center pointer-events-none text-[var(--color-text-muted)]">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
        </svg>
    </div>
</div>

@if($error)
    <p class="mt-1 text-sm text-[var(--color-danger-600)]">{{ $error }}</p>
@endif
