@props(['align' => 'right']) {{-- right | center | left --}}

@php
    $alignClasses = ['right' => 'text-right', 'center' => 'text-center', 'left' => 'text-left'];
@endphp

<td {{ $attributes->merge(['class' => "px-4 py-3 text-sm text-[var(--color-text-primary)] {$alignClasses[$align]}"]) }}>
    {{ $slot }}
</td>
