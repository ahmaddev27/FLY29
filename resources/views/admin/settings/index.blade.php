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

        // Mail / SMTP
        'mail_enabled' => [
            'label' => 'تفعيل إعدادات SMTP من قاعدة البيانات',
            'hint'  => 'إذا متوقّف، يستخدم النظام إعدادات .env الافتراضية',
        ],
        'mail_from_address' => [
            'label' => 'بريد المرسل',
            'hint'  => 'يظهر للمستلم في حقل "From"',
        ],
        'mail_from_name' => [
            'label' => 'اسم المرسل الظاهر',
        ],
        'smtp_host' => [
            'label' => 'SMTP Host',
            'hint'  => 'مثال: smtp.mailgun.org, smtp.gmail.com',
        ],
        'smtp_port' => [
            'label' => 'SMTP Port',
            'hint'  => '587 لـ STARTTLS، 465 لـ SSL، 25 بدون تشفير',
        ],
        'smtp_username' => [
            'label' => 'SMTP Username',
        ],
        'smtp_password' => [
            'label' => 'SMTP Password',
            'hint'  => 'يُخزَّن مشفّراً (AES). اتركه فارغاً لعدم التغيير.',
        ],
        'smtp_encryption' => [
            'label' => 'التشفير',
            'enum'  => ['tls' => 'TLS (STARTTLS)', 'ssl' => 'SSL', 'none' => 'بدون تشفير'],
        ],
    ];

    // Per-category icon (heroicon paths)
    $categoryIcons = [
        'points'     => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'redemption' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
        'tier'       => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
        'webhook'    => 'M13 10V3L4 14h7v7l9-11h-7z',
        'agents'     => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
        'auth'       => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',
        'security'   => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
        'mail'       => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        'general'    => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
    ];

    // Find first category as default for tab
    $firstCat = $grouped->keys()->first();
@endphp

<x-layouts.admin
    title="الإعدادات"
    pageTitle="إعدادات النظام"
    :breadcrumbs="[
        ['label' => 'لوحة الإدارة', 'href' => route('admin.dashboard')],
        ['label' => 'الإعدادات'],
    ]"
