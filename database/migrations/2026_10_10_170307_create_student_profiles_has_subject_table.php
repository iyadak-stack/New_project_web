<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Student_profiles_has_Subject', function (Blueprint $table) {
            // รองรับทั้ง id เดิมแบบ 10 ตัวและ UUID ที่ Model เคยสร้างไว้
            $table->string('Student_profiles_student_id', 36);
            $table->string('Subject_subject_id', 10);
            $table->timestamps();

            $table->primary(['Student_profiles_student_id', 'Subject_subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Student_profiles_has_Subject');
    }
};
