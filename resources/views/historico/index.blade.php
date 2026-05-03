@extends('layouts.app')
@section('title', 'Histórico')

@section('content')
<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="text-xl font-bold text-gray-900">🗂️ Histórico</h1>
        <p class="text-xs text-gray-400 mt-0.5">{{ number_format($total) }} demanda(s) concluída(s) no total</p>
    </div>
</div>

{{-- Filtros --}}
<form method="GET" class="bg-white rounded-xl shadow-sm p-4 mb-4">
    <div class="flex flex-wrap gap-3">
        <input type="text" name="busca" value="{{ request('busca') }}"
               placeholder="Buscar por título, observação ou responsável..."
               class="flex-1 min-w-48 text-sm border rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">

        <select name="categoria" class="text-sm border rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Todas categorias</option>
            @foreach($categorias as $cat)
                <option value="{{ $cat->nome }}" {{ request('categoria') === $cat->nome ? 'selected' : '' }}>{{ $cat->nome }}</option>
            @endforeach
        </select>

        <div class="flex items-center gap-1 text-sm">
            <span class="text-gray-500 text-xs">De</span>
            <input type="date" name="de" value="{{ request('de') }}"
                   class="border rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="flex items-center gap-1 text-sm">
            <span class="text-gray-500 text-xs">Até</span>
            <input type="date" name="ate" value="{{ request('ate') }}"
                   class="border rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <button type="submit"
                class="px-4 py-1.5 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">
            Filtrar
        </button>

        @if(request('busca') || request('categoria') || request('de') || request('ate'))
            <a href="{{ route('historico') }}"
               class="px-3 py-1.5 text-sm text-gray-500 hover:text-gray-800">✕ Limpar</a>
        @endif
    </div>
</form>

{{-- Lista --}}
@forelse($demandas as $demanda)
@php
    $foiAtrasada = $demanda->data_limite < $demanda->updated_at->toDateString();
    $borderColor = match($demanda->urgencia) {
        'urgente' => 'border-red-400',
        'alta'    => 'border-orange-400',
        'media'   => 'border-blue-400',
        'baixa'   => 'border-green-400',
        default   => 'border-gray-300',
    };
@endphp
<div class="bg-white rounded-xl shadow-sm border-l-4 {{ $borderColor }} p-4 mb-2 opacity-80 hover:opacity-100 transition-opacity">
    <div class="flex flex-wrap items-start justify-between gap-2">
        <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-1">
                <span class="font-semibold text-gray-700 truncate line-through decoration-gray-400">{{ $demanda->titulo }}</span>

                @if($demanda->pasta)
                    <span class="text-xs px-2 py-0.5 bg-indigo-50 text-indigo-600 rounded-full">📁 {{ $demanda->pasta->nome }}</span>
                @endif

                <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-500 rounded-full">{{ ucfirst($demanda->urgencia) }}</span>

                @if($foiAtrasada)
                    <span class="text-xs px-2 py-0.5 bg-red-50 text-red-500 rounded-full">⚠ Concluída em atraso</span>
                @else
                    <span class="text-xs px-2 py-0.5 bg-green-50 text-green-600 rounded-full">✓ Concluída no prazo</span>
                @endif

                @if($demanda->auto_escalado)
                    <span class="text-xs px-2 py-0.5 bg-purple-50 text-purple-500 rounded-full">auto</span>
                @endif
            </div>

            <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-400">
                <span>📁 {{ $demanda->categoria }}</span>
                @if($demanda->responsavel)
                    <span>👤 {{ $demanda->responsavel }}</span>
                @endif
                <span>📅 Prazo: {{ $demanda->data_limite->format('d/m/Y') }}</span>
                <span>✓ Concluída: {{ $demanda->updated_at->format('d/m/Y \à\s H:i') }}</span>
            </div>

            @if($demanda->observacoes)
                <p class="mt-1 text-xs text-gray-400 line-clamp-1">{{ $demanda->observacoes }}</p>
            @endif
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('demandas.show', $demanda) }}"
               class="text-xs px-2 py-1 bg-gray-100 text-gray-600 rounded hover:bg-gray-200">
                👁 Ver
            </a>
            <form method="POST" action="{{ route('demandas.concluir', $demanda) }}">
                @csrf @method('PATCH')
                <button type="submit"
                        class="text-xs px-2 py-1 bg-blue-50 text-blue-600 rounded hover:bg-blue-100">
                    ↩ Reabrir
                </button>
            </form>
        </div>
    </div>
</div>
@empty
<div class="text-center py-16 text-gray-300">
    <div class="text-5xl mb-3">🗂️</div>
    <p class="text-sm">Nenhuma demanda concluída encontrada.</p>
</div>
@endforelse

{{-- Paginação --}}
@if($demandas->hasPages())
    <div class="mt-4">
        {{ $demandas->links() }}
    </div>
@endif

@endsection
