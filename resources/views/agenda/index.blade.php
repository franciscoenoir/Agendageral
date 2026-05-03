@extends('layouts.app')
@section('title', 'Agenda')

@section('content')
<div x-data="agenda()" x-init="carregar()">

    {{-- ===== AGENDA SEMANAL ===== --}}
    <div class="mb-8">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-xl font-bold text-gray-900">Agenda Semanal</h1>
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-500">{{ now()->locale('pt_BR')->translatedFormat('d \d\e F \d\e Y') }}</span>
                <a href="{{ route('agenda.pdf') }}"
                   class="text-xs px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium">
                    📄 Exportar PDF
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <div class="grid grid-cols-7 gap-2 min-w-[700px]">
                @php $diasSemana = ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb']; @endphp
                @for($i = 0; $i < 7; $i++)
                    @php $dia = now()->addDays($i); $isHoje = $dia->isToday(); @endphp
                    <div class="rounded-xl {{ $isHoje ? 'bg-blue-50 ring-2 ring-blue-400' : 'bg-white' }} shadow-sm p-3 min-h-[160px]">
                        <div class="text-xs font-semibold {{ $isHoje ? 'text-blue-700' : 'text-gray-500' }} mb-0.5">
                            {{ $diasSemana[$dia->dayOfWeek] }}
                        </div>
                        <div class="text-lg font-bold {{ $isHoje ? 'text-blue-700' : 'text-gray-800' }} mb-2">
                            {{ $dia->format('d/m') }}
                        </div>
                        <template x-for="d in demandasDoDia('{{ $dia->toDateString() }}')" :key="d.id">
                            <a :href="`/demandas/${d.id}`"
                               class="block mb-1.5 px-2 py-1 rounded-lg text-xs font-medium truncate"
                               :class="{
                                   'bg-red-100 text-red-800':       d.urgencia === 'urgente',
                                   'bg-orange-100 text-orange-800': d.urgencia === 'alta',
                                   'bg-blue-100 text-blue-800':     d.urgencia === 'media',
                                   'bg-green-100 text-green-800':   d.urgencia === 'baixa',
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
    </div>

    {{-- ===== AGENDA MENSAL ===== --}}
    <div>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-900">Agenda Mensal</h2>
            <div class="flex items-center gap-3">
                <button @click="mesAnterior()" class="w-8 h-8 flex items-center justify-center rounded-full bg-white shadow hover:bg-gray-50 text-gray-600">‹</button>
                <span class="text-sm font-semibold text-gray-700 min-w-[120px] text-center" x-text="nomeMes()"></span>
                <button @click="proximoMes()" class="w-8 h-8 flex items-center justify-center rounded-full bg-white shadow hover:bg-gray-50 text-gray-600">›</button>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            {{-- Cabeçalho dias da semana --}}
            <div class="grid grid-cols-7 border-b">
                @foreach(['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'] as $d)
                <div class="py-2 text-center text-xs font-semibold text-gray-500 {{ $loop->first || $loop->last ? 'text-red-400' : '' }}">
                    {{ $d }}
                </div>
                @endforeach
            </div>

            {{-- Grade de dias --}}
            <div class="grid grid-cols-7">
                <template x-for="(dia, i) in diasDoMes" :key="i">
                    <div
                        class="min-h-[90px] p-1.5 border-b border-r relative"
                        :class="{
                            'bg-gray-50': !dia.mesAtual,
                            'bg-blue-50': dia.hoje,
                            'border-blue-300': dia.hoje,
                        }"
                    >
                        {{-- Número do dia --}}
                        <div class="text-xs font-semibold mb-1 flex items-center justify-center w-6 h-6 rounded-full"
                             :class="{
                                 'bg-blue-600 text-white': dia.hoje,
                                 'text-gray-300': !dia.mesAtual,
                                 'text-gray-700': dia.mesAtual && !dia.hoje,
                                 'text-red-400':  dia.mesAtual && !dia.hoje && (i % 7 === 0 || i % 7 === 6),
                             }"
                             x-text="dia.numero">
                        </div>

                        {{-- Demandas do dia --}}
                        <template x-for="d in demandasMensal(dia.data)" :key="d.id">
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

</div>

<script>
function agenda() {
    return {
        demandas: [],
        demandasMes: [],
        mesAtual: new Date().getMonth(),
        anoAtual: new Date().getFullYear(),

        async carregar() {
            // Semanal
            const res = await fetch('/agenda/data');
            this.demandas = await res.json();

            // Mensal (mês corrente ao iniciar)
            await this.carregarMes();
        },

        async carregarMes() {
            const inicio = new Date(this.anoAtual, this.mesAtual, 1);
            const fim    = new Date(this.anoAtual, this.mesAtual + 1, 0);
            const fmt    = d => d.toISOString().split('T')[0];
            const res    = await fetch(`/agenda/data?inicio=${fmt(inicio)}&fim=${fmt(fim)}`);
            this.demandasMes = await res.json();
        },

        demandasDoDia(data) {
            return this.demandas.filter(d => d.data_limite === data);
        },

        demandasMensal(data) {
            return this.demandasMes.filter(d => d.data_limite === data);
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

        get diasDoMes() {
            const hoje     = new Date();
            const primeiro = new Date(this.anoAtual, this.mesAtual, 1);
            const ultimo   = new Date(this.anoAtual, this.mesAtual + 1, 0);
            const dias     = [];

            // Dias do mês anterior para completar a primeira semana
            for (let i = 0; i < primeiro.getDay(); i++) {
                const d = new Date(this.anoAtual, this.mesAtual, -primeiro.getDay() + i + 1);
                dias.push({ numero: d.getDate(), data: this.fmtData(d), mesAtual: false, hoje: false });
            }

            // Dias do mês
            for (let d = 1; d <= ultimo.getDate(); d++) {
                const dt = new Date(this.anoAtual, this.mesAtual, d);
                dias.push({
                    numero: d,
                    data:   this.fmtData(dt),
                    mesAtual: true,
                    hoje: dt.toDateString() === hoje.toDateString(),
                });
            }

            // Dias do próximo mês para completar a última semana
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
