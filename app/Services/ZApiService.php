<?php

namespace App\Services;

use App\Models\Configuracao;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZApiService
{
    private function baseUrl(): string
    {
        $instance = Configuracao::get('zapi_instance');
        return "https://api.z-api.io/instances/{$instance}/token/" . Configuracao::get('zapi_token');
    }

    public function enviarMensagem(string $numero, string $mensagem): bool
    {
        $instance = Configuracao::get('zapi_instance');
        $token = Configuracao::get('zapi_token');

        if (!$instance || !$token) return false;

        $response = Http::post("{$this->baseUrl()}/send-text", [
            'phone'   => $numero,
            'message' => $mensagem,
        ]);

        if ($response->failed()) {
            Log::error('Z-API: erro ao enviar mensagem', ['body' => $response->body()]);
        }

        return $response->successful();
    }

    public function interpretarMensagem(string $texto): ?array
    {
        if (!str_starts_with(trim($texto), '/demanda')) {
            return null;
        }

        $linhas = array_map('trim', explode("\n", $texto));
        $dados = [];

        foreach ($linhas as $linha) {
            if (str_starts_with($linha, 'Título:')) {
                $dados['titulo'] = trim(substr($linha, 7));
            } elseif (str_starts_with($linha, 'Categoria:')) {
                $dados['categoria'] = trim(substr($linha, 10));
            } elseif (str_starts_with($linha, 'Prazo:')) {
                $raw = trim(substr($linha, 6));
                $dados['data_limite'] = \Carbon\Carbon::createFromFormat('d/m/Y', $raw)?->toDateString();
            } elseif (str_starts_with($linha, 'Início:')) {
                $raw = trim(substr($linha, 7));
                $dados['data_inicio'] = \Carbon\Carbon::createFromFormat('d/m/Y', $raw)?->toDateString();
            } elseif (str_starts_with($linha, 'Urgência:')) {
                $dados['urgencia'] = trim(substr($linha, 9));
            } elseif (str_starts_with($linha, 'Obs:')) {
                $dados['observacoes'] = trim(substr($linha, 4));
            }
        }

        if (empty($dados['titulo']) || empty($dados['data_limite'])) {
            return null;
        }

        return $dados;
    }
}
