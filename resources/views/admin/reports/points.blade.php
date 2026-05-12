<x-layouts.admin
    title="تقرير النقاط"
    pageTitle="تقرير النقاط"
    :breadcrumbs="[
        ['label' => 'لوحة الإدارة', 'href' => route('admin.dashboard')],
        ['label' => 'التقارير', 'href' => route('admin.reports')],
        ['label' => 'النقاط'],
    ]"
>
    @include('admin.reports._range', ['from' => $range['from'], 'to' => $range['to']])
    <div class="flex justify-end mb-4 -mt-2">
        <x-ui.button variant="secondary" size="sm" href="{{ route('admin.reports.points.pdf', request()->only('from', 'to')) }}">
            <x-ui.icon name="download" size="sm" /> تنزيل PDF
        </x-ui.button>
    </div>

    <div class="grid grid-cols-3 gap-3 mb-6">
        <div class="bg-white rounded-xl p-4 shadow-sm">
            <p class="text-xs text-slate-500">نقاط ممنوحة</p>
            <p class="text-2xl font-bold text-emerald-600" dir="ltr">+{{ number_format($totals['awarded']) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm">
            <p class="text-xs text-slate-500">نقاط مستبدلة</p>
            <p class="text-2xl font-bold text-rose-600" dir="ltr">−{{ number_format($totals['redeemed']) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm">
            <p class="text-xs text-slate-500">الصافي (التزام)</p>
            <p class="text-2xl font-bold text-amber-600" dir="ltr">{{ number_format($totals['net']) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-5 mb-6 min-w-0">
        <h3 class="font-semibold text-slate-900 mb-4">حركة النقاط اليومية</h3>
        <div class="relative h-72 w-full">
            <canvas id="pointsChart"></canvas>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (!window.Chart) return;
                const series = @json($series);
                new Chart(document.getElementById('pointsChart'), {
                    type: 'bar',
                    data: {
                        labels: series.map(d => d.day),
                        datasets: [
                            {
                                label: 'ممنوحة', data: series.map(d => d.awarded),
                                backgroundColor: '#10B981', borderRadius: 4,
                            },
                            {
                                label: 'مستبدلة', data: series.map(d => d.redeemed),
                                backgroundColor: '#EF4444', borderRadius: 4,
                            },
                        ],
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        scales: {
                            y: { beginAtZero: true, stacked: false },
                            x: { grid: { display: false } },
                        },
                    },
                });
            });
        </script>
    @endpush
</x-layouts.admin>
