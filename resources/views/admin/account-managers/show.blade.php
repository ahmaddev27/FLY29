<x-layouts.admin
    :title="$manager->full_name"
    :pageTitle="'ملف مدير الحسابات: ' . $manager->full_name"
    :breadcrumbs="[
        ['label' => 'لوحة الإدارة', 'href' => route('admin.dashboard')],
        ['label' => 'مدراء الحسابات', 'href' => route('admin.account-managers')],
        ['label' => $manager->full_name],
    ]"
>
    <div class="grid lg:grid-cols-3 gap-6">

        {{-- Profile column --}}
        <div class="lg:col-span-1 space-y-6">
            <x-ui.card>
                <div class="text-center">
                    <div class="mx-auto w-20 h-20 rounded-full bg-[var(--color-primary-50)] text-[var(--color-primary-700)] flex items-center justify-center text-2xl font-bold mb-3">
                        {{ mb_substr($manager->full_name, 0, 1) }}
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">{{ $manager->full_name }}</h3>
                    <p class="text-sm font-latin text-slate-500 mt-0.5">{{ $manager->email }}</p>

                    <div class="mt-3">
                        @switch($manager->status)
                            @case('active')    <x-ui.badge variant="success" :dot="true">نشط</x-ui.badge> @break
                            @case('suspended') <x-ui.badge variant="warning" :dot="true">معلّق</x-ui.badge> @break
                            @default          <x-ui.badge variant="neutral">{{ $manager->status }}</x-ui.badge>
                        @endswitch
                    </div>
                </div>

                <dl class="mt-5 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-slate-500">الجوال</dt>
                        <dd class="font-latin">{{ $manager->phone ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">عدد الوكلاء</dt>
                        <dd class="font-bold font-latin">{{ $assigned->total() }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">تاريخ الإضافة</dt>
                        <dd>{{ $manager->created_at->format('Y-m-d') }}</dd>
                    </div>
                </dl>

                <div class="mt-5 space-y-2">
                    @if($manager->status === 'active')
                        <x-ui.button
                            variant="warning"
                            :full="true"
                            :auto-loading="false"
                            x-on:click="$dispatch('open-modal', 'suspend-mgr')"
                        >تعليق الحساب</x-ui.button>
                    @elseif($manager->status === 'suspended')
                        <form method="POST" action="{{ route('admin.account-managers.unsuspend', $manager) }}">
                            @csrf
                            @method('PATCH')
                            <x-ui.button type="submit" variant="cta" :full="true">إلغاء التعليق</x-ui.button>
                        </form>
                    @endif

                    <x-ui.button
                        type="button"
                        variant="danger"
                        :full="true"
                        :auto-loading="false"
                        x-on:click="$dispatch('open-modal', 'delete-mgr')"
                    >
                        حذف المدير
                    </x-ui.button>

                    <x-ui.confirm-dialog
                        name="delete-mgr"
                        title="حذف مدير الحسابات؟"
                        :message="'سيُحذف «' . $manager->full_name . '» وستُحرَّر الوكلاء المعيّنون له (يصبحون بدون مدير). يمكن إعادة تعيينهم لاحقاً.'"
                        :action="route('admin.account-managers.destroy', $manager)"
                        method="DELETE"
                        confirm-label="نعم، احذف المدير"
                        variant="danger"
                        icon="trash"
                    />
                </div>
            </x-ui.card>
        </div>

        {{-- Assigned agents + assign panel --}}
        <div class="lg:col-span-2 space-y-6">
            <x-ui.card title="الوكلاء المُعيّنون ({{ $assigned->total() }})">
                @if($assigned->isEmpty())
                    <p class="text-center text-sm text-slate-500 py-6">لا يوجد وكلاء معيّنون لهذا المدير. استخدم لوحة التعيين أدناه لإضافة وكلاء.</p>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach($assigned as $agent)
                            <div class="flex items-center justify-between py-3 first:pt-0 last:pb-0">
                                <div>
                                    <a href="{{ route('admin.agents.show', $agent) }}" class="font-medium text-[var(--color-primary-700)] hover:underline">
                                        {{ $agent->business_name }}
                                    </a>
                                    <div class="flex items-center gap-2 text-xs text-slate-500 mt-0.5">
                                        <span class="font-latin">{{ $agent->external_agent_id }}</span>
                                        <span>•</span>
                                        <span>{{ $agent->country }}</span>
                                        <span>•</span>
                                        <x-ui.tier-badge :tier="$agent->current_tier" size="sm" />
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('admin.account-managers.unassign', ['manager' => $manager, 'agent' => $agent]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <x-ui.icon-button type="submit" icon="x" variant="danger" tooltip="إلغاء التعيين" />
                                </form>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-100">
                        {{ $assigned->links() }}
                    </div>
                @endif
            </x-ui.card>

            {{-- Assign from pool --}}
            @if($unassigned->isNotEmpty())
                <x-ui.card title="تعيين وكلاء جدد" subtitle="اختر من قائمة الوكلاء بدون مدير حالياً">
                    <form method="POST" action="{{ route('admin.account-managers.assign', $manager) }}" x-data="{ selected: [] }">
                        @csrf

                        <div class="max-h-80 overflow-y-auto border border-slate-200 rounded-lg divide-y divide-slate-100">
                            @foreach($unassigned as $agent)
                                <label class="flex items-center gap-3 p-3 hover:bg-slate-50 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        name="agent_ids[]"
                                        value="{{ $agent->id }}"
                                        x-model="selected"
                                        class="rounded border-slate-300 text-[var(--color-primary-500)] focus:ring-[var(--color-primary-100)] w-4 h-4"
                                    >
                                    <div class="flex-1 min-w-0">
                                        <div class="font-medium text-sm text-slate-900">{{ $agent->business_name }}</div>
                                        <div class="text-xs text-slate-500 font-latin">{{ $agent->external_agent_id }} · {{ $agent->country }}</div>
                                    </div>
                                    <x-ui.tier-badge :tier="$agent->current_tier" size="sm" />
                                </label>
                            @endforeach
                        </div>

                        <div class="flex items-center justify-between mt-3">
                            <span class="text-sm text-slate-600">
                                المختار: <span class="font-bold font-latin" x-text="selected.length"></span> من {{ $unassigned->count() }}
                            </span>
                            <x-ui.button type="submit" variant="cta" x-bind:disabled="selected.length === 0">
                                تعيين المختارين
                            </x-ui.button>
                        </div>
                    </form>
                </x-ui.card>
            @endif
        </div>
    </div>

    {{-- Suspend modal --}}
    @if($manager->status === 'active')
        <x-ui.modal name="suspend-mgr" title="تعليق {{ $manager->full_name }}" size="sm">
            <form method="POST" action="{{ route('admin.account-managers.suspend', $manager) }}">
                @csrf
                @method('PATCH')
                <p class="text-sm text-slate-600 mb-3">لن يستطيع المدير الدخول حتى يُلغى التعليق.</p>
                <x-forms.form-group label="سبب التعليق" for="mgr_reason" required>
                    <x-ui.textarea id="mgr_reason" name="reason" rows="3" required />
                </x-forms.form-group>

                <x-slot:footer>
                    <x-ui.button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'suspend-mgr')">إلغاء</x-ui.button>
                    <x-ui.button type="submit" variant="warning">تعليق الحساب</x-ui.button>
                </x-slot:footer>
            </form>
        </x-ui.modal>
    @endif

</x-layouts.admin>
