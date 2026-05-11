<x-layouts.app title="لوحة الأدمن">
    <div class="min-h-screen flex items-center justify-center bg-[var(--color-surface-secondary)] p-6">
        <x-ui.card class="max-w-md w-full">
            <x-ui.badge variant="primary" :dot="true" class="mb-4">{{ auth()->user()->role }}</x-ui.badge>
            <h1 class="text-2xl font-bold mb-2">أهلاً، {{ auth()->user()->full_name }}</h1>
            <p class="text-[var(--color-text-secondary)] mb-4">
                لوحة الأدمن التجريبية. الصفحة الكاملة قيد التطوير.
            </p>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-ui.button type="submit" variant="secondary" :full="true">تسجيل الخروج</x-ui.button>
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>
