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
            'pending'   => ['label' => 'قيد المراجعة', 'variant' => 'warning'],
            'approved'  => ['label' => 'معتمدة',       'variant' => 'success'],
            'rejected'  => ['label' => 'مرفوضة',       'variant' => 'danger'],
            'cancelled' => ['label' => 'ملغاة',        'variant' => 'neutral'],
        ] as $statusKey => $meta)
            <a href="{{ route('admin.redemptions', ['status' => $statusKey]) }}"
               class="bg-white rounded-[var(--radius-md)] border border-[var(--color-surface-border)] p-4 hover:shadow-[var(--shadow-card-hover)] transition-base
                      {{ $currentStatus === $statusKey ? 'ring-2 ring-[var(--color-primary-500)]' : '' }}">
                <p class="text-xs text-[var(--color-text-secondary)]">{{ $meta['label'] }}</p>
                <p class="text-2xl font-bold text-[var(--color-text-primary)] mt-1">{{ $counts[$statusKey] ?? 0 }}</p>
            </a>
        @endforeach
    </div>

    <x-ui.card>
        {{-- Tabs --}}
        <div class="flex flex-wrap items-center gap-2 mb-4 pb-4 border-b border-[var(--color-surface-divider)]">
            @foreach(['pending' => 'قيد المراجعة', 'approved' => 'معتمدة', 'rejected' => 'مرفوضة', 'cancelled' => 'ملغاة', 'all' => 'الكل'] as $key => $label)
                <a href="{{ route('admin.redemptions', ['status' => $key === 'all' ? null : $key, 'type' => $currentType]) }}"
                   class="px-3 py-1.5 text-sm rounded-[var(--radius-sm)] transition-base
                          {{ $currentStatus === $key ? 'bg-[var(--color-primary-500)] text-white' : 'text-[var(--color-text-secondary)] hover:bg-[var(--color-surface-secondary)]' }}">
                    {{ $label }}
                </a>
            @endforeach

            <div class="me-auto"></div>

            {{-- Type filter --}}
            <select onchange="window.location.href=this.value"
                    class="rounded-[var(--radius-sm)] border-[var(--color-surface-border)] text-sm focus:ring-[var(--color-primary-100)] focus:border-[var(--color-primary-500)]">
                <option value="{{ route('admin.redemptions', ['status' => $currentStatus]) }}" @selected(!$currentType)>كل الأنواع</option>
                <option value="{{ route('admin.redemptions', ['status' => $currentStatus, 'type' => 'cash']) }}" @selected($currentType === 'cash')>نقدي فقط</option>
                <option value="{{ route('admin.redemptions', ['status' => $currentStatus, 'type' => 'package']) }}" @selected($currentType === 'package')>باكج فقط</option>
            </select>
        </div>

        @if($requests->isEmpty())
            <x-ui.empty-state title="لا توجد طلبات في هذه الفئة" />
        @else
            <x-ui.table :headers="['#', 'الوكيل', 'النوع', 'النقاط', 'القيمة/الباكج', 'الحالة', 'التاريخ', 'إجراء']">
                @foreach($requests as $req)
                    <x-ui.table-row>
                        <x-ui.table-cell>
                            <span class="font-latin text-xs text-[var(--color-text-muted)]">#{{ $req->id }}</span>
                        </x-ui.table-cell>

                        <x-ui.table-cell>
                            <div class="text-sm font-medium">{{ $req->agent->business_name }}</div>
                            <div class="text-xs text-[var(--color-text-muted)] font-latin">{{ $req->agent->external_agent_id }}</div>
                        </x-ui.table-cell>

                        <x-ui.table-cell>
                            <x-ui.badge :variant="$req->type === 'cash' ? 'success' : 'primary'" size="sm">
                                {{ $req->type === 'cash' ? 'نقدي' : 'باكج' }}
                            </x-ui.badge>
                        </x-ui.table-cell>

                        <x-ui.table-cell>
                            <span dir="ltr" class="font-semibold">{{ number_format($req->points) }}</span>
                        </x-ui.table-cell>

                        <x-ui.table-cell>
                            @if($req->type === 'cash')
                                <span dir="ltr" class="text-[var(--color-cta-700)] font-medium">${{ number_format($req->cash_value_usd, 2) }}</span>
                            @else
                                <span class="text-sm">{{ $req->package?->name ?? '—' }}</span>
                            @endif
                        </x-ui.table-cell>

                        <x-ui.table-cell>
                            @switch($req->status)
                                @case('pending')   <x-ui.badge variant="warning" :dot="true">قيد المراجعة</x-ui.badge> @break
                                @case('approved')  <x-ui.badge variant="success" :dot="true">معتمد</x-ui.badge> @break
                                @case('rejected')  <x-ui.badge variant="danger"  :dot="true">مرفوض</x-ui.badge> @break
                                @case('cancelled') <x-ui.badge variant="neutral" :dot="true">ملغي</x-ui.badge> @break
                                @case('fulfilled') <x-ui.badge variant="info"    :dot="true">منفّذ</x-ui.badge> @break
                            @endswitch
                            @if($req->rejection_reason)
                                <div class="text-xs text-[var(--color-text-muted)] mt-1 max-w-xs">{{ $req->rejection_reason }}</div>
                            @endif
                        </x-ui.table-cell>

                        <x-ui.table-cell>
                            <div class="text-sm">{{ $req->requested_at->format('Y-m-d') }}</div>
                            <div class="text-xs text-[var(--color-text-muted)]">{{ $req->requested_at->format('H:i') }}</div>
                        </x-ui.table-cell>

                        <x-ui.table-cell>
                            @if($req->status === 'pending' && $req->type === 'cash')
                                <div class="flex gap-1">
                                    <form method="POST" action="{{ route('admin.redemptions.approve', $req) }}">
                                        @csrf
                                        <x-ui.button type="submit" variant="cta" size="sm" loadingText="…">موافقة</x-ui.button>
                                    </form>
                                    <x-ui.button
                                        type="button"
                                        variant="danger"
                                        size="sm"
                                        :auto-loading="false"
                                        x-on:click="$dispatch('open-modal', 'reject-{{ $req->id }}')"
                                    >رفض</x-ui.button>
                                </div>

                                {{-- Reject modal --}}
                                <x-ui.modal :name="'reject-' . $req->id" title="رفض الطلب #{{ $req->id }}" size="sm">
                                    <form method="POST" action="{{ route('admin.redemptions.reject', $req) }}">
                                        @csrf
                                        <p class="text-sm text-[var(--color-text-secondary)] mb-3">
                                            سيُسترَدّ <strong dir="ltr">{{ number_format($req->points) }}</strong> نقطة للوكيل ويصله إشعار بالسبب.
                                        </p>
                                        <x-forms.form-group label="سبب الرفض" for="rejection_reason_{{ $req->id }}" required>
                                            <x-ui.textarea
                                                id="rejection_reason_{{ $req->id }}"
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
                                <span class="text-xs text-[var(--color-text-muted)]">
                                    @if($req->processor)
                                        بواسطة {{ $req->processor->full_name }}
                                    @else
                                        —
                                    @endif
                                </span>
                            @endif
                        </x-ui.table-cell>
                    </x-ui.table-row>
                @endforeach
            </x-ui.table>

            <div class="mt-4">
                {{ $requests->links() }}
            </div>
        @endif
    </x-ui.card>

</x-layouts.admin>
