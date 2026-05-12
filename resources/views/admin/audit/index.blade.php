@php
    /**
     * Map of common audit-actions → display label + variant.
     * Anything not listed falls back to the raw key in slate.
     */
    $actionMeta = [
        // Auth
        'login_success'  => ['label' => 'تسجيل دخول',          'variant' => 'success'],
        'login_failed'   => ['label' => 'محاولة دخول فاشلة',   'variant' => 'danger'],
        'logout'         => ['label' => 'تسجيل خروج',          'variant' => 'neutral'],
        'password_reset' => ['label' => 'إعادة تعيين كلمة المرور', 'variant' => 'info'],

        // Agents
        'agent_created'              => ['label' => 'إنشاء وكيل',          'variant' => 'success'],
        'agent_suspended'            => ['label' => 'تعليق وكيل',          'variant' => 'warning'],
        'agent_unsuspended'          => ['label' => 'إلغاء تعليق وكيل',    'variant' => 'success'],
        'agent_deleted'              => ['label' => 'حذف وكيل',            'variant' => 'danger'],
        'agent_notes_updated'        => ['label' => 'تحديث ملاحظات وكيل',  'variant' => 'info'],
        'agents_imported'            => ['label' => 'استيراد وكلاء',       'variant' => 'success'],

        // Adjustments
        'adjustment_applied'   => ['label' => 'تطبيق تعديل يدوي',  'variant' => 'info'],
        'adjustment_queued'    => ['label' => 'طلب تعديل (موافقة مزدوجة)', 'variant' => 'warning'],
        'adjustment_approved'  => ['label' => 'اعتماد تعديل',      'variant' => 'success'],
        'adjustment_rejected'  => ['label' => 'رفض تعديل',         'variant' => 'danger'],
        'adjustment_cancelled' => ['label' => 'إلغاء طلب تعديل',   'variant' => 'neutral'],

        // Settings
        'settings_updated' => ['label' => 'تحديث الإعدادات', 'variant' => 'warning'],

        // Account managers
        'account_manager_created'             => ['label' => 'إنشاء مدير حسابات',          'variant' => 'success'],
        'account_manager_suspended'           => ['label' => 'تعليق مدير حسابات',          'variant' => 'warning'],
        'account_manager_unsuspended'         => ['label' => 'إلغاء تعليق مدير حسابات',    'variant' => 'success'],
        'account_manager_deleted'             => ['label' => 'حذف مدير حسابات',            'variant' => 'danger'],
        'account_manager_assigned_agents'     => ['label' => 'تعيين وكلاء لمدير',          'variant' => 'info'],
        'account_manager_unassigned_agent'    => ['label' => 'إلغاء تعيين وكيل',           'variant' => 'neutral'],

        // Redemptions
        'redemption_approved' => ['label' => 'اعتماد استبدال', 'variant' => 'success'],
        'redemption_rejected' => ['label' => 'رفض استبدال',     'variant' => 'danger'],
        'redemption_created'  => ['label' => 'طلب استبدال',     'variant' => 'info'],
        'redemption_cancelled'=> ['label' => 'إلغاء استبدال',   'variant' => 'neutral'],
    ];
@endphp

<x-layouts.admin
    title="سجل التدقيق"
    pageTitle="سجل التدقيق"
    :breadcrumbs="[
        ['label' => 'لوحة الإدارة', 'href' => route('admin.dashboard')],
        ['label' => 'سجل التدقيق'],
    ]"
