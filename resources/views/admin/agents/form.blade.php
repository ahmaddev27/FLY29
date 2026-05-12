@php
    $isEdit = $agent->exists;
    $title  = $isEdit ? 'تعديل الوكيل: ' . $agent->business_name : 'وكيل جديد';
@endphp

<x-layouts.admin
    :title="$title"
    :pageTitle="$title"
    :breadcrumbs="[
        ['label' => 'لوحة الإدارة', 'href' => route('admin.dashboard')],
        ['label' => 'الوكلاء', 'href' => route('admin.agents')],
        ['label' => $isEdit ? 'تعديل' : 'إضافة'],
    ]"
>
    <form method="POST" action="{{ route('admin.agents.store') }}">
        @csrf

        <div class="grid lg:grid-cols-3 gap-6">

            {{-- Main column: account + business --}}
            <div class="lg:col-span-2 space-y-6">

                <x-ui.card title="بيانات الحساب" subtitle="بيانات الدخول الشخصية للوكيل">
                    <div class="grid sm:grid-cols-2 gap-4">
                        <x-forms.form-group label="الاسم الكامل" for="full_name" required>
                            <x-ui.input id="full_name" name="full_name" :value="old('full_name', $user->full_name)" required />
                        </x-forms.form-group>

                        <x-forms.form-group label="البريد الإلكتروني" for="email" required hint="سيُرسل إليه رابط تعيين كلمة المرور">
                            <x-ui.input type="email" id="email" name="email" :value="old('email', $user->email)" required dir="ltr" />
                        </x-forms.form-group>

                        <x-forms.form-group label="رقم الجوال" for="phone">
                            <x-ui.input id="phone" name="phone" :value="old('phone', $user->phone)" dir="ltr" placeholder="+966..." />
                        </x-forms.form-group>

                        <x-forms.form-group label="ID على الموقع الرئيسي" for="external_agent_id" required hint="معرّف الوكيل في fly29.net">
                            <x-ui.input id="external_agent_id" name="external_agent_id" :value="old('external_agent_id', $agent->external_agent_id)" dir="ltr" placeholder="AGT-1234" required />
                        </x-forms.form-group>
                    </div>
                </x-ui.card>

                <x-ui.card title="بيانات الشركة">
                    <div class="grid sm:grid-cols-2 gap-4">
                        <x-forms.form-group label="الاسم التجاري" for="business_name" required>
                            <x-ui.input id="business_name" name="business_name" :value="old('business_name', $agent->business_name)" required />
                        </x-forms.form-group>

                        <x-forms.form-group label="رقم الترخيص" for="license_number" required>
                            <x-ui.input id="license_number" name="license_number" :value="old('license_number', $agent->license_number)" dir="ltr" required />
                        </x-forms.form-group>

                        <x-forms.form-group label="الدولة" for="country" required>
                            <x-ui.input id="country" name="country" :value="old('country', $agent->country)" placeholder="SA" required />
                        </x-forms.form-group>

                        <x-forms.form-group label="المدينة" for="city">
                            <x-ui.input id="city" name="city" :value="old('city', $agent->city)" placeholder="Riyadh" />
                        </x-forms.form-group>
                    </div>
                </x-ui.card>
            </div>

            {{-- Side column: tier + summary --}}
            <div class="lg:col-span-1 space-y-6">
                <x-ui.card title="التصنيف الابتدائي" subtitle="يمكن أن يتغير لاحقاً وفق المبيعات">
                    <x-forms.form-group label="التصنيف" for="current_tier">
                        <x-ui.select
                            id="current_tier"
                            name="current_tier"
                            :options="[
                                'bronze'  => 'برونزي',
                                'silver'  => 'فضي',
                                'gold'    => 'ذهبي',
                                'diamond' => 'ماسي',
                            ]"
                            :selected="old('current_tier', 'bronze')"
                        />
                    </x-forms.form-group>
                </x-ui.card>

                <x-ui.card title="بعد إنشاء الحساب">
                    <ul class="text-sm text-slate-600 space-y-2 list-disc list-inside marker:text-[var(--color-primary-500)]">
                        <li>تُنشأ محفظتان (كاش + باكجات) برصيد صفر.</li>
                        <li>يُرسل بريد للوكيل لتعيين كلمة المرور.</li>
                        <li>تبدأ المعاملات بالوصول من fly29.net مباشرة.</li>
                        <li>صلاحية التصنيف 30 يوم، ثم يُعاد التقييم.</li>
                    </ul>
                </x-ui.card>
            </div>
        </div>

        <div class="flex justify-end gap-2 mt-6">
            <x-ui.button variant="secondary" href="{{ route('admin.agents') }}">إلغاء</x-ui.button>
            <x-ui.button type="submit" variant="cta">
                <x-ui.icon name="plus" size="sm" /> إنشاء الوكيل
            </x-ui.button>
        </div>
    </form>
</x-layouts.admin>
