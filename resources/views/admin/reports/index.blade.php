<x-layouts.admin
    title="التقارير"
    pageTitle="مركز التقارير"
    :breadcrumbs="[
        ['label' => 'لوحة الإدارة', 'href' => route('admin.dashboard')],
        ['label' => 'التقارير'],
    ]"
>
    <p class="text-sm text-slate-600 mb-6">اختر التقرير المطلوب. يمكنك بعد فتح أي تقرير تحديد فترة مخصصة وتصديره PDF أو Excel.</p>

    @php
        $reports = [
            ['key' => 'sales',       'label' => 'تقرير المبيعات',    'desc' => 'حجم المبيعات والإيرادات حسب الفترة', 'route' => 'admin.reports.sales',       'color' => 'bg-emerald-50 text-emerald-700', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13h2l3-9 4 18 3-9 4 9h2"/>'],
            ['key' => 'points',      'label' => 'تقرير النقاط',      'desc' => 'النقاط الممنوحة والمستبدلة',         'route' => 'admin.reports.points',      'color' => 'bg-amber-50 text-amber-700',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
            ['key' => 'tiers',       'label' => 'تقرير التصنيفات',    'desc' => 'التوزيع الحالي وحركة الترقيات',      'route' => 'admin.reports.tiers',       'color' => 'bg-sky-50 text-sky-700',         'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>'],
            ['key' => 'redemptions', 'label' => 'تقرير الاستبدالات', 'desc' => 'الطلبات حسب الحالة والنوع',          'route' => 'admin.reports.redemptions', 'color' => 'bg-rose-50 text-rose-700',       'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M20 12V8H6a2 2 0 110-4h12v4m0 0v8a2 2 0 002 2H6a2 2 0 01-2-2V6a2 2 0 002-2"/>'],
            ['key' => 'top-agents',  'label' => 'أفضل الوكلاء',      'desc' => 'ترتيب الوكلاء حسب المبيعات أو النقاط', 'route' => 'admin.reports.top-agents',  'color' => 'bg-indigo-50 text-indigo-700',   'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>'],
        ];
    @endphp

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($reports as $r)
            <a
                href="{{ route($r['route']) }}"
                class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow p-5 border border-slate-100 hover:border-slate-200"
            >
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-lg {{ $r['color'] }} flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            {!! $r['icon'] !!}
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="font-bold text-slate-900">{{ $r['label'] }}</h3>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $r['desc'] }}</p>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</x-layouts.admin>
