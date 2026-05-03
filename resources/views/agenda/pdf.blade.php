<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Agenda</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 10px;
    color: #1f2937;
    background: #f3f4f6;
    padding: 18px;
}

/* ---- Topo ---- */
.topo {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 14px;
}
.topo-titulo { font-size: 17px; font-weight: bold; color: #111827; }
.topo-data   { font-size: 9px; color: #6b7280; }

/* ---- Título de seção ---- */
.section-label {
    font-size: 10px;
    font-weight: bold;
    color: #374151;
    border-left: 3px solid #3b82f6;
    padding-left: 7px;
    margin-bottom: 8px;
}

/* ======= AGENDA SEMANAL ======= */
/* Wrapper via table para espaçamento entre cards */
.semana-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 5px;
    margin-bottom: 20px;
    table-layout: fixed;
}
.semana-table td { vertical-align: top; padding: 0; width: 14.28%; }

/* Card do dia — div interno (border-radius não funciona em td no DomPDF) */
.day-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 9px 8px;
    min-height: 148px;
}
.day-card.hoje-card {
    background: #eff6ff;
    border: 2px solid #60a5fa;
}

.day-abbr        { font-size: 8px; font-weight: bold; color: #9ca3af; margin-bottom: 2px; }
.day-abbr.hoje   { color: #1d4ed8; }
.day-num         { font-size: 19px; font-weight: bold; color: #111827; margin-bottom: 7px; }
.day-num.hoje    { color: #1d4ed8; }
.day-mmdd        { font-size: 8px; color: #9ca3af; margin-bottom: 7px; }
.day-mmdd.hoje   { color: #3b82f6; }

/* Pills semanais */
.pill {
    display: block;
    padding: 3px 6px;
    border-radius: 6px;
    margin-bottom: 3px;
    font-size: 8px;
    font-weight: 600;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}
.pill.urgente { background: #fee2e2; color: #991b1b; }
.pill.alta    { background: #ffedd5; color: #9a3412; }
.pill.media   { background: #dbeafe; color: #1e40af; }
.pill.baixa   { background: #dcfce7; color: #166534; }
.pill-empty   { font-size: 8px; color: #d1d5db; font-style: italic; }

/* ======= AGENDA MENSAL ======= */
.mes-wrapper {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 12px;
}
.mes-header-row {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}
.mes-header-row th {
    padding: 5px 2px;
    text-align: center;
    font-size: 8px;
    font-weight: bold;
    color: #6b7280;
    background: #f8fafc;
    border-bottom: 1px solid #e5e7eb;
}
.mes-header-row th.fds { color: #ef4444; }

.mes-grid {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}
.mes-grid td {
    border: 1px solid #f1f5f9;
    vertical-align: top;
    padding: 4px 3px;
    width: 14.28%;
    height: 58px;
    background: #fff;
}
.mes-grid td.outro-mes { background: #f9fafb; }
.mes-grid td.hoje-cel  { background: #eff6ff; border: 2px solid #93c5fd; }

.mnum-wrap { margin-bottom: 3px; }
.mnum {
    display: inline-block;
    width: 17px;
    height: 17px;
    font-size: 8.5px;
    font-weight: bold;
    text-align: center;
    line-height: 17px;
    border-radius: 50%;
    color: #374151;
}
.mnum.hoje-num    { background: #2563eb; color: #ffffff; }
.mnum.outro-num   { color: #d1d5db; }
.mnum.fds-num     { color: #ef4444; }

/* Pills mensais */
.mpill {
    display: block;
    padding: 1px 4px;
    border-radius: 3px;
    font-size: 6.5px;
    font-weight: 600;
    margin-bottom: 2px;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}
.mpill.urgente { background: #fee2e2; color: #991b1b; }
.mpill.alta    { background: #ffedd5; color: #9a3412; }
.mpill.media   { background: #dbeafe; color: #1e40af; }
.mpill.baixa   { background: #dcfce7; color: #166534; }

/* ---- Legenda ---- */
.legenda { margin-top: 8px; }
.legenda table { border-collapse: collapse; }
.legenda td { padding-right: 14px; font-size: 8px; white-space: nowrap; }
.ldot {
    display: inline-block;
    width: 9px;
    height: 9px;
    border-radius: 3px;
    margin-right: 4px;
    vertical-align: middle;
}
</style>
</head>
<body>

{{-- Topo --}}
<div class="topo">
    <div class="topo-titulo">📅 Agenda</div>
    <div class="topo-data">Gerado em {{ $hoje->locale('pt_BR')->translatedFormat('d \d\e F \d\e Y') }}</div>
</div>

{{-- ===== SEMANAL ===== --}}
<div class="section-label">Agenda Semanal — próximos 7 dias</div>

<table class="semana-table">
<tr>
@foreach($semana as $s)
@php $isHoje = $s['data']->isToday(); @endphp
<td>
    <div class="day-card {{ $isHoje ? 'hoje-card' : '' }}">
        <div class="day-abbr {{ $isHoje ? 'hoje' : '' }}">
            {{ mb_strtoupper($s['data']->locale('pt_BR')->translatedFormat('D')) }}
        </div>
        <div class="day-num {{ $isHoje ? 'hoje' : '' }}">
            {{ $s['data']->format('d') }}
        </div>
        <div class="day-mmdd {{ $isHoje ? 'hoje' : '' }}">
            {{ $s['data']->format('d/m') }}
        </div>

        @forelse($s['demandas'] as $d)
            <span class="pill {{ $d->urgencia }}">{{ $d->titulo }}</span>
        @empty
            <span class="pill-empty">—</span>
        @endforelse
    </div>
</td>
@endforeach
</tr>
</table>

{{-- ===== MENSAL ===== --}}
<div class="section-label">{{ ucfirst($nomeMes) }}</div>

<div class="mes-wrapper">
    {{-- Cabeçalho dias da semana --}}
    <table class="mes-header-row">
        <tr>
            @foreach(['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'] as $i => $dn)
            <th class="{{ in_array($i,[0,6]) ? 'fds' : '' }}">{{ $dn }}</th>
            @endforeach
        </tr>
    </table>

    {{-- Grade de dias --}}
    <table class="mes-grid">
        @foreach(array_chunk($diasMes, 7) as $row)
        <tr>
            @foreach($row as $colIdx => $cell)
            @if($cell === null)
                <td class="outro-mes"></td>
            @else
            @php
                $isFds    = in_array($colIdx, [0, 6]);
                $numClass = $cell['hoje'] ? 'hoje-num' : ($cell['fds'] ? 'fds-num' : '');
            @endphp
            <td class="{{ $cell['hoje'] ? 'hoje-cel' : '' }}">
                <div class="mnum-wrap">
                    <span class="mnum {{ $numClass }}">{{ $cell['data']->day }}</span>
                </div>
                @foreach($cell['demandas'] as $d)
                    <span class="mpill {{ $d->urgencia }}">{{ $d->titulo }}</span>
                @endforeach
            </td>
            @endif
            @endforeach
        </tr>
        @endforeach
    </table>
</div>

{{-- Legenda --}}
<div class="legenda">
    <table><tr>
        @foreach([
            'urgente' => ['#fee2e2','#991b1b','Urgente'],
            'alta'    => ['#ffedd5','#9a3412','Alta'],
            'media'   => ['#dbeafe','#1e40af','Média'],
            'baixa'   => ['#dcfce7','#166534','Baixa'],
        ] as $k => $v)
        <td>
            <span class="ldot" style="background:{{ $v[0] }}"></span>
            <span style="color:{{ $v[1] }}; font-size:8px; font-weight:600;">{{ $v[2] }}</span>
        </td>
        @endforeach
    </tr></table>
</div>

</body>
</html>
