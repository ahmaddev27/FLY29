<x-layouts.app title="لوحة الوكيل">
    <div class="min-h-screen flex items-center justify-center bg-[var(--color-surface-secondary)] p-6">
        <x-ui.card class="max-w-md w-full">
            <x-ui.tier-badge tier="bronze" size="lg" class="mb-4" />
            <h1 class="text-2xl font-bold mb-2">أهلاً، {{ auth()->user()->full_name }}</h1>
            <p class="text-[var(--color-text-secondary)] mb-4">
                أنت داخل لوحة الوكيل التجريبية. ستُبنى لوحة التحكم الكاملة في Sprint 1.3.
            </p>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-ui.button type="submit" variant="secondary" :full="true">تسجيل الخروج</x-ui.button>
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>
