<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('demandas', function (Blueprint $table) {
            $table->index(['status', 'data_limite'], 'demandas_status_data_limite_idx');
            $table->index('urgencia', 'demandas_urgencia_idx');
        });
    }

    public function down(): void
    {
        Schema::table('demandas', function (Blueprint $table) {
            $table->dropIndex('demandas_status_data_limite_idx');
            $table->dropIndex('demandas_urgencia_idx');
        });
    }
};
