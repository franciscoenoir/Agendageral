<?php

namespace App\Http\Controllers;

use App\Models\Demanda;
use App\Models\Lembrete;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));

        if (strlen($q) < 2) {
            return view('busca.index', ['q' => $q, 'demandas' => collect(), 'concluidas' => collect(), 'lembretes' => collect()]);
        }

        $like = "%{$q}%";

        $demandas = Demanda::pendentes()
            ->with('pasta')
            ->where(fn($query) => $query
                ->where('titulo', 'like', $like)
                ->orWhere('observacoes', 'like', $like)
                ->orWhere('responsavel', 'like', $like)
                ->orWhere('categoria', 'like', $like)
            )
            ->orderBy('data_limite')
            ->limit(15)
            ->get();

        $concluidas = Demanda::where('status', 'concluido')
            ->where(fn($query) => $query
                ->where('titulo', 'like', $like)
                ->orWhere('observacoes', 'like', $like)
                ->orWhere('responsavel', 'like', $like)
            )
            ->orderByDesc('updated_at')
            ->limit(8)
            ->get();

        $lembretes = Lembrete::where('texto', 'like', $like)->limit(8)->get();

        return view('busca.index', compact('q', 'demandas', 'concluidas', 'lembretes'));
    }
}
