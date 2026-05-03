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
        Schema::table('demandas', function (Blueprint $table) {
            $table->boolean('recorrente')->default(false)->after('auto_escalado');
            $table->string('frequencia')->nullable()->after('recorrente');
        });
    }

    public function down(): void
    {
        Schema::table('demandas', function (Blueprint $table) {
            $table->dropColumn(['recorrente', 'frequencia']);
        });
    }
};
