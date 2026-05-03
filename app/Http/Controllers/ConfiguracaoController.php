<?php

namespace App\Http\Controllers;

use App\Models\Configuracao;
use Illuminate\Http\Request;

class ConfiguracaoController extends Controller
{
    public function index()
    {
        $config = Configuracao::pluck('valor', 'chave');
        return view('configuracoes.index', compact('config'));
    }

    public function update(Request $request)
    {
        $campos = [
            'email_alertas', 'email_dias_aviso',
            'zapi_instance', 'zapi_token', 'zapi_numero',
            'google_client_id', 'google_client_secret', 'google_calendar_id',
        ];

        foreach ($campos as $campo) {
            if ($campo === 'email_dias_aviso') {
                $dias = $request->input('email_dias_aviso', []);
                Configuracao::set($campo, json_encode(array_map('intval', $dias)));
            } else {
                Configuracao::set($campo, $request->input($campo));
            }
        }

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:8|confirmed']);
            auth()->user()->update(['password' => bcrypt($request->password)]);
        }

        return back()->with('success', 'Configurações salvas.');
    }
}
