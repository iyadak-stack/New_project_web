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
        Schema::create('report_evidences', function (Blueprint $table) {
            $table->string('FilePath', 255);
            $table->string('file_type', 45);
            $table->timestamp('created_at')->useCurrent();

            $table->char('Report_Report_id', 10);

            $table->foreign('Report_Report_id')
                ->references('Report_id')
                ->on('reports');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_evidences');
    }
};