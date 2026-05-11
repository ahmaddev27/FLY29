@props([
    'pageTitle' => '',
    'breadcrumbs' => [], // [['label' => 'الرئيسية', 'href' => '/'], ['label' => 'الوكلاء']]
    'user' => null,      // ['name' => '...', 'email' => '...', 'avatar' => null]
    'notificationsCount' => 0,
])

<header class="flex items-center justify-between gap-4 px-6 py-3 bg-white border-b border-[var(--color-surface-border)] shadow-[var(--shadow-card)]">

    {{-- Page title + breadcrumbs --}}
    <div class="min-w-0 flex-1">
        @if(!empty($breadcrumbs))
            <nav class="flex items-center gap-2 text-xs text-[var(--color-text-secondary)] mb-1" aria-label="مسار التنقل">
                @foreach($breadcrumbs as $i => $crumb)
                    @if(!empty($crumb['href']))
                        <a href="{{ $crumb['href'] }}" class="hover:text-[var(--color-primary-500)] transition-base">{{ $crumb['label'] }}</a>
                    @else
                        <span class="text-[var(--color-text-primary)]">{{ $crumb['label'] }}</span>
                    @endif
                    @if($i < count($breadcrumbs) - 1)
                        <span class="text-[var(--color-text-muted)]">/</span>
                    @endif
                @endforeach
            </nav>
        @endif
        @if($pageTitle)
            <h1 class="text-xl font-bold text-[var(--color-text-primary)] truncate">{{ $pageTitle }}</h1>
        @endif
    </div>

    {{-- Actions: search, notifications, user --}}
    <div class="flex items-center gap-3 flex-shrink-0">

        {{-- Notifications dropdown --}}
        <div x-data="{ open: false }" class="relative">
            <button
                x-on:click="open = !open"
                class="relative w-10 h-10 rounded-full hover:bg-[var(--color-surface-secondary)] flex items-center justify-center transition-base"
                aria-label="الإشعارات"
            >
                <svg class="h-5 w-5 text-[var(--color-text-secondary)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                @if($notificationsCount > 0)
                    <span class="absolute top-1 end-1 min-w-[18px] h-[18px] px-1 rounded-full bg-[var(--color-danger-500)] text-white text-[10px] font-bold flex items-center justify-center">
                        {{ $notificationsCount > 99 ? '99+' : $notificationsCount }}
                    </span>
                @endif
            </button>

            <div
                x-show="open"
                x-cloak
                x-on:click.outside="open = false"
                x-transition
                class="absolute end-0 mt-2 w-80 bg-white rounded-[var(--radius-md)] shadow-[var(--shadow-dropdown)] border border-[var(--color-surface-border)] z-50"
            >
                <div class="px-4 py-3 border-b border-[var(--color-surface-divider)] flex items-center justify-between">
                    <h3 class="font-semibold">الإشعارات</h3>
                    <a href="#" class="text-xs text-[var(--color-primary-500)] hover:underline">تمييز الكل كمقروء</a>
                </div>
                <div class="max-h-96 overflow-y-auto">
                    {{ $notifications ?? '' }}
                    @if(empty($notifications))
                        <div class="px-4 py-8 text-center text-sm text-[var(--color-text-muted)]">
                            لا توجد إشعارات جديدة
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- User dropdown --}}
        @if($user)
            <div x-data="{ open: false }" class="relative">
                <button
                    x-on:click="open = !open"
                    class="flex items-center gap-2 px-2 py-1 rounded-[var(--radius-md)] hover:bg-[var(--color-surface-secondary)] transition-base"
                >
                    <div class="w-8 h-8 rounded-full bg-[var(--color-primary-500)] text-white flex items-center justify-center text-sm font-semibold">
                        {{ mb_substr($user['name'] ?? 'U', 0, 1) }}
                    </div>
                    <div class="hidden md:block text-right">
                        <p class="text-sm font-semibold text-[var(--color-text-primary)] leading-tight">{{ $user['name'] ?? '' }}</p>
                        <p class="text-xs text-[var(--color-text-secondary)] leading-tight">{{ $user['email'] ?? '' }}</p>
                    </div>
                    <svg class="h-4 w-4 text-[var(--color-text-muted)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div
                    x-show="open"
                    x-cloak
                    x-on:click.outside="open = false"
                    x-transition
                    class="absolute end-0 mt-2 w-48 bg-white rounded-[var(--radius-md)] shadow-[var(--shadow-dropdown)] border border-[var(--color-surface-border)] z-50 py-1"
                >
                    {{ $userMenu ?? '' }}
                </div>
            </div>
        @endif
    </div>
</header>
