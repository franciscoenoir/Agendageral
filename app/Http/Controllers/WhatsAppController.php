<?php

namespace App\Http\Controllers;

use App\Models\Configuracao;
use App\Models\Demanda;
use App\Services\ZApiService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WhatsAppController extends Controller
{
    public function webhook(Request $request, ZApiService $zapi): Response
    {
        $tokenEsperado = Configuracao::get('zapi_webhook_token');
        if ($tokenEsperado && $request->header('X-Webhook-Token') !== $tokenEsperado) {
            return response('Unauthorized', 401);
        }

        $texto = $request->input('text.message') ?? $request->input('body') ?? '';
        $numero = $request->input('phone') ?? '';

        $dados = $zapi->interpretarMensagem($texto);

        if (!$dados) {
            return response('ignored', 200);
        }

        $demanda = Demanda::create(array_merge(['status' => 'pendente'], $dados));

        $zapi->enviarMensagem($numero, "✅ Demanda criada: *{$demanda->titulo}* (prazo: {$demanda->data_limite->format('d/m/Y')})");

        return response('ok', 200);
    }
}
