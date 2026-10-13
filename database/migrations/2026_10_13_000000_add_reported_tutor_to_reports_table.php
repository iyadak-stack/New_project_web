<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            // nullable เพื่อให้รายงานเก่าที่ยังไม่ได้ระบุติวเตอร์อยู่ต่อได้
            $table->char('Tutor_profiles_tutor_id', 10)->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn('Tutor_profiles_tutor_id');
        });
    }
};
