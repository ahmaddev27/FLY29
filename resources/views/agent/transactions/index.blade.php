<x-layouts.agent
    title="سجل النقاط"
    pageTitle="سجل النقاط"
    :breadcrumbs="[
        ['label' => 'الرئيسية', 'href' => route('agent.dashboard')],
        ['label' => 'سجل النقاط'],
    ]"
>

    {{-- Summary strip --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-4">
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs text-slate-500">عدد المعاملات</p>
            <p class="text-2xl font-bold text-slate-900 mt-1" dir="ltr">{{ number_format($summary->txn_count ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs text-slate-500">إجمالي النقاط</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1" dir="ltr">+{{ number_format($summary->total_points ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 col-span-2 sm:col-span-1">
            <p class="text-xs text-slate-500">إجمالي المبيعات</p>
            <p class="text-2xl font-bold text-slate-900 mt-1" dir="ltr">${{ number_format($summary->total_amount ?? 0, 2) }}</p>
        </div>
    </div>

    @php
        $sort = $filters['sort'] ?? 'date';
        $dir  = $filters['dir']  ?? 'desc';
        $flip = fn ($col) => array_merge(request()->query(), [
            'sort' => $col,
            'dir'  => ($sort === $col && $dir === 'desc') ? 'asc' : 'desc',
        ]);
        $arrow = fn ($col) => $sort === $col ? ($dir === 'desc' ? '▼' : '▲') : '';
    @endphp

    <x-ui.data-table
        :paginator="$transactions"
        search-placeholder="رقم المرجع..."
        search-param="reference"
        :filters="[
            [
                'name'    => 'type',
                'label'   => 'النوع',
                'options' => ['package' => 'باكج', 'service' => 'خدمة'],
            ],
            ['name' => 'from', 'label' => 'من تاريخ', 'type' => 'date'],
            ['name' => 'to',   'label' => 'إلى تاريخ', 'type' => 'date'],
        ]"
        :is-empty="$transactions->isEmpty()"
    >
        <x-slot:toolbar>
            <x-ui.button variant="ghost" size="sm" href="{{ route('agent.transactions.export.csv', request()->query()) }}">
                <x-ui.icon name="download" size="sm" /> CSV
            </x-ui.button>
            <x-ui.button variant="ghost" size="sm" href="{{ route('agent.transactions.export.excel', request()->query()) }}">
                <x-ui.icon name="download" size="sm" /> Excel
            </x-ui.button>
            <x-ui.button variant="ghost" size="sm" href="{{ route('agent.transactions.export.pdf', request()->query()) }}">
                <x-ui.icon name="download" size="sm" /> PDF
            </x-ui.button>
        </x-slot:toolbar>

        <x-slot:empty>
            <x-ui.empty-state title="لا توجد معاملات تطابق الفلاتر" description="جرّب توسيع نطاق التاريخ أو إزالة الفلاتر." />
        </x-slot:empty>

        <table class="w-full text-right">
            <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                <tr>
                    <th class="px-4 py-3">
                        <a href="?{{ http_build_query($flip('date')) }}" class="hover:text-[var(--color-primary-600)] inline-flex items-center gap-1">
                            التاريخ <span class="text-[10px]">{{ $arrow('date') }}</span>
                        </a>
                    </th>
                    <th class="px-4 py-3">النوع</th>
                    <th class="px-4 py-3">الوجهة</th>
                    <th class="px-4 py-3">
                        <a href="?{{ http_build_query($flip('amount')) }}" class="hover:text-[var(--color-primary-600)] inline-flex items-center gap-1">
                            المبلغ <span class="text-[10px]">{{ $arrow('amount') }}</span>
                        </a>
                    </th>
                    <th class="px-4 py-3">
                        <a href="?{{ http_build_query($flip('points')) }}" class="hover:text-[var(--color-primary-600)] inline-flex items-center gap-1">
                            النقاط <span class="text-[10px]">{{ $arrow('points') }}</span>
                        </a>
                    </th>
                    <th class="px-4 py-3">المرجع</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($transactions as $txn)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-4 py-3">
                            <div class="text-sm text-slate-700">{{ $txn->transaction_date->format('Y-m-d') }}</div>
                            <div class="text-xs text-slate-500">{{ $txn->transaction_date->format('H:i') }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <x-ui.badge :variant="$txn->transaction_type === 'package' ? 'primary' : 'neutral'" size="sm">
                                {{ $txn->transaction_type === 'package' ? 'باكج' : 'خدمة' }}
                            </x-ui.badge>
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-700">{{ $txn->destination ?? '—' }}</td>
                        <td class="px-4 py-3 font-medium text-slate-900" dir="ltr">${{ number_format($txn->amount_usd, 2) }}</td>
                        <td class="px-4 py-3 font-bold text-emerald-600" dir="ltr">+{{ $txn->points_awarded }}</td>
                        <td class="px-4 py-3"><span class="font-latin text-xs text-slate-500">{{ $txn->reference_id }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-ui.data-table>

</x-layouts.agent>
