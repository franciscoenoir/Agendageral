<?php

namespace App\Console\Commands;

use App\Jobs\EnviarAlertaEmailJob;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:verificar-alertas')]
#[Description('Verifica demandas críticas e envia alerta por e-mail')]
class VerificarAlertas extends Command
{
    public function handle(): void
    {
        $this->info('Verificando demandas para alerta...');
        EnviarAlertaEmailJob::dispatch();
        $this->info('Job de alerta despachado.');
    }
}
