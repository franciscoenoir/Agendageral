@php
    $borderColor = match($demanda->urgencia) {
        'urgente' => 'border-red-500',
        'alta'    => 'border-orange-400',
        'media'   => 'border-blue-400',
        'baixa'   => 'border-green-400',
        default   => 'border-gray-300',
    };
    $bgColor = $demanda->isAtrasada() ? 'bg-red-50' : 'bg-white';
    $checkTotal    = $demanda->relationLoaded('checklistItems') ? $demanda->checklistItems->count() : 0;
    $checkConcluidos = $demanda->relationLoaded('checklistItems') ? $demanda->checklistItems->where('concluido', true)->count() : 0;
@endphp
<div class="rounded-xl shadow-sm border-l-4 {{ $borderColor }} {{ $bgColor }} p-4 mb-3">
    <div class="flex flex-wrap items-start justify-between gap-2">
        <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-1">
                <span class="font-semibold text-gray-900 truncate">{{ $demanda->titulo }}</span>
                @if($demanda->pasta)
                    <span class="text-xs px-2 py-0.5 bg-indigo-50 text-indigo-600 rounded-full">📁 {{ $demanda->pasta->nome }}</span>
                @endif
                @if($demanda->auto_escalado)
                    <span class="text-xs px-2 py-0.5 bg-purple-100 text-purple-700 rounded-full">auto</span>
                @endif
                @if($demanda->recorrente)
                    <span class="text-xs px-2 py-0.5 bg-sky-100 text-sky-700 rounded-full">🔁 {{ $demanda->frequencia }}</span>
                @endif
                @if($checkTotal > 0)
                    <span class="text-xs px-2 py-0.5 {{ $checkConcluidos === $checkTotal ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }} rounded-full">
                        ✅ {{ $checkConcluidos }}/{{ $checkTotal }}
                    </span>
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
            @if($demanda->links->isNotEmpty())
                <div class="mt-1 flex flex-wrap gap-2">
                    @foreach($demanda->links as $link)
                        <a href="{{ $link->url }}" target="_blank" class="text-xs text-blue-600 hover:underline">
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
            @if($demanda->status !== 'concluido')
            <form method="POST" action="{{ route('demandas.adiar', $demanda) }}">
                @csrf @method('PATCH')
                <button type="submit" title="Adiar prazo em 1 dia"
                        class="text-xs px-2 py-1 bg-yellow-100 text-yellow-700 rounded hover:bg-yellow-200">
                    +1d
                </button>
            </form>
            @endif
            <a href="{{ route('demandas.edit', $demanda) }}"
               class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200">✏️ Editar</a>
            <a href="{{ $demanda->googleCalendarUrl() }}" target="_blank"
               class="text-xs px-2 py-1 bg-blue-50 text-blue-700 rounded hover:bg-blue-100" title="Adicionar ao Google Agenda">📅</a>
            <form method="POST" action="{{ route('demandas.destroy', $demanda) }}"
                  onsubmit="return confirm('Excluir esta demanda?')">
                @csrf @method('DELETE')
                <button type="submit" class="text-xs px-2 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200">🗑</button>
            </form>
        </div>
    </div>
</div>
