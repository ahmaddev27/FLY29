<x-layouts.manager
    :title="$agent->business_name"
    :pageTitle="'ملف الوكيل: ' . $agent->business_name"
    :breadcrumbs="[
        ['label' => 'لوحة التحكم', 'href' => route('manager.dashboard')],
        ['label' => 'وكلائي', 'href' => route('manager.agents')],
        ['label' => $agent->business_name],
    ]"
>
    <div class="grid lg:grid-cols-3 gap-6">

        {{-- Profile column --}}
        <div class="lg:col-span-1 space-y-6">
            <x-ui.card>
                <div class="text-center">
                    <div class="mx-auto w-20 h-20 rounded-full bg-[var(--color-primary-500)] text-white flex items-center justify-center text-2xl font-bold mb-3">
                        {{ mb_substr($agent->business_name, 0, 1) }}
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">{{ $agent->business_name }}</h3>
                    <p class="text-sm font-latin text-slate-500 mt-0.5">{{ $agent->user->email ?? '—' }}</p>

                    <div class="mt-3">
                        <x-ui.tier-badge :tier="$agent->current_tier" />
                    </div>
                </div>

                <dl class="mt-5 space-y-2 text-sm">
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">المعرّف الخارجي</dt>
                        <dd class="font-latin">{{ $agent->external_agent_id }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">رقم الترخيص</dt>
                        <dd class="font-latin">{{ $agent->license_number }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">الدولة</dt>
                        <dd>{{ $agent->country }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">المدينة</dt>
                        <dd>{{ $agent->city ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">تاريخ الانضمام</dt>
                        <dd>{{ $agent->created_at->format('Y-m-d') }}</dd>
                    </div>
                </dl>

                {{-- Wallet balances --}}
                <div class="mt-5 grid grid-cols-2 gap-2">
                    <div class="bg-emerald-50 rounded-lg p-3 text-center">
                        <p class="text-xs text-emerald-700">محفظة كاش</p>
                        <p class="text-xl font-bold text-emerald-900" dir="ltr">{{ number_format($agent->cashWallet->available_points ?? 0) }}</p>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-3 text-center">
                        <p class="text-xs text-blue-700">محفظة باكجات</p>
                        <p class="text-xl font-bold text-blue-900" dir="ltr">{{ number_format($agent->packageWallet->available_points ?? 0) }}</p>
                    </div>
                </div>

                {{-- Suggest adjustment CTA --}}
                <div class="mt-5">
                    <x-ui.button
                        type="button"
                        variant="cta"
                        :full="true"
                        :auto-loading="false"
                        x-on:click="$dispatch('open-modal', 'suggest-adj-{{ $agent->id }}')"
                    >
                        <x-ui.icon name="edit" size="sm" /> اقترح تعديل نقاط
                    </x-ui.button>
                    <p class="text-xs text-slate-500 mt-2 text-center">يحتاج موافقة أدمن قبل التطبيق</p>
                </div>
            </x-ui.card>
        </div>

        {{-- Tabs column --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm">
                <x-ui.tabs :tabs="[
                    'transactions' => 'المعاملات (' . $recentTxns->count() . ')',
                    'redemptions' => 'الاستبدالات (' . $recentRedemptions->count() . ')',
                    'tier-history' => 'تاريخ التصنيف',
                ]" default="transactions">

                    <x-ui.tab-panel key="transactions">
                        <div class="px-5 pb-5">
                            @if($recentTxns->isEmpty())
                                <x-ui.empty-state title="لا توجد معاملات" />
                            @else
                                <div class="overflow-x-auto">
                                    <table class="w-full text-right text-sm">
                                        <thead class="text-xs text-slate-500 uppercase">
                                            <tr>
                                                <th class="py-2">التاريخ</th>
                                                <th class="py-2">المبلغ</th>
                                                <th class="py-2">النقاط</th>
                                                <th class="py-2">الوجهة</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            @foreach($recentTxns as $txn)
                                                <tr>
                                                    <td class="py-2 whitespace-nowrap">{{ $txn->transaction_date->format('Y-m-d') }}</td>
                                                    <td class="py-2 font-latin" dir="ltr">${{ number_format($txn->amount_usd, 2) }}</td>
                                                    <td class="py-2 font-bold text-emerald-600 font-latin" dir="ltr">+{{ $txn->points_awarded }}</td>
                                                    <td class="py-2">{{ $txn->destination ?? '—' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </x-ui.tab-panel>

                    <x-ui.tab-panel key="redemptions">
                        <div class="px-5 pb-5">
                            @if($recentRedemptions->isEmpty())
                                <x-ui.empty-state title="لا توجد طلبات استبدال" />
                            @else
                                <div class="space-y-2">
                                    @foreach($recentRedemptions as $r)
                                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                                            <div>
                                                <div class="text-sm font-medium">
                                                    {{ $r->type === 'cash' ? 'تحويل كاش' : ($r->package?->name ?? 'باكج') }}
                                                </div>
                                                <div class="text-xs text-slate-500">
                                                    <span class="font-latin">{{ number_format($r->points) }}</span> نقطة · {{ $r->requested_at->diffForHumans() }}
                                                </div>
                                            </div>
                                            @switch($r->status)
                                                @case('pending')   <x-ui.badge variant="warning" :dot="true">قيد المراجعة</x-ui.badge> @break
                                                @case('approved')  <x-ui.badge variant="success" :dot="true">معتمد</x-ui.badge> @break
                                                @case('rejected')  <x-ui.badge variant="danger"  :dot="true">مرفوض</x-ui.badge> @break
                                                @case('cancelled') <x-ui.badge variant="neutral" :dot="true">ملغي</x-ui.badge> @break
                                                @case('fulfilled') <x-ui.badge variant="info"    :dot="true">منفّذ</x-ui.badge> @break
                                            @endswitch
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </x-ui.tab-panel>

                    <x-ui.tab-panel key="tier-history">
                        <div class="px-5 pb-5">
                            @if($tierHistory->isEmpty())
                                <x-ui.empty-state title="لا يوجد سجل تصنيف" />
                            @else
                                <div class="space-y-2">
                                    @foreach($tierHistory as $h)
                                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                                            <div class="flex items-center gap-2">
                                                <x-ui.tier-badge :tier="$h->old_tier" size="sm" />
                                                <span class="text-slate-400">→</span>
                                                <x-ui.tier-badge :tier="$h->new_tier" size="sm" />
                                            </div>
                                            <div class="text-xs text-slate-500">{{ $h->created_at->format('Y-m-d') }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </x-ui.tab-panel>
                </x-ui.tabs>
            </div>
        </div>
    </div>

    {{-- Suggest adjustment modal --}}
    <x-ui.modal
        :name="'suggest-adj-' . $agent->id"
        :title="'اقتراح تعديل لـ ' . $agent->business_name"
        size="md"
        :action="route('manager.adjustments.store', $agent)"
        method="POST"
    >
        <div class="mb-3 p-3 rounded-lg bg-sky-50 border border-sky-100 text-sm text-sky-900">
            <p class="flex items-center gap-2">
                <x-ui.icon name="alert-triangle" size="sm" />
                <span>هذا اقتراح فقط — لن يُطبَّق على الرصيد حتى يوافق الأدمن.</span>
            </p>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <x-forms.form-group label="المحفظة" for="wallet_type" required>
                <x-ui.select id="wallet_type" name="wallet_type" :options="['cash' => 'كاش', 'package' => 'باكجات']" />
            </x-forms.form-group>

            <x-forms.form-group label="عدد النقاط (موجب أو سالب)" for="points_delta" required>
                <x-ui.input type="number" id="points_delta" name="points_delta" step="1" required placeholder="مثلاً: 100 أو -50" />
            </x-forms.form-group>
        </div>

        <x-forms.form-group label="السبب" for="adj_reason" required class="mt-4">
            <x-ui.textarea id="adj_reason" name="reason" rows="3" required placeholder="مكافأة على أداء ممتاز، تعويض عن خطأ سابق، ..." />
        </x-forms.form-group>

        <x-slot:footer>
            <div x-on:click="$dispatch('close-modal', 'suggest-adj-{{ $agent->id }}')" class="inline-block">
                <x-ui.button type="button" variant="secondary" :auto-loading="false">إلغاء</x-ui.button>
            </div>
            <x-ui.button type="submit" variant="cta">إرسال للأدمن</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</x-layouts.manager>
