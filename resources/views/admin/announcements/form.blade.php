<x-layouts.admin
    title="إعلان جديد"
    pageTitle="إنشاء إعلان جديد"
    :breadcrumbs="[
        ['label' => 'لوحة الإدارة', 'href' => route('admin.dashboard')],
        ['label' => 'الإعلانات', 'href' => route('admin.announcements')],
        ['label' => 'جديد'],
    ]"
>
    <form method="POST" action="{{ route('admin.announcements.store') }}" x-data="{ variant: 'info' }">
        @csrf

        <div class="grid lg:grid-cols-3 gap-6">

            {{-- Main content --}}
            <div class="lg:col-span-2 space-y-6">
                <x-ui.card title="محتوى الإعلان">
                    <div class="space-y-4">
                        <x-forms.form-group label="العنوان" for="title" required>
                            <x-ui.input id="title" name="title" :value="old('title')" maxlength="200" required placeholder="مثال: ترقية جديدة للنظام يوم الجمعة" />
                        </x-forms.form-group>

                        <x-forms.form-group label="النص" for="body" required hint="حد أقصى 5000 حرف">
                            <x-ui.textarea id="body" name="body" rows="8" required maxlength="5000" placeholder="اشرح التفاصيل هنا...">{{ old('body') }}</x-ui.textarea>
                        </x-forms.form-group>

                        <x-forms.form-group label="نوع الإعلان (لون البانر والإيميل)" for="variant" required>
                            <x-ui.select
                                id="variant"
                                name="variant"
                                :options="['info' => 'معلومة (أزرق)', 'success' => 'نجاح (أخضر)', 'warning' => 'تنبيه (برتقالي)', 'danger' => 'تحذير (أحمر)']"
                                selected="info"
                                x-on:change="variant = $event.target.value"
                            />
                        </x-forms.form-group>

                        <x-forms.form-group label="تاريخ انتهاء (اختياري)" for="expires_at" hint="إذا فاضي، يظل ظاهر حتى توقفه يدوياً">
                            <x-ui.input type="datetime-local" id="expires_at" name="expires_at" :value="old('expires_at')" />
                        </x-forms.form-group>
                    </div>
                </x-ui.card>

                {{-- Live preview --}}
                <x-ui.card title="معاينة البانر">
                    <div
                        class="rounded-lg p-4 border-s-4"
                        :class="{
                            'border-sky-400 bg-sky-50': variant === 'info',
                            'border-emerald-400 bg-emerald-50': variant === 'success',
                            'border-amber-400 bg-amber-50': variant === 'warning',
                            'border-rose-400 bg-rose-50': variant === 'danger',
                        }"
                    >
                        <p class="font-bold text-slate-900" x-text="document.getElementById('title')?.value || 'عنوان الإعلان'"></p>
                        <p class="text-sm text-slate-700 mt-1 whitespace-pre-wrap" x-text="document.getElementById('body')?.value || 'سيظهر النص هنا...'"></p>
                    </div>
                </x-ui.card>
            </div>

            {{-- Audience + delivery --}}
            <div class="lg:col-span-1 space-y-6">
                <x-ui.card title="الجمهور المستهدف" subtitle="اترك الكل فارغاً للوصول لكل الوكلاء النشطين">
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">التصنيفات</label>
                            <div class="space-y-1.5">
                                @foreach(['bronze' => 'برونزي', 'silver' => 'فضي', 'gold' => 'ذهبي', 'diamond' => 'ماسي'] as $key => $label)
                                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                                        <input type="checkbox" name="tier_filter[]" value="{{ $key }}" class="rounded border-slate-300 text-[var(--color-primary-500)]">
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        @if(! empty($countries))
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2 mt-2">الدول</label>
                                <div class="max-h-40 overflow-y-auto border border-slate-200 rounded-lg p-2 space-y-1.5">
                                    @foreach($countries as $code => $name)
                                        <label class="flex items-center gap-2 text-sm cursor-pointer">
                                            <input type="checkbox" name="country_filter[]" value="{{ $code }}" class="rounded border-slate-300 text-[var(--color-primary-500)]">
                                            <span class="font-latin">{{ $name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </x-ui.card>

                <x-ui.card title="الإرسال">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="hidden" name="send_email" value="0">
                        <input type="checkbox" name="send_email" value="1" class="mt-0.5 rounded border-slate-300 text-[var(--color-primary-500)] w-5 h-5">
                        <span>
                            <strong class="block text-sm">إرسال إيميل أيضاً</strong>
                            <span class="text-xs text-slate-500">إيميل لكل وكيل مستهدَف. قد يأخذ ثوانٍ حسب العدد.</span>
                        </span>
                    </label>
                </x-ui.card>
            </div>
        </div>

        <div class="flex justify-end gap-2 mt-6">
            <x-ui.button variant="secondary" href="{{ route('admin.announcements') }}">إلغاء</x-ui.button>
            <x-ui.button type="submit" variant="cta">
                <x-ui.icon name="check" size="sm" /> نشر الإعلان
            </x-ui.button>
        </div>
    </form>
</x-layouts.admin>
