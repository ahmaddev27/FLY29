<x-layouts.admin
    title="إعلان جديد"
    pageTitle="إنشاء إعلان جديد"
    :breadcrumbs="[
        ['label' => 'لوحة الإدارة', 'href' => route('admin.dashboard')],
        ['label' => 'الإعلانات', 'href' => route('admin.announcements')],
        ['label' => 'جديد'],
    ]"
>
    <form
        method="POST"
        action="{{ route('admin.announcements.store') }}"
        x-data="{
            variant: 'info',
            title: '',
            body: '',
        }"
    >
        @csrf

        <div class="grid lg:grid-cols-3 gap-6">

            {{-- Main content --}}
            <div class="lg:col-span-2 space-y-6">
                <x-ui.card title="محتوى الإعلان">
                    <div class="space-y-4">
                        <x-forms.form-group label="العنوان" for="title" required>
                            <x-ui.input
                                id="title"
                                name="title"
                                :value="old('title')"
                                maxlength="200"
                                required
                                placeholder="مثال: ترقية جديدة للنظام يوم الجمعة"
                                x-model="title"
                            />
                        </x-forms.form-group>

                        <x-forms.form-group label="النص" for="body" required hint="حد أقصى 5000 حرف">
                            <x-ui.textarea
                                id="body"
                                name="body"
                                rows="8"
                                required
                                maxlength="5000"
                                placeholder="اشرح التفاصيل هنا..."
                                x-model="body"
                            >{{ old('body') }}</x-ui.textarea>
                        </x-forms.form-group>

                        <x-forms.form-group label="نوع الإعلان (لون البانر والإيميل)" required>
                            {{-- Visual variant picker — 4 colour chips --}}
                            <input type="hidden" name="variant" x-model="variant">
                            <div class="grid grid-cols-4 gap-2">
                                @foreach([
                                    'info'    => ['label' => 'معلومة', 'cls' => 'bg-sky-500'],
                                    'success' => ['label' => 'نجاح',    'cls' => 'bg-emerald-500'],
                                    'warning' => ['label' => 'تنبيه',   'cls' => 'bg-amber-500'],
                                    'danger'  => ['label' => 'تحذير',   'cls' => 'bg-rose-500'],
                                ] as $key => $vMeta)
                                    <button
                                        type="button"
                                        x-on:click="variant = @js($key)"
                                        :class="variant === @js($key) ? 'ring-2 ring-offset-2 ring-slate-400 scale-[1.02]' : 'opacity-70 hover:opacity-100'"
                                        class="flex flex-col items-center gap-1.5 p-2 rounded-lg bg-white border border-slate-200 transition-all"
                                    >
                                        <span class="w-6 h-6 rounded-full {{ $vMeta['cls'] }}"></span>
                                        <span class="text-xs font-medium text-slate-700">{{ $vMeta['label'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </x-forms.form-group>

                        <x-forms.form-group label="تاريخ انتهاء (اختياري)" for="expires_at" hint="إذا فاضي، يظل ظاهر حتى توقفه يدوياً">
                            <x-ui.input type="datetime-local" id="expires_at" name="expires_at" :value="old('expires_at')" />
                        </x-forms.form-group>
                    </div>
                </x-ui.card>

                {{-- Live preview --}}
                <x-ui.card title="معاينة مباشرة" subtitle="يتحدّث أثناء الكتابة">
                    <div
                        class="rounded-lg p-4 border-s-4 transition-colors"
                        :class="{
                            'border-sky-400 bg-sky-50':         variant === 'info',
                            'border-emerald-400 bg-emerald-50': variant === 'success',
                            'border-amber-400 bg-amber-50':     variant === 'warning',
                            'border-rose-400 bg-rose-50':       variant === 'danger',
                        }"
                    >
                        <p
                            class="font-bold transition-colors"
                            :class="{
                                'text-sky-900':     variant === 'info',
                                'text-emerald-900': variant === 'success',
                                'text-amber-900':   variant === 'warning',
                                'text-rose-900':    variant === 'danger',
                            }"
                            x-text="title.trim() || 'عنوان الإعلان'"
                        ></p>
                        <p
                            class="text-sm text-slate-700 mt-1 leading-relaxed whitespace-pre-wrap"
                            x-text="body.trim() || 'سيظهر النص هنا أثناء الكتابة...'"
                        ></p>
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
