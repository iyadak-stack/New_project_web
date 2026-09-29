<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Tutor_profiles_has_Subject', function (Blueprint $table) {
            $table->id();
            $table->integer('Tutor_profiles_tutor_id');
            $table->char('Subject_subject_id', 10);

            $table->unique([
                'Tutor_profiles_tutor_id',
                'Subject_subject_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Tutor_profiles_has_Subject');
    }
};