@props([
    'currentPage' => 1,
    'totalPages'  => 1,
    'baseUrl'     => '?page=',
])

@php
    $current = (int) $currentPage;
    $total   = (int) $totalPages;
    $window  = 2; // pages on each side of current

    $start = max(1, $current - $window);
    $end   = min($total, $current + $window);
@endphp

@if($total > 1)
    <nav class="flex items-center justify-between mt-4" aria-label="ترقيم الصفحات">
        <div class="text-sm text-[var(--color-text-secondary)]">
            صفحة <span class="font-semibold">{{ $current }}</span> من <span class="font-semibold">{{ $total }}</span>
        </div>

        <div class="flex items-center gap-1">
            {{-- Previous --}}
            @if($current > 1)
                <a href="{{ $baseUrl . ($current - 1) }}"
                   class="px-3 py-1.5 text-sm rounded-[var(--radius-sm)] border border-[var(--color-surface-border)] hover:bg-[var(--color-surface-secondary)] transition-base">
                    السابق
                </a>
            @else
                <span class="px-3 py-1.5 text-sm rounded-[var(--radius-sm)] border border-[var(--color-surface-border)] text-[var(--color-text-muted)] cursor-not-allowed">
                    السابق
                </span>
            @endif

            {{-- First page --}}
            @if($start > 1)
                <a href="{{ $baseUrl }}1" class="px-3 py-1.5 text-sm rounded-[var(--radius-sm)] hover:bg-[var(--color-surface-secondary)] transition-base">1</a>
                @if($start > 2)
                    <span class="px-2 text-[var(--color-text-muted)]">…</span>
                @endif
            @endif

            {{-- Page numbers --}}
            @for($i = $start; $i <= $end; $i++)
                @if($i === $current)
                    <span class="px-3 py-1.5 text-sm rounded-[var(--radius-sm)] bg-[var(--color-primary-500)] text-white font-semibold">{{ $i }}</span>
                @else
                    <a href="{{ $baseUrl . $i }}" class="px-3 py-1.5 text-sm rounded-[var(--radius-sm)] hover:bg-[var(--color-surface-secondary)] transition-base">{{ $i }}</a>
                @endif
            @endfor

            {{-- Last page --}}
            @if($end < $total)
                @if($end < $total - 1)
                    <span class="px-2 text-[var(--color-text-muted)]">…</span>
                @endif
                <a href="{{ $baseUrl . $total }}" class="px-3 py-1.5 text-sm rounded-[var(--radius-sm)] hover:bg-[var(--color-surface-secondary)] transition-base">{{ $total }}</a>
            @endif

            {{-- Next --}}
            @if($current < $total)
                <a href="{{ $baseUrl . ($current + 1) }}"
                   class="px-3 py-1.5 text-sm rounded-[var(--radius-sm)] border border-[var(--color-surface-border)] hover:bg-[var(--color-surface-secondary)] transition-base">
                    التالي
                </a>
            @else
                <span class="px-3 py-1.5 text-sm rounded-[var(--radius-sm)] border border-[var(--color-surface-border)] text-[var(--color-text-muted)] cursor-not-allowed">
                    التالي
                </span>
            @endif
        </div>
    </nav>
@endif
