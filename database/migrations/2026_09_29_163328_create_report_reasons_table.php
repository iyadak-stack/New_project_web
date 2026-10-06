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
        if (!Schema::hasTable('report_reasons')) {
            Schema::create('report_reasons', function (Blueprint $table) {
                $table->char('Reason_id', 10)->primary();
                $table->string('reason_name', 45);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('reports')) {
            Schema::dropIfExists('report_reasons');
        }
    }
};
