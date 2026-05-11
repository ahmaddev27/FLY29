@php
    $isEdit = $package->exists;
    $title  = $isEdit ? 'تعديل باكج: ' . $package->name : 'إضافة باكج جديد';
@endphp

<x-layouts.admin
    :title="$title"
    :pageTitle="$title"
    :breadcrumbs="[
        ['label' => 'لوحة الإدارة', 'href' => route('admin.dashboard')],
        ['label' => 'الباكجات', 'href' => route('admin.packages')],
        ['label' => $isEdit ? 'تعديل' : 'إضافة'],
    ]"
>
    <form method="POST"
          action="{{ $isEdit ? route('admin.packages.update', $package) : route('admin.packages.store') }}"
          enctype="multipart/form-data"
          class="max-w-3xl mx-auto"
    >
        @csrf
        @if($isEdit) @method('PUT') @endif

        <x-ui.card title="بيانات الباكج">
            <div class="grid md:grid-cols-2 gap-4">
                <x-forms.form-group label="اسم الباكج" for="name" required>
                    <x-ui.input id="name" name="name" :value="old('name', $package->name)" required />
                </x-forms.form-group>

                <x-forms.form-group label="الوجهة" for="destination" required>
                    <x-ui.input id="destination" name="destination" :value="old('destination', $package->destination)" placeholder="مثلاً: Thailand" required />
                </x-forms.form-group>

                <x-forms.form-group label="عدد النقاط المطلوبة" for="points_required" required>
                    <x-ui.input type="number" id="points_required" name="points_required" :value="old('points_required', $package->points_required)" min="1" required />
                </x-forms.form-group>

                <x-forms.form-group label="مدة الباكج (أيام)" for="duration_days">
                    <x-ui.input type="number" id="duration_days" name="duration_days" :value="old('duration_days', $package->duration_days)" min="1" placeholder="مثلاً: 7" />
                </x-forms.form-group>

                <x-forms.form-group label="ترتيب العرض" for="display_order" hint="الأرقام الأصغر تظهر أولاً">
                    <x-ui.input type="number" id="display_order" name="display_order" :value="old('display_order', $package->display_order ?? 0)" min="0" />
                </x-forms.form-group>

                <x-forms.form-group label="تاريخ الانتهاء (اختياري)" for="valid_until" hint="اتركه فارغاً إذا الباكج دائم">
                    <x-ui.input type="date" id="valid_until" name="valid_until" :value="old('valid_until', $package->valid_until?->format('Y-m-d'))" />
                </x-forms.form-group>
            </div>

            <x-forms.form-group label="الوصف" for="description" class="mt-4">
                <x-ui.textarea id="description" name="description" rows="3" placeholder="وصف موجز للباكج (الوجهة، المعالم، ما يشمله...)">{{ old('description', $package->description) }}</x-ui.textarea>
            </x-forms.form-group>

            <x-forms.form-group label="صورة الباكج" for="image" hint="JPG/PNG/WebP — حد أقصى 2 ميجا">
                @if($package->image_url)
                    <div class="mb-3 flex items-center gap-3">
                        <img src="{{ asset($package->image_url) }}" alt="" class="w-24 h-24 rounded object-cover border border-[var(--color-surface-border)]">
                        <span class="text-xs text-[var(--color-text-secondary)]">الصورة الحالية. ارفع صورة جديدة لاستبدالها.</span>
                    </div>
                @endif
                <input type="file" id="image" name="image" accept="image/*"
                       class="block w-full text-sm text-[var(--color-text-secondary)]
                              file:me-3 file:py-2 file:px-4 file:rounded file:border-0
                              file:bg-[var(--color-primary-500)] file:text-white
                              hover:file:bg-[var(--color-primary-600)]">
            </x-forms.form-group>

            <div class="flex items-center gap-3 mt-4">
                <input type="checkbox" id="is_active" name="is_active" value="1"
                       {{ old('is_active', $package->is_active ?? true) ? 'checked' : '' }}
                       class="rounded border-[var(--color-surface-border)] text-[var(--color-primary-500)] focus:ring-[var(--color-primary-100)] w-5 h-5">
                <label for="is_active" class="text-sm">
                    <strong>نشط</strong> — يظهر للوكلاء ويمكن استبداله
                </label>
            </div>
        </x-ui.card>

        <div class="flex justify-end gap-2 mt-4">
            <x-ui.button variant="secondary" href="{{ route('admin.packages') }}">إلغاء</x-ui.button>
            <x-ui.button type="submit" variant="cta">
                {{ $isEdit ? 'حفظ التغييرات' : 'إنشاء الباكج' }}
            </x-ui.button>
        </div>
    </form>
</x-layouts.admin>
