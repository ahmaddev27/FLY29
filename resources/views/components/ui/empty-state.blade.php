@props([
    'title'       => 'لا توجد بيانات',
    'description' => null,
    'icon'        => null, // SVG slot (optional)
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center py-12 px-6 text-center']) }}>
    <div class="w-16 h-16 rounded-full bg-[var(--color-surface-secondary)] text-[var(--color-text-muted)] flex items-center justify-center mb-4">
        @if($icon)
            {{ $icon }}
        @else
            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
        @endif
    </div>

    <h3 class="text-base font-semibold text-[var(--color-text-primary)] mb-1">{{ $title }}</h3>

    @if($description)
        <p class="text-sm text-[var(--color-text-secondary)] max-w-md">{{ $description }}</p>
    @endif

    @isset($actions)
        <div class="mt-4 flex gap-2">{{ $actions }}</div>
    @endisset
</div>
