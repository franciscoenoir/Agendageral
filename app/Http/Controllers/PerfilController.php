<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PerfilController extends Controller
{
    public function index()
    {
        return view('perfil.index');
    }

    public function atualizarEmail(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'unique:users,email,' . auth()->id()],
        ], [
            'email.required' => 'O e-mail é obrigatório.',
            'email.email'    => 'Informe um e-mail válido.',
            'email.unique'   => 'Este e-mail já está em uso.',
        ]);

        auth()->user()->update(['email' => $request->email]);

        return back()->with('success', 'E-mail atualizado com sucesso.');
    }

    public function atualizarSenha(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.required'    => 'Informe a senha atual.',
            'current_password.current_password' => 'A senha atual está incorreta.',
            'password.required'            => 'Informe a nova senha.',
            'password.confirmed'           => 'As senhas não coincidem.',
            'password.min'                 => 'A senha deve ter pelo menos 8 caracteres.',
        ]);

        auth()->user()->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Senha atualizada com sucesso.');
    }
}
