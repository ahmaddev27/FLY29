<x-layouts.manager
    title="لوحة التحكم"
    pageTitle="لوحة مدير الحسابات"
    :breadcrumbs="[['label' => 'لوحة التحكم']]"
>
    {{-- KPI tiles --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl p-5 shadow-sm">
            <p class="text-xs text-slate-500 mb-1">وكلائي</p>
            <p class="text-3xl font-bold text-slate-900" dir="ltr">{{ number_format($kpis['total_agents']) }}</p>
            <p class="text-xs text-emerald-600 mt-2">
                <span dir="ltr">{{ number_format($kpis['active_agents']) }}</span> نشط
            </p>
        </div>

        <div class="bg-white rounded-xl p-5 shadow-sm">
            <p class="text-xs text-slate-500 mb-1">معاملات هذا الشهر</p>
            <p class="text-3xl font-bold text-slate-900" dir="ltr">{{ number_format($kpis['monthly_txns']) }}</p>
            <p class="text-xs text-slate-500 mt-2" dir="ltr">${{ number_format($kpis['monthly_revenue'], 0) }} مبيعات</p>
        </div>

        <div class="bg-white rounded-xl p-5 shadow-sm">
            <p class="text-xs text-slate-500 mb-1">طلبات استبدال معلّقة</p>
            <p class="text-3xl font-bold text-amber-600" dir="ltr">{{ number_format($kpis['pending_redemptions']) }}</p>
            <p class="text-xs text-slate-500 mt-2">لوكلائي</p>
        </div>

        <div class="bg-white rounded-xl p-5 shadow-sm">
            <p class="text-xs text-slate-500 mb-1">اقتراحاتي قيد المراجعة</p>
            <p class="text-3xl font-bold text-sky-600" dir="ltr">{{ number_format($kpis['pending_adjustments']) }}</p>
            <a href="{{ route('manager.adjustments') }}" class="text-xs text-[var(--color-primary-600)] hover:underline mt-2 inline-block">عرض</a>
        </div>
    </div>

    {{-- Top performers + Tier breakdown --}}
    <div class="grid lg:grid-cols-3 gap-4">

        {{-- Top 5 agents (2/3) --}}
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4">
                <div>
                    <h3 class="font-semibold text-slate-900">أفضل وكلائي (هذا الشهر)</h3>
                    <p class="text-xs text-slate-500">حسب إجمالي المبيعات</p>
                </div>
                <x-ui.button variant="ghost" size="sm" href="{{ route('manager.agents') }}">
                    عرض الكل
                </x-ui.button>
            </div>

            @if($top_agents->isEmpty())
                <x-ui.empty-state title="لا توجد مبيعات هذا الشهر" />
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-right">
                        <thead class="bg-slate-50 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3 w-12">#</th>
                                <th class="px-4 py-3">الوكيل</th>
                                <th class="px-4 py-3">التصنيف</th>
                                <th class="px-4 py-3">معاملات</th>
                                <th class="px-4 py-3">مبيعات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($top_agents as $i => $agent)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-4 py-3 font-bold text-slate-400" dir="ltr">{{ $i + 1 }}</td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('manager.agents.show', $agent) }}" class="font-medium text-[var(--color-primary-700)] hover:underline whitespace-nowrap">
                                            {{ $agent->business_name }}
                                        </a>
                                        <div class="text-xs text-slate-500 font-latin">{{ $agent->external_agent_id }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <x-ui.tier-badge :tier="$agent->current_tier" size="sm" />
                                    </td>
                                    <td class="px-4 py-3 text-sm" dir="ltr">{{ $agent->monthly_txns ?? 0 }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-slate-900 whitespace-nowrap" dir="ltr">${{ number_format($agent->monthly_revenue ?? 0, 0) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Tier breakdown panel (1/3) --}}
        <div class="bg-white rounded-xl p-5 shadow-sm">
            <h3 class="font-semibold text-slate-900 mb-1">توزيع وكلائي</h3>
            <p class="text-xs text-slate-500 mb-4">حسب التصنيف</p>

            <div class="space-y-3">
                @foreach(['diamond' => 'ماسي', 'gold' => 'ذهبي', 'silver' => 'فضي', 'bronze' => 'برونزي'] as $tier => $label)
                    @php $count = $tier_breakdown[$tier] ?? 0; @endphp
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <x-ui.tier-badge :tier="$tier" size="sm" />
                        </div>
                        <span class="font-latin font-bold text-slate-700">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-layouts.manager>
