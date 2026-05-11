@props([
    'brand'        => '29FLY Loyalty',
    'subtitle'     => 'برنامج الولاء',
    'items'        => [],
    'currentRoute' => '',
])

{{--
    Responsive sidebar:
    • Desktop (md+): sticky right-side, collapsible width via toggle.
    • Mobile (<md): hidden off-screen by default; slides in as a drawer when
      `open-sidebar` window event fires. Closes on backdrop click,
      ESC key, or nav link click.

    Use @open-sidebar from anywhere (e.g. topbar hamburger):
        $dispatch('open-sidebar')
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
        class="fixed inset-0 bg-black/50 z-30 md:hidden"
        aria-hidden="true"
    ></div>

    {{-- Sidebar.
         RTL positioning:
           - `start-0` = right:0 in RTL → sidebar pinned to the visual right.
           - `border-e` = sidebar's left-physical edge in RTL, separating it
             from the main content area.
         Visibility: explicit inline :style is used instead of Tailwind's
         translate-x utilities because in RTL mode v4 flips their sign,
         which makes the drawer slide in from the wrong side. --}}
    <aside
        {{ $attributes->class([
            'flex flex-col bg-white border-e border-[var(--color-surface-border)]',
            'fixed inset-y-0 start-0 z-40 transition-transform duration-300',
            'md:sticky md:top-0 md:h-screen',
        ]) }}
        :style="(mobileOpen || isDesktop) ? 'transform: translateX(0)' : 'transform: translateX(100%)'"
        :class="{
            'w-72 md:w-64': !collapsed,
            'w-72 md:w-16': collapsed,
        }"
    >
        {{-- Brand --}}
        <div class="flex items-center justify-between px-4 py-4 border-b border-[var(--color-surface-divider)]">
            <div class="flex items-center gap-2 overflow-hidden">
                <img x-show="!collapsed"
                     src="{{ asset('images/fly29-logo.png') }}"
                     alt="29FLY"
                     class="h-9 object-contain flex-shrink-0">
                <img x-show="collapsed"
                     x-cloak
                     src="{{ asset('favicon.png') }}"
                     alt="29FLY"
                     class="w-9 h-9 object-contain flex-shrink-0">
                <div x-show="!collapsed" x-cloak class="min-w-0 ms-2">
                    <p class="text-xs text-[var(--color-text-secondary)] truncate">{{ $subtitle }}</p>
                </div>
            </div>

            {{-- Collapse toggle (desktop) + Close (mobile) --}}
            <button
                @click="window.innerWidth < 768 ? close() : (collapsed = !collapsed)"
                class="text-[var(--color-text-muted)] hover:text-[var(--color-text-primary)] transition-base flex-shrink-0"
                :aria-label="window.innerWidth < 768 ? 'إغلاق القائمة' : 'طي القائمة'"
            >
                {{-- Mobile: X icon | Desktop: hamburger --}}
                <svg x-show="mobileOpen" class="h-5 w-5 md:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <svg :class="mobileOpen ? 'hidden md:block' : ''" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>

        {{-- Nav items --}}
        <nav class="flex-1 overflow-y-auto py-4 px-2 space-y-1">
            @foreach($items as $item)
                @php
                    $isActive = $item['active'] ?? ($currentRoute && ($item['route'] ?? '') === $currentRoute);
                    $href = isset($item['route'])
                        ? (\Illuminate\Support\Facades\Route::has($item['route']) ? route($item['route']) : '#')
                        : ($item['href'] ?? '#');
                @endphp
                <a
                    href="{{ $href }}"
                    @click="close()"
                    class="flex items-center gap-3 px-3 py-2 rounded-[var(--radius-sm)] text-sm transition-base
                           {{ $isActive
                                ? 'bg-[var(--color-primary-50)] text-[var(--color-primary-700)] font-semibold'
                                : 'text-[var(--color-text-secondary)] hover:bg-[var(--color-surface-secondary)] hover:text-[var(--color-text-primary)]' }}"
                >
                    @if(!empty($item['icon']))
                        <span class="flex-shrink-0 w-5 h-5">{!! $item['icon'] !!}</span>
                    @endif
                    <span x-show="!collapsed" class="truncate">{{ $item['label'] ?? '' }}</span>
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
            <div x-show="!collapsed" x-cloak class="border-t border-[var(--color-surface-divider)] p-4">
                {{ $footer }}
            </div>
        @endisset
    </aside>
</div>
