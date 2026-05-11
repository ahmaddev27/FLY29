<x-layouts.agent
    title="تحويل النقاط لرصيد نقدي"
    pageTitle="تحويل النقاط لرصيد نقدي"
    :breadcrumbs="[
        ['label' => 'الرئيسية', 'href' => route('agent.dashboard')],
        ['label' => 'التحويلات', 'href' => route('agent.redemptions.index')],
        ['label' => 'تحويل نقدي'],
    ]"
>
    <div class="grid lg:grid-cols-3 gap-6"
         x-data="{
            points: {{ (int) $config['min_redemption'] }},
            available: {{ $config['available'] }},
            min: {{ $config['min_redemption'] }},
            rate: {{ $config['point_value'] }},
            get usd() { return (this.points * this.rate).toFixed(2); },
            get isValid() { return this.points >= this.min && this.points <= this.available; },
         }">

        {{-- Form --}}
        <div class="lg:col-span-2 space-y-4">
            <x-ui.card title="اختر عدد النقاط للتحويل">
                <form method="POST" action="{{ route('agent.redemptions.cash.store') }}" id="cash-form">
                    @csrf

                    {{-- Slider --}}
                    <div class="mb-6">
                        <div class="flex items-center justify-between text-sm text-[var(--color-text-secondary)] mb-2">
                            <span>الحد الأدنى: <span dir="ltr">{{ number_format($config['min_redemption']) }}</span></span>
                            <span>المتاح: <span dir="ltr" class="font-semibold text-[var(--color-text-primary)]">{{ number_format($config['available']) }}</span></span>
                        </div>
                        <input
                            type="range"
                            x-model.number="points"
                            :min="min"
                            :max="available"
                            step="10"
                            class="w-full accent-[var(--color-primary-500)]"
                            :disabled="available < min"
                        >
                    </div>

                    {{-- Number input --}}
                    <x-forms.form-group label="عدد النقاط" for="points" required>
                        <x-ui.input
                            type="number"
                            id="points"
                            name="points"
                            x-model.number="points"
                            :min="$config['min_redemption']"
                            :max="$config['available']"
                            step="10"
                            required
                        />
                    </x-forms.form-group>

                    {{-- Live USD value --}}
                    <div class="bg-[var(--color-surface-tertiary)] rounded-[var(--radius-md)] p-4 mb-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-[var(--color-text-secondary)]">القيمة الدولارية</span>
                            <span class="text-2xl font-bold text-[var(--color-cta-700)]" dir="ltr">$<span x-text="usd"></span></span>
                        </div>
                        <p class="text-xs text-[var(--color-text-muted)] mt-1" dir="ltr">
                            ${{ $config['point_value'] }} لكل نقطة
                        </p>
                    </div>

                    {{-- Validation hint --}}
                    <p x-show="!isValid" x-cloak class="text-sm text-[var(--color-danger-600)] mb-3">
                        يجب أن يكون العدد بين <span dir="ltr" x-text="min"></span> و <span dir="ltr" x-text="available"></span>.
                    </p>

                    @if($errors->has('points'))
                        <x-ui.alert variant="danger" class="mb-3">{{ $errors->first('points') }}</x-ui.alert>
                    @endif

                    <div class="flex justify-end gap-2">
                        <x-ui.button variant="secondary" href="{{ route('agent.dashboard') }}">
                            إلغاء
                        </x-ui.button>
                        <x-ui.button
                            type="button"
                            variant="cta"
                            x-on:click="$dispatch('open-modal', 'confirm-cash')"
                            ::disabled="!isValid"
                            :auto-loading="false"
                        >
                            تقديم الطلب
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>

        {{-- Side info --}}
        <div class="space-y-4">
            <x-ui.card title="كيف يعمل؟" padding="md">
                <ol class="space-y-3 text-sm">
                    <li class="flex gap-2">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-[var(--color-primary-50)] text-[var(--color-primary-700)] font-bold flex items-center justify-center text-xs">1</span>
                        <span>تختار عدد النقاط (الحد الأدنى <strong>{{ number_format($config['min_redemption']) }}</strong>).</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-[var(--color-primary-50)] text-[var(--color-primary-700)] font-bold flex items-center justify-center text-xs">2</span>
                        <span>تُحجز النقاط مؤقتاً (لا يمكن استخدامها حتى يتم الرد).</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-[var(--color-primary-50)] text-[var(--color-primary-700)] font-bold flex items-center justify-center text-xs">3</span>
                        <span>الأدمن يراجع ويوافق خلال <strong>24 ساعة</strong> عادةً.</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-[var(--color-cta-50)] text-[var(--color-cta-700)] font-bold flex items-center justify-center text-xs">✓</span>
                        <span>يتم تحويل المبلغ لحسابك ويصلك إشعار.</span>
                    </li>
                </ol>
            </x-ui.card>

            <x-ui.alert variant="info">
                💡 لو رُفض الطلب لأي سبب، تعود نقاطك كاملة لرصيدك المتاح.
            </x-ui.alert>
        </div>

        {{-- Confirmation modal --}}
        <x-ui.modal name="confirm-cash" title="تأكيد طلب التحويل" size="sm">
            <div class="text-center mb-4">
                <p class="text-[var(--color-text-secondary)] mb-3">سيتم حجز نقاطك حتى يراجع المدير الطلب.</p>
                <div class="bg-[var(--color-surface-tertiary)] rounded-[var(--radius-md)] p-4 space-y-1">
                    <div class="flex justify-between"><span class="text-sm text-[var(--color-text-secondary)]">عدد النقاط:</span> <span class="font-bold" dir="ltr" x-text="points.toLocaleString()"></span></div>
                    <div class="flex justify-between"><span class="text-sm text-[var(--color-text-secondary)]">القيمة:</span> <span class="font-bold text-[var(--color-cta-700)]" dir="ltr">$<span x-text="usd"></span></span></div>
                </div>
            </div>

            <x-slot:footer>
                <x-ui.button variant="secondary" x-on:click="$dispatch('close-modal', 'confirm-cash')">تراجع</x-ui.button>
                <x-ui.button variant="cta" x-on:click="document.getElementById('cash-form').submit()">
                    نعم، قدّم الطلب
                </x-ui.button>
            </x-slot:footer>
        </x-ui.modal>
    </div>
</x-layouts.agent>
