<x-layouts.admin
    :title="$agent->business_name"
    :pageTitle="$agent->business_name"
    :breadcrumbs="[
        ['label' => 'لوحة الإدارة', 'href' => route('admin.dashboard')],
        ['label' => 'الوكلاء', 'href' => route('admin.agents')],
        ['label' => $agent->business_name],
    ]"
>
    <div class="grid lg:grid-cols-3 gap-4 mb-6">

        {{-- Profile card --}}
        <div class="lg:col-span-1 space-y-4">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="text-center mb-4">
                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-[var(--color-primary-500)] to-[var(--color-primary-700)] text-white text-3xl font-bold mx-auto mb-3 flex items-center justify-center">
                        {{ mb_substr($agent->user->full_name ?? 'A', 0, 1) }}
                    </div>
                    <h3 class="font-bold text-slate-900">{{ $agent->business_name }}</h3>
                    <p class="text-sm text-slate-500 font-latin">{{ $agent->user->email ?? '—' }}</p>

                    <div class="mt-3">
                        <x-ui.tier-badge :tier="$agent->current_tier" size="lg" />
                    </div>
                </div>

                <hr class="my-4 border-slate-100">

                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-500">المعرف الخارجي</dt><dd class="font-latin font-medium text-slate-900">{{ $agent->external_agent_id }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">رقم الترخيص</dt><dd class="font-latin font-medium text-slate-900">{{ $agent->license_number }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">الدولة</dt><dd class="text-slate-900">{{ $agent->country }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">المدينة</dt><dd class="text-slate-900">{{ $agent->city ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">الهاتف</dt><dd class="font-latin text-slate-900">{{ $agent->user->phone ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">تاريخ الانضمام</dt><dd class="font-latin text-slate-900">{{ $agent->created_at->format('Y-m-d') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">الحالة</dt>
                        <dd>
                            @switch($agent->user->status ?? 'unknown')
                                @case('active')    <x-ui.badge variant="success" :dot="true">نشط</x-ui.badge> @break
                                @case('suspended') <x-ui.badge variant="warning" :dot="true">معلّق</x-ui.badge> @break
                                @case('deleted')   <x-ui.badge variant="neutral" :dot="true">محذوف</x-ui.badge> @break
                            @endswitch
                        </dd>
                    </div>
                </dl>

                <hr class="my-4 border-slate-100">

                {{-- Wallets summary --}}
                <div class="grid grid-cols-2 gap-3 text-center">
                    <div class="bg-emerald-50 rounded-lg p-3">
                        <p class="text-xs text-emerald-700">محفظة نقدية</p>
                        <p class="text-xl font-bold text-emerald-900" dir="ltr">{{ number_format($agent->cashWallet->available_points ?? 0) }}</p>
                        @if(($agent->cashWallet->locked_points ?? 0) > 0)
                            <p class="text-[10px] text-amber-700 mt-1">+{{ number_format($agent->cashWallet->locked_points) }} محجوز</p>
                        @endif
                    </div>
                    <div class="bg-blue-50 rounded-lg p-3">
                        <p class="text-xs text-blue-700">محفظة باكجات</p>
                        <p class="text-xl font-bold text-blue-900" dir="ltr">{{ number_format($agent->packageWallet->available_points ?? 0) }}</p>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="mt-4 space-y-2">
                    <x-ui.button
                        variant="primary"
                        :full="true"
                        :auto-loading="false"
                        x-on:click="$dispatch('open-modal', 'adjust-wallet-{{ $agent->id }}')"
                    >
                        <x-ui.icon name="edit" size="sm" /> تعديل يدوي للنقاط
                    </x-ui.button>

                    @if($agent->user && $agent->user->status === 'active')
                        <x-ui.button
                            variant="warning"
                            :full="true"
                            :auto-loading="false"
                            x-on:click="$dispatch('open-modal', 'suspend-modal')"
                        >تعليق الحساب</x-ui.button>
                    @elseif($agent->user && $agent->user->status === 'suspended')
                        <form method="POST" action="{{ route('admin.agents.unsuspend', $agent) }}">
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
                        x-on:click="$dispatch('open-modal', 'delete-agent-{{ $agent->id }}')"
                    >
                        حذف الوكيل
                    </x-ui.button>

                    <x-ui.confirm-dialog
                        :name="'delete-agent-' . $agent->id"
                        title="حذف الوكيل؟"
                        :message="'سيتم حذف «' . $agent->business_name . '» ونقله إلى الأرشيف لمدة 90 يوماً قبل الحذف النهائي. لن يستطيع الدخول، ولكن بياناته ومحفظتيه ستبقى محفوظة.'"
                        :action="route('admin.agents.destroy', $agent)"
                        method="DELETE"
                        confirm-label="نعم، احذف الوكيل"
                        cancel-label="إلغاء"
                        variant="danger"
                        icon="trash"
                    />
                </div>
            </div>
        </div>

        {{-- Tabs column --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm">
                <x-ui.tabs :tabs="['transactions' => 'المعاملات (' . $recentTxns->count() . ')', 'redemptions' => 'الاستبدالات (' . $recentRedemptions->count() . ')', 'tier-history' => 'تاريخ التصنيف', 'notes' => 'ملاحظات داخلية']" default="transactions">

                    {{-- Transactions tab --}}
                    <x-ui.tab-panel key="transactions">
                        <div class="px-5 pb-5">
                            @if($recentTxns->isEmpty())
                                <x-ui.empty-state title="لا توجد معاملات" />
                            @else
                                <table class="w-full text-right text-sm">
                                    <thead class="bg-slate-50 text-xs font-semibold text-slate-600 uppercase">
                                        <tr>
                                            <th class="px-3 py-2">التاريخ</th>
                                            <th class="px-3 py-2">النوع</th>
                                            <th class="px-3 py-2">الوجهة</th>
                                            <th class="px-3 py-2">المبلغ</th>
                                            <th class="px-3 py-2">النقاط</th>
                                            <th class="px-3 py-2">المرجع</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach($recentTxns as $txn)
                                            <tr class="hover:bg-slate-50/50">
                                                <td class="px-3 py-2 text-slate-700">{{ $txn->transaction_date->format('Y-m-d H:i') }}</td>
                                                <td class="px-3 py-2"><x-ui.badge :variant="$txn->transaction_type === 'package' ? 'primary' : 'neutral'" size="sm">{{ $txn->transaction_type === 'package' ? 'باكج' : 'خدمة' }}</x-ui.badge></td>
                                                <td class="px-3 py-2 text-slate-700">{{ $txn->destination ?? '—' }}</td>
                                                <td class="px-3 py-2 font-medium" dir="ltr">${{ number_format($txn->amount_usd, 2) }}</td>
                                                <td class="px-3 py-2 font-bold text-emerald-600" dir="ltr">+{{ $txn->points_awarded }}</td>
                                                <td class="px-3 py-2 font-latin text-xs text-slate-500">{{ $txn->reference_id }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </x-ui.tab-panel>

                    {{-- Redemptions tab --}}
                    <x-ui.tab-panel key="redemptions">
                        <div class="px-5 pb-5">
                            @if($recentRedemptions->isEmpty())
                                <x-ui.empty-state title="لا توجد طلبات استبدال" />
                            @else
                                <table class="w-full text-right text-sm">
                                    <thead class="bg-slate-50 text-xs font-semibold text-slate-600 uppercase">
                                        <tr>
                                            <th class="px-3 py-2">#</th>
                                            <th class="px-3 py-2">النوع</th>
                                            <th class="px-3 py-2">النقاط</th>
                                            <th class="px-3 py-2">القيمة/الباكج</th>
                                            <th class="px-3 py-2">الحالة</th>
                                            <th class="px-3 py-2">التاريخ</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach($recentRedemptions as $req)
                                            <tr class="hover:bg-slate-50/50">
                                                <td class="px-3 py-2 font-latin text-xs text-slate-500">#{{ $req->id }}</td>
                                                <td class="px-3 py-2"><x-ui.badge :variant="$req->type === 'cash' ? 'success' : 'primary'" size="sm">{{ $req->type === 'cash' ? 'نقدي' : 'باكج' }}</x-ui.badge></td>
                                                <td class="px-3 py-2 font-semibold" dir="ltr">{{ number_format($req->points) }}</td>
                                                <td class="px-3 py-2">
                                                    @if($req->type === 'cash')
                                                        <span class="text-emerald-700" dir="ltr">${{ number_format($req->cash_value_usd, 2) }}</span>
                                                    @else
                                                        {{ $req->package?->name ?? '—' }}
                                                    @endif
                                                </td>
                                                <td class="px-3 py-2">
                                                    @switch($req->status)
                                                        @case('pending')   <x-ui.badge variant="warning" :dot="true" size="sm">قيد المراجعة</x-ui.badge> @break
                                                        @case('approved')  <x-ui.badge variant="success" :dot="true" size="sm">معتمد</x-ui.badge> @break
                                                        @case('rejected')  <x-ui.badge variant="danger"  :dot="true" size="sm">مرفوض</x-ui.badge> @break
                                                        @case('cancelled') <x-ui.badge variant="neutral" :dot="true" size="sm">ملغي</x-ui.badge> @break
                                                        @case('fulfilled') <x-ui.badge variant="info"    :dot="true" size="sm">منفّذ</x-ui.badge> @break
                                                    @endswitch
                                                </td>
                                                <td class="px-3 py-2 text-slate-700">{{ $req->requested_at->format('Y-m-d') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </x-ui.tab-panel>

                    {{-- Tier history tab --}}
                    <x-ui.tab-panel key="tier-history">
                        <div class="px-5 pb-5">
                            @if($tierHistory->isEmpty())
                                <x-ui.empty-state title="لا يوجد تاريخ تصنيف" />
                            @else
                                <ul class="space-y-2">
                                    @foreach($tierHistory as $h)
                                        <li class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                                            <div class="flex items-center gap-3">
                                                @if($h->from_tier)
                                                    <x-ui.tier-badge :tier="$h->from_tier" size="sm" />
                                                    <span class="text-slate-400">→</span>
                                                @endif
                                                <x-ui.tier-badge :tier="$h->to_tier" size="sm" />
                                                <span class="text-xs text-slate-500">{{ ['upgrade'=>'ترقية', 'downgrade'=>'تخفيض', 'manual'=>'يدوي', 'initial'=>'بداية', 'renewal'=>'تجديد'][$h->action] ?? $h->action }}</span>
                                            </div>
                                            <span class="text-xs text-slate-500">{{ $h->created_at->format('Y-m-d') }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </x-ui.tab-panel>

                    {{-- Internal notes tab --}}
                    <x-ui.tab-panel key="notes">
                        <div class="px-5 pb-5">
                            <p class="text-xs text-amber-700 bg-amber-50 rounded-lg p-3 mb-3">
                                💡 هذه الملاحظات مرئية للإدارة فقط — الوكيل لا يراها.
                            </p>
                            <form method="POST" action="{{ route('admin.agents.notes', $agent) }}">
                                @csrf
                                @method('PATCH')
                                <x-ui.textarea name="internal_notes" rows="8" placeholder="ملاحظات داخلية حول هذا الوكيل...">{{ $agent->internal_notes }}</x-ui.textarea>
                                <div class="flex justify-end mt-3">
                                    <x-ui.button type="submit" variant="primary">حفظ الملاحظات</x-ui.button>
                                </div>
                            </form>
                        </div>
                    </x-ui.tab-panel>
                </x-ui.tabs>
            </div>
        </div>
    </div>

    {{-- Suspend modal --}}
    @if($agent->user && $agent->user->status === 'active')
        <x-ui.modal name="suspend-modal" title="تعليق {{ $agent->business_name }}" size="sm">
            <form method="POST" action="{{ route('admin.agents.suspend', $agent) }}">
                @csrf
                @method('PATCH')
                <p class="text-sm text-slate-600 mb-3">
                    الوكيل لن يستطيع الدخول أو استلام نقاط جديدة حتى تُلغي التعليق.
                </p>
                <x-forms.form-group label="سبب التعليق" for="reason" required>
                    <x-ui.textarea id="reason" name="reason" rows="3" required placeholder="مخالفة، انتهاء عقد، ..." />
                </x-forms.form-group>

                <x-slot:footer>
                    <x-ui.button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'suspend-modal')">إلغاء</x-ui.button>
                    <x-ui.button type="submit" variant="warning">تعليق الحساب</x-ui.button>
                </x-slot:footer>
            </form>
        </x-ui.modal>
    @endif

    {{-- Adjust-wallet modal (manual adjustment with dual-approval gate) --}}
    @php
        $threshold = (int) app(\App\Services\SettingsService::class)->get('dual_approval_threshold', 500);
    @endphp
    <x-ui.modal :name="'adjust-wallet-' . $agent->id" title="تعديل يدوي لنقاط {{ $agent->business_name }}" size="md">
        <form method="POST" action="{{ route('admin.adjustments.store', $agent) }}" x-data="{ delta: 0 }">
            @csrf

            <div class="mb-3 p-3 rounded-lg bg-sky-50 border border-sky-100 text-sm text-sky-900">
                <p class="flex items-center gap-2">
                    <x-ui.icon name="alert-triangle" size="sm" />
                    <span>الحد الحالي للموافقة المزدوجة: <strong class="font-latin">{{ number_format($threshold) }}</strong> نقطة. أي تعديل أكبر من هذا الرقم يحتاج موافقة سوبر أدمن.</span>
                </p>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <x-forms.form-group label="المحفظة" for="wallet_type" required>
                    <x-ui.select id="wallet_type" name="wallet_type" :options="['cash' => 'كاش', 'package' => 'باكجات']" />
                </x-forms.form-group>

                <x-forms.form-group label="عدد النقاط (موجب = إضافة، سالب = خصم)" for="points_delta" required>
                    <x-ui.input
                        type="number"
                        id="points_delta"
                        name="points_delta"
                        step="1"
                        required
                        placeholder="مثال: 100 أو -50"
                        x-model.number="delta"
                    />
                </x-forms.form-group>
            </div>

            <x-forms.form-group label="السبب" for="adj_reason" required class="mt-4">
                <x-ui.textarea id="adj_reason" name="reason" rows="3" required placeholder="مكافأة، تعويض عن خطأ، تصحيح، ..." />
            </x-forms.form-group>

            {{-- Impact preview --}}
            <div
                x-show="delta !== 0"
                x-cloak
                x-transition.opacity
                class="mt-3 p-3 rounded-lg text-sm"
                :class="Math.abs(delta) > {{ $threshold }}
                    ? 'bg-amber-50 border border-amber-200 text-amber-900'
                    : 'bg-emerald-50 border border-emerald-200 text-emerald-900'"
            >
                <p x-show="Math.abs(delta) <= {{ $threshold }}" x-cloak>
                    ✓ سيتم تطبيق التعديل فوراً (ضمن الحد المسموح).
                </p>
                <p x-show="Math.abs(delta) > {{ $threshold }}" x-cloak>
                    ⚠ هذا التعديل (<span x-text="Math.abs(delta).toLocaleString()" class="font-latin font-bold"></span> نقطة) أكبر من الحد. سيُرسل لقائمة الموافقة ولن يتغير الرصيد حتى يوافق سوبر أدمن.
                </p>
            </div>

            <x-slot:footer>
                <x-ui.button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'adjust-wallet-{{ $agent->id }}')">إلغاء</x-ui.button>
                <x-ui.button type="submit" variant="cta">حفظ التعديل</x-ui.button>
            </x-slot:footer>
        </form>
    </x-ui.modal>

</x-layouts.admin>
