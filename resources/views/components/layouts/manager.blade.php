@props([
    'title'       => null,
    'pageTitle'   => null,
    'breadcrumbs' => [],
])

@php
    $user         = auth()->user();
    $currentRoute = request()->route()?->getName();

    $iconDashboard = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>';
    $iconAgents    = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>';
    $iconAdjust    = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>';

    $navItems = [
        ['label' => 'لوحة التحكم',  'route' => 'manager.dashboard',   'icon' => $iconDashboard],
        ['label' => 'وكلائي',       'route' => 'manager.agents',      'icon' => $iconAgents],
        ['label' => 'اقتراحاتي',    'route' => 'manager.adjustments', 'icon' => $iconAdjust],
    ];
@endphp

<x-layouts.app :title="$title ?? $pageTitle">
    <div class="flex min-h-screen bg-[var(--color-surface-secondary)]">

        <x-layout.sidebar
            brand="29FLY Manager"
            subtitle="مدير الحسابات"
            :items="$navItems"
            :currentRoute="$currentRoute"
        >
            <x-slot:footer>
                <div class="text-center">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/15 text-white text-xs font-medium">
                        <span class="w-1.5 h-1.5 rounded-full bg-[var(--color-cta-500)]"></span>
                        مدير حسابات
                    </span>
                    <p class="text-xs text-white/70 mt-2 truncate">{{ $user->full_name }}</p>
                </div>
            </x-slot:footer>
        </x-layout.sidebar>

        <div class="flex-1 min-w-0 flex flex-col">
            <x-layout.topbar
                :page-title="$pageTitle"
                :breadcrumbs="$breadcrumbs"
                :user="['name' => $user->full_name ?? '', 'email' => $user->email ?? '']"
            >
                <x-slot:userMenu>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-right px-4 py-2 text-sm text-[var(--color-danger-600)] hover:bg-[var(--color-danger-50)] transition-base">
                            تسجيل الخروج
                        </button>
                    </form>
                </x-slot:userMenu>
            </x-layout.topbar>

            <main class="flex-1 px-3 sm:px-6 py-4 sm:py-6">
                {{ $slot }}
            </main>
        </div>
    </div>
</x-layouts.app>
