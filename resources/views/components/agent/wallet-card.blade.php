@props([
    'type'           => 'cash',         // cash | package
    'available'      => 0,
    'locked'         => 0,
    'lifetimeEarned' => 0,
    'usdValue'       => null,           // for cash
    'minRedemption'  => 800,            // for cash
    'canRedeem'      => false,          // for cash
    'nearestPackage' => null,           // for package — array from dashboard service
    'actionUrl'      => '#',
])

@php
    $isCash = $type === 'cash';
    $title  = $isCash ? 'المحفظة النقدية' : 'محفظة الباكجات المجانية';
    $subtitle = $isCash
        ? 'حوّل نقاطك إلى رصيد مالي.'
        : 'استبدل نقاطك بباكج سياحي.';
    $accent = $isCash ? 'var(--color-cta-500)' : 'var(--color-accent-500)';
    $accentBg = $isCash ? 'var(--color-cta-50)' : 'var(--color-accent-50)';
    $btnVariant = $isCash ? 'cta' : 'primary';
    $btnLabel = $isCash ? 'تحويل لرصيد نقدي' : 'استبدال بباكج';
@endphp

<div class="bg-white rounded-[var(--radius-lg)] border border-[var(--color-surface-border)] shadow-[var(--shadow-card)] p-6 flex flex-col">
    {{-- Header --}}
    <div class="flex items-start justify-between gap-3 mb-5">
        <div>
            <h3 class="text-base font-semibold text-[var(--color-text-primary)]">{{ $title }}</h3>
            <p class="text-xs text-[var(--color-text-secondary)] mt-0.5">{{ $subtitle }}</p>
        </div>
        <div class="w-10 h-10 rounded-[var(--radius-md)] flex items-center justify-center flex-shrink-0"
             style="background-color: {{ $accentBg }}; color: {{ $accent }};">
            @if($isCash)
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            @else
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h14a2 2 0 012 2v3zM12 6V4m0 0L9 7m3-3l3 3M5 12v8a2 2 0 002 2h10a2 2 0 002-2v-8" />
                </svg>
            @endif
        </div>
    </div>

    {{-- Balance --}}
    <div class="mb-4">
        <p class="text-xs text-[var(--color-text-secondary)] mb-1">الرصيد المتاح</p>
        <div class="flex items-baseline gap-2">
            <span class="text-4xl font-bold text-[var(--color-text-primary)]" dir="ltr">{{ number_format($available) }}</span>
            <span class="text-sm text-[var(--color-text-secondary)]">نقطة</span>
        </div>

        @if($isCash && $usdValue !== null)
            <p class="text-sm text-[var(--color-text-secondary)] mt-1" dir="ltr">≈ ${{ number_format($usdValue, 2) }} USD</p>
        @endif
    </div>

    {{-- Status indicator (cash: progress to redemption; package: progress to nearest pkg) --}}
    @if($isCash)
        @php
            $pct = $minRedemption > 0 ? min(100, (int) round(($available / $minRedemption) * 100)) : 0;
            $remaining = max(0, $minRedemption - $available);
        @endphp

        <div class="mb-4">
            <div class="flex items-center justify-between text-xs mb-1.5">
                <span class="text-[var(--color-text-secondary)]">الحد الأدنى للتحويل</span>
                <span class="font-medium text-[var(--color-text-primary)]" dir="ltr">{{ number_format($minRedemption) }} نقطة</span>
            </div>
            <div class="w-full h-2 bg-[var(--color-surface-divider)] rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-700"
                     style="width: {{ $pct }}%; background-color: {{ $accent }};"></div>
            </div>
            @if($remaining > 0)
                <p class="text-xs text-[var(--color-text-secondary)] mt-1.5">
                    باقي <strong class="text-[var(--color-text-primary)]">{{ number_format($remaining) }}</strong> نقطة للتحويل.
                </p>
            @else
                <p class="text-xs text-[var(--color-cta-700)] mt-1.5 font-medium">✓ يمكنك التحويل الآن!</p>
            @endif
        </div>
    @elseif($nearestPackage)
        <div class="mb-4 rounded-[var(--radius-md)] bg-[var(--color-surface-tertiary)] p-3">
            <p class="text-xs text-[var(--color-text-secondary)] mb-1">أقرب باكج</p>
            <p class="text-sm font-semibold text-[var(--color-text-primary)]">{{ $nearestPackage['package']->name }}</p>
            @if($nearestPackage['can_redeem_now'])
                <p class="text-xs text-[var(--color-cta-700)] mt-1 font-medium">✓ يمكنك استبداله الآن</p>
            @else
                <p class="text-xs text-[var(--color-text-secondary)] mt-1">
                    باقي <strong class="text-[var(--color-text-primary)]">{{ number_format($nearestPackage['points_needed']) }}</strong> نقطة.
                </p>
            @endif
        </div>
    @endif

    {{-- Lifetime stats --}}
    <div class="flex items-center justify-between text-xs text-[var(--color-text-secondary)] mb-5 pt-3 border-t border-[var(--color-surface-divider)]">
        <span>إجمالي مكتسب: <strong class="text-[var(--color-text-primary)]" dir="ltr">{{ number_format($lifetimeEarned) }}</strong></span>
        @if($locked > 0)
            <span>محجوز: <strong class="text-[var(--color-warning-700)]" dir="ltr">{{ number_format($locked) }}</strong></span>
        @endif
    </div>

    {{-- CTA --}}
    <div class="mt-auto">
        <x-ui.button :variant="$btnVariant" :href="$actionUrl" :full="true" :disabled="$isCash && !$canRedeem">
            {{ $btnLabel }}
        </x-ui.button>
    </div>
</div>
