<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Agenda Mensal</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 10px;
    color: #1f2937;
    background: #ffffff;
    padding: 16px;
}

.topo {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 12px;
}
.topo-titulo { font-size: 18px; font-weight: bold; color: #111827; }
.topo-mes    { font-size: 13px; font-weight: bold; color: #3b82f6; margin-top: 2px; }
.topo-data   { font-size: 8px; color: #9ca3af; }

.mes-wrapper {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
}

.mes-header-row {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}
.mes-header-row th {
    padding: 7px 2px;
    text-align: center;
    font-size: 9px;
    font-weight: bold;
    color: #6b7280;
    background: #f8fafc;
    border-bottom: 2px solid #e5e7eb;
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
    padding: 5px 4px;
    width: 14.28%;
    height: 100px;
    background: #fff;
}
.mes-grid td.outro-mes { background: #f9fafb; }
.mes-grid td.hoje-cel  { background: #eff6ff; border: 2px solid #93c5fd; }

.mnum-wrap { margin-bottom: 4px; }
.mnum {
    display: inline-block;
    width: 20px;
    height: 20px;
    font-size: 9.5px;
    font-weight: bold;
    text-align: center;
    line-height: 20px;
    border-radius: 50%;
    color: #374151;
}
.mnum.hoje-num  { background: #2563eb; color: #ffffff; }
.mnum.outro-num { color: #d1d5db; }
.mnum.fds-num   { color: #ef4444; }

.mpill {
    display: block;
    padding: 2px 5px;
    border-radius: 4px;
    font-size: 7.5px;
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

<div class="topo">
    <div>
        <div class="topo-titulo">📅 Agenda Mensal</div>
        <div class="topo-mes">{{ ucfirst($nomeMes) }}</div>
    </div>
    <div class="topo-data">Gerado em {{ $hoje->locale('pt_BR')->translatedFormat('d \d\e F \d\e Y') }}</div>
</div>

<div class="mes-wrapper">
    <table class="mes-header-row">
        <tr>
            @foreach(['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'] as $i => $dn)
            <th class="{{ in_array($i, [0, 6]) ? 'fds' : '' }}">{{ $dn }}</th>
            @endforeach
        </tr>
    </table>

    <table class="mes-grid">
        @foreach(array_chunk($diasMes, 7) as $row)
        <tr>
            @foreach($row as $colIdx => $cell)
            @if($cell === null)
                <td class="outro-mes"></td>
            @else
            @php $numClass = $cell['hoje'] ? 'hoje-num' : ($cell['fds'] ? 'fds-num' : ''); @endphp
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

<div class="legenda">
    <table><tr>
        @foreach([
            ['#fee2e2','#991b1b','Urgente'],
            ['#ffedd5','#9a3412','Alta'],
            ['#dbeafe','#1e40af','Média'],
            ['#dcfce7','#166534','Baixa'],
        ] as $v)
        <td>
            <span class="ldot" style="background:{{ $v[0] }}"></span>
            <span style="color:{{ $v[1] }}; font-size:8px; font-weight:600;">{{ $v[2] }}</span>
        </td>
        @endforeach
    </tr></table>
</div>

</body>
</html>
