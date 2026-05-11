<x-layouts.auth title="تعيين كلمة مرور جديدة">

    <h2 class="text-xl font-bold text-[var(--color-text-primary)] mb-1">تعيين كلمة مرور جديدة</h2>
    <p class="text-sm text-[var(--color-text-secondary)] mb-6">أدخل كلمة مرور قوية.</p>

    @if($errors->any())
        <x-ui.alert variant="danger" class="mb-4">{{ $errors->first() }}</x-ui.alert>
    @endif

    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <x-forms.form-group label="البريد الإلكتروني" for="email" required>
            <x-ui.input
                type="email"
                id="email"
                name="email"
                :value="$email"
                required
                readonly
            />
        </x-forms.form-group>

        <x-forms.form-group label="كلمة المرور الجديدة" for="password" hint="8 أحرف على الأقل" required>
            <x-ui.input
                type="password"
                id="password"
                name="password"
                required
            />
        </x-forms.form-group>

        <x-forms.form-group label="تأكيد كلمة المرور" for="password_confirmation" required>
            <x-ui.input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                required
            />
        </x-forms.form-group>

        <x-ui.button type="submit" variant="primary" :full="true">
            حفظ كلمة المرور
        </x-ui.button>
    </form>

</x-layouts.auth>
