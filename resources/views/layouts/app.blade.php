<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} — @yield('title', 'Dashboard')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans antialiased" x-data="{ sidebarOpen: false }">

    <div x-show="sidebarOpen" @click="sidebarOpen = false"
         class="fixed inset-0 z-20 bg-black bg-opacity-50 lg:hidden" x-cloak></div>

    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="fixed inset-y-0 left-0 z-30 w-64 bg-gray-900 text-white transition-transform duration-200 lg:translate-x-0">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-700">
            <span class="text-lg font-bold">📋 Demandas</span>
            <button @click="sidebarOpen = false" class="lg:hidden text-gray-400 hover:text-white">✕</button>
        </div>
        <nav class="mt-4 px-3 space-y-1">
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                🏠 Dashboard
            </a>
            <a href="{{ route('agenda') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('agenda*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                📅 Agenda Semanal
            </a>
            <a href="{{ route('demandas.create') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-gray-300 hover:bg-gray-800">
                ➕ Nova Demanda
            </a>
            <hr class="my-3 border-gray-700">
            <a href="{{ route('configuracoes') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('configuracoes*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                ⚙️ Configurações
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-gray-300 hover:bg-gray-800 text-left">
                    🚪 Sair
                </button>
            </form>
        </nav>
        <div class="absolute bottom-0 left-0 right-0 px-4 py-3 border-t border-gray-700 text-xs text-gray-500">
            {{ config('app.name') }}
        </div>
    </aside>

    <div class="lg:ml-64 min-h-screen flex flex-col">
        <header class="bg-white shadow-sm px-4 py-3 flex items-center justify-between">
            <button @click="sidebarOpen = true" class="lg:hidden text-gray-600 hover:text-gray-900 text-xl">☰</button>
            <div class="text-sm text-gray-600">Olá, <strong>{{ auth()->user()->name }}</strong></div>
            <div class="flex gap-2">
                <form method="POST" action="{{ route('alertas.enviar') }}">
                    @csrf
                    <button type="submit" class="text-xs px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full hover:bg-yellow-200">
                        📧 Enviar alerta
                    </button>
                </form>
                <a href="{{ route('demandas.pdf') }}" class="text-xs px-3 py-1 bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200">
                    📄 PDF semanal
                </a>
            </div>
        </header>

        @if(session('success'))
            <div class="mx-4 mt-4 px-4 py-3 bg-green-100 text-green-800 rounded-lg text-sm">✓ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mx-4 mt-4 px-4 py-3 bg-red-100 text-red-800 rounded-lg text-sm">✗ {{ session('error') }}</div>
        @endif

        <main class="flex-1 p-4 md:p-6">
            @yield('content')
        </main>
    </div>
</body>
</html>
