<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tutor_profiles', function (Blueprint $table) {
            $table->char('tutor_id', 10)->primary();
            $table->char('Users_user_id', 10);
            $table->text('bio')->nullable();
            $table->integer('experience_years')->default(0);
            $table->integer('total_teaching_seconds')->default(0);
            $table->decimal('average_rating', 3, 2)->default(0.00);
            $table->string('teaching_mode')->default('both');
            $table->timestamps();

            $table->unique('Users_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tutor_profiles');
    }
};