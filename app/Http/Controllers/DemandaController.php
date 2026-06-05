<?php

namespace App\Http\Controllers;

use App\Http\Requests\DemandaRequest;
use App\Models\Demanda;
use App\Models\Categoria;
use App\Models\Pasta;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class DemandaController extends Controller
{
    public function index(Request $request)
    {
        $query = Demanda::with('links', 'pasta', 'checklistItems')->orderByRaw("
            CASE urgencia
                WHEN 'urgente' THEN 1
                WHEN 'alta'    THEN 2
                WHEN 'media'   THEN 3
                WHEN 'baixa'   THEN 4
            END
        ")->orderBy('data_limite');

        $filtro = $request->get('filtro', 'todos');

        match ($filtro) {
            'atrasadas'  => $query->atrasadas(),
            'urgentes'   => $query->urgentes(),
            'hoje'       => $query->where('data_limite', today())->pendentes(),
            'semana'     => $query->semana(),
            'pendentes'  => $query->pendentes(),
            default      => $query->pendentes(),
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

        $demandas   = $query->get();
        $pastas     = Pasta::orderBy('nome')->get();
        $categorias = Categoria::orderBy('nome')->get();

        // Quando filtro='todos' a coleção já tem tudo — derivar sem queries extras.
        // Nos outros filtros a coleção está recortada, então consultar o banco.
        if ($filtro === 'todos' || $filtro === 'pendentes') {
            $base = $demandas->where('status', 'pendente');
            $stats = [
                'total'     => $base->count(),
                'urgentes'  => $base->where('urgencia', 'urgente')->count(),
                'atrasadas' => $base->filter(fn($d) => $d->isAtrasada())->count(),
                'semana'    => $base->filter(fn($d) =>
                                   $d->data_limite->between(today(), today()->addDays(7))
                               )->count(),
            ];
        } else {
            $stats = [
                'total'     => Demanda::pendentes()->count(),
                'urgentes'  => Demanda::urgentes()->count(),
                'atrasadas' => Demanda::atrasadas()->count(),
                'semana'    => Demanda::semana()->count(),
            ];
        }

        return view('demandas.index', compact('demandas', 'stats', 'filtro', 'pastas', 'categorias'));
    }

    public function create()
    {
        $pastas     = Pasta::orderBy('nome')->get();
        $categorias = Categoria::orderBy('nome')->get();
        return view('demandas.create', compact('pastas', 'categorias'));
    }

    public function store(DemandaRequest $request)
    {
        $demanda = Demanda::create($request->except(['links', 'checklist']));
        $this->syncLinks($demanda, $request->input('links', []));
        $this->syncChecklist($demanda, $request->input('checklist', []));
        return redirect()->route('dashboard')->with('success', 'Demanda criada com sucesso.');
    }

    public function show(Demanda $demanda)
    {
        $demanda->load('links', 'pasta', 'checklistItems');
        return view('demandas.show', compact('demanda'));
    }

    public function edit(Demanda $demanda)
    {
        $demanda->load('links', 'checklistItems');
        $pastas     = Pasta::orderBy('nome')->get();
        $categorias = Categoria::orderBy('nome')->get();
        return view('demandas.edit', compact('demanda', 'pastas', 'categorias'));
    }

    public function update(DemandaRequest $request, Demanda $demanda)
    {
        $demanda->update($request->except(['links', 'checklist']));
        $this->syncLinks($demanda, $request->input('links', []));
        $this->syncChecklist($demanda, $request->input('checklist', []));
        return redirect()->route('dashboard')->with('success', 'Demanda atualizada.');
    }

    public function destroy(Demanda $demanda)
    {
        $demanda->delete();
        return redirect()->route('dashboard')->with('success', 'Demanda excluída.');
    }

    public function concluir(Demanda $demanda)
    {
        $eraConcluido = $demanda->status === 'concluido';
        $demanda->status = $eraConcluido ? 'pendente' : 'concluido';
        $demanda->saveQuietly();

        if (!$eraConcluido && $demanda->recorrente && $demanda->frequencia) {
            $novaLimite = match ($demanda->frequencia) {
                'semanal'   => $demanda->data_limite->addWeek(),
                'quinzenal' => $demanda->data_limite->addDays(15),
                'mensal'    => $demanda->data_limite->addMonth(),
                'anual'     => $demanda->data_limite->addYear(),
                default     => null,
            };

            if ($novaLimite) {
                $nova = $demanda->replicate(['status', 'auto_escalado', 'google_event_id']);
                $nova->status        = 'pendente';
                $nova->auto_escalado = false;
                $nova->data_limite   = $novaLimite;
                $nova->save();

                foreach ($demanda->checklistItems as $item) {
                    $nova->checklistItems()->create(['texto' => $item->texto, 'ordem' => $item->ordem]);
                }
            }
        }

        return back()->with('success', 'Status atualizado.');
    }

    public function moverPasta(Request $request, Demanda $demanda)
    {
        $demanda->updateQuietly(['pasta_id' => $request->input('pasta_id')]);
        return response()->json(['ok' => true]);
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

    private function syncChecklist(Demanda $demanda, array $items): void
    {
        $demanda->checklistItems()->delete();
        foreach (array_values(array_filter($items)) as $ordem => $texto) {
            $demanda->checklistItems()->create(['texto' => $texto, 'ordem' => $ordem]);
        }
    }
}
