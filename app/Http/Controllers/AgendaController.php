<?php

namespace App\Http\Controllers;

use App\Models\Demanda;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgendaController extends Controller
{
    public function index()
    {
        return view('agenda.index');
    }

    public function exportPdf()
    {
        // Semanal: próximos 7 dias
        $semanaInicio = today();
        $semanaFim    = today()->addDays(6);
        $demandas     = Demanda::pendentes()
            ->whereBetween('data_limite', [$semanaInicio, $semanaFim])
            ->orderBy('data_limite')
            ->get();

        $semana = [];
        for ($i = 0; $i < 7; $i++) {
            $dia = today()->addDays($i);
            $semana[] = [
                'data'     => $dia,
                'demandas' => $demandas->filter(fn($d) => $d->data_limite->toDateString() === $dia->toDateString()),
            ];
        }

        // Mensal: mês corrente
        $hoje      = today();
        $primeiro  = $hoje->copy()->startOfMonth();
        $ultimo    = $hoje->copy()->endOfMonth();
        $mesDemandas = Demanda::pendentes()
            ->whereBetween('data_limite', [$primeiro, $ultimo])
            ->orderBy('data_limite')
            ->get()
            ->groupBy(fn($d) => $d->data_limite->toDateString());

        // Montar grade mensal (células com padding de dias de outros meses)
        $diasMes = [];
        $offset  = $primeiro->dayOfWeek; // 0=Dom
        for ($i = 0; $i < $offset; $i++) {
            $diasMes[] = null;
        }
        for ($d = 1; $d <= $ultimo->day; $d++) {
            $dt = $primeiro->copy()->addDays($d - 1);
            $diasMes[] = [
                'data'     => $dt,
                'hoje'     => $dt->isToday(),
                'fds'      => $dt->isWeekend(),
                'demandas' => $mesDemandas->get($dt->toDateString(), collect()),
                'mesAtual' => true,
            ];
        }
        // Completar última semana
        $resto = count($diasMes) % 7;
        if ($resto > 0) {
            for ($i = 0; $i < (7 - $resto); $i++) {
                $diasMes[] = null;
            }
        }

        $nomeMes = $primeiro->locale('pt_BR')->translatedFormat('F \d\e Y');

        $pdf = Pdf::loadView('agenda.pdf', compact('semana', 'diasMes', 'nomeMes', 'hoje'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('agenda-' . $hoje->format('Y-m') . '.pdf');
    }

    public function exportPdfMensal(Request $request)
    {
        $hoje     = today();
        $mes      = (int) $request->input('mes', $hoje->month);
        $ano      = (int) $request->input('ano', $hoje->year);

        $primeiro    = Carbon::create($ano, $mes, 1);
        $ultimo      = $primeiro->copy()->endOfMonth();
        $mesDemandas = Demanda::pendentes()
            ->whereBetween('data_limite', [$primeiro, $ultimo])
            ->orderBy('data_limite')
            ->get()
            ->groupBy(fn($d) => $d->data_limite->toDateString());

        $diasMes = [];
        $offset  = $primeiro->dayOfWeek;
        for ($i = 0; $i < $offset; $i++) {
            $diasMes[] = null;
        }
        for ($d = 1; $d <= $ultimo->day; $d++) {
            $dt = $primeiro->copy()->addDays($d - 1);
            $diasMes[] = [
                'data'     => $dt,
                'hoje'     => $dt->isToday(),
                'fds'      => $dt->isWeekend(),
                'demandas' => $mesDemandas->get($dt->toDateString(), collect()),
            ];
        }
        $resto = count($diasMes) % 7;
        if ($resto > 0) {
            for ($i = 0; $i < (7 - $resto); $i++) {
                $diasMes[] = null;
            }
        }

        $nomeMes = $primeiro->locale('pt_BR')->translatedFormat('F \d\e Y');

        $pdf = Pdf::loadView('agenda.pdf-mensal', compact('diasMes', 'nomeMes', 'hoje', 'primeiro'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('agenda-mensal-' . $primeiro->format('Y-m') . '.pdf');
    }

    public function data(Request $request): JsonResponse
    {
        $inicio = $request->input('inicio') ? Carbon::parse($request->input('inicio')) : today();
        $fim    = $request->input('fim')    ? Carbon::parse($request->input('fim'))    : today()->addDays(6);

        $demandas = Demanda::pendentes()
            ->whereBetween('data_limite', [$inicio, $fim])
            ->orderBy('data_limite')
            ->get()
            ->map(fn($d) => [
                'id'          => $d->id,
                'titulo'      => $d->titulo,
                'categoria'   => $d->categoria,
                'urgencia'    => $d->urgencia,
                'data_limite' => $d->data_limite->toDateString(),
                'prazo_label' => $d->prazo_label,
                'responsavel' => $d->responsavel,
            ]);

        return response()->json($demandas);
    }
}
