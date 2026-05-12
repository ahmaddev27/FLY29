@props([
    'name'           => 'confirm',                // unique modal id
    'title'          => 'تأكيد الإجراء',
    'message'        => 'هل أنت متأكد؟',
    'action'         => null,                     // form action URL
    'method'         => 'DELETE',                 // DELETE | POST | PATCH | PUT
    'confirmLabel'   => 'تأكيد الحذف',
    'cancelLabel'    => 'إلغاء',
    'variant'        => 'danger',                 // danger | warning | primary
    'icon'           => null,                     // override icon (defaults to warning glyph)
])

@php
    // Variant-driven palette.
    $palettes = [
        'danger'  => [
            'ring'   => 'ring-rose-100',
            'iconBg' => 'bg-rose-50',
            'iconFg' => 'text-rose-600',
            'glow'   => 'shadow-rose-500/15',
            'btn'    => 'danger',
        ],
        'warning' => [
            'ring'   => 'ring-amber-100',
            'iconBg' => 'bg-amber-50',
            'iconFg' => 'text-amber-600',
            'glow'   => 'shadow-amber-500/15',
            'btn'    => 'warning',
        ],
        'primary' => [
            'ring'   => 'ring-sky-100',
            'iconBg' => 'bg-sky-50',
            'iconFg' => 'text-sky-600',
            'glow'   => 'shadow-sky-500/15',
            'btn'    => 'cta',
        ],
    ];
    $p = $palettes[$variant] ?? $palettes['danger'];
@endphp

<div
    x-data="{ open: false }"
    x-on:open-modal.window="if ($event.detail === '{{ $name }}') open = true"
    x-on:close-modal.window="if ($event.detail === '{{ $name }}') open = false"
    x-on:keydown.escape.window="open = false"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    role="dialog"
    aria-modal="true"
>
    {{-- Backdrop (with blur) --}}
    <div
        x-show="open"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-on:click="open = false"
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
        aria-hidden="true"
    ></div>

    {{-- Dialog --}}
    <div class="flex min-h-full items-center justify-center p-4">
        <div
            x-show="open"
            x-transition:enter="ease-out duration-250"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            x-on:click.stop
            @class([
                'relative w-full max-w-md bg-white rounded-2xl shadow-2xl ring-1 ring-slate-200 overflow-hidden',
                $p['glow'],
            ])
        >
            {{-- Top accent strip (variant-coloured) --}}
            <div @class(['absolute top-0 inset-x-0 h-1', match($variant) {
                'danger'  => 'bg-gradient-to-r from-rose-400 to-rose-600',
                'warning' => 'bg-gradient-to-r from-amber-400 to-amber-600',
                default   => 'bg-gradient-to-r from-sky-400 to-sky-600',
            }])></div>

            {{-- Close (top-end) --}}
            <button
                type="button"
                x-on:click="open = false"
                class="absolute top-4 end-4 w-8 h-8 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 flex items-center justify-center transition-base z-10"
                aria-label="إغلاق"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            {{-- Body --}}
            <div class="px-6 pt-8 pb-5 text-center">
                {{-- Icon disc --}}
                <div @class([
                    'mx-auto w-16 h-16 rounded-full flex items-center justify-center ring-8',
                    $p['ring'],
                    $p['iconBg'],
                    $p['iconFg'],
                ])>
                    @if($icon === 'trash')
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22m-3 0V5a2 2 0 00-2-2H8a2 2 0 00-2 2v2"/>
                        </svg>
                    @else
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                        </svg>
                    @endif
                </div>

                <h3 class="mt-5 text-lg font-bold text-slate-900 tracking-tight">{{ $title }}</h3>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">{{ $message }}</p>

                {{-- Extra slot for inputs / extra detail --}}
                @isset($body)
                    <div class="mt-4 text-start">
                        {{ $body }}
                    </div>
                @endisset
            </div>

            {{-- Footer actions --}}
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                <x-ui.button
                    type="button"
                    variant="secondary"
                    :auto-loading="false"
                    x-on:click="open = false"
                >
                    {{ $cancelLabel }}
                </x-ui.button>

                @if($action)
                    <form method="POST" action="{{ $action }}" class="contents">
                        @csrf
                        @if(in_array(strtoupper($method), ['DELETE', 'PUT', 'PATCH'], true))
                            @method($method)
                        @endif
                        <x-ui.button type="submit" :variant="$p['btn']">
                            {{ $confirmLabel }}
                        </x-ui.button>
                    </form>
                @else
                    <x-ui.button type="button" :variant="$p['btn']" x-on:click="open = false; $dispatch('confirmed-{{ $name }}')">
                        {{ $confirmLabel }}
                    </x-ui.button>
                @endif
            </div>
        </div>
    </div>
</div>
