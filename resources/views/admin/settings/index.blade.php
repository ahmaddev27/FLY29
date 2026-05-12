@php
    /**
     * Per-setting UI metadata: human label, hint, and (optionally)
     * an enum of allowed string values.
     */
    $meta = [
        'calculation_method' => [
            'label' => 'طريقة حساب النقاط',
            'hint'  => 'package_based = نقطة لكل باكج / amount_based = نقطة لكل مبلغ',
            'enum'  => ['package_based' => 'حسب الباكج', 'amount_based' => 'حسب المبلغ'],
        ],
        'point_value_usd' => [
            'label' => 'قيمة النقطة الواحدة (USD)',
            'hint'  => 'كم دولار يساوي كل نقطة عند التحويل الكاش',
        ],
        'min_redemption_points' => [
            'label' => 'الحد الأدنى لاستبدال نقاط الكاش',
            'hint'  => 'الوكيل لا يستطيع تحويل أقل من هذا العدد',
        ],
        'dual_approval_threshold' => [
            'label' => 'حد التعديل اليدوي الذي يحتاج موافقة مزدوجة',
            'hint'  => 'أي تعديل يدوي أكبر من هذا الرقم يحتاج موافقة سوبر أدمن',
        ],
        'tier_evaluation_mode' => [
            'label' => 'وضع تقييم التصنيف',
            'enum'  => ['calendar_month' => 'شهر ميلادي', 'rolling_30_days' => 'آخر 30 يوم متحرّك'],
        ],
        'tier_warning_days' => [
            'label' => 'أيام التنبيه قبل تخفيض التصنيف',
        ],
        'webhook_signature_verification' => [
            'label' => 'التحقق من توقيع HMAC على Webhooks',
            'hint'  => 'إيقافه يسمح بطلبات بدون توقيع — استخدمه فقط للاختبار',
        ],
        'webhook_rate_limit_per_min' => [
            'label' => 'الحد الأقصى للـ Webhooks في الدقيقة',
        ],
        'default_tier_for_new_agent' => [
            'label' => 'التصنيف الافتراضي للوكيل الجديد',
            'enum'  => ['bronze' => 'برونزي', 'silver' => 'فضي', 'gold' => 'ذهبي', 'diamond' => 'ماسي'],
        ],
        'login_max_attempts' => [
            'label' => 'الحد الأقصى لمحاولات الدخول الفاشلة',
        ],
        'login_lockout_minutes' => [
            'label' => 'مدة قفل الحساب بعد تخطي المحاولات (دقيقة)',
        ],
        'session_lifetime_minutes' => [
            'label' => 'مدة الجلسة قبل انتهائها (دقيقة)',
        ],
        'two_factor_required_for_admin' => [
            'label' => 'إجبار 2FA على الأدمن',
            'hint'  => 'يمنع تسجيل دخول الأدمن بدون مصادقة ثنائية',
        ],
    ];
@endphp

<x-layouts.admin
    title="الإعدادات"
    pageTitle="إعدادات النظام"
    :breadcrumbs="[
        ['label' => 'لوحة الإدارة', 'href' => route('admin.dashboard')],
        ['label' => 'الإعدادات'],
    ]"