>
    <form method="POST" action="{{ route('admin.settings.update') }}"
          x-data="{
              activeTab: @js($firstCat),
              dirty: false,
          }"
          @input="dirty = true"
          @change="dirty = true">
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
                <span>لديك تغييرات غير محفوظة في تبويب <strong x-text="$store ? '' : ''"></strong>.</span>
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

        <div class="grid lg:grid-cols-4 gap-6">

            {{-- Vertical tab nav (left in RTL = "start") --}}
            <aside class="lg:col-span-1">
                <nav class="bg-white rounded-xl shadow-sm p-2 lg:sticky lg:top-4">
                    @foreach($grouped as $category => $settings)
                        @php $iconPath = $categoryIcons[$category] ?? $categoryIcons['general']; @endphp
                        <button
                            type="button"
                            x-on:click="activeTab = @js($category)"
                            :class="activeTab === @js($category)
                                ? 'bg-[var(--color-primary-50)] text-[var(--color-primary-700)] font-semibold shadow-inner'
                                : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
                            class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-start text-sm transition-colors"
                        >
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}"/>
                            </svg>
                            <span class="flex-1 truncate">{{ $categoryLabels[$category] ?? ucfirst($category) }}</span>
                            <span class="text-xs text-slate-400 font-latin">{{ $settings->count() }}</span>
                        </button>
                    @endforeach
                </nav>
            </aside>

            {{-- Tab panels --}}
            <div class="lg:col-span-3 space-y-6">
                @foreach($grouped as $category => $settings)
                    <div
                        x-show="activeTab === @js($category)"
                        x-cloak
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                    >
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
                                                <label class="inline-flex items-center gap-3 cursor-pointer select-none" x-data="{ on: {{ $value ? 'true' : 'false' }} }">
                                                    <input type="hidden" name="{{ $inputName }}" value="0">
                                                    <input
                                                        type="checkbox"
                                                        id="{{ $inputId }}"
                                                        name="{{ $inputName }}"
                                                        value="1"
                                                        x-model="on"
                                                        class="sr-only peer"
                                                    >
                                                    {{-- Toggle pill --}}
                                                    <span
                                                        class="relative w-11 h-6 rounded-full transition-colors"
                                                        :class="on ? 'bg-[var(--color-primary-500)]' : 'bg-slate-300'"
                                                    >
                                                        <span
                                                            class="absolute top-0.5 w-5 h-5 rounded-full bg-white shadow transition-all"
                                                            :class="on ? 'start-5' : 'start-0.5'"
                                                        ></span>
                                                    </span>
                                                    <span class="text-sm">
                                                        <span x-show="on" class="text-emerald-700 font-medium">مفعّل</span>
                                                        <span x-show="!on" class="text-slate-500">معطّل</span>
                                                    </span>
                                                </label>
                                            @elseif($setting->value_type === 'int')
                                                <x-ui.input type="number" :id="$inputId" :name="$inputName" :value="$value" step="1" />
                                            @elseif($setting->value_type === 'float')
                                                <x-ui.input type="number" :id="$inputId" :name="$inputName" :value="$value" step="0.01" />
                                            @elseif($setting->value_type === 'json')
                                                <x-ui.textarea :id="$inputId" :name="$inputName" rows="4" dir="ltr" class="font-latin text-xs">{{ json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</x-ui.textarea>
                                            @elseif($setting->value_type === 'password')
                                                {{-- Masked password: never re-render the value. Sentinel says
                                                     'don't change' unless the admin types something new. --}}
                                                <x-ui.input
                                                    type="password"
                                                    :id="$inputId"
                                                    :name="$inputName"
                                                    :value="$value ? '__UNCHANGED__' : ''"
                                                    autocomplete="new-password"
                                                    dir="ltr"
                                                    onfocus="if(this.value==='__UNCHANGED__') this.value='';"
                                                    onblur="if(this.value==='') this.value='__UNCHANGED__';"
                                                />
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

                    </div>
                @endforeach

                <div class="flex justify-end gap-2">
                    <x-ui.button type="submit" variant="cta">
                        <x-ui.icon name="check" size="sm" /> حفظ الإعدادات
                    </x-ui.button>
                </div>
            </div>
        </div>

        {{-- Floating test-email trigger (only on the mail tab) --}}
        <div
            x-show="activeTab === 'mail'"
            x-cloak
            x-transition.opacity
            class="fixed bottom-6 end-6 z-40"
        >
            <x-ui.button
                type="button"
                variant="warning"
                :auto-loading="false"
                x-on:click="$dispatch('open-modal', 'test-email-modal')"
            >
                <x-ui.icon name="mail" size="sm" /> إرسال إيميل اختبار
            </x-ui.button>
        </div>
    </form>

    <x-ui.modal name="test-email-modal" title="إرسال رسالة اختبار SMTP" size="sm">
        <form method="POST" action="{{ route('admin.settings.test-email') }}">
            @csrf
            <p class="text-sm text-slate-600 mb-3">
                ستُستخدم الإعدادات المحفوظة حالياً. احفظ التغييرات أولاً إذا أجريت تعديلاً.
            </p>
            <x-forms.form-group label="عنوان البريد للاستلام" for="test_email" required>
                <x-ui.input type="email" id="test_email" name="test_email" dir="ltr" required placeholder="you@example.com" />
            </x-forms.form-group>

            <x-slot:footer>
                <x-ui.button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'test-email-modal')">إلغاء</x-ui.button>
                <x-ui.button type="submit" variant="cta">إرسال الاختبار</x-ui.button>
            </x-slot:footer>
        </form>
    </x-ui.modal>
</x-layouts.admin>
