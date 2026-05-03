<?php

namespace App\Http\Controllers;

use App\Http\Requests\DemandaRequest;
use App\Models\Demanda;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class DemandaController extends Controller
{
    public function index(Request $request)
    {
        $query = Demanda::with('links')->orderByRaw("
            CASE urgencia
                WHEN 'urgente' THEN 1
                WHEN 'alta'    THEN 2
                WHEN 'media'   THEN 3
                WHEN 'baixa'   THEN 4
            END
        ")->orderBy('data_limite');

        $filtro = $request->get('filtro', 'todos');

        match ($filtro) {
            'atrasadas' => $query->atrasadas(),
            'urgentes'  => $query->urgentes(),
            'hoje'      => $query->where('data_limite', today())->pendentes(),
            'semana'    => $query->semana(),
            'pendentes' => $query->pendentes(),
            'concluidas'=> $query->where('status', 'concluido'),
            default     => null,
        };

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

        $demandas = $query->get();

        $stats = [
            'total'    => Demanda::pendentes()->count(),
            'urgentes' => Demanda::urgentes()->count(),
            'atrasadas'=> Demanda::atrasadas()->count(),
            'semana'   => Demanda::semana()->count(),
        ];

        return view('demandas.index', compact('demandas', 'stats', 'filtro'));
    }

    public function create()
    {
        return view('demandas.create');
    }

    public function store(DemandaRequest $request)
    {
        $demanda = Demanda::create($request->except('links'));

        $this->syncLinks($demanda, $request->input('links', []));

        return redirect()->route('dashboard')->with('success', 'Demanda criada com sucesso.');
    }

    public function show(Demanda $demanda)
    {
        $demanda->load('links');
        return view('demandas.show', compact('demanda'));
    }

    public function edit(Demanda $demanda)
    {
        $demanda->load('links');
        return view('demandas.edit', compact('demanda'));
    }

    public function update(DemandaRequest $request, Demanda $demanda)
    {
        $demanda->update($request->except('links'));

        $this->syncLinks($demanda, $request->input('links', []));

        return redirect()->route('dashboard')->with('success', 'Demanda atualizada.');
    }

    public function destroy(Demanda $demanda)
    {
        $demanda->delete();
        return redirect()->route('dashboard')->with('success', 'Demanda excluída.');
    }

    public function concluir(Demanda $demanda)
    {
        $demanda->status = $demanda->status === 'concluido' ? 'pendente' : 'concluido';
        $demanda->saveQuietly();

        return back()->with('success', 'Status atualizado.');
    }

    public function exportPdf()
    {
        $demandas = Demanda::semana()->with('links')->orderBy('data_limite')->get();
        $pdf = Pdf::loadView('demandas.pdf', compact('demandas'));
        return $pdf->download('demandas-semana.pdf');
    }

    private function syncLinks(Demanda $demanda, array $links): void
    {
        $demanda->links()->delete();

        foreach ($links as $link) {
            if (!empty($link['url'])) {
                $demanda->links()->create([
                    'url'   => $link['url'],
                    'label' => $link['label'] ?? null,
                ]);
            }
        }
    }
}