>
    <form method="POST" action="{{ route('admin.settings.update') }}" x-data="{ dirty: false }" @input="dirty = true" @change="dirty = true">
        @csrf
        @method('PATCH')

        {{-- Sticky save bar (shows when dirty) --}}
        <div
            x-show="dirty"
            x-cloak
            x-transition.opacity
            class="sticky top-0 z-30 -mx-3 sm:-mx-6 -mt-4 sm:-mt-6 mb-6 px-3 sm:px-6 py-3 bg-amber-50 border-b border-amber-200 flex items-center justify-between gap-3"
        >
            <div class="flex items-center gap-2 text-amber-800 text-sm">
                <x-ui.icon name="alert-triangle" size="sm" />
                <span>لديك تغييرات غير محفوظة.</span>
            </div>
            <div class="flex gap-2">
                <x-ui.button type="button" variant="secondary" size="sm" :auto-loading="false" x-on:click="window.location.reload()">
                    تجاهل
                </x-ui.button>
                <x-ui.button type="submit" variant="cta" size="sm">
                    حفظ التغييرات
                </x-ui.button>
            </div>
        </div>

        <div class="space-y-6">
            @foreach($grouped as $category => $settings)
                <x-ui.card :title="$categoryLabels[$category] ?? ucfirst($category)">
                    <div class="divide-y divide-slate-100">
                        @foreach($settings as $setting)
                            @php
                                $info  = $meta[$setting->key] ?? ['label' => $setting->key];
                                $value = $setting->typedValue();
                                $inputName = "settings[{$setting->key}]";
                                $inputId = 'set_' . str_replace('.', '_', $setting->key);
                            @endphp

                            <div class="grid sm:grid-cols-3 gap-4 py-4 first:pt-0 last:pb-0">
                                {{-- Label + description --}}
                                <div class="sm:col-span-1">
                                    <label for="{{ $inputId }}" class="block font-medium text-sm text-slate-900">
                                        {{ $info['label'] }}
                                    </label>
                                    <p class="text-xs font-latin text-slate-400 mt-0.5">{{ $setting->key }}</p>
                                    @if(! empty($info['hint']) || $setting->description)
                                        <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                                            {{ $info['hint'] ?? $setting->description }}
                                        </p>
                                    @endif
                                </div>

                                {{-- Input (type-aware) --}}
                                <div class="sm:col-span-2">
                                    @if(isset($info['enum']))
                                        <x-ui.select
                                            :id="$inputId"
                                            :name="$inputName"
                                            :options="$info['enum']"
                                            :selected="$value"
                                        />
                                    @elseif($setting->value_type === 'bool')
                                        <label class="inline-flex items-center gap-3 cursor-pointer">
                                            <input type="hidden" name="{{ $inputName }}" value="0">
                                            <input
                                                type="checkbox"
                                                id="{{ $inputId }}"
                                                name="{{ $inputName }}"
                                                value="1"
                                                @checked($value)
                                                class="rounded border-slate-300 text-[var(--color-primary-500)] focus:ring-[var(--color-primary-100)] w-5 h-5"
                                            >
                                            <span class="text-sm text-slate-600" x-data="{ on: {{ $value ? 'true' : 'false' }} }" x-init="$watch('on', v => v); document.getElementById('{{ $inputId }}').addEventListener('change', e => on = e.target.checked)">
                                                <span x-show="on" x-cloak class="text-emerald-700 font-medium">مفعّل</span>
                                                <span x-show="!on" x-cloak class="text-slate-500">معطّل</span>
                                            </span>
                                        </label>
                                    @elseif($setting->value_type === 'int')
                                        <x-ui.input type="number" :id="$inputId" :name="$inputName" :value="$value" step="1" />
                                    @elseif($setting->value_type === 'float')
                                        <x-ui.input type="number" :id="$inputId" :name="$inputName" :value="$value" step="0.01" />
                                    @elseif($setting->value_type === 'json')
                                        <x-ui.textarea :id="$inputId" :name="$inputName" rows="4" dir="ltr" class="font-latin text-xs">{{ json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</x-ui.textarea>
                                    @else
                                        <x-ui.input :id="$inputId" :name="$inputName" :value="$value" />
                                    @endif

                                    @error("settings.{$setting->key}")
                                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-ui.card>
            @endforeach
        </div>

        <div class="flex justify-end gap-2 mt-6">
            <x-ui.button type="submit" variant="cta">
                <x-ui.icon name="check" size="sm" /> حفظ الإعدادات
            </x-ui.button>
        </div>
    </form>
</x-layouts.admin>
