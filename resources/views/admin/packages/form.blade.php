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
    >
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="grid lg:grid-cols-3 gap-6">

            {{-- Main column: data --}}
            <div class="lg:col-span-2 space-y-6">

                <x-ui.card title="بيانات الباكج">
                    <div class="grid sm:grid-cols-2 gap-4">
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
                        <x-ui.textarea id="description" name="description" rows="4" placeholder="وصف موجز للباكج (الوجهة، المعالم، ما يشمله...)">{{ old('description', $package->description) }}</x-ui.textarea>
                    </x-forms.form-group>
                </x-ui.card>

                <x-ui.card title="الإعدادات">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" id="is_active" name="is_active" value="1"
                               {{ old('is_active', $package->is_active ?? true) ? 'checked' : '' }}
                               class="mt-0.5 rounded border-[var(--color-surface-border)] text-[var(--color-primary-500)] focus:ring-[var(--color-primary-100)] w-5 h-5">
                        <span>
                            <strong class="block text-sm">نشط</strong>
                            <span class="text-xs text-[var(--color-text-secondary)]">يظهر للوكلاء ويمكن استبداله بنقاطهم</span>
                        </span>
                    </label>
                </x-ui.card>
            </div>

            {{-- Side column: image upload --}}
            <div class="lg:col-span-1">
                <x-ui.card title="صورة الباكج" subtitle="JPG/PNG/WebP — حد أقصى 2 ميجا">
                    <div
                        x-data="{
                            preview: @js($package->image_url ? asset($package->image_url) : null),
                            pickFile() { this.$refs.input.click(); },
                            onPick(event) {
                                const file = event.target.files?.[0];
                                if (!file) return;
                                if (file.size > 2 * 1024 * 1024) {
                                    window.toast({ variant: 'danger', message: 'حجم الصورة الأقصى 2 ميجابايت.' });
                                    event.target.value = '';
                                    return;
                                }
                                const reader = new FileReader();
                                reader.onload = (e) => { this.preview = e.target.result; };
                                reader.readAsDataURL(file);
                            },
                            clearPreview() {
                                this.preview = null;
                                this.$refs.input.value = '';
                            },
                        }"
                    >
                        {{-- Drop zone / preview --}}
                        <div
                            class="relative rounded-[var(--radius-md)] border-2 border-dashed border-[var(--color-surface-border)] hover:border-[var(--color-primary-500)] transition-base overflow-hidden bg-[var(--color-surface-tertiary)]"
                            :class="preview ? 'border-solid' : ''"
                        >
                            <template x-if="preview">
                                <div class="relative group">
                                    <img :src="preview" alt="معاينة" class="w-full h-56 object-cover">
                                    <button
                                        type="button"
                                        x-on:click="clearPreview()"
                                        class="absolute top-2 end-2 w-8 h-8 rounded-full bg-black/60 hover:bg-black/80 text-white flex items-center justify-center transition-base"
                                        aria-label="إزالة الصورة"
                                    >
                                        <x-ui.icon name="x" size="sm" />
                                    </button>
                                </div>
                            </template>

                            <template x-if="!preview">
                                <button
                                    type="button"
                                    x-on:click="pickFile()"
                                    class="w-full h-56 flex flex-col items-center justify-center gap-2 text-[var(--color-text-muted)] hover:text-[var(--color-primary-500)] transition-base"
                                >
                                    <x-ui.icon name="image" size="lg" class="opacity-50" />
                                    <span class="text-sm font-medium">اضغط لاختيار صورة</span>
                                    <span class="text-xs">أو اسحب الصورة هنا</span>
                                </button>
                            </template>
                        </div>

                        {{-- Hidden file input --}}
                        <input
                            type="file"
                            x-ref="input"
                            id="image"
                            name="image"
                            accept="image/jpeg,image/png,image/webp"
                            class="hidden"
                            x-on:change="onPick($event)"
                        >

                        {{-- Change button when preview exists --}}
                        <div class="mt-3" x-show="preview" x-cloak>
                            <x-ui.button
                                type="button"
                                variant="secondary"
                                size="sm"
                                :full="true"
                                :auto-loading="false"
                                x-on:click="pickFile()"
                            >
                                <x-ui.icon name="upload" size="sm" /> تغيير الصورة
                            </x-ui.button>
                        </div>
                    </div>
                </x-ui.card>
            </div>
        </div>

        {{-- Footer actions --}}
        <div class="flex justify-end gap-2 mt-6">
            <x-ui.button variant="secondary" href="{{ route('admin.packages') }}">إلغاء</x-ui.button>
            <x-ui.button type="submit" variant="cta">
                {{ $isEdit ? 'حفظ التغييرات' : 'إنشاء الباكج' }}
            </x-ui.button>
        </div>
    </form>
</x-layouts.admin>
