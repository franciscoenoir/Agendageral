<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Demandas da Semana</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        .sub { color: #666; font-size: 11px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1e3a5f; color: white; padding: 6px 8px; text-align: left; font-size: 11px; }
        td { padding: 6px 8px; border-bottom: 1px solid #eee; vertical-align: top; }
        tr:nth-child(even) td { background: #f8f8f8; }
        .urgente { color: #dc2626; font-weight: bold; }
        .alta { color: #ea580c; }
        .media { color: #2563eb; }
        .baixa { color: #16a34a; }
        .atrasada { background: #fef2f2 !important; }
    </style>
</head>
<body>
    <h1>📋 Demandas da Semana</h1>
    <p class="sub">Gerado em {{ now()->format('d/m/Y \à\s H:i') }}</p>

    @if($demandas->isEmpty())
        <p>Nenhuma demanda para os próximos 7 dias.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Categoria</th>
                    <th>Urgência</th>
                    <th>Limite</th>
                    <th>Responsável</th>
                    <th>Observações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($demandas as $d)
                <tr class="{{ $d->isAtrasada() ? 'atrasada' : '' }}">
                    <td>{{ $d->titulo }}</td>
                    <td>{{ $d->categoria }}</td>
                    <td class="{{ $d->urgencia }}">{{ ucfirst($d->urgencia) }}</td>
                    <td>{{ $d->data_limite->format('d/m/Y') }}</td>
                    <td>{{ $d->responsavel ?? '-' }}</td>
                    <td>{{ Str::limit($d->observacoes, 60) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
