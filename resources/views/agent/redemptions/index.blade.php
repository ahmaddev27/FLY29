<x-layouts.agent
    title="طلباتي"
    pageTitle="طلباتي"
    :breadcrumbs="[
        ['label' => 'الرئيسية', 'href' => route('agent.dashboard')],
        ['label' => 'طلباتي'],
    ]"
>

    <x-ui.data-table
        :paginator="$requests"
        :search="false"
        :filters="[
            [
                'name'    => 'status',
                'label'   => 'الحالة',
                'options' => [
                    'pending'   => 'قيد المراجعة',
                    'approved'  => 'معتمد',
                    'rejected'  => 'مرفوض',
                    'cancelled' => 'ملغي',
                    'fulfilled' => 'منفّذ',
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
        <x-slot:toolbar>
            <x-ui.button variant="cta" size="sm" href="{{ route('agent.redemptions.cash') }}">
                <x-ui.icon name="plus" size="sm" /> تحويل نقدي
            </x-ui.button>
            <x-ui.button variant="primary" size="sm" href="{{ route('agent.redemptions.packages') }}">
                <x-ui.icon name="plus" size="sm" /> استبدال باكج
            </x-ui.button>
        </x-slot:toolbar>

        <x-slot:empty>
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
        </x-slot:empty>

        <table class="w-full text-right">
            <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                <tr>
                    <th class="px-4 py-3">#</th>
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
                        <td class="px-4 py-3">
                            <span class="font-latin text-xs text-slate-500">#{{ $req->id }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @if($req->type === 'cash')
                                <x-ui.badge variant="success" size="sm">نقدي</x-ui.badge>
                            @else
                                <x-ui.badge variant="primary" size="sm">باكج</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span dir="ltr" class="font-semibold">{{ number_format($req->points) }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @if($req->type === 'cash')
                                <span dir="ltr" class="text-emerald-700 font-medium">${{ number_format($req->cash_value_usd, 2) }}</span>
                            @else
                                <span class="text-sm">{{ $req->package?->name ?? '—' }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @switch($req->status)
                                @case('pending')   <x-ui.badge variant="warning" :dot="true">قيد المراجعة</x-ui.badge> @break
                                @case('approved')  <x-ui.badge variant="success" :dot="true">معتمد</x-ui.badge> @break
                                @case('rejected')
                                    <x-ui.badge variant="danger" :dot="true">مرفوض</x-ui.badge>
                                    @if($req->rejection_reason)
                                        <div class="text-xs text-slate-500 mt-1 max-w-xs">{{ $req->rejection_reason }}</div>
                                    @endif
                                    @break
                                @case('cancelled') <x-ui.badge variant="neutral" :dot="true">ملغي</x-ui.badge> @break
                                @case('fulfilled')
                                    <x-ui.badge variant="info" :dot="true">تمّ التنفيذ</x-ui.badge>
                                    @if($req->fulfillment_reference)
                                        <div class="text-xs text-slate-500 mt-1 font-latin">
                                            مرجع: {{ $req->fulfillment_reference }}
                                        </div>
                                    @endif
                                    @if($req->fulfilled_at)
                                        <div class="text-xs text-slate-400 mt-0.5">{{ $req->fulfilled_at->diffForHumans() }}</div>
                                    @endif
                                    @break
                            @endswitch
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm">{{ $req->requested_at->format('Y-m-d') }}</div>
                            <div class="text-xs text-slate-500">{{ $req->requested_at->format('H:i') }}</div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($req->status === 'pending')
                                <form method="POST" action="{{ route('agent.redemptions.cancel', $req) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <x-ui.icon-button type="submit" icon="x" variant="danger" tooltip="إلغاء الطلب" />
                                </form>
                            @else
                                <span class="text-xs text-slate-400">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-ui.data-table>

</x-layouts.agent>
