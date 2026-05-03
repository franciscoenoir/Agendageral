@extends('layouts.app')
@section('title', 'Configurações')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-xl font-bold text-gray-900 mb-6">Configurações</h1>

    <form method="POST" action="{{ route('configuracoes.update') }}">
        @csrf @method('PUT')

        @if($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Email --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-4">
            <h2 class="text-base font-semibold text-gray-900 mb-4">📧 Alertas por E-mail</h2>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">E-mail para alertas</label>
                <input type="email" name="email_alertas" value="{{ $config['email_alertas'] ?? '' }}"
                       class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Avisar com antecedência de (dias)</label>
                <div class="flex gap-4">
                    @php $diasSelecionados = json_decode($config['email_dias_aviso'] ?? '[1]', true) ?? [1]; @endphp
                    @foreach([1, 2, 3, 7] as $d)
                        <label class="flex items-center gap-1.5 text-sm">
                            <input type="checkbox" name="email_dias_aviso[]" value="{{ $d }}"
                                   {{ in_array($d, $diasSelecionados) ? 'checked' : '' }}
                                   class="rounded">
                            {{ $d }}d
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Google Calendar --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-4">
            <h2 class="text-base font-semibold text-gray-900 mb-4">📅 Google Calendar</h2>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Client ID</label>
                    <input type="text" name="google_client_id" value="{{ $config['google_client_id'] ?? '' }}"
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Client Secret</label>
                    <input type="password" name="google_client_secret" value="{{ $config['google_client_secret'] ?? '' }}"
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">ID do Calendário</label>
                <input type="text" name="google_calendar_id" value="{{ $config['google_calendar_id'] ?? '' }}"
                       placeholder="primary ou id@group.calendar.google.com"
                       class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            @php $googleToken = $config['google_access_token'] ?? null; @endphp
            @if($googleToken)
                <div class="flex items-center gap-3">
                    <span class="text-sm text-green-700 font-medium">✓ Google Calendar conectado</span>
                    <a href="{{ route('google.redirect') }}" class="text-xs text-blue-600 hover:underline">Reconectar</a>
                </div>
            @else
                <a href="{{ route('google.redirect') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">
                    Conectar Google Calendar
                </a>
            @endif
        </div>

        {{-- Z-API --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-4">
            <h2 class="text-base font-semibold text-gray-900 mb-4">💬 Z-API WhatsApp</h2>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ID da Instância</label>
                    <input type="text" name="zapi_instance" value="{{ $config['zapi_instance'] ?? '' }}"
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Token</label>
                    <input type="password" name="zapi_token" value="{{ $config['zapi_token'] ?? '' }}"
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">URL do Webhook</label>
                <div class="text-sm bg-gray-50 border rounded-lg px-3 py-2 text-gray-600 font-mono">
                    {{ url('/webhook/whatsapp') }}
                </div>
            </div>
        </div>

        {{-- Password --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-4">
            <h2 class="text-base font-semibold text-gray-900 mb-4">🔑 Alterar Senha</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nova senha</label>
                    <input type="password" name="password"
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar senha</label>
                    <input type="password" name="password_confirmation"
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        <button type="submit" class="px-6 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 font-medium">
            Salvar configurações
        </button>
    </form>
</div>
@endsection
