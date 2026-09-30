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

            $table->unique([
                'Tutor_profiles_tutor_id',
                'Subject_subject_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tutor_profiles_has_subjects');
    }
};