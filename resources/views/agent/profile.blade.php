<x-layouts.agent
    title="ملفي الشخصي"
    pageTitle="ملفي الشخصي"
    :breadcrumbs="[['label' => 'الرئيسية', 'href' => route('agent.dashboard')], ['label' => 'ملفي الشخصي']]"
>

    <div class="grid lg:grid-cols-3 gap-6">

        {{-- Summary card --}}
        <div class="lg:col-span-1 space-y-4">
            <x-ui.card>
                <div class="text-center">
                    <div class="w-20 h-20 rounded-full bg-[var(--color-primary-500)] text-white text-3xl font-bold mx-auto mb-3 flex items-center justify-center">
                        {{ mb_substr(auth()->user()->full_name, 0, 1) }}
                    </div>
                    <h3 class="font-semibold text-[var(--color-text-primary)]">{{ auth()->user()->full_name }}</h3>
                    <p class="text-sm text-[var(--color-text-secondary)] mt-1 font-latin">{{ auth()->user()->email }}</p>

                    <div class="mt-4">
                        <x-ui.tier-badge :tier="$agent->current_tier" size="lg" />
                    </div>
                </div>

                <hr class="my-4 border-[var(--color-surface-divider)]">

                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-[var(--color-text-secondary)]">المعرف الخارجي</dt>
                        <dd class="font-latin font-medium">{{ $agent->external_agent_id }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-[var(--color-text-secondary)]">رقم الترخيص</dt>
                        <dd class="font-latin font-medium">{{ $agent->license_number }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-[var(--color-text-secondary)]">الدولة</dt>
                        <dd class="font-medium">{{ $agent->country }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-[var(--color-text-secondary)]">تاريخ الانضمام</dt>
                        <dd class="font-latin">{{ $agent->created_at->format('Y-m-d') }}</dd>
                    </div>
                </dl>
            </x-ui.card>
        </div>

        {{-- Edit forms --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Personal info form --}}
            <x-ui.card title="البيانات الشخصية" subtitle="البريد الإلكتروني ورقم الترخيص يتطلبان موافقة الإدارة لتعديلهما.">
                <form method="POST" action="{{ route('agent.profile.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid md:grid-cols-2 gap-4">
                        <x-forms.form-group label="الاسم الكامل" for="full_name" required>
                            <x-ui.input id="full_name" name="full_name" :value="old('full_name', auth()->user()->full_name)" required />
                        </x-forms.form-group>

                        <x-forms.form-group label="رقم الهاتف" for="phone">
                            <x-ui.input id="phone" name="phone" :value="old('phone', auth()->user()->phone)" />
                        </x-forms.form-group>

                        <x-forms.form-group label="المدينة" for="city">
                            <x-ui.input id="city" name="city" :value="old('city', $agent->city)" />
                        </x-forms.form-group>

                        <x-forms.form-group label="البريد الإلكتروني (للقراءة فقط)" for="email">
                            <x-ui.input id="email" :value="auth()->user()->email" disabled />
                        </x-forms.form-group>
                    </div>

                    <div class="flex justify-end">
                        <x-ui.button type="submit" variant="primary">حفظ التغييرات</x-ui.button>
                    </div>
                </form>
            </x-ui.card>

            {{-- Password change form --}}
            <x-ui.card title="تغيير كلمة المرور" subtitle="استخدم كلمة مرور قوية، 8 أحرف على الأقل.">
                <form method="POST" action="{{ route('agent.profile.password') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <x-forms.form-group label="كلمة المرور الحالية" for="current_password" required>
                        <x-ui.input type="password" id="current_password" name="current_password" required />
                    </x-forms.form-group>

                    <div class="grid md:grid-cols-2 gap-4">
                        <x-forms.form-group label="كلمة المرور الجديدة" for="password" required hint="8 أحرف على الأقل">
                            <x-ui.input type="password" id="password" name="password" required />
                        </x-forms.form-group>

                        <x-forms.form-group label="تأكيد كلمة المرور" for="password_confirmation" required>
                            <x-ui.input type="password" id="password_confirmation" name="password_confirmation" required />
                        </x-forms.form-group>
                    </div>

                    <div class="flex justify-end">
                        <x-ui.button type="submit" variant="primary">تغيير كلمة المرور</x-ui.button>
                    </div>
                </form>
            </x-ui.card>

        </div>
    </div>

</x-layouts.agent>
