<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Agenda') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-sm">
        {{-- Logo / Título --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600 rounded-2xl shadow-lg mb-4">
                <span class="text-3xl">📋</span>
            </div>
            <h1 class="text-2xl font-bold text-white">{{ config('app.name', 'Agenda') }}</h1>
            <p class="text-gray-400 text-sm mt-1">Gestão de demandas</p>
        </div>

        {{-- Card --}}
        <div class="bg-white/10 backdrop-blur-sm border border-white/10 rounded-2xl p-8 shadow-2xl">
            {{ $slot }}
        </div>

        <p class="text-center text-gray-600 text-xs mt-6">
            © {{ date('Y') }} {{ config('app.name') }}
        </p>
    </div>

</body>
</html>
