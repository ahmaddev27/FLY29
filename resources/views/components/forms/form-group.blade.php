@props([
    'label'    => null,
    'for'      => null,
    'hint'     => null,
    'required' => false,
    'error'    => null,
])

<div {{ $attributes->merge(['class' => 'mb-4']) }}>
    @if($label)
        <label
            @if($for) for="{{ $for }}" @endif
            class="block text-sm font-medium text-[var(--color-text-primary)] mb-1.5"
        >
            {{ $label }}
            @if($required)<span class="text-[var(--color-danger-500)]">*</span>@endif
        </label>
    @endif

    {{ $slot }}

    @if($hint && !$error)
        <p class="mt-1 text-xs text-[var(--color-text-secondary)]">{{ $hint }}</p>
    @endif

    @if($error)
        <p class="mt-1 text-sm text-[var(--color-danger-600)] flex items-center gap-1">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            {{ $error }}
        </p>
    @endif
</div>
