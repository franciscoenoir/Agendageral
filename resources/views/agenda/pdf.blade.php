<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Agenda</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #333; background: #f3f4f6; }

        /* ---- Cabeçalho ---- */
        .header { background: #1e293b; color: #fff; padding: 10px 16px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-radius: 8px; }
        .header h1 { font-size: 15px; font-weight: bold; }
        .header span { font-size: 9px; color: #94a3b8; }

        /* ---- Seção ---- */
        .section-title { font-size: 11px; font-weight: bold; color: #1e293b; margin-bottom: 6px; padding-left: 2px; }

        /* ========= SEMANAL ========= */
        .week-grid { width: 100%; border-collapse: separate; border-spacing: 4px; margin-bottom: 14px; }
        .week-grid td { width: 14.28%; vertical-align: top; background: #fff; border-radius: 8px; padding: 7px; min-height: 90px; border: 1px solid #e5e7eb; }
        .week-grid td.hoje { background: #eff6ff; border: 2px solid #60a5fa; }
        .day-label { font-size: 8px; font-weight: bold; color: #6b7280; margin-bottom: 1px; }
        .day-label.hoje { color: #2563eb; }
        .day-num  { font-size: 14px; font-weight: bold; color: #1f2937; margin-bottom: 5px; }
        .day-num.hoje  { color: #2563eb; }

        /* ========= MENSAL ========= */
        .month-grid { width: 100%; border-collapse: collapse; }
        .month-grid th { background: #f8fafc; color: #6b7280; font-size: 8px; font-weight: bold; text-align: center; padding: 4px 2px; border: 1px solid #e5e7eb; }
        .month-grid th.fds { color: #ef4444; }
        .month-grid td { vertical-align: top; border: 1px solid #e5e7eb; padding: 3px; min-height: 54px; background: #fff; width: 14.28%; }
        .month-grid td.outro-mes { background: #f9fafb; }
        .month-grid td.hoje-mes  { background: #eff6ff; border: 2px solid #60a5fa; }
        .mday { font-size: 9px; font-weight: bold; color: #374151; margin-bottom: 2px; display: inline-block; width: 18px; height: 18px; text-align: center; line-height: 18px; border-radius: 50%; }
        .mday.hoje-circle { background: #2563eb; color: #fff; }
        .mday.fds-color { color: #ef4444; }

        /* ---- Pills de demanda ---- */
        .pill { display: block; padding: 2px 4px; border-radius: 4px; margin-bottom: 2px; font-size: 7.5px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .pill.urgente { background: #fee2e2; color: #991b1b; }
        .pill.alta    { background: #ffedd5; color: #9a3412; }
        .pill.media   { background: #dbeafe; color: #1e40af; }
        .pill.baixa   { background: #dcfce7; color: #166534; }

        .mpill { display: block; padding: 1px 3px; border-radius: 3px; margin-bottom: 1px; font-size: 6.5px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .mpill.urgente { background: #fee2e2; color: #991b1b; }
        .mpill.alta    { background: #ffedd5; color: #9a3412; }
        .mpill.media   { background: #dbeafe; color: #1e40af; }
        .mpill.baixa   { background: #dcfce7; color: #166534; }

        .empty { color: #d1d5db; font-style: italic; font-size: 8px; }

        /* ---- Legenda ---- */
        .legend { margin-top: 8px; display: table; }
        .legend-item { display: table-cell; padding-right: 12px; font-size: 8px; color: #6b7280; }
        .legend-dot { display: inline-block; width: 8px; height: 8px; border-radius: 2px; margin-right: 3px; vertical-align: middle; }
    </style>
</head>
<body>

<div class="header">
    <h1>📅 Agenda</h1>
    <span>Gerado em {{ $hoje->locale('pt_BR')->translatedFormat('d \d\e F \d\e Y') }}</span>
</div>

{{-- ===== SEMANAL ===== --}}
<div class="section-title">Agenda Semanal — próximos 7 dias</div>
<table class="week-grid">
    <tr>
        @foreach($semana as $s)
        @php $isHoje = $s['data']->isToday(); @endphp
        <td class="{{ $isHoje ? 'hoje' : '' }}">
            <div class="day-label {{ $isHoje ? 'hoje' : '' }}">
                {{ $s['data']->locale('pt_BR')->translatedFormat('D') }}
            </div>
            <div class="day-num {{ $isHoje ? 'hoje' : '' }}">
                {{ $s['data']->format('d/m') }}
            </div>
            @forelse($s['demandas'] as $d)
                <span class="pill {{ $d->urgencia }}">{{ $d->titulo }}</span>
            @empty
                <span class="empty">—</span>
            @endforelse
        </td>
        @endforeach
    </tr>
</table>

{{-- ===== MENSAL ===== --}}
<div class="section-title">{{ ucfirst($nomeMes) }}</div>
<table class="month-grid">
    <thead>
        <tr>
            @foreach(['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'] as $i => $d)
            <th class="{{ in_array($i, [0,6]) ? 'fds' : '' }}">{{ $d }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach(array_chunk($diasMes, 7) as $semanaRow)
        <tr>
            @foreach($semanaRow as $cell)
            @if($cell === null)
                <td class="outro-mes"></td>
            @else
            @php
                $colIdx = $loop->index;
                $isFds  = in_array($colIdx, [0, 6]);
            @endphp
            <td class="{{ $cell['hoje'] ? 'hoje-mes' : '' }}">
                <div class="mday {{ $cell['hoje'] ? 'hoje-circle' : ($isFds ? 'fds-color' : '') }}">
                    {{ $cell['data']->day }}
                </div>
                @foreach($cell['demandas'] as $d)
                    <span class="mpill {{ $d->urgencia }}">{{ $d->titulo }}</span>
                @endforeach
            </td>
            @endif
            @endforeach
        </tr>
        @endforeach
    </tbody>
</table>

{{-- Legenda --}}
<div class="legend">
    @foreach(['urgente'=>['#fee2e2','#991b1b','Urgente'],'alta'=>['#ffedd5','#9a3412','Alta'],'media'=>['#dbeafe','#1e40af','Média'],'baixa'=>['#dcfce7','#166534','Baixa']] as $k => $v)
    <div class="legend-item">
        <span class="legend-dot" style="background:{{ $v[0] }};"></span>
        <span style="color:{{ $v[1] }}">{{ $v[2] }}</span>
    </div>
    @endforeach
</div>

</body>
</html>
