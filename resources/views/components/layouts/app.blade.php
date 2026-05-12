@props(['title' => null])

<!DOCTYPE html>
<html lang="ar" dir="rtl" class="preload">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', '29FLY Loyalty') }}</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">

    {{-- Cairo font from Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Vite assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Preload trick: kill every transition until the page is fully ready. --}}
    <style>
        html.preload *,
        html.preload *::before,
        html.preload *::after {
            transition: none !important;
            animation: none !important;
        }
    </style>

    @stack('head')
</head>
<body class="antialiased min-h-screen" @auth data-authenticated="1" @endauth>

    {{ $slot }}

    {{-- Global toaster --}}
    <x-ui.toaster />

    {{-- Session flash → toaster --}}
    @if(session('status'))
        <script>
            window.addEventListener('DOMContentLoaded', () => {
                window.toast({ variant: 'success', message: @json(session('status')) });
            });
        </script>
    @endif

    @if($errors->any())
        <script>
            window.addEventListener('DOMContentLoaded', () => {
                window.toast({ variant: 'danger', message: @json($errors->first()), duration: 6000 });
            });
        </script>
    @endif

    {{-- Drop the preload class once everything (HTML + CSS + first paint)
         has settled, then on the next animation frame so transitions kick
         in cleanly. --}}
    <script>
        window.addEventListener('load', () => {
            requestAnimationFrame(() => {
                document.documentElement.classList.remove('preload');
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
