<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tutor_profiles_has_subjects', function (Blueprint $table) {
            $table->char('Tutor_profiles_tutor_id', 10);
            $table->char('Subject_subject_id', 10);

            $table->timestamps();
            $table->softDeletes();

            // ปกป้องแก้ให้: กำหนดชื่อ index สั้น เพราะชื่ออัตโนมัติยาวเกินที่ MySQL รับได้
            // โค้ดเดิม: $table->unique(['Tutor_profiles_tutor_id', 'Subject_subject_id']);
            $table->unique([
                'Tutor_profiles_tutor_id',
                'Subject_subject_id',
            ], 'tutor_subject_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tutor_profiles_has_subjects');
    }
};
