<x-layouts.admin
    title="طلبات الاستبدال"
    pageTitle="طلبات الاستبدال"
    :breadcrumbs="[
        ['label' => 'لوحة الإدارة', 'href' => route('admin.dashboard')],
        ['label' => 'الطلبات'],
    ]"
>

    {{-- Stats strip --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
        @foreach([
            'pending'   => ['label' => 'قيد المراجعة', 'color' => 'amber'],
            'approved'  => ['label' => 'معتمدة',       'color' => 'emerald'],
            'rejected'  => ['label' => 'مرفوضة',       'color' => 'rose'],
            'cancelled' => ['label' => 'ملغاة',        'color' => 'slate'],
        ] as $statusKey => $meta)
            <a href="{{ route('admin.redemptions', ['status' => $statusKey]) }}"
               class="bg-white rounded-xl border border-slate-200 p-4 hover:shadow-md transition-shadow
                      {{ $currentStatus === $statusKey ? 'ring-2 ring-[var(--color-primary-500)] ring-offset-1' : '' }}">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-500">{{ $meta['label'] }}</p>
                        <p class="text-2xl font-bold text-slate-900 mt-1">{{ $counts[$statusKey] ?? 0 }}</p>
                    </div>
                    <span class="w-2 h-12 rounded-full bg-{{ $meta['color'] }}-400"></span>
                </div>
            </a>
        @endforeach
    </div>

    <div
        x-data="{
            selected: [],
            allOnPage: @js($requests->where('status', 'pending')->where('type', 'cash')->pluck('id')->values()->toArray()),
            toggleAll(checked) {
                this.selected = checked ? [...this.allOnPage] : [];
            },
        }"
    >
        {{-- Bulk action bar (only when items selected) --}}
        <div
            x-show="selected.length > 0"
            x-cloak
            x-transition.opacity
            class="sticky top-0 z-30 -mx-3 sm:-mx-6 mb-4 px-4 sm:px-6 py-3 bg-[var(--color-primary-50)] border-y border-[var(--color-primary-200)] flex items-center justify-between gap-3"
        >
            <div class="text-sm text-[var(--color-primary-900)]">
                <strong x-text="selected.length"></strong> طلب مختار
            </div>
            <div class="flex gap-2">
                <form method="POST" action="{{ route('admin.redemptions.bulk-approve') }}" class="inline">
                    @csrf
                    <template x-for="id in selected" :key="id">
                        <input type="hidden" name="ids[]" :value="id">
                    </template>
                    <x-ui.button type="submit" variant="cta" size="sm">
                        <x-ui.icon name="check" size="sm" /> موافقة على المختار
                    </x-ui.button>
                </form>

                <x-ui.button
                    type="button"
                    variant="danger"
                    size="sm"
                    :auto-loading="false"
                    x-on:click="$dispatch('open-modal', 'bulk-reject')"
                >
                    <x-ui.icon name="x" size="sm" /> رفض المختار
                </x-ui.button>
            </div>
        </div>

        {{-- Bulk reject modal --}}
        <x-ui.modal name="bulk-reject" title="رفض الطلبات المختارة" size="sm">
            <form method="POST" action="{{ route('admin.redemptions.bulk-reject') }}">
                @csrf
                <template x-for="id in selected" :key="id">
                    <input type="hidden" name="ids[]" :value="id">
                </template>
                <p class="text-sm text-slate-600 mb-3">
                    سيُسترد لكل وكيل عدد نقاطه المحجوزة، وسيصله إشعار بالسبب.
                </p>
                <x-forms.form-group label="سبب الرفض (يطبَّق على الكل)" for="bulk_reason" required>
                    <x-ui.textarea id="bulk_reason" name="rejection_reason" rows="3" required placeholder="مثلاً: بيانات الحساب البنكي غير صحيحة..." />
                </x-forms.form-group>

                <x-slot:footer>
                    <x-ui.button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'bulk-reject')">إلغاء</x-ui.button>
                    <x-ui.button type="submit" variant="danger">تأكيد رفض الجميع</x-ui.button>
                </x-slot:footer>
            </form>
        </x-ui.modal>

    <x-ui.data-table
        :paginator="$requests"
        search-placeholder="اسم الوكيل أو معرّفه..."
        :filters="[
            [
                'name'    => 'status',
                'label'   => 'الحالة',
                'options' => [
                    'pending'   => 'قيد المراجعة',
                    'approved'  => 'معتمدة',
                    'rejected'  => 'مرفوضة',
                    'cancelled' => 'ملغاة',
                    'fulfilled' => 'منفّذة',
                ],
            ],
            [
                'name'    => 'type',
                'label'   => 'النوع',
                'options' => ['cash' => 'نقدي', 'package' => 'باكج'],
            ],
        ]"
        :is-empty="$requests->isEmpty()"
    >
        <x-slot:empty>
            <x-ui.empty-state title="لا توجد طلبات في هذه الفئة" />
        </x-slot:empty>

        <table class="w-full text-right">
            <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                <tr>
                    @if($currentStatus === 'pending')
                        <th class="px-4 py-3 w-10">
                            <input
                                type="checkbox"
                                :checked="selected.length > 0 && selected.length === allOnPage.length"
                                :indeterminate.camel="selected.length > 0 && selected.length < allOnPage.length"
                                x-on:change="toggleAll($event.target.checked)"
                                class="rounded border-slate-300 text-[var(--color-primary-500)] focus:ring-[var(--color-primary-100)]"
                            >
                        </th>
                    @endif
                    <th class="px-4 py-3">#</th>
                    <th class="px-4 py-3">الوكيل</th>
                    <th class="px-4 py-3">النوع</th>
                    <th class="px-4 py-3">النقاط</th>
                    <th class="px-4 py-3">القيمة/الباكج</th>
                    <th class="px-4 py-3">الحالة</th>
                    <th class="px-4 py-3">التاريخ</th>
                    <th class="px-4 py-3 text-center">إجراء</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($requests as $req)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        @if($currentStatus === 'pending')
                            <td class="px-4 py-3">
                                @if($req->status === 'pending' && $req->type === 'cash')
                                    <input
                                        type="checkbox"
                                        :value="{{ $req->id }}"
                                        x-model.number="selected"
                                        class="rounded border-slate-300 text-[var(--color-primary-500)] focus:ring-[var(--color-primary-100)]"
                                    >
                                @endif
                            </td>
                        @endif
                        <td class="px-4 py-3">
                            <span class="font-latin text-xs text-slate-500">#{{ $req->id }}</span>
                        </td>

                        <td class="px-4 py-3">
                            <div class="text-sm font-medium text-slate-900">{{ $req->agent->business_name }}</div>
                            <div class="text-xs text-slate-500 font-latin">{{ $req->agent->external_agent_id }}</div>
                        </td>

                        <td class="px-4 py-3">
                            <x-ui.badge :variant="$req->type === 'cash' ? 'success' : 'primary'" size="sm">
                                {{ $req->type === 'cash' ? 'نقدي' : 'باكج' }}
                            </x-ui.badge>
                        </td>

                        <td class="px-4 py-3">
                            <span dir="ltr" class="font-semibold text-slate-900">{{ number_format($req->points) }}</span>
                        </td>

                        <td class="px-4 py-3">
                            @if($req->type === 'cash')
                                <span dir="ltr" class="text-emerald-700 font-medium">${{ number_format($req->cash_value_usd, 2) }}</span>
                            @else
                                <span class="text-sm text-slate-700">{{ $req->package?->name ?? '—' }}</span>
                            @endif
                        </td>

                        <td class="px-4 py-3">
                            @switch($req->status)
                                @case('pending')   <x-ui.badge variant="warning" :dot="true">قيد المراجعة</x-ui.badge> @break
                                @case('approved')  <x-ui.badge variant="success" :dot="true">معتمد</x-ui.badge> @break
                                @case('rejected')  <x-ui.badge variant="danger"  :dot="true">مرفوض</x-ui.badge> @break
                                @case('cancelled') <x-ui.badge variant="neutral" :dot="true">ملغي</x-ui.badge> @break
                                @case('fulfilled') <x-ui.badge variant="info"    :dot="true">منفّذ</x-ui.badge> @break
                            @endswitch
                            @if($req->rejection_reason)
                                <div class="text-xs text-slate-500 mt-1 max-w-xs">{{ $req->rejection_reason }}</div>
                            @endif
                        </td>

                        <td class="px-4 py-3">
                            <div class="text-sm text-slate-700">{{ $req->requested_at->format('Y-m-d') }}</div>
                            <div class="text-xs text-slate-500">{{ $req->requested_at->format('H:i') }}</div>
                        </td>

                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-1">
                                @if($req->status === 'pending' && $req->type === 'cash')
                                    <form method="POST" action="{{ route('admin.redemptions.approve', $req) }}" class="inline">
                                        @csrf
                                        <x-ui.icon-button type="submit" icon="check" variant="success" tooltip="موافقة" />
                                    </form>
                                    <x-ui.icon-button
                                        type="button"
                                        icon="x"
                                        variant="danger"
                                        tooltip="رفض"
                                        :auto-loading="false"
                                        x-on:click="$dispatch('open-modal', 'reject-{{ $req->id }}')"
                                    />

                                    {{-- Reject modal --}}
                                    <x-ui.modal :name="'reject-' . $req->id" title="رفض الطلب #{{ $req->id }}" size="sm">
                                        <form method="POST" action="{{ route('admin.redemptions.reject', $req) }}">
                                            @csrf
                                            <p class="text-sm text-slate-600 mb-3">
                                                سيُسترَدّ <strong dir="ltr">{{ number_format($req->points) }}</strong> نقطة للوكيل ويصله إشعار بالسبب.
                                            </p>
                                            <x-forms.form-group label="سبب الرفض" :for="'rejection_reason_' . $req->id" required>
                                                <x-ui.textarea
                                                    :id="'rejection_reason_' . $req->id"
                                                    name="rejection_reason"
                                                    rows="3"
                                                    placeholder="يرجى توضيح السبب باختصار..."
                                                    required
                                                />
                                            </x-forms.form-group>

                                            <x-slot:footer>
                                                <x-ui.button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'reject-{{ $req->id }}')">إلغاء</x-ui.button>
                                                <x-ui.button type="submit" variant="danger">تأكيد الرفض</x-ui.button>
                                            </x-slot:footer>
                                        </form>
                                    </x-ui.modal>
                                @else
                                    <span class="text-xs text-slate-400">
                                        @if($req->processor)
                                            بواسطة {{ $req->processor->full_name }}
                                        @else
                                            —
                                        @endif
                                    </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-ui.data-table>
    </div>{{-- /x-data bulk wrapper --}}

</x-layouts.admin>
