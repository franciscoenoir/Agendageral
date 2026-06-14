@extends('layouts.app')
@section('title', 'Meu Perfil')

@section('content')
<div class="max-w-xl mx-auto space-y-6">

    @if(session('success'))
    <div class="px-4 py-3 bg-green-100 text-green-800 rounded-xl text-sm">
        ✓ {{ session('success') }}
    </div>
    @endif

    {{-- Cabeçalho --}}
    <div>
        <h1 class="text-xl font-bold text-gray-900">Meu Perfil</h1>
        <p class="text-sm text-gray-500 mt-1">Altere seu e-mail e senha de acesso</p>
    </div>

    {{-- Card: E-mail --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
            ✉️ Alterar E-mail
        </h2>

        <form method="POST" action="{{ route('perfil.email') }}" class="space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">E-mail atual</label>
                <p class="text-sm text-gray-800 font-medium">{{ auth()->user()->email }}</p>
            </div>

            <div>
                <label for="email" class="block text-xs font-medium text-gray-500 mb-1">Novo e-mail</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                       class="w-full border rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('email') ? 'border-red-400' : 'border-gray-200' }}"
                       placeholder="novo@email.com">
                @error('email')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 rounded-xl transition">
                Salvar e-mail
            </button>
        </form>
    </div>

    {{-- Card: Senha --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
            🔒 Alterar Senha
        </h2>

        <form method="POST" action="{{ route('perfil.senha') }}" class="space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label for="current_password" class="block text-xs font-medium text-gray-500 mb-1">Senha atual</label>
                <input type="password" id="current_password" name="current_password"
                       class="w-full border rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('current_password') ? 'border-red-400' : 'border-gray-200' }}"
                       placeholder="••••••••">
                @error('current_password')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-xs font-medium text-gray-500 mb-1">Nova senha</label>
                <input type="password" id="password" name="password"
                       class="w-full border rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('password') ? 'border-red-400' : 'border-gray-200' }}"
                       placeholder="Mínimo 8 caracteres">
                @error('password')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-medium text-gray-500 mb-1">Confirmar nova senha</label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                       class="w-full border rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 border-gray-200"
                       placeholder="••••••••">
            </div>

            <button type="submit"
                    class="w-full bg-gray-800 hover:bg-gray-900 text-white text-sm font-medium py-2 rounded-xl transition">
                Salvar senha
            </button>
        </form>
    </div>

</div>
@endsection
