@props([
    'title'       => null,
    'pageTitle'   => null,
    'breadcrumbs' => [],
])

@php
    $user  = auth()->user();
    $agent = $user?->agent;

    $currentRoute = request()->route()?->getName();

    // Heroicons (outline) inline — keeps bundle small.
    $iconDashboard  = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>';
    $iconWallet     = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h14a2 2 0 012 2v3zM5 12v8a2 2 0 002 2h10a2 2 0 002-2v-8M12 4v8m-3-5l3-3 3 3"/></svg>';
    $iconRedeem     = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12V8H6a2 2 0 110-4h12v4m0 0v8a2 2 0 002 2H6a2 2 0 01-2-2V6a2 2 0 002-2"/></svg>';
    $iconHistory    = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>';
    $iconMessages   = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>';
    $iconProfile    = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>';
    $iconTiers      = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>';

    $navItems = [
        ['label' => 'لوحة التحكم', 'route' => 'agent.dashboard',          'icon' => $iconDashboard],
        ['label' => 'محفظتي',     'route' => 'agent.wallets',            'icon' => $iconWallet],
        ['label' => 'التحويلات والاستبدالات', 'route' => 'agent.redemptions',         'icon' => $iconRedeem],
        ['label' => 'سجل النقاط',  'route' => 'agent.transactions',       'icon' => $iconHistory],
        ['label' => 'مزايا التصنيفات','route' => 'agent.tiers',            'icon' => $iconTiers],
        ['label' => 'الرسائل',     'route' => 'agent.messages',           'icon' => $iconMessages],
        ['label' => 'ملفي الشخصي', 'route' => 'agent.profile',            'icon' => $iconProfile],
    ];
@endphp

<x-layouts.app :title="$title ?? $pageTitle">
    <div class="flex min-h-screen bg-[var(--color-surface-secondary)]">

        {{-- Sidebar (responsive: drawer on mobile, sticky on desktop) --}}
        <x-layout.sidebar
            brand="29FLY Loyalty"
            subtitle="بوابة الوكلاء"
            :items="$navItems"
            :currentRoute="$currentRoute"
        >
            <x-slot:footer>
                @if($agent)
                    <div class="text-center">
                        <x-ui.tier-badge :tier="$agent->current_tier" />
                        <p class="text-xs text-white/70 mt-2 truncate">{{ $agent->business_name }}</p>
                    </div>
                @endif
            </x-slot:footer>
        </x-layout.sidebar>

        {{-- Main column --}}
        <div class="flex-1 min-w-0 flex flex-col">

            {{-- Topbar --}}
            <x-layout.topbar
                :page-title="$pageTitle"
                :breadcrumbs="$breadcrumbs"
                :user="['name' => $user->full_name ?? '', 'email' => $user->email ?? '']"
            >
                <x-slot:userMenu>
                    <a href="{{ route('agent.profile') }}"
                       class="block px-4 py-2 text-sm hover:bg-[var(--color-surface-secondary)] transition-base">
                        ملفي الشخصي
                    </a>
                    <a href="{{ route('agent.notification-preferences') }}"
                       class="block px-4 py-2 text-sm hover:bg-[var(--color-surface-secondary)] transition-base">
                        تفضيلات الإشعارات
                    </a>
                    <hr class="my-1 border-[var(--color-surface-divider)]">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="w-full text-right px-4 py-2 text-sm text-[var(--color-danger-600)] hover:bg-[var(--color-danger-50)] transition-base">
                            تسجيل الخروج
                        </button>
                    </form>
                </x-slot:userMenu>
            </x-layout.topbar>

            {{-- Page content (flash messages bubble up via global toaster) --}}
            <main class="flex-1 px-3 sm:px-6 py-4 sm:py-6">
                {{-- Active announcement banners (per-agent, dismissible) --}}
                @if($agent)
                    @php
                        $announcements = \App\Models\Announcement::query()
                            ->active()
                            ->forAgent($agent)
                            ->whereDoesntHave('reads', fn ($q) => $q->where('agent_id', $agent->id))
                            ->latest()
                            ->limit(3)
                            ->get();
                    @endphp

                    @foreach($announcements as $announcement)
                        @php
                            $bannerClass = match($announcement->variant) {
                                'success' => 'border-emerald-300 bg-emerald-50',
                                'warning' => 'border-amber-300 bg-amber-50',
                                'danger'  => 'border-rose-300 bg-rose-50',
                                default   => 'border-sky-300 bg-sky-50',
                            };
                            $titleClass = match($announcement->variant) {
                                'success' => 'text-emerald-900',
                                'warning' => 'text-amber-900',
                                'danger'  => 'text-rose-900',
                                default   => 'text-sky-900',
                            };
                        @endphp
                        <div @class([
                            'mb-4 rounded-xl border-s-4 p-4 flex items-start gap-3',
                            $bannerClass,
                        ])>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold {{ $titleClass }} text-sm">{{ $announcement->title }}</h3>
                                <p class="text-sm text-slate-700 mt-1 leading-relaxed whitespace-pre-wrap">{{ $announcement->body }}</p>
                            </div>
                            <form method="POST" action="{{ route('agent.announcements.dismiss', $announcement) }}" class="flex-shrink-0">
                                @csrf
                                <button
                                    type="submit"
                                    class="w-8 h-8 rounded-lg text-slate-500 hover:text-slate-900 hover:bg-white/50 flex items-center justify-center transition-base"
                                    aria-label="إغلاق"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    @endforeach
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>
</x-layouts.app>
