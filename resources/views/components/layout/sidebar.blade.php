@props([
    'brand'   => '29FLY Loyalty',
    'subtitle' => 'برنامج الولاء',
    'items'   => [],
    'currentRoute' => '',
])

{{--
    Items example:
    [
      ['label' => 'لوحة التحكم', 'route' => 'agent.dashboard', 'icon' => '<svg.../>', 'active' => true],
      ['label' => 'المحفظة',    'route' => 'agent.wallet',    'icon' => '<svg.../>'],
    ]
--}}

<aside
    x-data="{ collapsed: false }"
    class="flex flex-col h-full bg-white border-l border-[var(--color-surface-border)] transition-base"
    :class="collapsed ? 'w-16' : 'w-64'"
>
    {{-- Brand --}}
    <div class="flex items-center justify-between px-4 py-4 border-b border-[var(--color-surface-divider)]">
        <div class="flex items-center gap-2 overflow-hidden">
            <div class="w-9 h-9 rounded-[var(--radius-md)] bg-[var(--color-primary-500)] text-white flex items-center justify-center flex-shrink-0">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M21 16v-2l-8-5V3.5c0-.83-.67-1.5-1.5-1.5S10 2.67 10 3.5V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z"/>
                </svg>
            </div>
            <div x-show="!collapsed" class="min-w-0">
                <h2 class="text-sm font-bold text-[var(--color-text-primary)] truncate">{{ $brand }}</h2>
                <p class="text-xs text-[var(--color-text-secondary)] truncate">{{ $subtitle }}</p>
            </div>
        </div>
        <button
            x-on:click="collapsed = !collapsed"
            class="text-[var(--color-text-muted)] hover:text-[var(--color-text-primary)] transition-base flex-shrink-0"
            aria-label="طي القائمة"
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>

    {{-- Nav items --}}
    <nav class="flex-1 overflow-y-auto py-4 px-2 space-y-1">
        @foreach($items as $item)
            @php
                $isActive = $item['active'] ?? ($currentRoute && ($item['route'] ?? '') === $currentRoute);
                $href = isset($item['route']) ? (\Illuminate\Support\Facades\Route::has($item['route']) ? route($item['route']) : '#') : ($item['href'] ?? '#');
            @endphp
            <a
                href="{{ $href }}"
                class="flex items-center gap-3 px-3 py-2 rounded-[var(--radius-sm)] text-sm transition-base
                       {{ $isActive
                            ? 'bg-[var(--color-primary-50)] text-[var(--color-primary-700)] font-semibold'
                            : 'text-[var(--color-text-secondary)] hover:bg-[var(--color-surface-secondary)] hover:text-[var(--color-text-primary)]' }}"
            >
                @if(!empty($item['icon']))
                    <span class="flex-shrink-0 w-5 h-5">{!! $item['icon'] !!}</span>
                @endif
                <span x-show="!collapsed">{{ $item['label'] ?? '' }}</span>
                @if(!empty($item['badge']))
                    <span x-show="!collapsed" class="ms-auto inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full bg-[var(--color-danger-500)] text-white text-xs font-medium">
                        {{ $item['badge'] }}
                    </span>
                @endif
            </a>
        @endforeach
    </nav>

    {{-- Footer slot --}}
    @isset($footer)
        <div class="border-t border-[var(--color-surface-divider)] p-4">
            {{ $footer }}
        </div>
    @endisset
</aside>
