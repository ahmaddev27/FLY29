<x-layouts.admin
    title="تقرير التصنيفات"
    pageTitle="تقرير التصنيفات"
    :breadcrumbs="[
        ['label' => 'لوحة الإدارة', 'href' => route('admin.dashboard')],
        ['label' => 'التقارير', 'href' => route('admin.reports')],
        ['label' => 'التصنيفات'],
    ]"
>
    @include('admin.reports._range', ['from' => $range['from'], 'to' => $range['to']])

    <div class="grid lg:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-5 min-w-0">
            <h3 class="font-semibold text-slate-900 mb-1">التوزيع الحالي</h3>
            <p class="text-xs text-slate-500 mb-4">حسب التصنيف لكل وكيل نشط</p>
            <div class="relative h-64 w-full">
                <canvas id="currentChart"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="font-semibold text-slate-900 mb-1">حركة الترقيات/التخفيضات</h3>
            <p class="text-xs text-slate-500 mb-4">داخل الفترة المحددة</p>

            <div class="space-y-3">
                <div class="flex items-center justify-between p-3 bg-emerald-50 rounded-lg">
                    <span class="text-sm text-emerald-900">ترقيات تلقائية</span>
                    <span class="font-bold text-emerald-700 font-latin">{{ $movement['upgrades'] }}</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-rose-50 rounded-lg">
                    <span class="text-sm text-rose-900">تخفيضات</span>
                    <span class="font-bold text-rose-700 font-latin">{{ $movement['downgrades'] }}</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-sky-50 rounded-lg">
                    <span class="text-sm text-sky-900">تعديلات يدوية</span>
                    <span class="font-bold text-sky-700 font-latin">{{ $movement['manual'] }}</span>
                </div>
            </div>

            @if(! empty($movement['breakdown']))
                <h4 class="font-semibold text-slate-900 mt-5 mb-2 text-sm">الترقيات حسب التصنيف الهدف</h4>
                <div class="grid grid-cols-2 gap-2">
                    @foreach(['silver' => 'فضي', 'gold' => 'ذهبي', 'diamond' => 'ماسي'] as $tier => $label)
                        <div class="p-3 bg-slate-50 rounded-lg text-center">
                            <div class="text-xs text-slate-500">{{ $label }}</div>
                            <div class="text-xl font-bold font-latin">{{ $movement['breakdown'][$tier] ?? 0 }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (!window.Chart) return;
                const current = @json($current);
                new Chart(document.getElementById('currentChart'), {
                    type: 'doughnut',
                    data: {
                        labels: ['برونزي', 'فضي', 'ذهبي', 'ماسي'],
                        datasets: [{
                            data: [current.bronze, current.silver, current.gold, current.diamond],
                            backgroundColor: ['#A16207', '#94A3B8', '#F59E0B', '#3B82F6'],
                            borderWidth: 0,
                        }],
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        cutout: '60%', layout: { padding: 8 },
                        plugins: { legend: { position: 'bottom', labels: { padding: 10, font: { size: 11 }, boxWidth: 12 } } },
                    },
                });
            });
        </script>
    @endpush
</x-layouts.admin>
