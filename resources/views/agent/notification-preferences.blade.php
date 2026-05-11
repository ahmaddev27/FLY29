<x-layouts.agent
    title="تفضيلات الإشعارات"
    pageTitle="تفضيلات الإشعارات"
    :breadcrumbs="[['label' => 'الرئيسية', 'href' => route('agent.dashboard')], ['label' => 'تفضيلات الإشعارات']]"
>

    <x-ui.card title="القنوات" subtitle="اختر كيف تريد استلام كل نوع إشعار.">
        <form method="POST" action="{{ route('agent.notification-preferences.update') }}">
            @csrf
            @method('PUT')

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-[var(--color-surface-tertiary)] border-b border-[var(--color-surface-border)]">
                        <tr>
                            <th class="px-4 py-3 text-right font-semibold">نوع الإشعار</th>
                            <th class="px-4 py-3 text-center font-semibold w-24">بريد</th>
                            <th class="px-4 py-3 text-center font-semibold w-24">SMS</th>
                            <th class="px-4 py-3 text-center font-semibold w-24">داخل التطبيق</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--color-surface-divider)]">
                        @foreach($preferences as $type => $pref)
                            <tr class="hover:bg-[var(--color-surface-tertiary)] transition-base">
                                <td class="px-4 py-3 font-medium">{{ $pref['label'] }}</td>
                                <td class="px-4 py-3 text-center">
                                    <input type="checkbox"
                                           name="preferences[{{ $type }}][email_enabled]"
                                           value="1"
                                           {{ $pref['email_enabled'] ? 'checked' : '' }}
                                           class="rounded border-[var(--color-surface-border)] text-[var(--color-primary-500)] focus:ring-[var(--color-primary-100)] w-5 h-5">
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input type="checkbox"
                                           name="preferences[{{ $type }}][sms_enabled]"
                                           value="1"
                                           {{ $pref['sms_enabled'] ? 'checked' : '' }}
                                           class="rounded border-[var(--color-surface-border)] text-[var(--color-primary-500)] focus:ring-[var(--color-primary-100)] w-5 h-5">
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input type="checkbox"
                                           name="preferences[{{ $type }}][in_app_enabled]"
                                           value="1"
                                           {{ $pref['in_app_enabled'] ? 'checked' : '' }}
                                           class="rounded border-[var(--color-surface-border)] text-[var(--color-primary-500)] focus:ring-[var(--color-primary-100)] w-5 h-5">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex items-center justify-between mt-6 pt-4 border-t border-[var(--color-surface-divider)]">
                <p class="text-xs text-[var(--color-text-muted)]">
                    💡 ستظل الإشعارات الحساسة (تأكيد طلباتك) تُرسل بريدياً دائماً.
                </p>
                <x-ui.button type="submit" variant="primary">حفظ التفضيلات</x-ui.button>
            </div>
        </form>
    </x-ui.card>

</x-layouts.agent>
