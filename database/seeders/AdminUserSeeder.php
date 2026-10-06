<?php

namespace Database\Seeders;

use App\Domains\Auth\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Create or refresh the shared local admin account used for testing.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'user_id' => 'ADMIN000001',
                'first_name' => 'Test',
                'last_name' => 'Admin',
                'password' => 'password',
                'role' => 'admin',
                'current_role' => 'student',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );
    }
}
