@props([
    'name'        => 'confirm',
    'title'       => 'تأكيد الإجراء',
    'message'     => 'هل أنت متأكد من المتابعة؟',
    'confirmText' => 'تأكيد',
    'cancelText'  => 'إلغاء',
    'variant'     => 'danger', // danger | primary | warning
    'action'      => null,    // URL for form submission (optional)
    'method'      => 'POST',
])

@php
    $variants = [
        'danger'  => ['icon_bg' => 'var(--color-danger-50)', 'icon_fg' => 'var(--color-danger-500)', 'btn' => 'danger'],
        'warning' => ['icon_bg' => 'var(--color-warning-50)', 'icon_fg' => 'var(--color-warning-500)', 'btn' => 'primary'],
        'primary' => ['icon_bg' => 'var(--color-primary-50)', 'icon_fg' => 'var(--color-primary-500)', 'btn' => 'primary'],
    ];
    $v = $variants[$variant] ?? $variants['danger'];
@endphp

<x-ui.modal :name="$name" size="sm">
    <div class="text-center">
        <div
            class="mx-auto w-12 h-12 rounded-full flex items-center justify-center mb-4"
            style="background-color: {{ $v['icon_bg'] }}; color: {{ $v['icon_fg'] }};"
        >
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>

        <h3 class="text-lg font-semibold text-[var(--color-text-primary)] mb-2">{{ $title }}</h3>
        <p class="text-sm text-[var(--color-text-secondary)] mb-6">{{ $message }}</p>

        <div class="flex justify-center gap-2">
            <x-ui.button variant="secondary" x-on:click="$dispatch('close-modal', '{{ $name }}')">
                {{ $cancelText }}
            </x-ui.button>

            @if($action)
                <form method="POST" action="{{ $action }}" class="inline">
                    @csrf
                    @if(in_array(strtoupper($method), ['PUT', 'PATCH', 'DELETE']))
                        @method($method)
                    @endif
                    <x-ui.button type="submit" :variant="$v['btn']">
                        {{ $confirmText }}
                    </x-ui.button>
                </form>
            @else
                <x-ui.button :variant="$v['btn']" {{ $attributes }}>
                    {{ $confirmText }}
                </x-ui.button>
            @endif
        </div>
    </div>
</x-ui.modal>
