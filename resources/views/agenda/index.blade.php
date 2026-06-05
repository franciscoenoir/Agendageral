@extends('layouts.app')
@section('title', 'Agenda')

@section('content')
<div x-data="agenda()" x-init="carregar()">

    {{-- Cabeçalho --}}
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
            <button @click="mesAnterior()" class="w-8 h-8 flex items-center justify-center rounded-full bg-white shadow hover:bg-gray-50 text-gray-600 text-lg">‹</button>
            <h1 class="text-xl font-bold text-gray-900 min-w-[180px] text-center capitalize" x-text="nomeMes()"></h1>
            <button @click="proximoMes()" class="w-8 h-8 flex items-center justify-center rounded-full bg-white shadow hover:bg-gray-50 text-gray-600 text-lg">›</button>
        </div>
        <div class="flex items-center gap-2">
            <button @click="irParaHoje()" class="text-xs px-3 py-1.5 bg-white border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-50 font-medium">
                Hoje
            </button>
            <a :href="`/agenda/pdf/mensal?mes=${mesAtual+1}&ano=${anoAtual}`"
               class="text-xs px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium">
                📄 Exportar PDF
            </a>
        </div>
    </div>

    {{-- Grade mensal --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        {{-- Cabeçalho dias da semana --}}
        <div class="grid grid-cols-7 border-b">
            @foreach(['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'] as $d)
            <div class="py-2 text-center text-xs font-semibold text-gray-500 {{ $loop->first || $loop->last ? 'text-red-400' : '' }}">
                {{ $d }}
            </div>
            @endforeach
        </div>

        {{-- Células --}}
        <div class="grid grid-cols-7">
            <template x-for="(dia, i) in diasDoMes" :key="i">
                <div
                    class="min-h-[110px] p-1.5 border-b border-r relative"
                    :class="{
                        'bg-gray-50': !dia.mesAtual,
                        'bg-blue-50 border-blue-200': dia.hoje,
                    }"
                >
                    <div class="text-xs font-semibold mb-1 flex items-center justify-center w-6 h-6 rounded-full"
                         :class="{
                             'bg-blue-600 text-white': dia.hoje,
                             'text-gray-300': !dia.mesAtual,
                             'text-gray-700': dia.mesAtual && !dia.hoje,
                             'text-red-400':  dia.mesAtual && !dia.hoje && (i % 7 === 0 || i % 7 === 6),
                         }"
                         x-text="dia.numero">
                    </div>

                    <template x-for="d in demandasDoDia(dia.data)" :key="d.id">
                        <a :href="`/demandas/${d.id}`"
                           class="block mb-0.5 px-1.5 py-0.5 rounded text-xs truncate leading-tight"
                           :class="{
                               'bg-red-100 text-red-700':       d.urgencia === 'urgente',
                               'bg-orange-100 text-orange-700': d.urgencia === 'alta',
                               'bg-blue-100 text-blue-700':     d.urgencia === 'media',
                               'bg-green-100 text-green-700':   d.urgencia === 'baixa',
                           }"
                           :title="d.titulo"
                           x-text="d.titulo">
                        </a>
                    </template>
                </div>
            </template>
        </div>
    </div>

    {{-- Legenda --}}
    <div class="flex gap-4 mt-3 text-xs text-gray-500">
        @foreach(['bg-red-100 text-red-700'=>'Urgente','bg-orange-100 text-orange-700'=>'Alta','bg-blue-100 text-blue-700'=>'Média','bg-green-100 text-green-700'=>'Baixa'] as $cls => $label)
        <span class="flex items-center gap-1">
            <span class="w-3 h-3 rounded {{ explode(' ', $cls)[0] }} inline-block"></span>{{ $label }}
        </span>
        @endforeach
    </div>

</div>

<script>
function agenda() {
    return {
        demandas: [],
        mesAtual: new Date().getMonth(),
        anoAtual: new Date().getFullYear(),

        async carregar() {
            await this.carregarMes();
        },

        async carregarMes() {
            const inicio = new Date(this.anoAtual, this.mesAtual, 1);
            const fim    = new Date(this.anoAtual, this.mesAtual + 1, 0);
            const fmt    = d => d.toISOString().split('T')[0];
            const res    = await fetch(`/agenda/data?inicio=${fmt(inicio)}&fim=${fmt(fim)}`);
            this.demandas = await res.json();
        },

        demandasDoDia(data) {
            return this.demandas.filter(d => d.data_limite === data);
        },

        nomeMes() {
            return new Date(this.anoAtual, this.mesAtual, 1)
                .toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' });
        },

        async mesAnterior() {
            if (this.mesAtual === 0) { this.mesAtual = 11; this.anoAtual--; }
            else { this.mesAtual--; }
            await this.carregarMes();
        },

        async proximoMes() {
            if (this.mesAtual === 11) { this.mesAtual = 0; this.anoAtual++; }
            else { this.mesAtual++; }
            await this.carregarMes();
        },

        async irParaHoje() {
            this.mesAtual = new Date().getMonth();
            this.anoAtual = new Date().getFullYear();
            await this.carregarMes();
        },

        get diasDoMes() {
            const hoje     = new Date();
            const primeiro = new Date(this.anoAtual, this.mesAtual, 1);
            const ultimo   = new Date(this.anoAtual, this.mesAtual + 1, 0);
            const dias     = [];

            for (let i = 0; i < primeiro.getDay(); i++) {
                const d = new Date(this.anoAtual, this.mesAtual, -primeiro.getDay() + i + 1);
                dias.push({ numero: d.getDate(), data: this.fmtData(d), mesAtual: false, hoje: false });
            }

            for (let d = 1; d <= ultimo.getDate(); d++) {
                const dt = new Date(this.anoAtual, this.mesAtual, d);
                dias.push({
                    numero: d,
                    data:   this.fmtData(dt),
                    mesAtual: true,
                    hoje: dt.toDateString() === hoje.toDateString(),
                });
            }

            const restantes = 7 - (dias.length % 7);
            if (restantes < 7) {
                for (let i = 1; i <= restantes; i++) {
                    const d = new Date(this.anoAtual, this.mesAtual + 1, i);
                    dias.push({ numero: d.getDate(), data: this.fmtData(d), mesAtual: false, hoje: false });
                }
            }

            return dias;
        },

        fmtData(d) {
            return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
        },
    }
}
</script>
@endsection
