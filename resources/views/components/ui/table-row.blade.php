@props(['hover' => true])

<tr
    {{ $attributes->merge(['class' => $hover ? 'hover:bg-[var(--color-surface-tertiary)] transition-base' : '']) }}
>
    {{ $slot }}
</tr>
