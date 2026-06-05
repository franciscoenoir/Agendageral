@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div
    x-data="painelPastas()"
    x-init="init()"
    @keydown.escape.window="fecharModal()"
>

{{-- Top bar: stats + ações --}}
<div class="flex flex-wrap items-center justify-between gap-2 mb-3">
    <div class="flex flex-wrap gap-2">
        @foreach([
            ['label'=>'Pendentes','value'=>$stats['total'],'color'=>'blue'],
            ['label'=>'Urgentes','value'=>$stats['urgentes'],'color'=>'red'],
            ['label'=>'Atrasadas','value'=>$stats['atrasadas'],'color'=>'orange'],
            ['label'=>'Esta semana','value'=>$stats['semana'],'color'=>'green'],
        ] as $stat)
        <div class="flex items-center gap-1.5 bg-white rounded-lg px-3 py-1 shadow-sm border-l-4 border-{{ $stat['color'] }}-500">
            <span class="text-sm font-bold text-{{ $stat['color'] }}-600">{{ $stat['value'] }}</span>
            <span class="text-xs text-gray-500">{{ $stat['label'] }}</span>
        </div>
        @endforeach
    </div>
    <div class="flex items-center gap-2">
        <button @click="abrirModal()"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 text-white text-xs font-medium rounded-lg hover:bg-indigo-700 shadow-sm">
            📁 Pastas
        </button>
        <a href="{{ route('demandas.create') }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded-lg hover:bg-green-700 shadow-sm">
            ➕ Nova Demanda
        </a>
    </div>
</div>

