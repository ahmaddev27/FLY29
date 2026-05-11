@props([
    'name'        => null,
    'id'          => null,
    'value'       => null,
    'placeholder' => null,
    'rows'        => 4,
    'error'       => null,
])

@php
    $id = $id ?? $name;
    $base = 'block w-full bg-white border rounded-[var(--radius-sm)] px-4 py-2 text-base transition-base '
          . 'focus:outline-none focus:ring-2 disabled:bg-[var(--color-surface-secondary)] disabled:cursor-not-allowed';

    $stateClasses = $error
        ? 'border-[var(--color-danger-500)] focus:border-[var(--color-danger-500)] focus:ring-[var(--color-danger-100)]'
        : 'border-[var(--color-surface-border)] focus:border-[var(--color-primary-500)] focus:ring-[var(--color-primary-100)]';

    $classes = collect([$base, $stateClasses])->filter()->implode(' ');
@endphp

<textarea
    @if($name) name="{{ $name }}" @endif
    @if($id) id="{{ $id }}" @endif
    @if($placeholder) placeholder="{{ $placeholder }}" @endif
    rows="{{ $rows }}"
    {{ $attributes->merge(['class' => $classes]) }}
>{{ $value ?? $slot }}</textarea>

@if($error)
    <p class="mt-1 text-sm text-[var(--color-danger-600)]">{{ $error }}</p>
@endif
