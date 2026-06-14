<?php

use App\Http\Controllers\AgendaController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ChecklistItemController;
use App\Http\Controllers\RecebimentoController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\HistoricoController;
use App\Http\Controllers\PastaController;
use App\Http\Controllers\LembreteController;
use App\Http\Controllers\AlertaController;
use App\Http\Controllers\ConfiguracaoController;
use App\Http\Controllers\DemandaController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\DeployController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\WhatsAppController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/', [DemandaController::class, 'index'])->name('dashboard');

    Route::resource('demandas', DemandaController::class);
    Route::patch('demandas/{demanda}/concluir', [DemandaController::class, 'concluir'])->name('demandas.concluir');
    Route::get('demandas-pdf', [DemandaController::class, 'exportPdf'])->name('demandas.pdf');

    Route::get('agenda', [AgendaController::class, 'index'])->name('agenda');
    Route::get('agenda/pdf/mensal', [AgendaController::class, 'exportPdfMensal'])->name('agenda.pdf.mensal');
    Route::get('agenda/pdf', [AgendaController::class, 'exportPdf'])->name('agenda.pdf');
    Route::get('agenda/data', [AgendaController::class, 'data'])->name('agenda.data');

    Route::get('configuracoes', [ConfiguracaoController::class, 'index'])->name('configuracoes');
    Route::put('configuracoes', [ConfiguracaoController::class, 'update'])->name('configuracoes.update');

    Route::post('alertas/enviar', [AlertaController::class, 'enviar'])->name('alertas.enviar');

    Route::get('google/redirect', [GoogleController::class, 'redirect'])->name('google.redirect');
    Route::get('google/callback', [GoogleController::class, 'callback'])->name('google.callback');

    Route::post('pastas', [PastaController::class, 'store'])->name('pastas.store');
    Route::patch('pastas/{pasta}', [PastaController::class, 'update'])->name('pastas.update');
    Route::delete('pastas/{pasta}', [PastaController::class, 'destroy'])->name('pastas.destroy');
    Route::patch('demandas/{demanda}/pasta', [DemandaController::class, 'moverPasta'])->name('demandas.pasta');

    Route::get('historico', [HistoricoController::class, 'index'])->name('historico');
    Route::delete('historico/limpar', [HistoricoController::class, 'limpar'])->name('historico.limpar');
    Route::delete('historico/{demanda}', [HistoricoController::class, 'destroy'])->name('historico.destroy');

    Route::get('busca', [SearchController::class, 'index'])->name('busca');

    Route::post('demandas/{demanda}/checklist', [ChecklistItemController::class, 'store'])->name('checklist.store');
    Route::patch('checklist/{item}', [ChecklistItemController::class, 'update'])->name('checklist.update');
    Route::delete('checklist/{item}', [ChecklistItemController::class, 'destroy'])->name('checklist.destroy');

    Route::post('categorias', [CategoriaController::class, 'store'])->name('categorias.store');
    Route::delete('categorias/{categoria}', [CategoriaController::class, 'destroy'])->name('categorias.destroy');

    Route::get('recebimentos', [RecebimentoController::class, 'index'])->name('recebimentos');
    Route::post('recebimentos/{demanda}', [RecebimentoController::class, 'store'])->name('recebimentos.store');
    Route::delete('recebimentos/pagamentos/{pagamento}', [RecebimentoController::class, 'destroy'])->name('recebimentos.destroy');

    Route::get('perfil', [PerfilController::class, 'index'])->name('perfil');
    Route::patch('perfil/email', [PerfilController::class, 'atualizarEmail'])->name('perfil.email');
    Route::patch('perfil/senha', [PerfilController::class, 'atualizarSenha'])->name('perfil.senha');

    Route::get('lembretes', [LembreteController::class, 'index'])->name('lembretes');
    Route::post('lembretes', [LembreteController::class, 'store'])->name('lembretes.store');
    Route::patch('lembretes/{lembrete}', [LembreteController::class, 'update'])->name('lembretes.update');
    Route::delete('lembretes/{lembrete}', [LembreteController::class, 'destroy'])->name('lembretes.destroy');
});

Route::post('webhook/whatsapp', [WhatsAppController::class, 'webhook'])->name('webhook.whatsapp');
Route::post('deploy/run', [DeployController::class, 'run'])->name('deploy.run');

require __DIR__.'/auth.php';
