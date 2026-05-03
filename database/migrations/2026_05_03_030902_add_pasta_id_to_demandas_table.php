<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('demandas', function (Blueprint $table) {
            $table->foreignId('pasta_id')->nullable()->after('id')->constrained('pastas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('demandas', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Pasta::class);
            $table->dropColumn('pasta_id');
        });
    }
};
