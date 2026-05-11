<x-layouts.agent
    title="لوحة التحكم"
    pageTitle="أهلاً، {{ explode(' ', auth()->user()->full_name)[0] }} 👋"
    :breadcrumbs="[['label' => 'الرئيسية']]"
>

    {{-- Warning banners --}}
    @foreach($warnings as $warning)
        <x-ui.alert variant="warning" class="mb-4">{{ $warning }}</x-ui.alert>
    @endforeach

    {{-- Empty state for brand new agents (no transactions yet) --}}
    @if($recent_transactions->isEmpty() && $wallets['cash']['lifetime_earned'] === 0)
        <x-ui.card class="mb-6">
            <x-ui.empty-state
                title="مرحباً بك في برنامج ولاء 29FLY!"
                description="ابدأ أول عملية بيع لكسب نقاطك الأولى. كل عملية تربطنا بك تلقائياً عبر الموقع الرئيسي."
            >
                <x-slot:icon>
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </x-slot:icon>
                <x-slot:actions>
                    <x-ui.button variant="primary" href="{{ route('agent.tiers') }}">
                        تعرّف على نظام التصنيفات
                    </x-ui.button>
                </x-slot:actions>
            </x-ui.empty-state>
        </x-ui.card>
    @endif

    {{-- Tier card --}}
    <div class="mb-6">
        <x-agent.tier-card :tier="$tier" />
    </div>

    {{-- KPIs (4 cards) --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <x-ui.stat-card
            label="نقاط هذا الشهر"
            :value="number_format($kpis['points_this_month'])"
            color="cta"
        >
            <x-slot:icon>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                </svg>
            </x-slot:icon>
        </x-ui.stat-card>

        <x-ui.stat-card
            label="باكجات هذا الشهر"
            :value="(string) $kpis['packages_this_month']"
            color="accent"
        >
            <x-slot:icon>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </x-slot:icon>
        </x-ui.stat-card>

        <x-ui.stat-card
            label="القيمة الدولارية"
            :value="'$' . number_format($kpis['usd_value_total'], 2)"
            color="primary"
        >
            <x-slot:icon>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </x-slot:icon>
        </x-ui.stat-card>

        <x-ui.stat-card
            label="أيام لإعادة التقييم"
            :value="$kpis['days_until_reeval'] . ' يوم'"
            :color="'tier-' . $tier['current']"
        >
            <x-slot:icon>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </x-slot:icon>
        </x-ui.stat-card>
    </div>

    {{-- Dual wallet cards --}}
    <div class="grid md:grid-cols-2 gap-4 mb-6">
        <x-agent.wallet-card
            type="cash"
            :available="$wallets['cash']['available']"
            :locked="$wallets['cash']['locked']"
            :lifetime-earned="$wallets['cash']['lifetime_earned']"
            :usd-value="$wallets['cash']['usd_value']"
            :min-redemption="$wallets['cash']['min_redemption']"
            :can-redeem="$wallets['cash']['can_redeem']"
            :action-url="route('agent.redemptions.cash')"
        />

        <x-agent.wallet-card
            type="package"
            :available="$wallets['package']['available']"
            :locked="$wallets['package']['locked']"
            :lifetime-earned="$wallets['package']['lifetime_earned']"
            :nearest-package="$nearest_package"
            :action-url="route('agent.redemptions.packages')"
        />
    </div>

    {{-- Recent transactions --}}
    <x-ui.card title="آخر المعاملات" subtitle="آخر 10 معاملات مسجّلة على حسابك.">
        <x-slot:actions>
            <x-ui.button variant="ghost" size="sm" href="{{ route('agent.transactions') }}">
                عرض الكل ←
            </x-ui.button>
        </x-slot:actions>

        @if($recent_transactions->isEmpty())
            <x-ui.empty-state title="لا توجد معاملات بعد" description="ستظهر معاملاتك هنا تلقائياً بعد كل عملية بيع." />
        @else
            <x-ui.table :headers="['التاريخ', 'النوع', 'الوجهة', 'المبلغ', 'النقاط', 'المرجع']">
                @foreach($recent_transactions as $txn)
                    <x-ui.table-row>
                        <x-ui.table-cell>
                            <div class="text-sm">{{ $txn->transaction_date->format('Y-m-d') }}</div>
                            <div class="text-xs text-[var(--color-text-muted)]">{{ $txn->transaction_date->format('H:i') }}</div>
                        </x-ui.table-cell>
                        <x-ui.table-cell>
                            <x-ui.badge :variant="$txn->transaction_type === 'package' ? 'primary' : 'neutral'" size="sm">
                                {{ $txn->transaction_type === 'package' ? 'باكج' : 'خدمة' }}
                            </x-ui.badge>
                        </x-ui.table-cell>
                        <x-ui.table-cell>
                            <span class="text-sm">{{ $txn->destination ?? '—' }}</span>
                        </x-ui.table-cell>
                        <x-ui.table-cell>
                            <span class="font-medium" dir="ltr">${{ number_format($txn->amount_usd, 2) }}</span>
                        </x-ui.table-cell>
                        <x-ui.table-cell>
                            <span class="font-bold text-[var(--color-cta-700)]" dir="ltr">+{{ $txn->points_awarded }}</span>
                        </x-ui.table-cell>
                        <x-ui.table-cell>
                            <span class="font-latin text-xs text-[var(--color-text-muted)]">{{ $txn->reference_id }}</span>
                        </x-ui.table-cell>
                    </x-ui.table-row>
                @endforeach
            </x-ui.table>
        @endif
    </x-ui.card>

</x-layouts.agent>
