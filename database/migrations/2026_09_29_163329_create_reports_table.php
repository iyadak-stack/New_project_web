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
        Schema::create('reports', function (Blueprint $table) {
            $table->char('Report_id', 10)->primary();

            $table->text('description');

            $table->string('status', 45);

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->char('ReportReason_Reason_id', 10);
            $table->foreign('ReportReason_Reason_id')
                ->references('Reason_id')
                ->on('report_reasons');

            $table->char('Users_user_id', 10);

            $table->foreign('Users_user_id')
                ->references('user_id')
                ->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
