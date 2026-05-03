<?php

namespace App\Http\Controllers;

use App\Models\Configuracao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GoogleController extends Controller
{
    private string $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth';
    private string $tokenUrl = 'https://oauth2.googleapis.com/token';

    public function redirect()
    {
        $params = http_build_query([
            'client_id'     => Configuracao::get('google_client_id'),
            'redirect_uri'  => route('google.callback'),
            'response_type' => 'code',
            'scope'         => 'https://www.googleapis.com/auth/calendar',
            'access_type'   => 'offline',
            'prompt'        => 'consent',
        ]);

        return redirect("{$this->authUrl}?{$params}");
    }

    public function callback(Request $request)
    {
        if (!$request->has('code')) {
            return redirect()->route('configuracoes')->with('error', 'Autorização negada pelo Google.');
        }

        $response = Http::post($this->tokenUrl, [
            'code'          => $request->code,
            'client_id'     => Configuracao::get('google_client_id'),
            'client_secret' => Configuracao::get('google_client_secret'),
            'redirect_uri'  => route('google.callback'),
            'grant_type'    => 'authorization_code',
        ]);

        if ($response->failed()) {
            return redirect()->route('configuracoes')->with('error', 'Erro ao obter token do Google.');
        }

        $data = $response->json();
        Configuracao::set('google_access_token', $data['access_token']);
        Configuracao::set('google_token_expiry', now()->addSeconds($data['expires_in'])->timestamp);

        if (isset($data['refresh_token'])) {
            Configuracao::set('google_refresh_token', $data['refresh_token']);
        }

        return redirect()->route('configuracoes')->with('success', 'Google Calendar conectado com sucesso!');
    }
}
