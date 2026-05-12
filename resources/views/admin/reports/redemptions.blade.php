@php
    $statusLabels = [
        'pending' => 'قيد المراجعة',
        'approved' => 'معتمد',
        'rejected' => 'مرفوض',
        'cancelled' => 'ملغي',
        'fulfilled' => 'منفّذ',
    ];
@endphp

<x-layouts.admin
    title="تقرير الاستبدالات"
    pageTitle="تقرير الاستبدالات"
    :breadcrumbs="[
        ['label' => 'لوحة الإدارة', 'href' => route('admin.dashboard')],
        ['label' => 'التقارير', 'href' => route('admin.reports')],
        ['label' => 'الاستبدالات'],
    ]"
>
    @include('admin.reports._range', ['from' => $range['from'], 'to' => $range['to']])

    <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mb-6">
        <div class="bg-white rounded-xl p-4 shadow-sm">
            <p class="text-xs text-slate-500">متوسط وقت التنفيذ</p>
            <p class="text-2xl font-bold text-slate-900" dir="ltr">{{ $avg_lag_hours }} ساعة</p>
            <p class="text-xs text-slate-400 mt-1">من الموافقة حتى التنفيذ</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm">
            <p class="text-xs text-slate-500">طلبات نقدية</p>
            <p class="text-2xl font-bold text-emerald-600" dir="ltr">{{ $by_type['cash']->c ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm">
            <p class="text-xs text-slate-500">طلبات باكجات</p>
            <p class="text-2xl font-bold text-sky-600" dir="ltr">{{ $by_type['package']->c ?? 0 }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="font-semibold text-slate-900">حسب الحالة</h3>
        </div>
        <table class="w-full text-right">
            <thead class="bg-slate-50 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                <tr>
                    <th class="px-4 py-3">الحالة</th>
                    <th class="px-4 py-3">العدد</th>
                    <th class="px-4 py-3">إجمالي النقاط</th>
                    <th class="px-4 py-3">إجمالي USD</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($statusLabels as $status => $label)
                    @php $row = $by_status[$status] ?? null; @endphp
                    <tr class="hover:bg-slate-50/50">
                        <td class="px-4 py-3">
                            @switch($status)
                                @case('pending')   <x-ui.badge variant="warning" :dot="true">{{ $label }}</x-ui.badge> @break
                                @case('approved')  <x-ui.badge variant="success" :dot="true">{{ $label }}</x-ui.badge> @break
                                @case('rejected')  <x-ui.badge variant="danger"  :dot="true">{{ $label }}</x-ui.badge> @break
                                @case('cancelled') <x-ui.badge variant="neutral" :dot="true">{{ $label }}</x-ui.badge> @break
                                @case('fulfilled') <x-ui.badge variant="info"    :dot="true">{{ $label }}</x-ui.badge> @break
                            @endswitch
                        </td>
                        <td class="px-4 py-3 font-latin font-bold">{{ $row->c ?? 0 }}</td>
                        <td class="px-4 py-3 font-latin">{{ number_format($row->points ?? 0) }}</td>
                        <td class="px-4 py-3 font-latin">${{ number_format($row->usd ?? 0, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-layouts.admin>
