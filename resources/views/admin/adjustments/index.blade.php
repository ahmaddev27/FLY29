@php
    $tabs = [
        'pending'   => ['label' => 'قيد الموافقة',  'badge' => $counts['pending']   ?? 0],
        'approved'  => ['label' => 'معتمد',          'badge' => $counts['approved']  ?? 0],
        'rejected'  => ['label' => 'مرفوض',          'badge' => $counts['rejected']  ?? 0],
        'cancelled' => ['label' => 'ملغي',           'badge' => $counts['cancelled'] ?? 0],
    ];
    $user = auth()->user();
    $canApprove = $user->isSuperAdmin();
@endphp

<x-layouts.admin
    title="التعديلات اليدوية"
    pageTitle="التعديلات اليدوية (الموافقة المزدوجة)"
    :breadcrumbs="[
        ['label' => 'لوحة الإدارة', 'href' => route('admin.dashboard')],
        ['label' => 'التعديلات'],
    ]"
>
    {{-- Tabs --}}
    <div class="flex items-center gap-1 mb-4 border-b border-slate-200">
        @foreach($tabs as $key => $meta)
            <a
                href="{{ url()->current() }}?tab={{ $key }}"
                @class([
                    'flex items-center gap-2 px-4 py-2.5 text-sm font-medium transition-colors border-b-2 -mb-px',
                    'border-[var(--color-primary-500)] text-[var(--color-primary-700)]' => $tab === $key,
                    'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300' => $tab !== $key,
                ])
            >
                {{ $meta['label'] }}
                @if($meta['badge'] > 0)
                    <span @class([
                        'inline-flex items-center justify-center min-w-5 h-5 px-1.5 rounded-full text-xs font-bold',
                        'bg-amber-100 text-amber-800' => $key === 'pending',
                        'bg-emerald-100 text-emerald-700' => $key === 'approved',
                        'bg-rose-100 text-rose-700' => $key === 'rejected',
                        'bg-slate-200 text-slate-600' => $key === 'cancelled',
                    ])>{{ $meta['badge'] }}</span>
                @endif
            </a>
        @endforeach
    </div>

    @if($adjustments->isEmpty())
        <x-ui.empty-state
            title="لا توجد تعديلات هنا"
            description="جميع التعديلات اليدوية ستظهر في هذه الصفحة. التعديلات الكبيرة (>500 نقطة افتراضياً) تحتاج موافقة سوبر أدمن."
        />
    @else
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-right">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3">الوكيل</th>
                        <th class="px-4 py-3">المحفظة</th>
                        <th class="px-4 py-3">التعديل</th>
                        <th class="px-4 py-3">السبب</th>
                        <th class="px-4 py-3">طلب من</th>
                        <th class="px-4 py-3">التاريخ</th>
                        <th class="px-4 py-3 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($adjustments as $adj)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.agents.show', $adj->agent) }}" class="font-medium text-[var(--color-primary-700)] hover:underline">
                                    {{ $adj->agent->business_name }}
                                </a>
                                <div class="text-xs font-latin text-slate-500">{{ $adj->agent->external_agent_id }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <x-ui.badge :variant="$adj->wallet_type === 'cash' ? 'info' : 'success'">
                                    {{ $adj->wallet_type === 'cash' ? 'كاش' : 'باكجات' }}
                                </x-ui.badge>
                            </td>
                            <td class="px-4 py-3">
                                <span @class([
                                    'inline-flex items-center gap-1 font-bold font-latin',
                                    'text-emerald-600' => $adj->points_delta > 0,
                                    'text-rose-600'    => $adj->points_delta < 0,
                                ])>
                                    {{ $adj->points_delta > 0 ? '+' : '' }}{{ number_format($adj->points_delta) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-700 max-w-xs">
                                <div class="line-clamp-2">{{ $adj->reason }}</div>
                                @if($adj->admin_notes)
                                    <div class="text-xs text-slate-500 mt-1 italic">ملاحظة: {{ $adj->admin_notes }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <div>{{ $adj->requester->full_name }}</div>
                                @if($adj->approver)
                                    <div class="text-xs text-slate-500">اعتمد: {{ $adj->approver->full_name }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-600">
                                <div>{{ $adj->created_at->format('Y-m-d H:i') }}</div>
                                <div class="text-xs text-slate-400">{{ $adj->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    @if($adj->status === 'pending')
                                        @if($canApprove && $adj->requested_by !== $user->id)
                                            <x-ui.icon-button
                                                type="button"
                                                icon="check"
                                                variant="success"
                                                tooltip="اعتماد"
                                                :auto-loading="false"
                                                x-on:click="$dispatch('open-modal', 'approve-{{ $adj->id }}')"
                                            />
                                            <x-ui.icon-button
                                                type="button"
                                                icon="x"
                                                variant="danger"
                                                tooltip="رفض"
                                                :auto-loading="false"
                                                x-on:click="$dispatch('open-modal', 'reject-{{ $adj->id }}')"
                                            />
                                        @endif

                                        @if($adj->requested_by === $user->id)
                                            <form method="POST" action="{{ route('admin.adjustments.cancel', $adj) }}" class="inline">
                                                @csrf
                                                <x-ui.icon-button type="submit" icon="x" variant="warning" tooltip="إلغاء الطلب" />
                                            </form>
                                        @endif
                                    @else
                                        <span class="text-xs text-slate-400">—</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="px-4 py-3 border-t border-slate-100 bg-slate-50/50">
                {{ $adjustments->links() }}
            </div>
        </div>

        {{-- Modals: approve + reject for each pending row --}}
        @foreach($adjustments as $adj)
            @if($adj->status === 'pending' && $canApprove && $adj->requested_by !== $user->id)
                {{-- Approve modal --}}
                <x-ui.modal :name="'approve-' . $adj->id" title="اعتماد التعديل" size="sm">
                    <form method="POST" action="{{ route('admin.adjustments.approve', $adj) }}">
                        @csrf
                        <p class="text-sm text-slate-700 mb-3">
                            ستطبّق <strong>{{ $adj->points_delta > 0 ? 'إضافة' : 'خصم' }} {{ number_format(abs($adj->points_delta)) }} نقطة</strong>
                            على محفظة <strong>{{ $adj->wallet_type === 'cash' ? 'الكاش' : 'الباكجات' }}</strong>
                            للوكيل <strong>«{{ $adj->agent->business_name }}»</strong> فوراً.
                        </p>
                        <x-forms.form-group label="ملاحظة (اختياري)" :for="'a_notes_' . $adj->id">
                            <x-ui.textarea :id="'a_notes_' . $adj->id" name="notes" rows="2" placeholder="ملاحظات للسجل..." />
                        </x-forms.form-group>

                        <x-slot:footer>
                            <x-ui.button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'approve-{{ $adj->id }}')">إلغاء</x-ui.button>
                            <x-ui.button type="submit" variant="cta">تأكيد الاعتماد</x-ui.button>
                        </x-slot:footer>
                    </form>
                </x-ui.modal>

                {{-- Reject modal --}}
                <x-ui.modal :name="'reject-' . $adj->id" title="رفض التعديل" size="sm">
                    <form method="POST" action="{{ route('admin.adjustments.reject', $adj) }}">
                        @csrf
                        <p class="text-sm text-slate-700 mb-3">
                            سيُرفض الطلب ولن يتم تغيير الرصيد. السبب يُحفظ في السجل.
                        </p>
                        <x-forms.form-group label="سبب الرفض" :for="'r_notes_' . $adj->id" required>
                            <x-ui.textarea :id="'r_notes_' . $adj->id" name="notes" rows="3" required placeholder="مثلاً: الطلب غير مبرر / مكرر..." />
                        </x-forms.form-group>

                        <x-slot:footer>
                            <x-ui.button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'reject-{{ $adj->id }}')">إلغاء</x-ui.button>
                            <x-ui.button type="submit" variant="danger">رفض الطلب</x-ui.button>
                        </x-slot:footer>
                    </form>
                </x-ui.modal>
            @endif
        @endforeach
    @endif
</x-layouts.admin>
