<?php

namespace App\Http\Controllers;

use App\Models\Demanda;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgendaController extends Controller
{
    public function index()
    {
        return view('agenda.index');
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
