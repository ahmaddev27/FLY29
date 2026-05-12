<x-layouts.admin
    title="الوكلاء"
    pageTitle="إدارة الوكلاء"
    :breadcrumbs="[
        ['label' => 'لوحة الإدارة', 'href' => route('admin.dashboard')],
        ['label' => 'الوكلاء'],
    ]"
>

    <x-ui.data-table
        :paginator="$agents"
        search-placeholder="اسم تجاري، بريد، رقم ترخيص، ID..."
        :filters="[
            [
                'name'    => 'tier',
                'label'   => 'التصنيف',
                'options' => ['bronze' => 'برونزي', 'silver' => 'فضي', 'gold' => 'ذهبي', 'diamond' => 'ماسي'],
            ],
            [
                'name'    => 'status',
                'label'   => 'الحالة',
                'options' => ['active' => 'نشط', 'suspended' => 'معلّق'],
            ],
            [
                'name'    => 'country',
                'label'   => 'الدولة',
                'options' => $countries,
            ],
        ]"
        :is-empty="$agents->isEmpty()"
    >
        <x-slot:toolbar>
            <x-ui.button variant="secondary" size="sm" href="{{ route('admin.agents.import') }}">
                <x-ui.icon name="upload" size="sm" /> استيراد Excel
            </x-ui.button>
            <x-ui.button variant="cta" size="sm" href="{{ route('admin.agents.create') }}">
                <x-ui.icon name="plus" size="sm" /> وكيل جديد
            </x-ui.button>
        </x-slot:toolbar>

        <x-slot:empty>
            <x-ui.empty-state
                title="لا يوجد وكلاء بعد"
                description="ابدأ بإضافة وكيل جديد أو استورد قائمة من Excel."
            >
                <x-slot:actions>
                    <x-ui.button variant="cta" href="{{ route('admin.agents.create') }}">
                        إضافة أول وكيل
                    </x-ui.button>
                </x-slot:actions>
            </x-ui.empty-state>
        </x-slot:empty>

        <table class="w-full text-right">
            <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                <tr>
                    <th class="px-4 py-3">ID</th>
                    <th class="px-4 py-3">الوكيل</th>
                    <th class="px-4 py-3">التصنيف</th>
                    <th class="px-4 py-3">الدولة</th>
                    <th class="px-4 py-3">الحالة</th>
                    <th class="px-4 py-3">تاريخ الانضمام</th>
                    <th class="px-4 py-3 text-center">إجراءات</th>
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

                        <td class="px-4 py-3 text-sm text-slate-700">{{ $agent->country }}</td>

                        <td class="px-4 py-3">
                            @php $userStatus = $agent->user->status ?? 'unknown'; @endphp
                            @switch($userStatus)
                                @case('active')    <x-ui.badge variant="success" :dot="true">نشط</x-ui.badge> @break
                                @case('suspended') <x-ui.badge variant="warning" :dot="true">معلّق</x-ui.badge> @break
                                @case('deleted')   <x-ui.badge variant="neutral" :dot="true">محذوف</x-ui.badge> @break
                                @default          <x-ui.badge variant="neutral">{{ $userStatus }}</x-ui.badge>
                            @endswitch
                        </td>

                        <td class="px-4 py-3">
                            <div class="text-sm">{{ $agent->created_at->format('Y-m-d') }}</div>
                            <div class="text-xs text-slate-500">{{ $agent->created_at->diffForHumans() }}</div>
                        </td>

                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-1">
                                <x-ui.icon-button icon="eye" variant="primary" tooltip="عرض" href="{{ route('admin.agents.show', $agent) }}" />

                                @if($agent->user && $agent->user->status === 'active')
                                    <x-ui.icon-button
                                        type="button"
                                        icon="eye-off"
                                        variant="warning"
                                        tooltip="تعليق"
                                        :auto-loading="false"
                                        x-on:click="$dispatch('open-modal', 'suspend-{{ $agent->id }}')"
                                    />
                                @elseif($agent->user && $agent->user->status === 'suspended')
                                    <form method="POST" action="{{ route('admin.agents.unsuspend', $agent) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <x-ui.icon-button type="submit" icon="check" variant="success" tooltip="إلغاء التعليق" />
                                    </form>
                                @endif
                            </div>

                            {{-- Suspend modal --}}
                            @if($agent->user && $agent->user->status === 'active')
                                <x-ui.modal :name="'suspend-' . $agent->id" title="تعليق {{ $agent->business_name }}" size="sm">
                                    <form method="POST" action="{{ route('admin.agents.suspend', $agent) }}">
                                        @csrf
                                        @method('PATCH')
                                        <p class="text-sm text-slate-600 mb-3">
                                            الوكيل لن يستطيع الدخول أو استلام نقاط جديدة حتى تُلغي التعليق.
                                        </p>
                                        <x-forms.form-group label="سبب التعليق" :for="'reason_' . $agent->id" required>
                                            <x-ui.textarea
                                                :id="'reason_' . $agent->id"
                                                name="reason"
                                                rows="3"
                                                required
                                                placeholder="مخالفة، انتهاء عقد، ..."
                                            />
                                        </x-forms.form-group>

                                        <x-slot:footer>
                                            <x-ui.button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'suspend-{{ $agent->id }}')">إلغاء</x-ui.button>
                                            <x-ui.button type="submit" variant="warning">تعليق الحساب</x-ui.button>
                                        </x-slot:footer>
                                    </form>
                                </x-ui.modal>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-ui.data-table>

</x-layouts.admin>
