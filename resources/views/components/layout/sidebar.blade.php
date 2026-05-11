@props([
    'brand'        => '29FLY Loyalty',
    'subtitle'     => 'برنامج الولاء',
    'items'        => [],
    'currentRoute' => '',
])

{{--
    Premium sidebar — refined typography, generous spacing, subtle
    accent bar on the active item. Mobile: slides in from the visual
    right (start-0 in RTL). Desktop: sticky and collapsible.

    Open from anywhere via:  $dispatch('open-sidebar')
--}}

<div
    x-data="{
        collapsed: false,
        mobileOpen: false,
        isDesktop: window.innerWidth >= 768,
        init() {
            window.addEventListener('resize', () => { this.isDesktop = window.innerWidth >= 768; });
        },
        close() { this.mobileOpen = false; },
    }"
    @open-sidebar.window="mobileOpen = true"
    @close-sidebar.window="mobileOpen = false"
    @keydown.escape.window="mobileOpen = false"
>
    {{-- Mobile backdrop --}}
    <div
        x-show="mobileOpen"
        x-cloak
        x-transition.opacity.duration.200ms
        @click="close()"
        class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-30 md:hidden"
        aria-hidden="true"
    ></div>

    <aside
        {{ $attributes->class([
            'flex flex-col bg-white border-e border-[var(--color-surface-border)]',
            'fixed inset-y-0 start-0 z-40 transition-transform duration-300',
            'md:sticky md:top-0 md:h-screen shadow-[var(--shadow-card)] md:shadow-none',
        ]) }}
        :style="(mobileOpen || isDesktop) ? 'transform: translateX(0)' : 'transform: translateX(100%)'"
        :class="{
            'w-72 md:w-72': !collapsed,
            'w-72 md:w-20': collapsed,
        }"
    >
        {{-- Brand --}}
        <div class="flex items-center justify-between px-5 py-5 border-b border-[var(--color-surface-divider)]">
            <div class="flex items-center gap-3 overflow-hidden flex-1 min-w-0">
                <img x-show="!collapsed"
                     src="{{ asset('images/fly29-logo.png') }}"
                     alt="29FLY"
                     class="h-10 object-contain flex-shrink-0">
                <img x-show="collapsed"
                     x-cloak
                     src="{{ asset('favicon.png') }}"
                     alt="29FLY"
                     class="w-10 h-10 object-contain flex-shrink-0 mx-auto">
                <div x-show="!collapsed" x-cloak class="min-w-0">
                    <p class="text-[11px] uppercase tracking-wider text-[var(--color-text-muted)] font-medium leading-tight">{{ $subtitle }}</p>
                </div>
            </div>

            <button
                @click="window.innerWidth < 768 ? close() : (collapsed = !collapsed)"
                class="text-[var(--color-text-muted)] hover:text-[var(--color-text-primary)] hover:bg-[var(--color-surface-secondary)] rounded-md transition-base p-1.5 flex-shrink-0"
                :aria-label="window.innerWidth < 768 ? 'إغلاق القائمة' : (collapsed ? 'توسيع القائمة' : 'طي القائمة')"
            >
                <svg x-show="mobileOpen && !isDesktop" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <svg x-show="isDesktop && !collapsed" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                </svg>
                <svg x-show="isDesktop && collapsed" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                </svg>
            </button>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 overflow-y-auto py-5" :class="collapsed ? 'px-2' : 'px-4'">
            <ul class="space-y-1">
                @foreach($items as $item)
                    @php
                        $isActive = $item['active'] ?? ($currentRoute && ($item['route'] ?? '') === $currentRoute);
                        $href = isset($item['route'])
                            ? (\Illuminate\Support\Facades\Route::has($item['route']) ? route($item['route']) : '#')
                            : ($item['href'] ?? '#');

                        $linkClasses = $isActive
                            ? 'bg-[var(--color-primary-50)] text-[var(--color-primary-700)] font-semibold shadow-sm'
                            : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900';

                        $iconClasses = $isActive
                            ? 'text-[var(--color-primary-500)]'
                            : 'text-slate-400 group-hover:text-slate-600';
                    @endphp

                    <li class="relative">
                        @if($isActive)
                            <span class="absolute -start-4 top-1/2 -translate-y-1/2 w-1 h-7 rounded-full bg-[var(--color-primary-500)]"
                                  x-show="!collapsed" aria-hidden="true"></span>
                        @endif

                        <a
                            href="{{ $href }}"
                            @click="close()"
                            class="group flex items-center gap-3 rounded-lg text-sm transition-all duration-200 {{ $linkClasses }}"
                            :class="collapsed ? 'justify-center px-2 py-3' : 'px-3.5 py-2.5'"
                            @if($isActive) aria-current="page" @endif
                            @if(!empty($item['label'])) title="{{ $item['label'] }}" @endif
                        >
                            @if(!empty($item['icon']))
                                <span class="flex-shrink-0 w-5 h-5 transition-base {{ $iconClasses }}">{!! $item['icon'] !!}</span>
                            @endif

                            <span x-show="!collapsed" class="truncate flex-1">{{ $item['label'] ?? '' }}</span>

                            @if(!empty($item['badge']))
                                @php
                                    $badgeClasses = $isActive
                                        ? 'bg-[var(--color-primary-500)] text-white'
                                        : 'bg-[var(--color-danger-100)] text-[var(--color-danger-700)]';
                                @endphp
                                <span x-show="!collapsed" class="ms-auto inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full text-[10px] font-bold {{ $badgeClasses }}">
                                    {{ $item['badge'] }}
                                </span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        {{-- Footer --}}
        @isset($footer)
            <div x-show="!collapsed" x-cloak class="border-t border-[var(--color-surface-divider)] p-4">
                <div class="rounded-xl bg-gradient-to-br from-slate-50 to-white border border-[var(--color-surface-divider)] p-3">
                    {{ $footer }}
                </div>
            </div>

            <div x-show="collapsed" x-cloak class="border-t border-[var(--color-surface-divider)] p-3 flex justify-center">
                <div class="w-10 h-10 rounded-full bg-[var(--color-primary-500)] text-white flex items-center justify-center text-sm font-bold">
                    {{ mb_substr(auth()->user()->full_name ?? 'U', 0, 1) }}
                </div>
            </div>
        @endisset
    </aside>
</div>
