@props([
    'tabs'    => [],  // ['key' => 'label']
    'default' => null,
])

@php
    $defaultKey = $default ?? array_key_first($tabs);
@endphp

<div x-data="{ activeTab: @js($defaultKey) }" {{ $attributes }}>
    {{-- Tabs nav --}}
    <div class="border-b border-[var(--color-surface-border)]">
        <nav class="flex gap-1" role="tablist">
            @foreach($tabs as $key => $label)
                <button
                    type="button"
                    x-on:click="activeTab = @js($key)"
                    :class="activeTab === @js($key)
                        ? 'border-[var(--color-primary-500)] text-[var(--color-primary-600)] font-semibold'
                        : 'border-transparent text-[var(--color-text-secondary)] hover:text-[var(--color-text-primary)]'"
                    class="px-4 py-3 text-sm border-b-2 transition-base focus:outline-none"
                    role="tab"
                >
                    {{ $label }}
                </button>
            @endforeach
        </nav>
    </div>

    {{-- Panels --}}
    <div class="pt-4">
        {{ $slot }}
    </div>
</div>
