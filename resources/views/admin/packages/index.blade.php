<x-layouts.admin
    title="الباكجات المجانية"
    pageTitle="الباكجات المجانية"
    :breadcrumbs="[
        ['label' => 'لوحة الإدارة', 'href' => route('admin.dashboard')],
        ['label' => 'الباكجات'],
    ]"
>

    <x-ui.data-table
        :paginator="$packages"
        search-placeholder="اسم الباكج أو الوجهة..."
        :filters="[
            [
                'name'    => 'status',
                'label'   => 'الحالة',
                'options' => ['active' => 'نشط', 'inactive' => 'معطّل'],
            ],
        ]"
        :is-empty="$packages->isEmpty()"
    >
        <x-slot:toolbar>
            <x-ui.button variant="cta" size="sm" href="{{ route('admin.packages.create') }}">
                <x-ui.icon name="plus" size="sm" /> باكج جديد
            </x-ui.button>
        </x-slot:toolbar>

        <x-slot:empty>
            <x-ui.empty-state
                title="لا توجد باكجات"
                description="ابدأ بإضافة باكج سياحي يمكن للوكلاء استبداله بنقاطهم."
            >
                <x-slot:actions>
                    <x-ui.button variant="cta" href="{{ route('admin.packages.create') }}">
                        إضافة أول باكج
                    </x-ui.button>
                </x-slot:actions>
            </x-ui.empty-state>
        </x-slot:empty>

        <table class="w-full text-right">
            <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                <tr>
                    <th class="px-4 py-3 w-16"></th>
                    <th class="px-4 py-3">الاسم</th>
                    <th class="px-4 py-3">الوجهة</th>
                    <th class="px-4 py-3">النقاط</th>
                    <th class="px-4 py-3">المدة</th>
                    <th class="px-4 py-3">الحالة</th>
                    <th class="px-4 py-3">الترتيب</th>
                    <th class="px-4 py-3 text-center">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($packages as $pkg)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-4 py-3">
                            @if($pkg->image_url)
                                <img src="{{ asset($pkg->image_url) }}" alt="" class="w-12 h-12 rounded-lg object-cover ring-1 ring-slate-200">
                            @else
                                <div class="w-12 h-12 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 ring-1 ring-slate-200">
                                    <x-ui.icon name="image" size="md" />
                                </div>
                            @endif
                        </td>

                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-900">{{ $pkg->name }}</div>
                            @if($pkg->valid_until)
                                <div class="text-xs text-slate-500">ينتهي: {{ $pkg->valid_until->format('Y-m-d') }}</div>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-slate-700">{{ $pkg->destination }}</td>

                        <td class="px-4 py-3">
                            <span dir="ltr" class="font-semibold text-slate-900">{{ number_format($pkg->points_required) }}</span>
                        </td>

                        <td class="px-4 py-3 text-slate-700">
                            @if($pkg->duration_days)
                                <span dir="ltr">{{ $pkg->duration_days }}</span> يوم
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>

                        <td class="px-4 py-3">
                            @if($pkg->is_active)
                                <x-ui.badge variant="success" :dot="true">نشط</x-ui.badge>
                            @else
                                <x-ui.badge variant="neutral" :dot="true">معطّل</x-ui.badge>
                            @endif
                        </td>

                        <td class="px-4 py-3">
                            <span class="text-sm text-slate-500" dir="ltr">{{ $pkg->display_order }}</span>
                        </td>

                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-1">
                                <x-ui.icon-button icon="edit" variant="primary" tooltip="تعديل" href="{{ route('admin.packages.edit', $pkg) }}" />

                                <form method="POST" action="{{ route('admin.packages.toggle', $pkg) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <x-ui.icon-button
                                        type="submit"
                                        :icon="$pkg->is_active ? 'eye-off' : 'eye'"
                                        :variant="$pkg->is_active ? 'warning' : 'success'"
                                        :tooltip="$pkg->is_active ? 'تعطيل' : 'تفعيل'"
                                    />
                                </form>

                                <x-ui.icon-button
                                    type="button"
                                    icon="trash"
                                    variant="danger"
                                    tooltip="حذف"
                                    :auto-loading="false"
                                    x-on:click="$dispatch('open-modal', 'delete-package-{{ $pkg->id }}')"
                                />
                            </div>

                            <x-ui.confirm-dialog
                                :name="'delete-package-' . $pkg->id"
                                title="حذف الباكج؟"
                                :message="'سيتم حذف الباكج «' . $pkg->name . '» نهائياً. هذا الإجراء لا يمكن التراجع عنه.'"
                                :action="route('admin.packages.destroy', $pkg)"
                                method="DELETE"
                                confirm-label="نعم، احذف الباكج"
                                cancel-label="إلغاء"
                                variant="danger"
                                icon="trash"
                            />
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-ui.data-table>

</x-layouts.admin>
