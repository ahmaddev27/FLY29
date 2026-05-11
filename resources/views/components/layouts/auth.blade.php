@props(['title' => 'تسجيل الدخول'])

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }} — {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased min-h-screen bg-[var(--color-surface-secondary)]">

<div class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        {{-- Logo --}}
        <div class="text-center mb-6">
            <div class="inline-flex w-16 h-16 rounded-2xl bg-[var(--color-primary-500)] text-white items-center justify-center mb-3">
                <svg class="h-8 w-8" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M21 16v-2l-8-5V3.5c0-.83-.67-1.5-1.5-1.5S10 2.67 10 3.5V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-[var(--color-text-primary)]">{{ config('app.name', '29FLY Loyalty') }}</h1>
            <p class="text-sm text-[var(--color-text-secondary)] mt-1">برنامج ولاء الوكلاء</p>
        </div>

        {{-- Card --}}
        <div class="bg-white rounded-[var(--radius-lg)] shadow-[var(--shadow-card)] border border-[var(--color-surface-border)] p-6">
            {{ $slot }}
        </div>

        <p class="text-center text-xs text-[var(--color-text-muted)] mt-6">
            © {{ date('Y') }} 29FLY Loyalty. كل الحقوق محفوظة.
        </p>
    </div>
</div>

</body>
</html>
