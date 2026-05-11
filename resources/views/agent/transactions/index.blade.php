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
        <div class="bg-white rounded-[var(--radius-md)] border border-[var(--color-surface-border)] p-3 sm:p-4">
            <p class="text-xs text-[var(--color-text-secondary)]">عدد المعاملات</p>
            <p class="text-xl sm:text-2xl font-bold" dir="ltr">{{ number_format($summary->txn_count ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-[var(--radius-md)] border border-[var(--color-surface-border)] p-3 sm:p-4">
            <p class="text-xs text-[var(--color-text-secondary)]">إجمالي النقاط</p>
            <p class="text-xl sm:text-2xl font-bold text-[var(--color-cta-700)]" dir="ltr">+{{ number_format($summary->total_points ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-[var(--radius-md)] border border-[var(--color-surface-border)] p-3 sm:p-4 col-span-2 sm:col-span-1">
            <p class="text-xs text-[var(--color-text-secondary)]">إجمالي المبيعات</p>
            <p class="text-xl sm:text-2xl font-bold" dir="ltr">${{ number_format($summary->total_amount ?? 0, 2) }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <x-ui.card class="mb-4">
        <form method="GET" class="grid sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <x-forms.form-group label="من تاريخ" for="from">
                <x-ui.input type="date" id="from" name="from" :value="$filters['from'] ?? ''" />
            </x-forms.form-group>

            <x-forms.form-group label="إلى تاريخ" for="to">
                <x-ui.input type="date" id="to" name="to" :value="$filters['to'] ?? ''" />
            </x-forms.form-group>

            <x-forms.form-group label="النوع" for="type">
                <x-ui.select
                    id="type"
                    name="type"
                    placeholder="الكل"
                    :options="['package' => 'باكج', 'service' => 'خدمة']"
                    :selected="$filters['type'] ?? ''"
                />
            </x-forms.form-group>

            <x-forms.form-group label="رقم المرجع" for="reference">
                <x-ui.input id="reference" name="reference" :value="$filters['reference'] ?? ''" placeholder="TXN-MAIN-..." />
            </x-forms.form-group>

            <div class="flex items-end gap-2">
                <x-ui.button type="submit" variant="primary">تطبيق</x-ui.button>
                <x-ui.button variant="secondary" href="{{ route('agent.transactions') }}" :auto-loading="false">مسح</x-ui.button>
            </div>
        </form>
    </x-ui.card>

    {{-- Exports --}}
    <div class="flex flex-wrap items-center gap-2 mb-3">
        <span class="text-sm text-[var(--color-text-secondary)] me-auto">{{ $transactions->total() }} نتيجة</span>
        <x-ui.button variant="ghost" size="sm" href="{{ route('agent.transactions.export.csv', request()->query()) }}">
            ⬇ CSV
        </x-ui.button>
        <x-ui.button variant="ghost" size="sm" href="{{ route('agent.transactions.export.excel', request()->query()) }}">
            ⬇ Excel
        </x-ui.button>
        <x-ui.button variant="ghost" size="sm" href="{{ route('agent.transactions.export.pdf', request()->query()) }}">
            ⬇ PDF
        </x-ui.button>
    </div>

    {{-- Table --}}
    <x-ui.card padding="none">
        @if($transactions->isEmpty())
            <x-ui.empty-state title="لا توجد معاملات تطابق الفلاتر" description="جرّب توسيع نطاق التاريخ أو إزالة الفلاتر." />
        @else
            @php
                $sort = $filters['sort'] ?? 'date';
                $dir  = $filters['dir']  ?? 'desc';
                $flip = fn ($col) => array_merge(request()->query(), [
                    'sort' => $col,
                    'dir'  => ($sort === $col && $dir === 'desc') ? 'asc' : 'desc',
                ]);
                $arrow = fn ($col) => $sort === $col ? ($dir === 'desc' ? '▼' : '▲') : '';
            @endphp

            <div class="overflow-x-auto">
                <table class="w-full text-right">
                    <thead class="bg-[var(--color-surface-tertiary)] border-b border-[var(--color-surface-border)]">
                        <tr>
                            <th class="px-4 py-3 text-sm font-semibold">
                                <a href="?{{ http_build_query($flip('date')) }}" class="hover:text-[var(--color-primary-500)]">
                                    التاريخ {{ $arrow('date') }}
                                </a>
                            </th>
                            <th class="px-4 py-3 text-sm font-semibold">النوع</th>
                            <th class="px-4 py-3 text-sm font-semibold">الوجهة</th>
                            <th class="px-4 py-3 text-sm font-semibold">
                                <a href="?{{ http_build_query($flip('amount')) }}" class="hover:text-[var(--color-primary-500)]">
                                    المبلغ {{ $arrow('amount') }}
                                </a>
                            </th>
                            <th class="px-4 py-3 text-sm font-semibold">
                                <a href="?{{ http_build_query($flip('points')) }}" class="hover:text-[var(--color-primary-500)]">
                                    النقاط {{ $arrow('points') }}
                                </a>
                            </th>
                            <th class="px-4 py-3 text-sm font-semibold">المرجع</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--color-surface-divider)]">
                        @foreach($transactions as $txn)
                            <tr class="hover:bg-[var(--color-surface-tertiary)]">
                                <td class="px-4 py-3">
                                    <div class="text-sm">{{ $txn->transaction_date->format('Y-m-d') }}</div>
                                    <div class="text-xs text-[var(--color-text-muted)]">{{ $txn->transaction_date->format('H:i') }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <x-ui.badge :variant="$txn->transaction_type === 'package' ? 'primary' : 'neutral'" size="sm">
                                        {{ $txn->transaction_type === 'package' ? 'باكج' : 'خدمة' }}
                                    </x-ui.badge>
                                </td>
                                <td class="px-4 py-3 text-sm">{{ $txn->destination ?? '—' }}</td>
                                <td class="px-4 py-3 font-medium" dir="ltr">${{ number_format($txn->amount_usd, 2) }}</td>
                                <td class="px-4 py-3 font-bold text-[var(--color-cta-700)]" dir="ltr">+{{ $txn->points_awarded }}</td>
                                <td class="px-4 py-3"><span class="font-latin text-xs text-[var(--color-text-muted)]">{{ $txn->reference_id }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 border-t border-[var(--color-surface-divider)]">
                {{ $transactions->links() }}
            </div>
        @endif
    </x-ui.card>

</x-layouts.agent>
