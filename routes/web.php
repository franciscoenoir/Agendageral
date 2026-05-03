<?php

use App\Http\Controllers\AgendaController;
use App\Http\Controllers\AlertaController;
use App\Http\Controllers\ConfiguracaoController;
use App\Http\Controllers\DemandaController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\WhatsAppController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/', [DemandaController::class, 'index'])->name('dashboard');

    Route::resource('demandas', DemandaController::class);
    Route::patch('demandas/{demanda}/concluir', [DemandaController::class, 'concluir'])->name('demandas.concluir');
    Route::get('demandas-pdf', [DemandaController::class, 'exportPdf'])->name('demandas.pdf');

    Route::get('agenda', [AgendaController::class, 'index'])->name('agenda');
    Route::get('agenda/data', [AgendaController::class, 'data'])->name('agenda.data');

    Route::get('configuracoes', [ConfiguracaoController::class, 'index'])->name('configuracoes');
    Route::put('configuracoes', [ConfiguracaoController::class, 'update'])->name('configuracoes.update');

    Route::post('alertas/enviar', [AlertaController::class, 'enviar'])->name('alertas.enviar');

    Route::get('google/redirect', [GoogleController::class, 'redirect'])->name('google.redirect');
    Route::get('google/callback', [GoogleController::class, 'callback'])->name('google.callback');
});

Route::post('webhook/whatsapp', [WhatsAppController::class, 'webhook'])->name('webhook.whatsapp');

require __DIR__.'/auth.php';
