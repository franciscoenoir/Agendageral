@extends('layouts.app')
@section('title', $demanda->titulo)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold text-gray-900">{{ $demanda->titulo }}</h1>
        <div class="flex gap-2">
            <a href="{{ route('demandas.edit', $demanda) }}" class="text-sm px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200">✏️ Editar</a>
            <a href="{{ route('dashboard') }}" class="text-sm text-gray-500 hover:text-gray-800">← Voltar</a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 space-y-4">
        <div class="flex flex-wrap gap-2">
            <span class="text-sm px-3 py-1 rounded-full
                {{ $demanda->urgencia === 'urgente' ? 'bg-red-100 text-red-700' : '' }}
                {{ $demanda->urgencia === 'alta' ? 'bg-orange-100 text-orange-700' : '' }}
                {{ $demanda->urgencia === 'media' ? 'bg-blue-100 text-blue-700' : '' }}
                {{ $demanda->urgencia === 'baixa' ? 'bg-green-100 text-green-700' : '' }}">
                {{ ucfirst($demanda->urgencia) }}
            </span>
            <span class="text-sm px-3 py-1 rounded-full {{ $demanda->status === 'concluido' ? 'bg-gray-100 text-gray-500' : 'bg-blue-50 text-blue-600' }}">
                {{ $demanda->status === 'concluido' ? 'Concluído' : 'Pendente' }}
            </span>
            @if($demanda->auto_escalado)
                <span class="text-sm px-3 py-1 bg-purple-100 text-purple-700 rounded-full">escalado automaticamente</span>
            @endif
        </div>

        <div class="grid grid-cols-2 gap-4 text-sm">
            <div><span class="text-gray-500">Categoria:</span> {{ $demanda->categoria }}</div>
            @if($demanda->responsavel)
                <div><span class="text-gray-500">Responsável:</span> {{ $demanda->responsavel }}</div>
            @endif
            @if($demanda->data_inicio)
                <div><span class="text-gray-500">Início:</span> {{ $demanda->data_inicio->format('d/m/Y') }}</div>
            @endif
            <div><span class="text-gray-500">Limite:</span>
                <span class="{{ $demanda->isAtrasada() ? 'text-red-600 font-semibold' : '' }}">
                    {{ $demanda->data_limite->format('d/m/Y') }} ({{ $demanda->prazo_label }})
                </span>
            </div>
        </div>

        @if($demanda->observacoes)
            <div>
                <div class="text-sm text-gray-500 mb-1">Observações:</div>
                <p class="text-sm text-gray-800 whitespace-pre-line">{{ $demanda->observacoes }}</p>
            </div>
        @endif

        {{-- Checklist --}}
        @if($demanda->checklistItems->isNotEmpty())
        <div x-data="checklistShow({{ $demanda->id }}, @json($demanda->checklistItems))">
            <div class="text-sm font-medium text-gray-700 mb-2">✅ Checklist
                <span class="text-xs font-normal text-gray-400 ml-1"
                      x-text="`(${items.filter(i=>i.concluido).length}/${items.length})`"></span>
            </div>
            {{-- barra de progresso --}}
            <div class="h-1.5 bg-gray-100 rounded-full mb-3 overflow-hidden">
                <div class="h-1.5 bg-green-500 rounded-full transition-all"
                     :style="`width:${items.length ? Math.round(items.filter(i=>i.concluido).length/items.length*100) : 0}%`"></div>
            </div>
            <template x-for="item in items" :key="item.id">
                <div class="flex items-center gap-2 py-1.5 border-b border-gray-50 group">
                    <input type="checkbox" :checked="item.concluido"
                           @change="toggle(item)"
                           class="rounded border-gray-300 text-green-600 focus:ring-green-500 cursor-pointer">
                    <span :class="item.concluido ? 'line-through text-gray-400' : 'text-gray-700'"
                          class="flex-1 text-sm" x-text="item.texto"></span>
                    <button @click="excluir(item)"
                            class="opacity-0 group-hover:opacity-100 text-red-400 hover:text-red-600 text-xs transition-opacity">✕</button>
                </div>
            </template>
            {{-- Adicionar inline --}}
            <div class="flex gap-2 mt-2">
                <input x-model="novoItem" @keydown.enter.prevent="adicionar()"
                       type="text" placeholder="Novo item..."
                       class="flex-1 text-sm border rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <button @click="adicionar()"
                        class="px-3 py-1.5 text-xs bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">+ Adicionar</button>
            </div>
        </div>
        @else
        <div x-data="checklistShow({{ $demanda->id }}, [])">
            <div class="flex items-center justify-between mb-1">
                <span class="text-sm font-medium text-gray-700">✅ Checklist</span>
            </div>
            <div class="flex gap-2">
                <input x-model="novoItem" @keydown.enter.prevent="adicionar()"
                       type="text" placeholder="Adicionar primeiro item..."
                       class="flex-1 text-sm border rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <button @click="adicionar()"
                        class="px-3 py-1.5 text-xs bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">+ Adicionar</button>
            </div>
        </div>
        @endif

        @if($demanda->links->isNotEmpty())
            <div>
                <div class="text-sm text-gray-500 mb-2">Links:</div>
                <div class="space-y-1">
                    @foreach($demanda->links as $link)
                        <a href="{{ $link->url }}" target="_blank"
                           class="flex items-center gap-2 text-sm text-blue-600 hover:underline">
                            🔗 {{ $link->label ?: $link->dominio }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="pt-4 border-t flex gap-3 flex-wrap">
            <a href="{{ $demanda->googleCalendarUrl() }}" target="_blank"
               class="px-4 py-2 text-sm bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100">
                📅 Adicionar ao Google Agenda
            </a>
            <form method="POST" action="{{ route('demandas.concluir', $demanda) }}">
                @csrf @method('PATCH')
                <button type="submit"
                        class="px-4 py-2 text-sm rounded-lg {{ $demanda->status === 'concluido' ? 'bg-gray-200 text-gray-700' : 'bg-green-600 text-white hover:bg-green-700' }}">
                    {{ $demanda->status === 'concluido' ? '↩ Reabrir' : '✓ Marcar concluído' }}
                </button>
            </form>
            <form method="POST" action="{{ route('demandas.destroy', $demanda) }}"
                  onsubmit="return confirm('Excluir esta demanda?')">
                @csrf @method('DELETE')
                <button type="submit" class="px-4 py-2 text-sm bg-red-100 text-red-700 rounded-lg hover:bg-red-200">🗑 Excluir</button>
            </form>
        </div>
    </div>
</div>
@endsection

<script>
function checklistShow(demandaId, initialItems) {
    return {
        items: initialItems,
        novoItem: '',
        csrf: document.querySelector('meta[name=csrf-token]').content,

        async toggle(item) {
            item.concluido = !item.concluido;
            await fetch(`/checklist/${item.id}`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf },
                body: JSON.stringify({ concluido: item.concluido }),
            });
        },

        async adicionar() {
            const texto = this.novoItem.trim();
            if (!texto) return;
            const r = await fetch(`/demandas/${demandaId}/checklist`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf },
                body: JSON.stringify({ texto }),
            });
            const item = await r.json();
            this.items.push(item);
            this.novoItem = '';
        },

        async excluir(item) {
            await fetch(`/checklist/${item.id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': this.csrf },
            });
            this.items = this.items.filter(i => i.id !== item.id);
        },
    };
}
</script>
