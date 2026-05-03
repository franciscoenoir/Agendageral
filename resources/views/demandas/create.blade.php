@extends('layouts.app')
@section('title', 'Nova Demanda')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold text-gray-900">Nova Demanda</h1>
        <a href="{{ route('dashboard') }}" class="text-sm text-gray-500 hover:text-gray-800">← Voltar</a>
    </div>

    @include('demandas._form', ['demanda' => null, 'action' => route('demandas.store'), 'method' => 'POST'])
</div>
@endsection
