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
        Schema::table('lembretes', function (Blueprint $table) {
            $table->tinyInteger('quadrante')->nullable()->after('pos_y');
        });
    }

    public function down(): void
    {
        Schema::table('lembretes', function (Blueprint $table) {
            $table->dropColumn('quadrante');
        });
    }
};
