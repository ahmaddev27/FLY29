<x-layouts.manager
    title="وكلائي"
    pageTitle="الوكلاء المُعيّنون لي"
    :breadcrumbs="[
        ['label' => 'لوحة التحكم', 'href' => route('manager.dashboard')],
        ['label' => 'وكلائي'],
    ]"
>
    <x-ui.data-table
        :paginator="$agents"
        search-placeholder="اسم تجاري، بريد، ID..."
        :filters="[
            [
                'name'    => 'tier',
                'label'   => 'التصنيف',
                'options' => ['bronze' => 'برونزي', 'silver' => 'فضي', 'gold' => 'ذهبي', 'diamond' => 'ماسي'],
            ],
        ]"
        :is-empty="$agents->isEmpty()"
    >
        <x-slot:empty>
            <x-ui.empty-state
                title="لم يُعيَّن لك أي وكيل بعد"
                description="تواصل مع الأدمن لتعيين وكلاء لمتابعتهم."
            />
        </x-slot:empty>

        <table class="w-full text-right">
            <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                <tr>
                    <th class="px-4 py-3">ID</th>
                    <th class="px-4 py-3">الوكيل</th>
                    <th class="px-4 py-3">التصنيف</th>
                    <th class="px-4 py-3">رصيد كاش</th>
                    <th class="px-4 py-3">رصيد باكجات</th>
                    <th class="px-4 py-3">الدولة</th>
                    <th class="px-4 py-3 text-center">عرض</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($agents as $agent)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-4 py-3">
                            <span class="font-latin text-xs text-slate-500">{{ $agent->external_agent_id }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-900">{{ $agent->business_name }}</div>
                            <div class="text-xs text-slate-500 font-latin">{{ $agent->user->email ?? '—' }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <x-ui.tier-badge :tier="$agent->current_tier" size="sm" />
                        </td>
                        <td class="px-4 py-3 text-sm font-latin font-bold text-emerald-700" dir="ltr">
                            {{ number_format($agent->cashWallet->available_points ?? 0) }}
                        </td>
                        <td class="px-4 py-3 text-sm font-latin font-bold text-sky-700" dir="ltr">
                            {{ number_format($agent->packageWallet->available_points ?? 0) }}
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-700">{{ $agent->country }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center">
                                <x-ui.icon-button icon="eye" variant="primary" tooltip="عرض الملف" href="{{ route('manager.agents.show', $agent) }}" />
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-ui.data-table>
</x-layouts.manager>
