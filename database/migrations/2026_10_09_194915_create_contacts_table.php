<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->char('contact_id', 10)->primary();
            $table->string('line_id', 45)->nullable();
            $table->string('discord_id', 255)->nullable();
            $table->string('google_meet_link', 255)->nullable();
            $table->string('zoom_link', 255)->nullable();
            $table->char('Users_user_id', 10);
            $table->timestamps();

            $table->foreign('Users_user_id')
                  ->references('user_id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};