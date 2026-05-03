@extends('layouts.app')
@section('title', 'Valores a Receber')

@section('content')

{{-- Resumo financeiro --}}
<div class="flex flex-wrap gap-3 mb-4">
    <div class="flex-1 min-w-36 bg-white rounded-xl shadow-sm p-4 border-l-4 border-blue-500">
        <p class="text-xs text-gray-400 mb-0.5">Total a Receber</p>
        <p class="text-xl font-bold text-blue-600">R$ {{ number_format($totalGeral, 2, ',', '.') }}</p>
    </div>
    <div class="flex-1 min-w-36 bg-white rounded-xl shadow-sm p-4 border-l-4 border-green-500">
        <p class="text-xs text-gray-400 mb-0.5">Total Recebido</p>
        <p class="text-xl font-bold text-green-600">R$ {{ number_format($totalRecebido, 2, ',', '.') }}</p>
    </div>
    <div class="flex-1 min-w-36 bg-white rounded-xl shadow-sm p-4 border-l-4 border-orange-500">
        <p class="text-xs text-gray-400 mb-0.5">Saldo Pendente</p>
        <p class="text-xl font-bold text-orange-600">R$ {{ number_format($totalPendente, 2, ',', '.') }}</p>
    </div>
</div>

@if($demandas->isEmpty())
<div class="text-center py-20 text-gray-300">
    <div class="text-5xl mb-3">💰</div>
    <p class="text-sm">Nenhuma demanda com valor cadastrado.</p>
    <p class="text-xs mt-1">Adicione um valor R$ ao criar ou editar uma demanda.</p>
</div>
@else

@foreach($demandas as $demanda)
@php
    $pago     = $demanda->pagamentos->sum('valor');
    $pendente = $demanda->valor - $pago;
    $quitado  = $pendente <= 0;
    $pct      = $demanda->valor > 0 ? min(100, round($pago / $demanda->valor * 100)) : 0;
@endphp
<div class="bg-white rounded-xl shadow-sm mb-3 overflow-hidden" x-data="{ aberto: false }">
    {{-- Linha principal --}}
    <div class="flex flex-wrap items-center gap-3 p-4 cursor-pointer" @click="aberto = !aberto">
        <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-0.5">
                <span class="font-semibold text-gray-800 truncate">{{ $demanda->titulo }}</span>
                <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-500 rounded-full">{{ $demanda->categoria }}</span>
                @if($quitado)
                    <span class="text-xs px-2 py-0.5 bg-green-100 text-green-700 rounded-full">✓ Quitado</span>
                @elseif($pago > 0)
                    <span class="text-xs px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full">Parcial</span>
                @else
                    <span class="text-xs px-2 py-0.5 bg-red-50 text-red-500 rounded-full">Pendente</span>
                @endif
            </div>
            <div class="flex flex-wrap gap-x-4 text-xs text-gray-400">
                @if($demanda->responsavel)
                    <span>👤 {{ $demanda->responsavel }}</span>
                @endif
                <span>📅 Prazo: {{ $demanda->data_limite->format('d/m/Y') }}</span>
            </div>
        </div>

        <div class="text-right shrink-0">
            <div class="text-xs text-gray-400">Valor total</div>
            <div class="text-base font-bold text-gray-700">R$ {{ number_format($demanda->valor, 2, ',', '.') }}</div>
        </div>
        <div class="text-right shrink-0">
            <div class="text-xs text-gray-400">Recebido</div>
            <div class="text-base font-bold text-green-600">R$ {{ number_format($pago, 2, ',', '.') }}</div>
        </div>
        <div class="text-right shrink-0">
            <div class="text-xs text-gray-400">Pendente</div>
            <div class="text-base font-bold {{ $quitado ? 'text-gray-400' : 'text-orange-600' }}">
                R$ {{ number_format(max(0, $pendente), 2, ',', '.') }}
            </div>
        </div>
        <svg :class="aberto ? 'rotate-180' : ''" class="w-4 h-4 text-gray-400 transition-transform shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </div>

    {{-- Barra de progresso --}}
    <div class="h-1 bg-gray-100">
        <div class="h-1 {{ $quitado ? 'bg-green-500' : 'bg-blue-500' }} transition-all"
             style="width: {{ $pct }}%"></div>
    </div>

    {{-- Detalhes expandíveis --}}
    <div x-show="aberto" x-collapse class="border-t border-gray-100">
        <div class="p-4">

            {{-- Histórico de pagamentos --}}
            @if($demanda->pagamentos->isNotEmpty())
            <table class="w-full text-xs mb-4">
                <thead>
                    <tr class="text-gray-400 border-b">
                        <th class="text-left pb-1 font-medium">Data</th>
                        <th class="text-left pb-1 font-medium">Observação</th>
                        <th class="text-right pb-1 font-medium">Valor</th>
                        <th class="pb-1"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($demanda->pagamentos->sortByDesc('data') as $pgt)
                    <tr class="border-b border-gray-50">
                        <td class="py-1.5 text-gray-600">{{ $pgt->data->format('d/m/Y') }}</td>
                        <td class="py-1.5 text-gray-500">{{ $pgt->observacao ?: '—' }}</td>
                        <td class="py-1.5 text-right font-semibold text-green-700">R$ {{ number_format($pgt->valor, 2, ',', '.') }}</td>
                        <td class="py-1.5 text-right">
                            <form method="POST" action="{{ route('recebimentos.destroy', $pgt) }}"
                                  onsubmit="return confirm('Remover este pagamento?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-600">✕</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif

            {{-- Registrar novo pagamento --}}
            @if(!$quitado)
            <form method="POST" action="{{ route('recebimentos.store', $demanda) }}"
                  class="flex flex-wrap gap-2 items-end">
                @csrf
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Valor recebido (R$) *</label>
                    <input type="number" name="valor" min="0.01" step="0.01"
                           placeholder="0,00" required
                           class="border rounded-lg px-3 py-1.5 text-sm w-36 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Data *</label>
                    <input type="date" name="data" value="{{ today()->format('Y-m-d') }}" required
                           class="border rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div class="flex-1 min-w-32">
                    <label class="block text-xs text-gray-500 mb-1">Observação</label>
                    <input type="text" name="observacao" placeholder="Ex: PIX, NF 123..."
                           class="w-full border rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <button type="submit"
                        class="px-4 py-1.5 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 font-medium shrink-0">
                    ✓ Dar baixa
                </button>
            </form>
            @else
            <p class="text-xs text-green-600 font-medium">✓ Demanda totalmente quitada.</p>
            @endif

        </div>
    </div>
</div>
@endforeach

@endif

@endsection
