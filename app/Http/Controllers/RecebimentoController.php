<?php

namespace App\Http\Controllers;

use App\Models\Demanda;
use App\Models\Pagamento;
use Illuminate\Http\Request;

class RecebimentoController extends Controller
{
    public function index()
    {
        $demandas = Demanda::whereNotNull('valor')
            ->with('pagamentos')
            ->orderBy('data_limite')
            ->get();

        $totalGeral    = $demandas->sum('valor');
        $totalRecebido = $demandas->sum(fn($d) => $d->pagamentos->sum('valor'));
        $totalPendente = $totalGeral - $totalRecebido;

        return view('recebimentos.index', compact('demandas', 'totalGeral', 'totalRecebido', 'totalPendente'));
    }

    public function store(Request $request, Demanda $demanda)
    {
        $request->validate([
            'valor'      => 'required|numeric|min:0.01',
            'data'       => 'required|date',
            'observacao' => 'nullable|string|max:200',
        ]);

        $demanda->pagamentos()->create([
            'valor'      => $request->valor,
            'data'       => $request->data,
            'observacao' => $request->observacao,
        ]);

        return back()->with('success', 'Pagamento registrado.');
    }

    public function destroy(Pagamento $pagamento)
    {
        $pagamento->delete();

        return back()->with('success', 'Pagamento removido.');
    }
}
