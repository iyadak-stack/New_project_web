<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favorites', function (Blueprint $table) {
            $table->char('favorite_id', 10)->primary();
            $table->char('Users_user_id', 10);
            $table->char('Tutor_profiles_tutor_id', 10)->nullable();
            $table->char('Subject_subject_id', 10)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};