{{-- Filtros --}}
<div class="bg-white rounded-xl shadow-sm p-3 mb-3">
    <div class="flex flex-wrap items-center gap-2 mb-2">
        <div class="flex rounded-lg border border-gray-200 overflow-hidden mr-2">
            <button @click="vista='lista'"
                    :class="vista==='lista' ? 'bg-gray-800 text-white' : 'bg-white text-gray-500 hover:bg-gray-50'"
                    class="px-3 py-1 text-xs font-medium transition-colors">☰ Lista</button>
            <button @click="vista='pastas'"
                    :class="vista==='pastas' ? 'bg-gray-800 text-white' : 'bg-white text-gray-500 hover:bg-gray-50'"
                    class="px-3 py-1 text-xs font-medium transition-colors">📁 Pastas</button>
        </div>
        @foreach([
            'todos'=>'Todos','pendentes'=>'Pendentes','atrasadas'=>'🔴 Atrasadas',
            'urgentes'=>'🟠 Urgentes','hoje'=>'Hoje','semana'=>'Esta semana'
        ] as $key => $label)
        <a href="{{ route('dashboard', array_merge(request()->except('filtro'), ['filtro' => $key])) }}"
           class="px-3 py-1 rounded-full text-xs font-medium {{ $filtro === $key ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>
    <form method="GET" class="flex flex-wrap gap-2">
        <input type="hidden" name="filtro" value="{{ $filtro }}">
        <input type="text" name="busca" value="{{ request('busca') }}"
               placeholder="Buscar por título, observação ou responsável..."
               class="flex-1 text-sm border rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <select name="categoria" class="text-sm border rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Todas categorias</option>
            @foreach($categorias as $cat)
                <option value="{{ $cat->nome }}" {{ request('categoria') === $cat->nome ? 'selected' : '' }}>{{ $cat->nome }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-1.5 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">Buscar</button>
        @if(request('busca') || request('categoria'))
            <a href="{{ route('dashboard', ['filtro' => $filtro]) }}" class="px-3 py-1.5 text-sm text-gray-500 hover:text-gray-800">✕</a>
        @endif
    </form>
</div>

{{-- ===== VISTA LISTA ===== --}}
<div x-show="vista==='lista'" x-cloak>
    @forelse($demandas as $demanda)
        @include('demandas._card', ['demanda' => $demanda])
    @empty
    <div class="text-center py-16 text-gray-400">
        <div class="text-4xl mb-3">🎉</div>
        <p class="text-sm">Nenhuma demanda encontrada.</p>
        <a href="{{ route('demandas.create') }}" class="mt-3 inline-block text-sm text-blue-600 hover:underline">Criar nova demanda</a>
    </div>
    @endforelse
</div>

{{-- ===== VISTA PASTAS ===== --}}
<div x-show="vista==='pastas'" x-cloak>
    @php
        $demandasSemPasta = $demandas->whereNull('pasta_id');
        $demandasComPasta = $demandas->whereNotNull('pasta_id')->groupBy('pasta_id');
    @endphp

    @foreach($pastas as $pasta)
    @php
        $corBg = match($pasta->cor) {
            'red'    => 'bg-red-50 border-red-200',
            'orange' => 'bg-orange-50 border-orange-200',
            'yellow' => 'bg-yellow-50 border-yellow-200',
            'green'  => 'bg-green-50 border-green-200',
            'blue'   => 'bg-blue-50 border-blue-200',
            'purple' => 'bg-purple-50 border-purple-200',
            default  => 'bg-gray-50 border-gray-200',
        };
        $corIcon = match($pasta->cor) {
            'red'    => 'text-red-500',
            'orange' => 'text-orange-500',
            'yellow' => 'text-yellow-500',
            'green'  => 'text-green-500',
            'blue'   => 'text-blue-500',
            'purple' => 'text-purple-500',
            default  => 'text-gray-500',
        };
        $demandasDaPasta = $demandasComPasta->get($pasta->id, collect());
    @endphp
    <div class="border {{ $corBg }} rounded-xl mb-4 overflow-hidden" x-data="{ aberta: false }">
        <div class="flex items-center justify-between px-4 py-3 cursor-pointer" @click="aberta = !aberta">
            <div class="flex items-center gap-2">
                <span class="text-lg {{ $corIcon }}">📁</span>
                <span class="font-semibold text-gray-800">{{ $pasta->nome }}</span>
                <span class="text-xs px-2 py-0.5 bg-white/70 text-gray-500 rounded-full">
                    {{ $demandasDaPasta->count() }} demanda(s)
                </span>
            </div>
            <svg :class="aberta ? 'rotate-180' : ''" class="w-4 h-4 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </div>
        <div x-show="aberta" x-collapse class="px-4 pb-4">
            @forelse($demandasDaPasta as $demanda)
                @include('demandas._card', ['demanda' => $demanda])
            @empty
                <p class="text-xs text-gray-400 italic py-2">Nenhuma demanda nesta pasta.</p>
            @endforelse
        </div>
    </div>
    @endforeach

    @if($demandasSemPasta->isNotEmpty())
    <div class="border border-gray-200 bg-gray-50 rounded-xl mb-4 overflow-hidden" x-data="{ aberta: false }">
        <div class="flex items-center justify-between px-4 py-3 cursor-pointer" @click="aberta = !aberta">
            <div class="flex items-center gap-2">
                <span class="text-lg text-gray-400">📂</span>
                <span class="font-semibold text-gray-600">Sem pasta</span>
                <span class="text-xs px-2 py-0.5 bg-white/70 text-gray-500 rounded-full">
                    {{ $demandasSemPasta->count() }} demanda(s)
                </span>
            </div>
            <svg :class="aberta ? 'rotate-180' : ''" class="w-4 h-4 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </div>
        <div x-show="aberta" x-collapse class="px-4 pb-4">
            @foreach($demandasSemPasta as $demanda)
                @include('demandas._card', ['demanda' => $demanda])
            @endforeach
        </div>
    </div>
    @endif

    @if($pastas->isEmpty() && $demandas->isEmpty())
    <div class="text-center py-16 text-gray-400">
        <div class="text-4xl mb-3">📁</div>
        <p class="text-sm">Nenhuma pasta criada ainda.</p>
        <button @click="abrirModal()" class="mt-3 text-sm text-indigo-600 hover:underline">Criar primeira pasta</button>
    </div>
    @endif
</div>

{{-- ===== MODAL GERENCIAR PASTAS ===== --}}
<div x-show="modal" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     @click.self="fecharModal()">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md z-10">
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h2 class="text-lg font-bold text-gray-900">📁 Gerenciar Pastas</h2>
            <button @click="fecharModal()" class="text-gray-400 hover:text-gray-700 text-xl leading-none">✕</button>
        </div>

        <div class="px-6 py-4 max-h-80 overflow-y-auto space-y-2">
            <template x-for="pasta in pastas" :key="pasta.id">
                <div class="flex items-center gap-2 group">
                    {{-- Bolinha de cor --}}
                    <select x-model="pasta.cor" @change="salvarPasta(pasta)"
                            class="w-8 h-8 rounded-full border-2 border-white shadow cursor-pointer appearance-none text-center text-xs"
                            :style="`background-color: ${corHex(pasta.cor)}`"
                            title="Cor da pasta">
                        @foreach(['gray'=>'Cinza','red'=>'Vermelho','orange'=>'Laranja','yellow'=>'Amarelo','green'=>'Verde','blue'=>'Azul','purple'=>'Roxo'] as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>

                    {{-- Nome editável --}}
                    <input type="text" x-model="pasta.nome"
                           @blur="salvarPasta(pasta)"
                           @keydown.enter="salvarPasta(pasta); $event.target.blur()"
                           class="flex-1 text-sm border border-transparent rounded-lg px-2 py-1 focus:outline-none focus:border-indigo-400 focus:bg-indigo-50 hover:border-gray-300 bg-transparent">

                    {{-- Excluir --}}
                    <button @click="excluirPasta(pasta)"
                            class="opacity-0 group-hover:opacity-100 text-gray-300 hover:text-red-500 transition-all text-sm px-1">✕</button>
                </div>
            </template>

            <div x-show="pastas.length === 0" class="text-sm text-gray-400 italic py-2 text-center">
                Nenhuma pasta ainda. Crie a primeira abaixo.
            </div>
        </div>

        {{-- Nova pasta --}}
        <div class="px-6 py-4 border-t bg-gray-50 rounded-b-2xl">
            <div class="flex gap-2">
                <select x-model="novaCor"
                        class="w-8 h-8 rounded-full border-2 border-white shadow cursor-pointer appearance-none"
                        :style="`background-color: ${corHex(novaCor)}`">
                    @foreach(['gray'=>'Cinza','red'=>'Vermelho','orange'=>'Laranja','yellow'=>'Amarelo','green'=>'Verde','blue'=>'Azul','purple'=>'Roxo'] as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
                <input type="text" x-model="nomeNova" @keydown.enter="criarPasta()"
                       placeholder="Nome da nova pasta..."
                       class="flex-1 text-sm border rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <button @click="criarPasta()"
                        class="px-4 py-1.5 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 font-medium">
                    + Criar
                </button>
            </div>
        </div>
    </div>
</div>

</div>{{-- /x-data --}}

<script>
function painelPastas() {
    return {
        vista: localStorage.getItem('dashboard_vista') || 'lista',
        modal: false,
        pastas: @json($pastas),
        nomeNova: '',
        novaCor: 'gray',
        csrf: document.querySelector('meta[name="csrf-token"]').content,

        init() {
            this.$watch('vista', v => localStorage.setItem('dashboard_vista', v));
        },

        abrirModal() { this.modal = true },
        fecharModal() { this.modal = false },

        async criarPasta() {
            const nome = this.nomeNova.trim();
            if (!nome) return;
            const res  = await this.req('POST', '/pastas', { nome, cor: this.novaCor });
            const nova = await res.json();
            this.pastas.push(nova);
            this.nomeNova = '';
        },

        async salvarPasta(pasta) {
            await this.req('PATCH', `/pastas/${pasta.id}`, { nome: pasta.nome, cor: pasta.cor });
        },

        async excluirPasta(pasta) {
            if (!confirm(`Excluir a pasta "${pasta.nome}"? As demandas não serão apagadas.`)) return;
            await this.req('DELETE', `/pastas/${pasta.id}`);
            this.pastas = this.pastas.filter(p => p.id !== pasta.id);
        },

        req(method, url, data = null) {
            return fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf },
                body: data ? JSON.stringify(data) : null,
            });
        },

        corHex(cor) {
            return { gray:'#e5e7eb', red:'#fca5a5', orange:'#fdba74', yellow:'#fef08a', green:'#86efac', blue:'#93c5fd', purple:'#d8b4fe' }[cor] ?? '#e5e7eb';
        },
    }
}
</script>
@endsection
