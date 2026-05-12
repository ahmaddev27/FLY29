<x-layouts.admin
    title="مدير حسابات جديد"
    pageTitle="إضافة مدير حسابات"
    :breadcrumbs="[
        ['label' => 'لوحة الإدارة', 'href' => route('admin.dashboard')],
        ['label' => 'مدراء الحسابات', 'href' => route('admin.account-managers')],
        ['label' => 'إضافة'],
    ]"
>
    <form method="POST" action="{{ route('admin.account-managers.store') }}">
        @csrf

        <div class="grid lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <x-ui.card title="بيانات الحساب">
                    <div class="grid sm:grid-cols-2 gap-4">
                        <x-forms.form-group label="الاسم الكامل" for="full_name" required>
                            <x-ui.input id="full_name" name="full_name" :value="old('full_name')" required />
                        </x-forms.form-group>

                        <x-forms.form-group label="البريد الإلكتروني" for="email" required hint="سيُرسل إليه رابط تعيين كلمة المرور">
                            <x-ui.input type="email" id="email" name="email" :value="old('email')" required dir="ltr" />
                        </x-forms.form-group>

                        <x-forms.form-group label="رقم الجوال" for="phone">
                            <x-ui.input id="phone" name="phone" :value="old('phone')" dir="ltr" placeholder="+966..." />
                        </x-forms.form-group>
                    </div>
                </x-ui.card>
            </div>

            <div class="lg:col-span-1">
                <x-ui.card title="بعد إنشاء الحساب">
                    <ul class="text-sm text-slate-600 space-y-2 list-disc list-inside marker:text-[var(--color-primary-500)]">
                        <li>يُرسل بريد للمدير لتعيين كلمة المرور.</li>
                        <li>يستطيع رؤية وكلائه فقط (لا يصل لباقي الفريق).</li>
                        <li>يستطيع اقتراح تعديلات نقاط (تحتاج موافقتك).</li>
                        <li>يستطيع التواصل مع الوكلاء عبر الرسائل الداخلية.</li>
                    </ul>
                </x-ui.card>
            </div>
        </div>

        <div class="flex justify-end gap-2 mt-6">
            <x-ui.button variant="secondary" href="{{ route('admin.account-managers') }}">إلغاء</x-ui.button>
            <x-ui.button type="submit" variant="cta">
                <x-ui.icon name="plus" size="sm" /> إنشاء المدير
            </x-ui.button>
        </div>
    </form>
</x-layouts.admin>
