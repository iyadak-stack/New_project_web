<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->char('review_id', 10)->nullable()->after('Report_id');
            $table->char('handled_by', 10)->nullable()->after('Users_user_id');
            $table->timestamp('handled_at')->nullable()->after('handled_by');
            $table->char('resolved_by', 10)->nullable()->after('handled_at');
            $table->timestamp('resolved_at')->nullable()->after('resolved_by');

        });

        DB::table('reports')->where('status', 'reviewing')->update(['status' => 'investigating']);
        DB::table('reports')->where('status', 'dismissed')->update(['status' => 'rejected']);
    }

    public function down(): void
    {
        DB::table('reports')->where('status', 'investigating')->update(['status' => 'reviewing']);
        DB::table('reports')->where('status', 'rejected')->update(['status' => 'dismissed']);

        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn(['review_id', 'handled_by', 'handled_at', 'resolved_by', 'resolved_at']);
        });
    }
};
