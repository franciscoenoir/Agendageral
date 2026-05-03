<?php

namespace App\Http\Controllers;

use App\Jobs\EnviarAlertaEmailJob;

class AlertaController extends Controller
{
    public function enviar()
    {
        EnviarAlertaEmailJob::dispatch();
        return back()->with('success', 'Alerta enviado por e-mail.');
    }
}
