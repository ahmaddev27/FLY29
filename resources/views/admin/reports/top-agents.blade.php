<x-layouts.admin
    title="أفضل الوكلاء"
    pageTitle="ترتيب الوكلاء"
    :breadcrumbs="[
        ['label' => 'لوحة الإدارة', 'href' => route('admin.dashboard')],
        ['label' => 'التقارير', 'href' => route('admin.reports')],
        ['label' => 'أفضل الوكلاء'],
    ]"
>
    @include('admin.reports._range', ['from' => $range['from'], 'to' => $range['to']])

    <div class="flex items-center justify-between mb-4 gap-3 flex-wrap">
        <div class="flex items-center gap-2">
            <span class="text-sm text-slate-600">ترتيب حسب:</span>
            @foreach(['revenue' => 'الإيرادات', 'txns' => 'عدد المعاملات', 'points' => 'النقاط'] as $key => $label)
                <a
                    href="{{ url()->current() }}?{{ http_build_query(array_merge(request()->only('from', 'to'), ['order_by' => $key])) }}"
                    @class([
                        'px-3 py-1.5 rounded-lg text-sm transition-colors',
                        'bg-[var(--color-primary-50)] text-[var(--color-primary-700)] font-semibold' => $order_by === $key,
                        'text-slate-500 hover:bg-slate-100' => $order_by !== $key,
                    ])
                >{{ $label }}</a>
            @endforeach
        </div>

        <x-ui.button variant="secondary" size="sm" href="{{ route('admin.reports.top-agents.xlsx', request()->only('from', 'to', 'order_by')) }}">
            <x-ui.icon name="download" size="sm" /> تنزيل Excel
        </x-ui.button>
    </div>

    @if($agents->isEmpty())
        <x-ui.empty-state title="لا توجد بيانات في هذه الفترة" />
    @else
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-right">
                    <thead class="bg-slate-50 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3 w-12">#</th>
                            <th class="px-4 py-3">الوكيل</th>
                            <th class="px-4 py-3">التصنيف</th>
                            <th class="px-4 py-3">معاملات</th>
                            <th class="px-4 py-3">إيرادات</th>
                            <th class="px-4 py-3">نقاط</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($agents as $i => $agent)
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-4 py-3 font-bold text-slate-400" dir="ltr">{{ $i + 1 }}</td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.agents.show', $agent) }}" class="font-medium text-[var(--color-primary-700)] hover:underline whitespace-nowrap">
                                        {{ $agent->business_name }}
                                    </a>
                                    <div class="text-xs text-slate-500 font-latin">{{ $agent->external_agent_id }} · {{ $agent->country }}</div>
                                </td>
                                <td class="px-4 py-3"><x-ui.tier-badge :tier="$agent->current_tier" size="sm" /></td>
                                <td class="px-4 py-3 font-latin font-bold">{{ number_format($agent->period_txns ?? 0) }}</td>
                                <td class="px-4 py-3 font-latin font-bold text-emerald-700" dir="ltr">${{ number_format($agent->period_revenue ?? 0, 0) }}</td>
                                <td class="px-4 py-3 font-latin font-bold text-sky-700" dir="ltr">{{ number_format($agent->period_points ?? 0) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-layouts.admin>
