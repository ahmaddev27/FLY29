<x-layouts.admin
    title="تقرير المبيعات"
    pageTitle="تقرير المبيعات"
    :breadcrumbs="[
        ['label' => 'لوحة الإدارة', 'href' => route('admin.dashboard')],
        ['label' => 'التقارير', 'href' => route('admin.reports')],
        ['label' => 'المبيعات'],
    ]"
>
    @include('admin.reports._range', ['from' => $range['from'], 'to' => $range['to']])
    <div class="flex justify-end mb-4 -mt-2">
        <x-ui.button variant="secondary" size="sm" href="{{ route('admin.reports.sales.pdf', request()->only('from', 'to')) }}">
            <x-ui.icon name="download" size="sm" /> تنزيل PDF
        </x-ui.button>
    </div>

    {{-- KPI tiles --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
        <div class="bg-white rounded-xl p-4 shadow-sm">
            <p class="text-xs text-slate-500">عدد المعاملات</p>
            <p class="text-2xl font-bold text-slate-900" dir="ltr">{{ number_format($totals['count']) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm">
            <p class="text-xs text-slate-500">إجمالي الإيرادات</p>
            <p class="text-2xl font-bold text-emerald-600" dir="ltr">${{ number_format($totals['revenue'], 0) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm">
            <p class="text-xs text-slate-500">متوسط قيمة المعاملة</p>
            <p class="text-2xl font-bold text-slate-900" dir="ltr">${{ number_format($totals['avg_value'], 0) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm">
            <p class="text-xs text-slate-500">باكجات / خدمات</p>
            <p class="text-2xl font-bold text-slate-900" dir="ltr">{{ number_format($totals['packages']) }} / {{ number_format($totals['services']) }}</p>
        </div>
    </div>

    {{-- Chart --}}
    <div class="bg-white rounded-xl shadow-sm p-5 mb-6 min-w-0">
        <h3 class="font-semibold text-slate-900 mb-4">الإيرادات اليومية</h3>
        <div class="relative h-72 w-full">
            <canvas id="salesChart"></canvas>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (!window.Chart) return;
                const series = @json($series);
                new Chart(document.getElementById('salesChart'), {
                    type: 'line',
                    data: {
                        labels: series.map(d => d.day),
                        datasets: [
                            {
                                label: 'إيرادات USD',
                                data: series.map(d => d.revenue),
                                borderColor: '#0066CC',
                                backgroundColor: 'rgba(0, 102, 204, 0.1)',
                                tension: 0.35,
                                fill: true,
                                yAxisID: 'y',
                            },
                            {
                                label: 'عدد المعاملات',
                                data: series.map(d => d.count),
                                borderColor: '#10B981',
                                backgroundColor: 'rgba(16, 185, 129, 0)',
                                tension: 0.35,
                                yAxisID: 'y1',
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y:  { beginAtZero: true, position: 'left' },
                            y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false } },
                            x:  { grid: { display: false } },
                        },
                    },
                });
            });
        </script>
    @endpush
</x-layouts.admin>
