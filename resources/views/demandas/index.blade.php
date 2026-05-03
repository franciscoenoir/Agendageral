@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-gray-900">Dashboard</h1>
    <a href="{{ route('demandas.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 shadow-sm">
        ➕ Nova Demanda
    </a>
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    @foreach([
        ['label'=>'Pendentes','value'=>$stats['total'],'color'=>'blue'],
        ['label'=>'Urgentes','value'=>$stats['urgentes'],'color'=>'red'],
        ['label'=>'Atrasadas','value'=>$stats['atrasadas'],'color'=>'orange'],
        ['label'=>'Esta semana','value'=>$stats['semana'],'color'=>'green'],
    ] as $stat)
    <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-{{ $stat['color'] }}-500">
        <div class="text-2xl font-bold text-{{ $stat['color'] }}-600">{{ $stat['value'] }}</div>
        <div class="text-xs text-gray-500 mt-1">{{ $stat['label'] }}</div>
    </div>
    @endforeach
</div>

{{-- Filters + search --}}
<div class="bg-white rounded-xl shadow-sm p-4 mb-4">
    <div class="flex flex-wrap gap-2 mb-3">
        @foreach([
            'todos'=>'Todos','pendentes'=>'Pendentes','atrasadas'=>'🔴 Atrasadas',
            'urgentes'=>'🟠 Urgentes','hoje'=>'Hoje','semana'=>'Esta semana','concluidas'=>'Concluídas'
        ] as $key => $label)
        <a href="{{ route('dashboard', array_merge(request()->except('filtro'), ['filtro' => $key])) }}"
           class="px-3 py-1 rounded-full text-xs font-medium {{ $filtro === $key ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>
    <form method="GET" class="flex gap-2">
        <input type="hidden" name="filtro" value="{{ $filtro }}">
        <input type="text" name="busca" value="{{ request('busca') }}"
               placeholder="Buscar por título, observação ou responsável..."
               class="flex-1 text-sm border rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <select name="categoria" class="text-sm border rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Todas categorias</option>
            @foreach(['Engenharia','Firedrill','Rosa Garden','Particular','Família','Administrativo','Outro'] as $cat)
                <option value="{{ $cat }}" {{ request('categoria') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-1.5 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">Buscar</button>
        @if(request('busca') || request('categoria'))
            <a href="{{ route('dashboard', ['filtro' => $filtro]) }}" class="px-3 py-1.5 text-sm text-gray-500 hover:text-gray-800">✕</a>
        @endif
    </form>
</div>

{{-- Demand list --}}
@forelse($demandas as $demanda)
@php
    $borderColor = match($demanda->urgencia) {
        'urgente' => 'border-red-500',
        'alta'    => 'border-orange-400',
        'media'   => 'border-blue-400',
        'baixa'   => 'border-green-400',
        default   => 'border-gray-300',
    };
    $bgColor = $demanda->isAtrasada() ? 'bg-red-50' : 'bg-white';
@endphp
<div class="rounded-xl shadow-sm border-l-4 {{ $borderColor }} {{ $bgColor }} p-4 mb-3">
    <div class="flex flex-wrap items-start justify-between gap-2">
        <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-1">
                <span class="font-semibold text-gray-900 truncate">{{ $demanda->titulo }}</span>
                @if($demanda->auto_escalado)
                    <span class="text-xs px-2 py-0.5 bg-purple-100 text-purple-700 rounded-full">auto</span>
                @endif
                <span class="text-xs px-2 py-0.5 rounded-full
                    {{ $demanda->urgencia === 'urgente' ? 'bg-red-100 text-red-700' : '' }}
                    {{ $demanda->urgencia === 'alta' ? 'bg-orange-100 text-orange-700' : '' }}
                    {{ $demanda->urgencia === 'media' ? 'bg-blue-100 text-blue-700' : '' }}
                    {{ $demanda->urgencia === 'baixa' ? 'bg-green-100 text-green-700' : '' }}">
                    {{ ucfirst($demanda->urgencia) }}
                </span>
                <span class="text-xs px-2 py-0.5 rounded-full {{ $demanda->status === 'concluido' ? 'bg-gray-100 text-gray-500' : 'bg-blue-50 text-blue-600' }}">
                    {{ $demanda->status === 'concluido' ? 'Concluído' : 'Pendente' }}
                </span>
            </div>
            <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500">
                <span>📁 {{ $demanda->categoria }}</span>
                @if($demanda->responsavel)
                    <span>👤 {{ $demanda->responsavel }}</span>
                @endif
                @if($demanda->data_inicio)
                    <span>🗓 Início: {{ $demanda->data_inicio->format('d/m/Y') }}</span>
                @endif
                <span class="{{ $demanda->isAtrasada() ? 'text-red-600 font-semibold' : '' }}">
                    ⏱ Limite: {{ $demanda->data_limite->format('d/m/Y') }} ({{ $demanda->prazo_label }})
                </span>
            </div>
            @if($demanda->observacoes)
                <p class="mt-1 text-xs text-gray-600 line-clamp-2">{{ $demanda->observacoes }}</p>
            @endif
            @if($demanda->links->isNotEmpty())
                <div class="mt-1 flex flex-wrap gap-2">
                    @foreach($demanda->links as $link)
                        <a href="{{ $link->url }}" target="_blank"
                           class="text-xs text-blue-600 hover:underline">
                            🔗 {{ $link->label ?: $link->dominio }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <form method="POST" action="{{ route('demandas.concluir', $demanda) }}">
                @csrf @method('PATCH')
                <button type="submit"
                        class="text-xs px-2 py-1 rounded {{ $demanda->status === 'concluido' ? 'bg-gray-200 text-gray-600' : 'bg-green-100 text-green-700 hover:bg-green-200' }}">
                    {{ $demanda->status === 'concluido' ? '↩ Reabrir' : '✓ Concluir' }}
                </button>
            </form>
            <a href="{{ route('demandas.edit', $demanda) }}"
               class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200">✏️ Editar</a>
            <a href="{{ $demanda->googleCalendarUrl() }}" target="_blank"
               class="text-xs px-2 py-1 bg-blue-50 text-blue-700 rounded hover:bg-blue-100" title="Adicionar ao Google Agenda">
                📅
            </a>
            <form method="POST" action="{{ route('demandas.destroy', $demanda) }}"
                  onsubmit="return confirm('Excluir esta demanda?')">
                @csrf @method('DELETE')
                <button type="submit" class="text-xs px-2 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200">🗑</button>
            </form>
        </div>
    </div>
</div>
@empty
<div class="text-center py-16 text-gray-400">
    <div class="text-4xl mb-3">🎉</div>
    <p class="text-sm">Nenhuma demanda encontrada.</p>
    <a href="{{ route('demandas.create') }}" class="mt-3 inline-block text-sm text-blue-600 hover:underline">Criar nova demanda</a>
</div>
@endforelse
@endsection
