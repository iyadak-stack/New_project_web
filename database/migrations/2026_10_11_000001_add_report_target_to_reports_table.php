<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table): void {
            if (! Schema::hasColumn('reports', 'Reported_users_user_id')) {
                $table->char('Reported_users_user_id', 10)->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table): void {
            if (Schema::hasColumn('reports', 'Reported_users_user_id')) {
                $table->dropColumn('Reported_users_user_id');
            }
        });
    }
};
