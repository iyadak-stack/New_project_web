<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', fn (Blueprint $table) => $table->string('meeting_location', 500)->nullable());
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('meeting_zoom', 500)->nullable();
            $table->string('meeting_meet', 500)->nullable();
            $table->string('meeting_location', 500)->nullable();
            $table->text('lesson_notes')->nullable();
            $table->text('rejection_reason')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('appointments', fn (Blueprint $table) => $table->dropColumn(['meeting_zoom', 'meeting_meet', 'meeting_location', 'lesson_notes', 'rejection_reason']));
        Schema::table('contacts', fn (Blueprint $table) => $table->dropColumn('meeting_location'));
    }
};
