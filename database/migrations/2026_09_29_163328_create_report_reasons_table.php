<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ปกป้องแก้ให้: ให้สร้าง report_reasons ก่อน reports ที่อ้าง FK มาตารางนี้
// ชื่อไฟล์เดิม: 2026_09_29_163335_create_report_reasons_table.php
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ปกป้องเพิ่มให้: รองรับฐานที่เคยรันชื่อไฟล์เดิม โดยไม่สร้างตารางซ้ำ
        if (Schema::hasTable('report_reasons')) {
            return;
        }

        Schema::create('report_reasons', function (Blueprint $table) {
            $table->char('Reason_id', 10)->primary();
            $table->string('reason_name', 45);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ปกป้องเพิ่มให้: ไม่ลบตารางที่ reports เดิมยังอ้าง FK อยู่
        if (Schema::hasTable('reports')) {
            return;
        }

        Schema::dropIfExists('report_reasons');
    }
};
