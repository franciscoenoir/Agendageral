<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Demanda;
use Illuminate\Http\Request;

class HistoricoController extends Controller
{
    public function index(Request $request)
    {
        $query = Demanda::where('status', 'concluido')
            ->with('links', 'pasta')
            ->orderBy('updated_at', 'desc');

        if ($busca = $request->get('busca')) {
            $query->where(fn($q) => $q
                ->where('titulo', 'like', "%{$busca}%")
                ->orWhere('observacoes', 'like', "%{$busca}%")
                ->orWhere('responsavel', 'like', "%{$busca}%")
            );
        }

        if ($categoria = $request->get('categoria')) {
            $query->where('categoria', $categoria);
        }

        if ($de = $request->get('de')) {
            $query->where('updated_at', '>=', $de . ' 00:00:00');
        }

        if ($ate = $request->get('ate')) {
            $query->where('updated_at', '<=', $ate . ' 23:59:59');
        }

        $demandas   = $query->paginate(30)->withQueryString();
        $total      = Demanda::where('status', 'concluido')->count();
        $categorias = Categoria::orderBy('nome')->get();

        return view('historico.index', compact('demandas', 'total', 'categorias'));
    }
}
