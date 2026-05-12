@props([
    'name'    => null,
    'id'      => null,
    'options' => [],   // ['value' => 'label']
    'selected' => null,
    'placeholder' => null,
    'error'   => null,
    'size'    => 'md',
    'disabled' => false,
])

@php
    $id = $id ?? $name;
    $sizes = [
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2 text-base',
        'lg' => 'px-5 py-3 text-lg',
    ];

    $stateClasses = $error
        ? 'border-[var(--color-danger-500)] focus:border-[var(--color-danger-500)] focus:ring-[var(--color-danger-100)]'
        : 'border-[var(--color-surface-border)] focus:border-[var(--color-primary-500)] focus:ring-[var(--color-primary-100)]';

    // JSON for Alpine
    $optionsJson  = json_encode($options, JSON_UNESCAPED_UNICODE);
    $selectedJson = json_encode((string) $selected, JSON_UNESCAPED_UNICODE);
@endphp

<div
    x-data="{
        open: false,
        value: {{ $selectedJson }},
        options: {{ $optionsJson }},
        labelFor(v) {
            return (v !== null && v !== '' && this.options[v] !== undefined) ? this.options[v] : @js($placeholder ?: '');
        },
        pick(v) {
            this.value = String(v);
            this.open = false;
            this.$nextTick(() => this.$refs.input?.dispatchEvent(new Event('change', { bubbles: true })));
        },
    }"
    @keydown.escape.window="open = false"
    @click.outside="open = false"
    class="relative"
>
    {{-- Hidden form input (the real value posted to the server) --}}
    <input
        type="hidden"
        x-ref="input"
        @if($name) name="{{ $name }}" @endif
        @if($id) id="{{ $id }}" @endif
        x-model="value"
    >

    {{-- Trigger (looks like an input) --}}
    <button
        type="button"
        x-on:click="open = !open"
        @if($disabled) disabled @endif
        @class([
            'w-full bg-white border rounded-[var(--radius-sm)] text-start transition-base flex items-center justify-between gap-2',
            'focus:outline-none focus:ring-2 disabled:bg-[var(--color-surface-secondary)] disabled:cursor-not-allowed',
            $sizes[$size] ?? $sizes['md'],
            $stateClasses,
        ])
        {{ $attributes->except(['class']) }}
    >
        <span
            class="truncate"
            :class="!value && @js((string) $placeholder) ? 'text-[var(--color-text-muted)]' : 'text-[var(--color-text-primary)]'"
            x-text="labelFor(value) || @js($placeholder ?: '—')"
        ></span>
        <svg class="h-4 w-4 text-[var(--color-text-muted)] flex-shrink-0 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    {{-- Dropdown panel --}}
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="absolute z-30 mt-1 w-full bg-white rounded-[var(--radius-sm)] shadow-lg ring-1 ring-slate-200 max-h-60 overflow-auto py-1"
        role="listbox"
    >
        @if($placeholder)
            <button
                type="button"
                x-on:click="pick('')"
                :class="value === '' ? 'bg-[var(--color-primary-50)] text-[var(--color-primary-700)] font-medium' : 'text-[var(--color-text-secondary)]'"
                class="w-full text-start px-4 py-2 text-sm hover:bg-slate-50 transition-colors"
                role="option"
            >{{ $placeholder }}</button>
        @endif

        @foreach($options as $value => $label)
            <button
                type="button"
                x-on:click="pick({{ json_encode((string) $value) }})"
                :class="value === {{ json_encode((string) $value) }} ? 'bg-[var(--color-primary-50)] text-[var(--color-primary-700)] font-semibold' : 'text-slate-700'"
                class="w-full text-start px-4 py-2 text-sm hover:bg-slate-50 transition-colors flex items-center justify-between gap-2"
                role="option"
            >
                <span>{{ $label }}</span>
                <svg
                    x-show="value === {{ json_encode((string) $value) }}"
                    x-cloak
                    class="h-4 w-4 text-[var(--color-primary-600)] flex-shrink-0"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </button>
        @endforeach
    </div>

    @if($error)
        <p class="mt-1 text-sm text-[var(--color-danger-600)]">{{ $error }}</p>
    @endif
</div>
