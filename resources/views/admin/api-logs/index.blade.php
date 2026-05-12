@php
    $statusLabels = [
        'success'           => ['label' => 'نجح',             'variant' => 'success'],
        'duplicate_ignored' => ['label' => 'مكرر (تم تجاهله)', 'variant' => 'info'],
        'unauthorized'      => ['label' => 'غير مصرّح',        'variant' => 'danger'],
        'failed'            => ['label' => 'فشل',              'variant' => 'danger'],
        'rate_limited'      => ['label' => 'تجاوز الحد',       'variant' => 'warning'],
    ];
@endphp

<x-layouts.admin
    title="سجل الـ API"
    pageTitle="سجل الـ API / Webhooks"
    :breadcrumbs="[
        ['label' => 'لوحة الإدارة', 'href' => route('admin.dashboard')],
        ['label' => 'سجل الـ API'],
    ]"
>
    {{-- Today's stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
        <div class="bg-white rounded-xl p-4 shadow-sm">
            <p class="text-xs text-slate-500 mb-1">طلبات اليوم</p>
            <p class="text-2xl font-bold text-slate-900" dir="ltr">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm">
            <p class="text-xs text-slate-500 mb-1">ناجحة</p>
            <p class="text-2xl font-bold text-emerald-600" dir="ltr">{{ number_format($stats['success']) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm">
            <p class="text-xs text-slate-500 mb-1">مكررة (تم تجاهلها)</p>
            <p class="text-2xl font-bold text-sky-600" dir="ltr">{{ number_format($stats['duplicate']) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm">
            <p class="text-xs text-slate-500 mb-1">فاشلة / مرفوضة</p>
            <p class="text-2xl font-bold text-rose-600" dir="ltr">{{ number_format($stats['failed']) }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-xl shadow-sm p-4 mb-4">
        <div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div class="lg:col-span-2">
                <x-ui.input
                    name="q"
                    :value="request('q')"
                    placeholder="بحث: endpoint, reference_id, IP..."
                />
            </div>

            <x-ui.select
                name="status"
                :options="array_map(fn ($s) => $s['label'], $statusLabels)"
                :selected="request('status')"
                placeholder="كل الحالات"
            />

            <x-ui.input
                type="date"
                name="from"
                :value="request('from')"
            />

            <x-ui.input
                type="date"
                name="to"
                :value="request('to')"
            />
        </div>

        <div class="flex justify-end gap-2 mt-3">
            @if(request()->hasAny(['q', 'status', 'method', 'from', 'to']))
                <x-ui.button variant="ghost" size="sm" href="{{ route('admin.api-logs') }}">إلغاء الفلاتر</x-ui.button>
            @endif
            <x-ui.button type="submit" variant="primary" size="sm">
                <x-ui.icon name="search" size="sm" /> بحث
            </x-ui.button>
        </div>
    </form>

    @if($logs->isEmpty())
        <x-ui.empty-state title="لا توجد سجلات" description="جرّب توسيع البحث أو إلغاء الفلاتر." />
    @else
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-right">
                    <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3">التاريخ</th>
                            <th class="px-4 py-3">Endpoint</th>
                            <th class="px-4 py-3">الحالة</th>
                            <th class="px-4 py-3">Reference</th>
                            <th class="px-4 py-3">IP</th>
                            <th class="px-4 py-3">مدة</th>
                            <th class="px-4 py-3 text-center">عرض</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($logs as $log)
                            @php $st = $statusLabels[$log->status] ?? ['label' => $log->status, 'variant' => 'neutral']; @endphp

                            <tr class="hover:bg-slate-50/50">
                                <td class="px-4 py-3 text-sm whitespace-nowrap">
                                    <div>{{ $log->created_at->format('Y-m-d H:i:s') }}</div>
                                    <div class="text-xs text-slate-500">{{ $log->created_at->diffForHumans() }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="inline-flex items-center gap-2">
                                        <span class="px-1.5 py-0.5 rounded text-xs font-bold font-latin
                                            @switch($log->method)
                                                @case('POST') bg-emerald-100 text-emerald-700 @break
                                                @case('GET')  bg-sky-100 text-sky-700 @break
                                                @case('PUT')
                                                @case('PATCH') bg-amber-100 text-amber-700 @break
                                                @case('DELETE') bg-rose-100 text-rose-700 @break
                                                @default bg-slate-100 text-slate-700
                                            @endswitch
                                        ">{{ $log->method }}</span>
                                        <span class="font-latin text-slate-700">{{ $log->endpoint }}</span>
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <x-ui.badge :variant="$st['variant']">{{ $st['label'] }}</x-ui.badge>
                                    <div class="text-xs text-slate-500 mt-0.5 font-latin">{{ $log->response_code }}</div>
                                </td>
                                <td class="px-4 py-3 text-xs font-latin text-slate-600">{{ $log->reference_id ?? '—' }}</td>
                                <td class="px-4 py-3 text-xs font-latin text-slate-500">{{ $log->ip_address ?? '—' }}</td>
                                <td class="px-4 py-3 text-xs text-slate-500 font-latin">{{ $log->duration_ms ? $log->duration_ms . 'ms' : '—' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <x-ui.icon-button icon="eye" variant="ghost" tooltip="تفاصيل" href="{{ route('admin.api-logs.show', $log) }}" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 border-t border-slate-100 bg-slate-50/50">
                {{ $logs->links() }}
            </div>
        </div>
    @endif
</x-layouts.admin>
