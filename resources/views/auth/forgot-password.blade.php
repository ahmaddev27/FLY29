<x-layouts.auth title="استعادة كلمة المرور">

    <h2 class="text-xl font-bold text-[var(--color-text-primary)] mb-1">استعادة كلمة المرور</h2>
    <p class="text-sm text-[var(--color-text-secondary)] mb-6">
        أدخل بريدك الإلكتروني وسنرسل لك رابطاً لإعادة تعيين كلمة المرور.
    </p>

    @if(session('status'))
        <x-ui.alert variant="success" class="mb-4">{{ session('status') }}</x-ui.alert>
    @endif

    @if($errors->any())
        <x-ui.alert variant="danger" class="mb-4">{{ $errors->first() }}</x-ui.alert>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
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

        <x-ui.button type="submit" variant="primary" :full="true">
            إرسال رابط الاستعادة
        </x-ui.button>

        <p class="text-center text-sm">
            <a href="{{ route('login') }}" class="text-[var(--color-primary-500)] hover:underline">
                عودة لتسجيل الدخول
            </a>
        </p>
    </form>

</x-layouts.auth>
