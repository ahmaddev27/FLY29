<x-layouts.admin
    title="الباكجات المجانية"
    pageTitle="الباكجات المجانية"
    :breadcrumbs="[
        ['label' => 'لوحة الإدارة', 'href' => route('admin.dashboard')],
        ['label' => 'الباكجات'],
    ]"
>

    <x-ui.card>
        <x-slot:actions>
            <x-ui.button variant="cta" size="sm" href="{{ route('admin.packages.create') }}">
                <x-ui.icon name="plus" size="sm" /> إضافة باكج جديد
            </x-ui.button>
        </x-slot:actions>

        @if($packages->isEmpty())
            <x-ui.empty-state
                title="لا توجد باكجات بعد"
                description="ابدأ بإضافة باكجات سياحية يمكن للوكلاء استبدالها بنقاطهم."
            >
                <x-slot:actions>
                    <x-ui.button variant="cta" href="{{ route('admin.packages.create') }}">
                        إضافة أول باكج
                    </x-ui.button>
                </x-slot:actions>
            </x-ui.empty-state>
        @else
            <x-ui.table :headers="['', 'الاسم', 'الوجهة', 'النقاط', 'المدة', 'الحالة', 'الترتيب', 'إجراءات']">
                @foreach($packages as $pkg)
                    <x-ui.table-row>
                        <x-ui.table-cell>
                            @if($pkg->image_url)
                                <img src="{{ asset($pkg->image_url) }}" alt="" class="w-12 h-12 rounded object-cover">
                            @else
                                <div class="w-12 h-12 rounded bg-[var(--color-surface-secondary)] flex items-center justify-center text-[var(--color-text-muted)]">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            @endif
                        </x-ui.table-cell>

                        <x-ui.table-cell>
                            <div class="font-medium">{{ $pkg->name }}</div>
                            @if($pkg->valid_until)
                                <div class="text-xs text-[var(--color-text-muted)]">ينتهي: {{ $pkg->valid_until->format('Y-m-d') }}</div>
                            @endif
                        </x-ui.table-cell>

                        <x-ui.table-cell>{{ $pkg->destination }}</x-ui.table-cell>

                        <x-ui.table-cell>
                            <span dir="ltr" class="font-semibold">{{ number_format($pkg->points_required) }}</span>
                        </x-ui.table-cell>

                        <x-ui.table-cell>
                            @if($pkg->duration_days)
                                <span dir="ltr">{{ $pkg->duration_days }}</span> يوم
                            @else
                                —
                            @endif
                        </x-ui.table-cell>

                        <x-ui.table-cell>
                            @if($pkg->is_active)
                                <x-ui.badge variant="success" :dot="true">نشط</x-ui.badge>
                            @else
                                <x-ui.badge variant="neutral" :dot="true">معطّل</x-ui.badge>
                            @endif
                        </x-ui.table-cell>

                        <x-ui.table-cell>
                            <span class="text-sm text-[var(--color-text-muted)]" dir="ltr">{{ $pkg->display_order }}</span>
                        </x-ui.table-cell>

                        <x-ui.table-cell>
                            <div class="flex items-center gap-1">
                                <x-ui.icon-button
                                    icon="edit"
                                    variant="primary"
                                    tooltip="تعديل"
                                    href="{{ route('admin.packages.edit', $pkg) }}"
                                />

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

                                <form method="POST" action="{{ route('admin.packages.destroy', $pkg) }}"
                                      onsubmit="return confirm('حذف الباكج «{{ $pkg->name }}»؟ لا يمكن التراجع.');"
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <x-ui.icon-button
                                        type="submit"
                                        icon="trash"
                                        variant="danger"
                                        tooltip="حذف"
                                        :auto-loading="false"
                                    />
                                </form>
                            </div>
                        </x-ui.table-cell>
                    </x-ui.table-row>
                @endforeach
            </x-ui.table>

            <div class="mt-4">{{ $packages->links() }}</div>
        @endif
    </x-ui.card>

</x-layouts.admin>
