<?php

namespace App\Jobs;

use App\Mail\AlertaDemandaMail;
use App\Models\Configuracao;
use App\Services\AlertaService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class EnviarAlertaEmailJob implements ShouldQueue
{
    use Queueable;

    public function handle(AlertaService $service): void
    {
        $email = Configuracao::get('email_alertas');
        if (!$email) return;

        $demandas = $service->demandasParaAlertar();
        if ($demandas->isEmpty()) return;

        Mail::to($email)->send(new AlertaDemandaMail($demandas));
    }
}
