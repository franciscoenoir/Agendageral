@extends('layouts.app')
@section('title', 'Agenda Semanal')

@section('content')
<div class="mb-4 flex items-center justify-between">
    <h1 class="text-xl font-bold text-gray-900">Agenda Semanal</h1>
    <span class="text-sm text-gray-500">{{ now()->format('d/m/Y') }}</span>
</div>

<div x-data="agenda()" x-init="carregar()" class="overflow-x-auto">
    <div class="grid grid-cols-7 gap-2 min-w-[700px]">
        @php
            $diasSemana = ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'];
        @endphp
        @for($i = 0; $i < 7; $i++)
            @php
                $dia = now()->addDays($i);
                $isHoje = $dia->isToday();
            @endphp
            <div class="rounded-xl {{ $isHoje ? 'bg-blue-50 ring-2 ring-blue-400' : 'bg-white' }} shadow-sm p-3 min-h-[180px]">
                <div class="text-xs font-semibold {{ $isHoje ? 'text-blue-700' : 'text-gray-500' }} mb-1">
                    {{ $diasSemana[$dia->dayOfWeek] }}
                </div>
                <div class="text-lg font-bold {{ $isHoje ? 'text-blue-700' : 'text-gray-800' }} mb-2">
                    {{ $dia->format('d/m') }}
                </div>
                <template x-for="d in demandasDoDia('{{ $dia->toDateString() }}')" :key="d.id">
                    <a :href="`/demandas/${d.id}`"
                       class="block mb-1.5 px-2 py-1.5 rounded-lg text-xs font-medium truncate cursor-pointer"
                       :class="{
                           'bg-red-100 text-red-800':    d.urgencia === 'urgente',
                           'bg-orange-100 text-orange-800': d.urgencia === 'alta',
                           'bg-blue-100 text-blue-800':  d.urgencia === 'media',
                           'bg-green-100 text-green-800': d.urgencia === 'baixa',
                       }"
                       :title="d.titulo">
                        <span x-text="d.titulo"></span>
                    </a>
                </template>
                <template x-if="demandasDoDia('{{ $dia->toDateString() }}').length === 0">
                    <div class="text-xs text-gray-300 italic">—</div>
                </template>
            </div>
        @endfor
    </div>
</div>

<script>
function agenda() {
    return {
        demandas: [],
        async carregar() {
            const res = await fetch('/agenda/data');
            this.demandas = await res.json();
        },
        demandasDoDia(data) {
            return this.demandas.filter(d => d.data_limite === data);
        }
    }
}
</script>
@endsection