>
    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-xl shadow-sm p-4 mb-4">
        <div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div class="lg:col-span-2">
                <x-ui.input
                    name="q"
                    :value="request('q')"
                    placeholder="بحث: إجراء، مستخدم، entity_id، نوع كيان..."
                />
            </div>

            <x-ui.select
                name="action"
                :options="$actions"
                :selected="request('action')"
                placeholder="كل الإجراءات"
            />

            <x-ui.input
                type="date"
                name="from"
                :value="request('from')"
                placeholder="من تاريخ"
            />

            <x-ui.input
                type="date"
                name="to"
                :value="request('to')"
                placeholder="إلى تاريخ"
            />
        </div>

        <div class="flex justify-end gap-2 mt-3">
            @if(request()->hasAny(['q', 'action', 'from', 'to']))
                <x-ui.button variant="ghost" size="sm" href="{{ route('admin.audit') }}">إلغاء الفلاتر</x-ui.button>
            @endif
            <x-ui.button type="submit" variant="primary" size="sm">
                <x-ui.icon name="search" size="sm" /> بحث
            </x-ui.button>
        </div>
    </form>

    @if($logs->isEmpty())
        <x-ui.empty-state
            title="لا توجد سجلات"
            description="جرّب توسيع نطاق البحث أو إلغاء الفلاتر."
        />
    @else
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-right">
                    <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3">التاريخ</th>
                            <th class="px-4 py-3">المستخدم</th>
                            <th class="px-4 py-3">الإجراء</th>
                            <th class="px-4 py-3">الكيان</th>
                            <th class="px-4 py-3">IP</th>
                            <th class="px-4 py-3 text-center">التفاصيل</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($logs as $log)
                            @php $meta = $actionMeta[$log->action] ?? ['label' => $log->action, 'variant' => 'neutral']; @endphp

                            <tr class="hover:bg-slate-50/50" x-data="{ open: false }">
                                <td class="px-4 py-3 text-sm whitespace-nowrap">
                                    <div>{{ $log->created_at->format('Y-m-d H:i:s') }}</div>
                                    <div class="text-xs text-slate-500">{{ $log->created_at->diffForHumans() }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if($log->user)
                                        <div class="font-medium">{{ $log->user->full_name }}</div>
                                        <div class="text-xs text-slate-500 font-latin">{{ $log->user->email }}</div>
                                    @else
                                        <span class="text-slate-400">نظام / مجهول</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <x-ui.badge :variant="$meta['variant']">{{ $meta['label'] }}</x-ui.badge>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <div class="text-xs font-latin text-slate-500">{{ class_basename($log->entity_type) }}</div>
                                    @if($log->entity_id)
                                        <div class="font-latin font-bold">#{{ $log->entity_id }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm font-latin text-slate-500">{{ $log->ip_address ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center">
                                        @if(! empty($log->old_values) || ! empty($log->new_values))
                                            <x-ui.icon-button
                                                type="button"
                                                icon="eye"
                                                variant="ghost"
                                                tooltip="عرض القيم"
                                                :auto-loading="false"
                                                x-on:click="open = !open"
                                            />
                                        @else
                                            <span class="text-xs text-slate-400">—</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            {{-- Inline expand row for values --}}
                            @if(! empty($log->old_values) || ! empty($log->new_values))
                                <tr x-show="open" x-cloak class="bg-slate-50/50">
                                    <td colspan="6" class="px-4 py-3">
                                        <div class="grid md:grid-cols-2 gap-3">
                                            @if(! empty($log->old_values))
                                                <div>
                                                    <h4 class="text-xs font-semibold text-rose-700 mb-1">القيم السابقة</h4>
                                                    <pre class="text-xs font-latin bg-rose-50 border border-rose-100 rounded-lg p-3 overflow-x-auto" dir="ltr">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                </div>
                                            @endif
                                            @if(! empty($log->new_values))
                                                <div>
                                                    <h4 class="text-xs font-semibold text-emerald-700 mb-1">القيم الجديدة</h4>
                                                    <pre class="text-xs font-latin bg-emerald-50 border border-emerald-100 rounded-lg p-3 overflow-x-auto" dir="ltr">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 border-t border-slate-100 bg-slate-50/50">
                {{ $logs->links() }}
            </div>
        </div>
    @endif
</x-layouts.admin>
