<x-layouts.admin
    title="استيراد وكلاء"
    pageTitle="استيراد وكلاء من Excel"
    :breadcrumbs="[
        ['label' => 'لوحة الإدارة', 'href' => route('admin.dashboard')],
        ['label' => 'الوكلاء', 'href' => route('admin.agents')],
        ['label' => 'استيراد'],
    ]"
>
    <div class="grid lg:grid-cols-3 gap-6">

        {{-- Upload form --}}
        <div class="lg:col-span-2 space-y-6">
            <x-ui.card title="رفع ملف Excel / CSV" subtitle="حد أقصى 5 ميجا — صف لكل وكيل">
                <form method="POST" action="{{ route('admin.agents.import.store') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <x-forms.form-group label="اختر الملف" for="file" required>
                        <input
                            type="file"
                            id="file"
                            name="file"
                            accept=".xlsx,.xls,.csv"
                            required
                            class="block w-full text-sm text-slate-700 file:me-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[var(--color-primary-50)] file:text-[var(--color-primary-700)] hover:file:bg-[var(--color-primary-100)] cursor-pointer">
                    </x-forms.form-group>

                    @error('file')
                        <p class="text-sm text-rose-600">{{ $message }}</p>
                    @enderror

                    <div class="flex justify-between items-center pt-2">
                        <x-ui.button variant="secondary" href="{{ route('admin.agents.import.template') }}">
                            <x-ui.icon name="download" size="sm" /> تنزيل قالب CSV
                        </x-ui.button>
                        <x-ui.button type="submit" variant="cta">
                            <x-ui.icon name="upload" size="sm" /> بدء الاستيراد
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.card>

            {{-- Errors from last import (if any) --}}
            @php $errors = session('import_errors', []); @endphp
            @if(! empty($errors))
                <x-ui.card title="صفوف فشلت ({{ count($errors) }})" subtitle="تم تجاوزها — الباقي تم استيراده">
                    <div class="max-h-96 overflow-y-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 text-xs font-semibold text-slate-600">
                                <tr>
                                    <th class="px-3 py-2 text-right">الصف</th>
                                    <th class="px-3 py-2 text-right">البريد</th>
                                    <th class="px-3 py-2 text-right">سبب الفشل</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($errors as $error)
                                    <tr>
                                        <td class="px-3 py-2 font-latin text-slate-500">{{ $error['row'] }}</td>
                                        <td class="px-3 py-2 font-latin">{{ $error['data']['email'] ?? '—' }}</td>
                                        <td class="px-3 py-2 text-rose-600">
                                            <ul class="list-disc list-inside space-y-0.5">
                                                @foreach($error['errors'] as $msg)
                                                    <li>{{ $msg }}</li>
                                                @endforeach
                                            </ul>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-ui.card>
            @endif
        </div>

        {{-- Sidebar: instructions --}}
        <div class="lg:col-span-1">
            <x-ui.card title="تعليمات">
                <div class="text-sm text-slate-600 space-y-3">
                    <p class="font-semibold text-slate-800">الأعمدة المطلوبة:</p>
                    <ul class="list-disc list-inside space-y-1 text-xs marker:text-[var(--color-primary-500)]">
                        <li><code class="font-latin">full_name</code> — الاسم الكامل</li>
                        <li><code class="font-latin">email</code> — البريد (فريد)</li>
                        <li><code class="font-latin">phone</code> — الجوال (اختياري)</li>
                        <li><code class="font-latin">external_agent_id</code> — ID الموقع</li>
                        <li><code class="font-latin">business_name</code> — الاسم التجاري</li>
                        <li><code class="font-latin">license_number</code> — رقم الترخيص</li>
                        <li><code class="font-latin">country</code> — الدولة (SA, AE...)</li>
                        <li><code class="font-latin">city</code> — المدينة (اختياري)</li>
                        <li><code class="font-latin">current_tier</code> — bronze/silver/gold/diamond</li>
                    </ul>

                    <p class="font-semibold text-slate-800 pt-2">ملاحظات:</p>
                    <ul class="list-disc list-inside space-y-1 text-xs marker:text-[var(--color-primary-500)]">
                        <li>كل وكيل ناجح يستلم بريد ترحيب فيه رابط لتعيين كلمة المرور.</li>
                        <li>الصفوف الفاشلة (بريد مكرر، ID مكرر، حقل ناقص) تُتجاوز ولا تكسر الاستيراد.</li>
                        <li>تُنشأ المحفظتان تلقائياً برصيد صفر.</li>
                    </ul>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.admin>
