@props([
    'title'       => null,
    'pageTitle'   => null,
    'breadcrumbs' => [],
])

@php
    $user         = auth()->user();
    $currentRoute = request()->route()?->getName();

    $iconDashboard = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>';
    $iconAgents    = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>';
    $iconRequests  = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>';
    $iconPackages  = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>';
    $iconReports   = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>';
    $iconSettings  = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>';
    $iconAudit     = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>';

    $pendingCount = \App\Models\RedemptionRequest::where('status', 'pending')->count();

    $navItems = [
        ['label' => 'لوحة التحكم', 'route' => 'admin.dashboard',  'icon' => $iconDashboard],
        ['label' => 'الوكلاء',     'route' => 'admin.agents',     'icon' => $iconAgents],
        ['label' => 'الطلبات',     'route' => 'admin.redemptions', 'icon' => $iconRequests, 'badge' => $pendingCount > 0 ? $pendingCount : null],
        ['label' => 'الباكجات',    'route' => 'admin.packages',   'icon' => $iconPackages],
        ['label' => 'التقارير',    'route' => 'admin.reports',    'icon' => $iconReports],
        ['label' => 'الإعدادات',   'route' => 'admin.settings',   'icon' => $iconSettings],
        ['label' => 'سجل التدقيق',  'route' => 'admin.audit',      'icon' => $iconAudit],
    ];
@endphp

<x-layouts.app :title="$title ?? $pageTitle">
    <div class="flex min-h-screen bg-[var(--color-surface-secondary)]">

        <x-layout.sidebar
            brand="29FLY Admin"
            subtitle="لوحة الإدارة"
            :items="$navItems"
            :currentRoute="$currentRoute"
        >
            <x-slot:footer>
                <div class="text-center">
                    <x-ui.badge variant="primary" :dot="true">{{ $user->role }}</x-ui.badge>
                    <p class="text-xs text-slate-400 mt-2 truncate">{{ $user->full_name }}</p>
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
