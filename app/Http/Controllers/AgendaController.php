<?php

namespace App\Http\Controllers;

use App\Models\Demanda;
use Illuminate\Http\JsonResponse;

class AgendaController extends Controller
{
    public function index()
    {
        return view('agenda.index');
    }

    public function data(): JsonResponse
    {
        $demandas = Demanda::pendentes()
            ->whereBetween('data_limite', [today(), today()->addDays(6)])
            ->with('links')
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
