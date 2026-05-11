@props([
    'tier' => [],     // dashboard service `tier` array
])

@php
    $tier = (array) $tier;
    $isMax = $tier['is_max_tier'] ?? false;
    $progressPct = $tier['progress_pct'] ?? 0;
    $tierName    = $tier['current'] ?? 'bronze';
@endphp

<div class="relative overflow-hidden bg-white rounded-[var(--radius-lg)] border border-[var(--color-surface-border)] shadow-[var(--shadow-card)] p-6">

    {{-- Decorative tint --}}
    <div class="absolute inset-0 opacity-5 pointer-events-none"
         style="background: linear-gradient(135deg, {{ $tier['current_color'] ?? '#A16207' }} 0%, transparent 60%);"></div>

    <div class="relative z-10">
        <div class="flex items-start justify-between gap-4 mb-4">
            <div>
                <p class="text-xs text-[var(--color-text-secondary)] mb-1">تصنيفك الحالي</p>
                <x-ui.tier-badge :tier="$tierName" size="lg" />
            </div>

            {{-- Countdown --}}
            <div class="text-end">
                <p class="text-xs text-[var(--color-text-secondary)]">إعادة التقييم خلال</p>
                <p class="text-2xl font-bold text-[var(--color-text-primary)]" dir="ltr">
                    {{ $tier['days_until_reeval'] ?? 30 }}
                    <span class="text-sm font-normal text-[var(--color-text-secondary)]">يوم</span>
                </p>
            </div>
        </div>

        @if($isMax)
            <div class="rounded-[var(--radius-md)] bg-[var(--color-tier-diamond-50)] text-[var(--color-tier-diamond)] p-3 text-center text-sm font-semibold">
                🏆 أنت على أعلى تصنيف! حافظ على نشاطك للبقاء هنا.
            </div>
        @else
            <div class="space-y-2">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-[var(--color-text-secondary)]">
                        التقدم نحو
                        <x-ui.tier-badge :tier="$tier['next_tier'] ?? 'silver'" size="sm" />
                    </span>
                    <span class="font-semibold text-[var(--color-text-primary)]">
                        {{ $tier['packages_in_window'] ?? 0 }} / {{ $tier['threshold_for_next'] ?? 0 }}
                        <span class="text-xs text-[var(--color-text-secondary)] mr-1">باكج</span>
                    </span>
                </div>

                <div class="w-full h-3 bg-[var(--color-surface-divider)] rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-700"
                         style="width: {{ $progressPct }}%; background-color: {{ $tier['current_color'] ?? '#A16207' }};"></div>
                </div>

                @if(($tier['packages_remaining'] ?? 0) > 0)
                    <p class="text-xs text-[var(--color-text-secondary)] mt-2">
                        تحتاج <strong class="text-[var(--color-text-primary)]">{{ $tier['packages_remaining'] }}</strong> باكج
                        إضافي للترقية إلى <strong>{{ $tier['next_tier_label'] }}</strong>.
                    </p>
                @endif
            </div>
        @endif
    </div>
</div>
