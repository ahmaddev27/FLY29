<x-layouts.admin
    title="مدراء الحسابات"
    pageTitle="مدراء الحسابات"
    :breadcrumbs="[
        ['label' => 'لوحة الإدارة', 'href' => route('admin.dashboard')],
        ['label' => 'مدراء الحسابات'],
    ]"
>
    <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-slate-600">إدارة فريق مدراء الحسابات وتعيين الوكلاء.</p>
        <x-ui.button variant="cta" size="sm" href="{{ route('admin.account-managers.create') }}">
            <x-ui.icon name="plus" size="sm" /> مدير حسابات جديد
        </x-ui.button>
    </div>

    @if($managers->isEmpty())
        <x-ui.empty-state
            title="لا يوجد مدراء حسابات بعد"
            description="مدير الحسابات يتابع مجموعة من الوكلاء ويستطيع اقتراح تعديلات لاعتمادها من الأدمن."
        >
            <x-slot:actions>
                <x-ui.button variant="cta" href="{{ route('admin.account-managers.create') }}">
                    إضافة أول مدير
                </x-ui.button>
            </x-slot:actions>
        </x-ui.empty-state>
    @else
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-right">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3">المدير</th>
                        <th class="px-4 py-3">الحالة</th>
                        <th class="px-4 py-3 text-center">الوكلاء المُعيّنون</th>
                        <th class="px-4 py-3">تاريخ الإضافة</th>
                        <th class="px-4 py-3 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($managers as $manager)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-[var(--color-primary-50)] text-[var(--color-primary-700)] flex items-center justify-center font-bold">
                                        {{ mb_substr($manager->full_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-slate-900">{{ $manager->full_name }}</div>
                                        <div class="text-xs font-latin text-slate-500">{{ $manager->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @switch($manager->status)
                                    @case('active')    <x-ui.badge variant="success" :dot="true">نشط</x-ui.badge> @break
                                    @case('suspended') <x-ui.badge variant="warning" :dot="true">معلّق</x-ui.badge> @break
                                    @default          <x-ui.badge variant="neutral">{{ $manager->status }}</x-ui.badge>
                                @endswitch
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center justify-center min-w-10 h-7 px-2 rounded-lg bg-slate-100 text-slate-700 font-bold font-latin">
                                    {{ $manager->managed_agents_count }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm">{{ $manager->created_at->format('Y-m-d') }}</div>
                                <div class="text-xs text-slate-500">{{ $manager->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    <x-ui.icon-button icon="eye" variant="primary" tooltip="عرض" href="{{ route('admin.account-managers.show', $manager) }}" />

                                    @if($manager->status === 'active')
                                        <x-ui.icon-button
                                            type="button"
                                            icon="eye-off"
                                            variant="warning"
                                            tooltip="تعليق"
                                            :auto-loading="false"
                                            x-on:click="$dispatch('open-modal', 'suspend-mgr-{{ $manager->id }}')"
                                        />
                                    @elseif($manager->status === 'suspended')
                                        <form method="POST" action="{{ route('admin.account-managers.unsuspend', $manager) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <x-ui.icon-button type="submit" icon="check" variant="success" tooltip="إلغاء التعليق" />
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="px-4 py-3 border-t border-slate-100 bg-slate-50/50">
                {{ $managers->links() }}
            </div>
        </div>

        {{-- Suspend modals --}}
        @foreach($managers as $manager)
            @if($manager->status === 'active')
                <x-ui.modal
                    :name="'suspend-mgr-' . $manager->id"
                    :title="'تعليق ' . $manager->full_name"
                    size="sm"
                    :action="route('admin.account-managers.suspend', $manager)"
                    method="PATCH"
                >
                    <p class="text-sm text-slate-600 mb-3">
                        لن يستطيع تسجيل الدخول ولن يرى وكلاءه حتى يتم إلغاء التعليق.
                    </p>
                    <x-forms.form-group label="سبب التعليق" :for="'mgr_reason_' . $manager->id" required>
                        <x-ui.textarea :id="'mgr_reason_' . $manager->id" name="reason" rows="3" required />
                    </x-forms.form-group>

                    <x-slot:footer>
                        <div x-on:click="$dispatch('close-modal', 'suspend-mgr-{{ $manager->id }}')" class="inline-block">
                            <x-ui.button type="button" variant="secondary" :auto-loading="false">إلغاء</x-ui.button>
                        </div>
                        <x-ui.button type="submit" variant="warning">تعليق الحساب</x-ui.button>
                    </x-slot:footer>
                </x-ui.modal>
            @endif
        @endforeach
    @endif
</x-layouts.admin>
