<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen bg-gray-100">
    @include('partials.sidebar')

    <div class="lg:pl-64">
        @include('partials.topbar')

        <main class="p-4 sm:p-6 lg:p-8">
            @include('partials.flash')
            @yield('content')
        </main>
    </div>

    <script>
        window.EchoEnabled = @json(config('broadcasting.default') === 'reverb');
    </script>
    @stack('scripts')
</body>
</html>