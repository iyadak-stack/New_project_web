<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tutor_profiles', function (Blueprint $table) {
            $table->string('approval_status', 20)->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->text('reject_reason')->nullable();
        });

        // ผู้ใช้เลือกให้ติวเตอร์เดิมรอแอดมินอนุมัติด้วย
        DB::table('tutor_profiles')->update([
            'approval_status' => 'pending', 'submitted_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('tutor_profiles', function (Blueprint $table) {
            $table->dropColumn(['approval_status', 'submitted_at', 'reject_reason']);
        });
    }
};
