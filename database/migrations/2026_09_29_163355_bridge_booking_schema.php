<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // เพิ่มคอลัมน์ที่ใช้สำหรับการจอง
        if (Schema::hasTable('appointments')) {
            if (!Schema::hasColumn('appointments', 'start_datetime')) {
                Schema::table('appointments', function (Blueprint $table) {
                    $table->timestamp('start_datetime')->nullable();
                });
            }

            if (!Schema::hasColumn('appointments', 'end_datetime')) {
                Schema::table('appointments', function (Blueprint $table) {
                    $table->timestamp('end_datetime')->nullable();
                });
            }

            if (!Schema::hasColumn('appointments', 'Subject_subject_id')) {
                Schema::table('appointments', function (Blueprint $table) {
                    $table->string('Subject_subject_id', 10)->nullable();
                });
            }

            if (!Schema::hasColumn('appointments', 'Tutor_profiles_tutor_id')) {
                Schema::table('appointments', function (Blueprint $table) {
                    $table->string('Tutor_profiles_tutor_id', 10)->nullable();
                });
            }

            if (!Schema::hasColumn('appointments', 'Student_profiles_student_id')) {
                Schema::table('appointments', function (Blueprint $table) {
                    $table->string('Student_profiles_student_id', 10)->nullable();
                });
            }

            if (!Schema::hasColumn('appointments', 'deleted_at')) {
                Schema::table('appointments', function (Blueprint $table) {
                    $table->timestamp('deleted_at')->nullable();
                });
            }
        }

        // เพิ่มคอลัมน์ที่ใช้สำหรับการแจ้งเตือน
        if (Schema::hasTable('notifications')) {
            if (!Schema::hasColumn('notifications', 'is_read')) {
                Schema::table('notifications', function (Blueprint $table) {
                    $table->boolean('is_read')->default(false);
                });
            }

            if (!Schema::hasColumn('notifications', 'Users_user_id')) {
                Schema::table('notifications', function (Blueprint $table) {
                    $table->string('Users_user_id', 10)->nullable();
                });
            }

            if (!Schema::hasColumn('notifications', 'NotificationType_notification_type_id')) {
                Schema::table('notifications', function (Blueprint $table) {
                    $table->string('NotificationType_notification_type_id', 10)->nullable();
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('appointments')) {
            $columns = [
                'start_datetime',
                'end_datetime',
                'Subject_subject_id',
                'Tutor_profiles_tutor_id',
                'Student_profiles_student_id',
                'deleted_at',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('appointments', $column)) {
                    Schema::table('appointments', function (Blueprint $table) use ($column) {
                        $table->dropColumn($column);
                    });
                }
            }
        }

        if (Schema::hasTable('notifications')) {
            $columns = [
                'is_read',
                'Users_user_id',
                'NotificationType_notification_type_id',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('notifications', $column)) {
                    Schema::table('notifications', function (Blueprint $table) use ($column) {
                        $table->dropColumn($column);
                    });
                }
            }
        }
    }
};