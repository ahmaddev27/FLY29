<x-layouts.auth title="تسجيل الدخول">

    <h2 class="text-xl font-bold text-[var(--color-text-primary)] mb-1">تسجيل الدخول</h2>
    <p class="text-sm text-[var(--color-text-secondary)] mb-6">أدخل بياناتك للوصول إلى لوحة التحكم.</p>

    @if(session('status'))
        <x-ui.alert variant="success" class="mb-4">{{ session('status') }}</x-ui.alert>
    @endif

    @if($errors->any())
        <x-ui.alert variant="danger" class="mb-4">{{ $errors->first() }}</x-ui.alert>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <x-forms.form-group label="البريد الإلكتروني" for="email" required>
            <x-ui.input
                type="email"
                id="email"
                name="email"
                :value="old('email')"
                placeholder="agent@example.com"
                required
                autofocus
            />
        </x-forms.form-group>

        <x-forms.form-group label="كلمة المرور" for="password" required>
            <x-ui.input
                type="password"
                id="password"
                name="password"
                placeholder="••••••••"
                required
            />
        </x-forms.form-group>

        <div class="flex items-center justify-between">
            <label class="inline-flex items-center gap-2 text-sm text-[var(--color-text-secondary)] cursor-pointer">
                <input type="checkbox" name="remember" value="1" class="rounded border-[var(--color-surface-border)] text-[var(--color-primary-500)] focus:ring-[var(--color-primary-100)]">
                <span>تذكّرني</span>
            </label>

            <a href="{{ route('password.request') }}" class="text-sm text-[var(--color-primary-500)] hover:underline">
                نسيت كلمة المرور؟
            </a>
        </div>

        <x-ui.button type="submit" variant="primary" :full="true">
            تسجيل الدخول
        </x-ui.button>
    </form>

</x-layouts.auth>
