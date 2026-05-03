<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('demandas', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->enum('categoria', ['Engenharia', 'Firedrill', 'Rosa Garden', 'Particular', 'Família', 'Administrativo', 'Outro']);
            $table->enum('urgencia', ['urgente', 'alta', 'media', 'baixa'])->default('media');
            $table->enum('status', ['pendente', 'concluido'])->default('pendente');
            $table->date('data_inicio')->nullable();
            $table->date('data_limite');
            $table->string('responsavel')->nullable();
            $table->text('observacoes')->nullable();
            $table->boolean('auto_escalado')->default(false);
            $table->string('google_event_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demandas');
    }
};
