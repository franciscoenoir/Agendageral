<!DOCTYPE html>
<html lang="pt-BR" x-data x-bind:class="$store.darkMode.on ? 'dark' : ''">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} — @yield('title', 'Dashboard')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 dark:bg-gray-950 font-sans antialiased transition-colors duration-200" x-data="{ sidebarOpen: false, buscaAberta: false }"
      @keydown.escape.window="buscaAberta = false"
      @keydown.meta.k.window.prevent="buscaAberta = true"
      @keydown.ctrl.k.window.prevent="buscaAberta = true">

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
                📅 Agenda
            </a>
            <a href="{{ route('demandas.create') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-gray-300 hover:bg-gray-800">
                ➕ Nova Demanda
            </a>
            <a href="{{ route('lembretes') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('lembretes*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                🗒️ Lembretes
            </a>
            <a href="{{ route('historico') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('historico*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                🗂️ Histórico
            </a>
            <a href="{{ route('recebimentos') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('recebimentos*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                💰 Valores a Receber
            </a>
            <hr class="my-3 border-gray-700">
            <button @click="$store.darkMode.toggle()"
                    class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-gray-300 hover:bg-gray-800 text-left">
                <span x-text="$store.darkMode.on ? '☀️' : '🌙'"></span>
                <span x-text="$store.darkMode.on ? 'Modo claro' : 'Modo escuro'"></span>
            </button>
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
        <header class="bg-white dark:bg-gray-900 dark:border-b dark:border-gray-800 shadow-sm px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = true" class="lg:hidden text-gray-600 hover:text-gray-900 text-xl">☰</button>
                <div class="text-sm text-gray-600">Olá, <strong>{{ auth()->user()->name }}</strong></div>
                <button @click="buscaAberta = true"
                        class="flex items-center gap-2 text-xs text-gray-400 border border-gray-200 rounded-lg px-3 py-1.5 hover:border-gray-400 hover:text-gray-600 transition-colors bg-gray-50">
                    🔍 <span class="hidden sm:inline">Buscar</span>
                    <span class="hidden sm:inline text-gray-300 ml-1">⌘K</span>
                </button>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-500 bg-gray-100 px-3 py-1 rounded-full hidden sm:inline-flex">
                    📆 {{ \Carbon\Carbon::now()->locale('pt_BR')->translatedFormat('d \d\e F \d\e Y') }}
                </span>

                {{-- Dica rotativa --}}
                <div x-data="dicaRotativa()" x-init="iniciar()"
                     class="hidden md:flex items-center gap-1.5 max-w-xs">
                    <span x-text="atual.icone" class="shrink-0"></span>
                    <span x-text="atual.texto"
                          x-transition:enter="transition ease-out duration-500"
                          x-transition:enter-start="opacity-0 translate-y-1"
                          x-transition:enter-end="opacity-100 translate-y-0"
                          x-transition:leave="transition ease-in duration-300"
                          x-transition:leave-start="opacity-100"
                          x-transition:leave-end="opacity-0"
                          :class="atual.cor"
                          class="text-xs font-medium truncate"></span>
                </div>
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

        <main class="flex-1 p-4 md:p-6 dark:text-gray-100">
            @yield('content')
        </main>
    </div>
{{-- Overlay de busca rápida --}}
<div x-show="buscaAberta" x-cloak
     class="fixed inset-0 z-50 flex items-start justify-center pt-24 px-4"
     @click.self="buscaAberta = false">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg z-10 overflow-hidden">
        <form method="GET" action="{{ route('busca') }}">
            <div class="flex items-center gap-3 px-4 py-3 border-b">
                <span class="text-gray-400">🔍</span>
                <input type="text" name="q" autofocus
                       placeholder="Buscar demandas, lembretes... (Enter para buscar)"
                       class="flex-1 text-sm focus:outline-none"
                       x-ref="buscaInput"
                       x-init="$watch('buscaAberta', v => v && $nextTick(() => $refs.buscaInput?.focus()))">
                <button type="submit" class="text-xs px-3 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Buscar</button>
                <button type="button" @click="buscaAberta = false" class="text-gray-400 hover:text-gray-700 text-lg leading-none">✕</button>
            </div>
        </form>
        <div class="px-4 py-3 text-xs text-gray-400 flex gap-4">
            <span>🔍 Busca em demandas pendentes, histórico e lembretes</span>
            <span class="ml-auto">ESC para fechar</span>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('darkMode', {
        on: localStorage.getItem('darkMode') === 'true',
        toggle() {
            this.on = !this.on;
            localStorage.setItem('darkMode', this.on);
        },
    });
});
function dicaRotativa() {
    const dicas = [
        // Foco
        { icone: '🎯', texto: 'Uma tarefa de cada vez. Foco total.', cor: 'text-blue-600' },
        { icone: '🎯', texto: 'Elimine distrações. O foco é sua maior vantagem.', cor: 'text-blue-600' },
        { icone: '🎯', texto: 'Trabalhe em blocos de tempo. Descanse com intenção.', cor: 'text-blue-600' },
        { icone: '🎯', texto: 'Feche abas desnecessárias. Abra espaço para pensar.', cor: 'text-blue-600' },
        // Prioridade
        { icone: '⚡', texto: 'O urgente nem sempre é o importante.', cor: 'text-orange-600' },
        { icone: '⚡', texto: 'Faça a tarefa mais difícil primeiro.', cor: 'text-orange-600' },
        { icone: '⚡', texto: 'Dizer não é uma forma de dizer sim ao que importa.', cor: 'text-orange-600' },
        { icone: '⚡', texto: 'Priorize o que move o jogo, não o que preenche o tempo.', cor: 'text-orange-600' },
        { icone: '⚡', texto: 'Três entregas bem-feitas valem mais que dez pela metade.', cor: 'text-orange-600' },
        // Motivação
        { icone: '🚀', texto: 'Progresso, não perfeição.', cor: 'text-green-600' },
        { icone: '🚀', texto: 'Cada pequeno passo conta. Continue.', cor: 'text-green-600' },
        { icone: '🚀', texto: 'Você é capaz de muito mais do que imagina.', cor: 'text-green-600' },
        { icone: '🚀', texto: 'Grandes resultados vêm de hábitos consistentes.', cor: 'text-green-600' },
        { icone: '🚀', texto: 'O melhor momento para começar é agora.', cor: 'text-green-600' },
        { icone: '🚀', texto: 'Disciplina é fazer o que precisa ser feito, mesmo sem vontade.', cor: 'text-green-600' },
        // Bíblicas
        { icone: '✝️', texto: 'Tudo posso naquele que me fortalece. Fp 4:13', cor: 'text-purple-600' },
        { icone: '✝️', texto: 'Busca primeiro o reino de Deus. Mt 6:33', cor: 'text-purple-600' },
        { icone: '✝️', texto: 'Eu sei os planos que tenho para você — planos de prosperidade. Jr 29:11', cor: 'text-purple-600' },
        { icone: '✝️', texto: 'Confia no Senhor com todo o teu coração. Pv 3:5', cor: 'text-purple-600' },
        { icone: '✝️', texto: 'O Senhor é o meu pastor, nada me faltará. Sl 23:1', cor: 'text-purple-600' },
        { icone: '✝️', texto: 'Seja forte e corajoso. Não temas. Js 1:9', cor: 'text-purple-600' },
        { icone: '✝️', texto: 'Tudo tem o seu tempo determinado. Ec 3:1', cor: 'text-purple-600' },
    ];

    return {
        atual: dicas[Math.floor(Math.random() * dicas.length)],
        timer: null,

        iniciar() {
            this.timer = setInterval(() => {
                let proxima;
                do { proxima = dicas[Math.floor(Math.random() * dicas.length)]; }
                while (proxima.texto === this.atual.texto);
                this.atual = proxima;
            }, 9000);
        },
    };
}
</script>
</body>
</html>
