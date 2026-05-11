@props(['title' => 'تسجيل الدخول'])

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }} — {{ config('app.name') }}</title>

    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">

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
            <img src="{{ asset('images/fly29-logo.png') }}"
                 alt="29FLY"
                 class="h-16 mx-auto mb-3 object-contain">
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
