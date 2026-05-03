@extends('layouts.app')
@section('title', 'Busca')

@section('content')
<div class="max-w-2xl mx-auto">

    {{-- Campo de busca --}}
    <form method="GET" action="{{ route('busca') }}" class="mb-6">
        <div class="flex gap-2">
            <input type="text" name="q" value="{{ $q }}" autofocus
                   placeholder="Buscar demandas, lembretes..."
                   class="flex-1 border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm">
            <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white text-sm rounded-xl hover:bg-blue-700 font-medium">
                🔍 Buscar
            </button>
        </div>
    </form>

    @if(strlen($q) < 2 && $q !== '')
        <p class="text-sm text-gray-400 text-center">Digite ao menos 2 caracteres.</p>
    @elseif(strlen($q) >= 2)

        @php $totalResultados = $demandas->count() + $concluidas->count() + $lembretes->count(); @endphp

        @if($totalResultados === 0)
        <div class="text-center py-16 text-gray-300">
            <div class="text-4xl mb-3">🔍</div>
            <p class="text-sm">Nenhum resultado para <strong class="text-gray-400">{{ $q }}</strong>.</p>
        </div>
        @else
        <p class="text-xs text-gray-400 mb-4">{{ $totalResultados }} resultado(s) para "<strong>{{ $q }}</strong>"</p>

        {{-- Demandas pendentes --}}
        @if($demandas->isNotEmpty())
        <div class="mb-5">
            <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">📋 Demandas ({{ $demandas->count() }})</h2>
            @foreach($demandas as $d)
            @php
                $cor = match($d->urgencia) {
                    'urgente' => 'border-red-400',
                    'alta'    => 'border-orange-400',
                    'media'   => 'border-blue-400',
                    'baixa'   => 'border-green-400',
                    default   => 'border-gray-300',
                };
            @endphp
            <a href="{{ route('demandas.show', $d) }}"
               class="flex items-start gap-3 bg-white rounded-xl shadow-sm border-l-4 {{ $cor }} px-4 py-3 mb-2 hover:shadow-md transition-shadow">
                <div class="flex-1 min-w-0">
                    <div class="font-semibold text-gray-800 text-sm truncate">{{ $d->titulo }}</div>
                    <div class="text-xs text-gray-400 mt-0.5 flex gap-3">
                        <span>{{ $d->categoria }}</span>
                        @if($d->responsavel)<span>👤 {{ $d->responsavel }}</span>@endif
                        <span class="{{ $d->isAtrasada() ? 'text-red-500 font-semibold' : '' }}">📅 {{ $d->data_limite->format('d/m/Y') }}</span>
                    </div>
                </div>
                <span class="text-xs px-2 py-0.5 rounded-full shrink-0
                    {{ $d->urgencia === 'urgente' ? 'bg-red-100 text-red-700' : '' }}
                    {{ $d->urgencia === 'alta'    ? 'bg-orange-100 text-orange-700' : '' }}
                    {{ $d->urgencia === 'media'   ? 'bg-blue-100 text-blue-700' : '' }}
                    {{ $d->urgencia === 'baixa'   ? 'bg-green-100 text-green-700' : '' }}">
                    {{ ucfirst($d->urgencia) }}
                </span>
            </a>
            @endforeach
        </div>
        @endif

        {{-- Histórico --}}
        @if($concluidas->isNotEmpty())
        <div class="mb-5">
            <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">🗂️ Histórico ({{ $concluidas->count() }})</h2>
            @foreach($concluidas as $d)
            <a href="{{ route('demandas.show', $d) }}"
               class="flex items-center gap-3 bg-white rounded-xl shadow-sm px-4 py-3 mb-2 opacity-70 hover:opacity-100 transition-opacity">
                <span class="text-gray-400 line-through text-sm flex-1 truncate">{{ $d->titulo }}</span>
                <span class="text-xs text-gray-400 shrink-0">{{ $d->updated_at->format('d/m/Y') }}</span>
            </a>
            @endforeach
        </div>
        @endif

        {{-- Lembretes --}}
        @if($lembretes->isNotEmpty())
        <div class="mb-5">
            <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">🗒️ Lembretes ({{ $lembretes->count() }})</h2>
            @foreach($lembretes as $l)
            <a href="{{ route('lembretes') }}"
               class="block bg-yellow-50 border border-yellow-200 rounded-xl px-4 py-3 mb-2 text-sm text-gray-700 hover:bg-yellow-100 transition-colors line-clamp-2">
                {{ $l->texto }}
            </a>
            @endforeach
        </div>
        @endif

        @endif
    @else
    <div class="text-center py-16 text-gray-300">
        <div class="text-4xl mb-3">🔍</div>
        <p class="text-sm">Digite algo para buscar em demandas e lembretes.</p>
    </div>
    @endif

</div>
@endsection
