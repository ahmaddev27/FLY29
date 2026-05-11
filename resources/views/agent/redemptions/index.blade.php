<x-layouts.agent
    title="طلباتي"
    pageTitle="طلباتي"
    :breadcrumbs="[
        ['label' => 'الرئيسية', 'href' => route('agent.dashboard')],
        ['label' => 'طلباتي'],
    ]"
>

    <x-ui.card>
        <x-slot:actions>
            <x-ui.button variant="cta" size="sm" href="{{ route('agent.redemptions.cash') }}">
                + تحويل نقدي
            </x-ui.button>
            <x-ui.button variant="primary" size="sm" href="{{ route('agent.redemptions.packages') }}">
                + استبدال باكج
            </x-ui.button>
        </x-slot:actions>

        @if($requests->isEmpty())
            <x-ui.empty-state
                title="لا توجد طلبات بعد"
                description="لم تقدّم أي طلب تحويل أو استبدال حتى الآن."
            >
                <x-slot:actions>
                    <x-ui.button variant="cta" href="{{ route('agent.redemptions.cash') }}">
                        ابدأ بطلب تحويل
                    </x-ui.button>
                </x-slot:actions>
            </x-ui.empty-state>
        @else
            <x-ui.table :headers="['#', 'النوع', 'النقاط', 'القيمة/الباكج', 'الحالة', 'التاريخ', 'إجراء']">
                @foreach($requests as $req)
                    <x-ui.table-row>
                        <x-ui.table-cell>
                            <span class="font-latin text-xs text-[var(--color-text-muted)]">#{{ $req->id }}</span>
                        </x-ui.table-cell>

                        <x-ui.table-cell>
                            @if($req->type === 'cash')
                                <x-ui.badge variant="success" size="sm">نقدي</x-ui.badge>
                            @else
                                <x-ui.badge variant="primary" size="sm">باكج</x-ui.badge>
                            @endif
                        </x-ui.table-cell>

                        <x-ui.table-cell>
                            <span dir="ltr" class="font-semibold">{{ number_format($req->points) }}</span>
                        </x-ui.table-cell>

                        <x-ui.table-cell>
                            @if($req->type === 'cash')
                                <span dir="ltr" class="text-[var(--color-cta-700)] font-medium">${{ number_format($req->cash_value_usd, 2) }}</span>
                            @else
                                <span>{{ $req->package?->name ?? '—' }}</span>
                            @endif
                        </x-ui.table-cell>

                        <x-ui.table-cell>
                            @switch($req->status)
                                @case('pending')   <x-ui.badge variant="warning" :dot="true">قيد المراجعة</x-ui.badge> @break
                                @case('approved')  <x-ui.badge variant="success" :dot="true">معتمد</x-ui.badge> @break
                                @case('rejected')
                                    <x-ui.badge variant="danger" :dot="true">مرفوض</x-ui.badge>
                                    @if($req->rejection_reason)
                                        <div class="text-xs text-[var(--color-text-muted)] mt-1 max-w-xs">{{ $req->rejection_reason }}</div>
                                    @endif
                                    @break
                                @case('cancelled') <x-ui.badge variant="neutral" :dot="true">ملغي</x-ui.badge> @break
                                @case('fulfilled') <x-ui.badge variant="info" :dot="true">تمّ التنفيذ</x-ui.badge> @break
                            @endswitch
                        </x-ui.table-cell>

                        <x-ui.table-cell>
                            <div class="text-sm">{{ $req->requested_at->format('Y-m-d') }}</div>
                            <div class="text-xs text-[var(--color-text-muted)]">{{ $req->requested_at->format('H:i') }}</div>
                        </x-ui.table-cell>

                        <x-ui.table-cell>
                            @if($req->status === 'pending')
                                <form method="POST" action="{{ route('agent.redemptions.cancel', $req) }}">
                                    @csrf
                                    @method('DELETE')
                                    <x-ui.button type="submit" variant="ghost" size="sm" loadingText="جاري الإلغاء…">
                                        إلغاء
                                    </x-ui.button>
                                </form>
                            @else
                                <span class="text-xs text-[var(--color-text-muted)]">—</span>
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

</x-layouts.agent>
