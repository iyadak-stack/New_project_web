<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('reports')->where('status', 'reviewing')->update(['status' => 'investigating']);
        DB::table('reports')->where('status', 'dismissed')->update(['status' => 'rejected']);
    }

    public function down(): void
    {
        DB::table('reports')->where('status', 'investigating')->update(['status' => 'reviewing']);
        DB::table('reports')->where('status', 'rejected')->update(['status' => 'dismissed']);
    }
};
