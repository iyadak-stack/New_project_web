<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ถ้ามีเวลาว่างที่ไม่มีเจ้าของ ให้หยุดก่อนเพิ่ม FK ไม่ลบข้อมูลทิ้งเอง
        $hasMissingUser = DB::table('availabilities')
            ->leftJoin('users', 'availabilities.user_id', '=', 'users.user_id')
            ->whereNull('users.user_id')
            ->exists();

        if ($hasMissingUser) {
            throw new RuntimeException('พบเวลาว่างที่อ้างถึงผู้ใช้ที่ไม่มีอยู่ กรุณาตรวจข้อมูลก่อนเพิ่ม FK');
        }

        Schema::table('availabilities', function (Blueprint $table) {
            // ใช้คอลัมน์เดิม และห้ามลบผู้ใช้จริงหากยังมีเวลาว่างอ้างถึงอยู่
            $table->foreign('user_id', 'availabilities_user_id_foreign')
                ->references('user_id')
                ->on('users')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        // ย้อนกลับเฉพาะ FK ไม่ลบตารางหรือคอลัมน์เดิม
        Schema::table('availabilities', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
    }
};
