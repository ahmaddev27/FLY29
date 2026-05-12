@php
    $tabs = [
        'pending'   => ['label' => 'قيد المراجعة',  'badge' => $counts['pending']   ?? 0],
        'approved'  => ['label' => 'معتمد',           'badge' => $counts['approved']  ?? 0],
        'rejected'  => ['label' => 'مرفوض',           'badge' => $counts['rejected']  ?? 0],
        'cancelled' => ['label' => 'ملغي',            'badge' => $counts['cancelled'] ?? 0],
    ];
@endphp

<x-layouts.manager
    title="اقتراحاتي"
    pageTitle="اقتراحات التعديل التي أرسلتها"
    :breadcrumbs="[
        ['label' => 'لوحة التحكم', 'href' => route('manager.dashboard')],
        ['label' => 'اقتراحاتي'],
    ]"
>
    {{-- Tabs --}}
    <div class="flex items-center gap-1 mb-4 border-b border-slate-200 overflow-x-auto">
        @foreach($tabs as $key => $meta)
            <a
                href="{{ url()->current() }}?tab={{ $key }}"
                @class([
                    'flex items-center gap-2 px-4 py-2.5 text-sm font-medium transition-colors border-b-2 -mb-px whitespace-nowrap',
                    'border-[var(--color-primary-500)] text-[var(--color-primary-700)]' => $tab === $key,
                    'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300' => $tab !== $key,
                ])
            >
                {{ $meta['label'] }}
                @if($meta['badge'] > 0)
                    <span @class([
                        'inline-flex items-center justify-center min-w-5 h-5 px-1.5 rounded-full text-xs font-bold',
                        'bg-amber-100 text-amber-800' => $key === 'pending',
                        'bg-emerald-100 text-emerald-700' => $key === 'approved',
                        'bg-rose-100 text-rose-700' => $key === 'rejected',
                        'bg-slate-200 text-slate-600' => $key === 'cancelled',
                    ])>{{ $meta['badge'] }}</span>
                @endif
            </a>
        @endforeach
    </div>

    @if($adjustments->isEmpty())
        <x-ui.empty-state
            title="لا توجد اقتراحات هنا"
            description="ابدأ من ملف وكيل واضغط «اقترح تعديل نقاط»."
        />
    @else
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-right">
                    <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3">الوكيل</th>
                            <th class="px-4 py-3">المحفظة</th>
                            <th class="px-4 py-3">التعديل</th>
                            <th class="px-4 py-3">السبب</th>
                            <th class="px-4 py-3">التاريخ</th>
                            <th class="px-4 py-3">قرار الأدمن</th>
                            <th class="px-4 py-3 text-center">إجراء</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($adjustments as $adj)
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-4 py-3">
                                    <a href="{{ route('manager.agents.show', $adj->agent) }}" class="font-medium text-[var(--color-primary-700)] hover:underline">
                                        {{ $adj->agent->business_name }}
                                    </a>
                                </td>
                                <td class="px-4 py-3">
                                    <x-ui.badge :variant="$adj->wallet_type === 'cash' ? 'info' : 'success'">
                                        {{ $adj->wallet_type === 'cash' ? 'كاش' : 'باكجات' }}
                                    </x-ui.badge>
                                </td>
                                <td class="px-4 py-3">
                                    <span @class([
                                        'inline-flex items-center gap-1 font-bold font-latin',
                                        'text-emerald-600' => $adj->points_delta > 0,
                                        'text-rose-600'    => $adj->points_delta < 0,
                                    ])>
                                        {{ $adj->points_delta > 0 ? '+' : '' }}{{ number_format($adj->points_delta) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-700 max-w-xs">
                                    <div class="line-clamp-2">{{ $adj->reason }}</div>
                                    @if($adj->admin_notes)
                                        <div class="text-xs text-slate-500 mt-1 italic">ملاحظة الأدمن: {{ $adj->admin_notes }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm whitespace-nowrap">
                                    <div>{{ $adj->created_at->format('Y-m-d') }}</div>
                                    <div class="text-xs text-slate-400">{{ $adj->created_at->diffForHumans() }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if($adj->approver)
                                        <div class="font-medium">{{ $adj->approver->full_name }}</div>
                                        <div class="text-xs text-slate-500">{{ $adj->approved_at?->diffForHumans() }}</div>
                                    @else
                                        <span class="text-xs text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($adj->status === 'pending')
                                        <form method="POST" action="{{ route('manager.adjustments.cancel', $adj) }}" class="inline">
                                            @csrf
                                            <x-ui.icon-button type="submit" icon="x" variant="warning" tooltip="إلغاء" />
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 border-t border-slate-100 bg-slate-50/50">
                {{ $adjustments->links() }}
            </div>
        </div>
    @endif
</x-layouts.manager>
