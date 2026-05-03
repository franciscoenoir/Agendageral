<?php

namespace App\Services;

use App\Models\Configuracao;
use App\Models\Demanda;
use Illuminate\Support\Collection;

class AlertaService
{
    public function demandasParaAlertar(): Collection
    {
        $diasAviso = json_decode(Configuracao::get('email_dias_aviso', '[1]'), true) ?? [1];

        return Demanda::pendentes()
            ->where(function ($q) use ($diasAviso) {
                $q->atrasadas();
                foreach ($diasAviso as $dias) {
                    $q->orWhere('data_limite', today()->addDays($dias));
                }
                $q->orWhere('urgencia', 'urgente');
            })
            ->with('links')
            ->orderBy('data_limite')
            ->get();
    }

    public function montarCorpo(Collection $demandas): string
    {
        $atrasadas = $demandas->filter(fn($d) => $d->isAtrasada());
        $urgentes  = $demandas->filter(fn($d) => $d->urgencia === 'urgente' && !$d->isAtrasada());
        $proximas  = $demandas->filter(fn($d) => !$d->isAtrasada() && $d->urgencia !== 'urgente');

        $linhas = ["=== Alerta de Demandas ===\n"];

        if ($atrasadas->isNotEmpty()) {
            $linhas[] = "\n🔴 ATRASADAS (" . $atrasadas->count() . ")";
            foreach ($atrasadas as $d) {
                $linhas[] = "- [{$d->categoria}] {$d->titulo} ({$d->prazo_label})";
            }
        }

        if ($urgentes->isNotEmpty()) {
            $linhas[] = "\n🟠 URGENTES (" . $urgentes->count() . ")";
            foreach ($urgentes as $d) {
                $linhas[] = "- [{$d->categoria}] {$d->titulo} ({$d->prazo_label})";
            }
        }

        if ($proximas->isNotEmpty()) {
            $linhas[] = "\n🟡 VENCENDO EM BREVE (" . $proximas->count() . ")";
            foreach ($proximas as $d) {
                $linhas[] = "- [{$d->categoria}] {$d->titulo} ({$d->prazo_label})";
            }
        }

        return implode("\n", $linhas);
    }
}
