<x-layouts.admin
    title="لوحة التحكم"
    pageTitle="لوحة التحكم"
    :breadcrumbs="[['label' => 'لوحة التحكم']]"
>

    {{-- ============ KPIs (4 cards) ============ --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl p-5 shadow-sm">
            <p class="text-xs text-slate-500 mb-1">إجمالي الوكلاء</p>
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
            <p class="text-xs text-slate-500 mb-1">نقاط مُمنوحة (شهرياً)</p>
            <p class="text-3xl font-bold text-emerald-600" dir="ltr">+{{ number_format($kpis['monthly_points']) }}</p>
            <p class="text-xs text-slate-500 mt-2">يحتسب من كل المعاملات</p>
        </div>

        <div class="bg-white rounded-xl p-5 shadow-sm">
            <p class="text-xs text-slate-500 mb-1">نقاط متراكمة (Liability)</p>
            <p class="text-3xl font-bold text-amber-600" dir="ltr">{{ number_format($kpis['liability_points']) }}</p>
            <p class="text-xs text-slate-500 mt-2">مُمنوحة − مستبدلة</p>
        </div>
    </div>

    {{-- ============ Charts (Sales + Tiers) ============ --}}
    <div class="grid md:grid-cols-3 gap-4 mb-6">

        {{-- Sales growth (line, 2/3 on md+) --}}
        <div class="bg-white rounded-xl p-5 shadow-sm md:col-span-2 min-w-0">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-semibold text-slate-900">نمو المبيعات</h3>
                    <p class="text-xs text-slate-500">آخر 12 شهر</p>
                </div>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        {{-- Tier distribution (doughnut) --}}
        <div class="bg-white rounded-xl p-5 shadow-sm min-w-0">
            <div class="mb-4">
                <h3 class="font-semibold text-slate-900">توزيع التصنيفات</h3>
                <p class="text-xs text-slate-500">حسب التصنيف الحالي</p>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="tiersChart"></canvas>
            </div>
        </div>
    </div>

    {{-- ============ Agent growth chart (full width) ============ --}}
    <div class="bg-white rounded-xl p-5 shadow-sm mb-6 min-w-0">
        <div class="mb-4">
            <h3 class="font-semibold text-slate-900">نمو قاعدة الوكلاء</h3>
            <p class="text-xs text-slate-500">عدد الوكلاء الجدد المُسجَّلين شهرياً</p>
        </div>
        <div class="relative h-48 w-full">
            <canvas id="agentsChart"></canvas>
        </div>
    </div>

    {{-- ============ Top 10 + Pending requests ============ --}}
    <div class="grid lg:grid-cols-3 gap-4">

        {{-- Top 10 leaderboard (2/3) --}}
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm overflow-hidden min-w-0">
            <div class="flex items-center justify-between px-5 py-4">
                <div>
                    <h3 class="font-semibold text-slate-900">أفضل 10 وكلاء (هذا الشهر)</h3>
                    <p class="text-xs text-slate-500">حسب إجمالي المبيعات</p>
                </div>
                <x-ui.button variant="ghost" size="sm" href="{{ route('admin.agents') }}">
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
                                <th class="px-4 py-3">باكجات</th>
                                <th class="px-4 py-3">مبيعات</th>
                                <th class="px-4 py-3">نقاط</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($top_agents as $i => $agent)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-4 py-3 font-bold text-slate-400" dir="ltr">{{ $i + 1 }}</td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-slate-900 whitespace-nowrap">{{ $agent->business_name }}</div>
                                        <div class="text-xs text-slate-500 font-latin">{{ $agent->external_agent_id }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <x-ui.tier-badge :tier="$agent->current_tier" size="sm" />
                                    </td>
                                    <td class="px-4 py-3 text-sm" dir="ltr">{{ $agent->monthly_packages ?? 0 }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-slate-900 whitespace-nowrap" dir="ltr">${{ number_format($agent->monthly_revenue ?? 0, 0) }}</td>
                                    <td class="px-4 py-3 text-sm font-bold text-emerald-600 whitespace-nowrap" dir="ltr">+{{ number_format($agent->monthly_points ?? 0) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Pending requests panel (1/3) --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4">
                <div>
                    <h3 class="font-semibold text-slate-900">طلبات معلّقة</h3>
                    <p class="text-xs text-slate-500">
                        @if($pending_count > 0)
                            <span class="font-semibold text-amber-600">{{ $pending_count }}</span> بانتظار المراجعة
                        @else
                            لا توجد طلبات
                        @endif
                    </p>
                </div>
                @if($pending_count > 0)
                    <x-ui.button variant="primary" size="sm" href="{{ route('admin.redemptions') }}">
                        مراجعة
                    </x-ui.button>
                @endif
            </div>

            @if($recent_pending->isEmpty())
                <div class="px-5 py-8 text-center text-sm text-slate-500">
                    ✓ كل الطلبات معالَجة
                </div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach($recent_pending as $req)
                        <a href="{{ route('admin.redemptions', ['status' => 'pending']) }}" class="block px-5 py-3 hover:bg-slate-50/50 transition-colors">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-slate-900 truncate">{{ $req->agent->business_name ?? '—' }}</p>
                                    <p class="text-xs text-slate-500">
                                        {{ $req->type === 'cash' ? 'تحويل نقدي' : 'باكج' }} ·
                                        <span dir="ltr">{{ number_format($req->points) }}</span> نقطة
                                    </p>
                                </div>
                                <span class="text-xs text-slate-400 flex-shrink-0">{{ $req->requested_at->diffForHumans() }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- ============ Chart bootstrap (inline JS) ============ --}}
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (!window.Chart) return;

                // Brand colours
                const primary = '#0066CC';
                const primaryFill = 'rgba(0, 102, 204, 0.1)';
                const tierColors = {
                    bronze:  '#A16207',
                    silver:  '#94A3B8',
                    gold:    '#F59E0B',
                    diamond: '#3B82F6',
                };

                /* ---- Sales chart ---- */
                const sales = @json($charts['sales_growth']);
                new Chart(document.getElementById('salesChart'), {
                    type: 'line',
                    data: {
                        labels: sales.map(d => d.label),
                        datasets: [{
                            label: 'المبيعات (USD)',
                            data: sales.map(d => d.revenue),
                            borderColor: primary,
                            backgroundColor: primaryFill,
                            tension: 0.35,
                            fill: true,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#fff',
                            pointBorderWidth: 2,
                            pointBorderColor: primary,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                            x: { grid: { display: false } },
                        },
                    },
                });

                /* ---- Agents growth chart ---- */
                const agents = @json($charts['agent_growth']);
                new Chart(document.getElementById('agentsChart'), {
                    type: 'bar',
                    data: {
                        labels: agents.map(d => d.label),
                        datasets: [{
                            label: 'وكلاء جدد',
                            data: agents.map(d => d.new_agents),
                            backgroundColor: '#10B981',
                            borderRadius: 6,
                            maxBarThickness: 32,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#f1f5f9' } },
                            x: { grid: { display: false } },
                        },
                    },
                });

                /* ---- Tier distribution ---- */
                const tiers = @json($tier_breakdown);
                new Chart(document.getElementById('tiersChart'), {
                    type: 'doughnut',
                    data: {
                        labels: ['برونزي', 'فضي', 'ذهبي', 'ماسي'],
                        datasets: [{
                            data: [tiers.bronze, tiers.silver, tiers.gold, tiers.diamond],
                            backgroundColor: [tierColors.bronze, tierColors.silver, tierColors.gold, tierColors.diamond],
                            borderWidth: 0,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '60%',
                        layout: { padding: 8 },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { padding: 10, font: { size: 11 }, boxWidth: 12 },
                            },
                        },
                    },
                });
            });
        </script>
    @endpush
</x-layouts.admin>
