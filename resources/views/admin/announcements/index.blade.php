<x-layouts.admin
    title="الإعلانات"
    pageTitle="الإعلانات للوكلاء"
    :breadcrumbs="[
        ['label' => 'لوحة الإدارة', 'href' => route('admin.dashboard')],
        ['label' => 'الإعلانات'],
    ]"
>
    <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-slate-600">إعلانات تظهر كبانر على لوحة الوكيل، مع خيار إرسال إيميل.</p>
        <x-ui.button variant="cta" size="sm" href="{{ route('admin.announcements.create') }}">
            <x-ui.icon name="plus" size="sm" /> إعلان جديد
        </x-ui.button>
    </div>

    @if($announcements->isEmpty())
        <x-ui.empty-state
            title="لا توجد إعلانات بعد"
            description="ابدأ بإنشاء إعلان للترحيب بالوكلاء أو الإعلان عن باكج/ميزة جديدة."
        >
            <x-slot:actions>
                <x-ui.button variant="cta" href="{{ route('admin.announcements.create') }}">
                    إنشاء أول إعلان
                </x-ui.button>
            </x-slot:actions>
        </x-ui.empty-state>
    @else
        <div class="grid gap-4">
            @foreach($announcements as $a)
                @php
                    $variantClass = match($a->variant) {
                        'success' => 'border-emerald-300 bg-emerald-50',
                        'warning' => 'border-amber-300 bg-amber-50',
                        'danger'  => 'border-rose-300 bg-rose-50',
                        default   => 'border-sky-300 bg-sky-50',
                    };
                    $iconColor = match($a->variant) {
                        'success' => 'text-emerald-600',
                        'warning' => 'text-amber-600',
                        'danger'  => 'text-rose-600',
                        default   => 'text-sky-600',
                    };
                    $isExpired = $a->expires_at && $a->expires_at->isPast();
                @endphp

                <div @class([
                    'bg-white rounded-xl shadow-sm border-s-4 overflow-hidden',
                    $variantClass,
                    'opacity-60' => ! $a->is_active || $isExpired,
                ])>
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="font-bold text-slate-900">{{ $a->title }}</h3>
                                    @if(! $a->is_active)
                                        <x-ui.badge variant="neutral">معطّل</x-ui.badge>
                                    @elseif($isExpired)
                                        <x-ui.badge variant="neutral">منتهي</x-ui.badge>
                                    @else
                                        <x-ui.badge variant="success" :dot="true">نشط</x-ui.badge>
                                    @endif
                                </div>
                                <p class="text-sm text-slate-600 whitespace-pre-wrap mb-3">{{ Str::limit($a->body, 300) }}</p>

                                <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500">
                                    <span class="flex items-center gap-1">
                                        <x-ui.icon name="users" size="sm" />
                                        @if(empty($a->tier_filter) && empty($a->country_filter))
                                            كل الوكلاء
                                        @else
                                            @if(! empty($a->tier_filter))
                                                <span>تصنيفات: {{ implode(', ', $a->tier_filter) }}</span>
                                            @endif
                                            @if(! empty($a->country_filter))
                                                <span>· دول: {{ implode(', ', $a->country_filter) }}</span>
                                            @endif
                                        @endif
                                    </span>

                                    @if($a->send_email)
                                        <span class="text-emerald-700">✉ أُرسل إيميل</span>
                                    @endif

                                    <span>· قراءات: <strong class="font-latin">{{ $a->reads_count }}</strong></span>

                                    @if($a->expires_at)
                                        <span>· ينتهي: {{ $a->expires_at->format('Y-m-d H:i') }}</span>
                                    @endif

                                    <span class="text-slate-400">· {{ $a->created_at->diffForHumans() }} · بواسطة {{ $a->creator?->full_name }}</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-1 flex-shrink-0">
                                <form method="POST" action="{{ route('admin.announcements.toggle', $a) }}">
                                    @csrf @method('PATCH')
                                    <x-ui.icon-button
                                        type="submit"
                                        :icon="$a->is_active ? 'eye-off' : 'eye'"
                                        :variant="$a->is_active ? 'warning' : 'success'"
                                        :tooltip="$a->is_active ? 'إيقاف' : 'تفعيل'"
                                    />
                                </form>

                                <x-ui.icon-button
                                    type="button"
                                    icon="trash"
                                    variant="danger"
                                    tooltip="حذف"
                                    :auto-loading="false"
                                    x-on:click="$dispatch('open-modal', 'delete-ann-{{ $a->id }}')"
                                />

                                <x-ui.confirm-dialog
                                    :name="'delete-ann-' . $a->id"
                                    title="حذف الإعلان؟"
                                    :message="'سيتم حذف «' . $a->title . '» نهائياً. لا يمكن التراجع.'"
                                    :action="route('admin.announcements.destroy', $a)"
                                    method="DELETE"
                                    variant="danger"
                                    icon="trash"
                                    confirm-label="نعم، احذف"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $announcements->links() }}
        </div>
    @endif
</x-layouts.admin>
