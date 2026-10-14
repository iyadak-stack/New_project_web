<?php

namespace Tests\Feature;

use App\Domains\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    private function seededCounts(): array
    {
        $counts = [];
        foreach (['users', 'student_profiles', 'tutor_profiles', 'subjects', 'favorites', 'appointments', 'reviews', 'reports', 'notifications'] as $table) {
            $counts[$table] = DB::table($table)->count();
        }

        return $counts;
    }

    public function test_fresh_seeding_keeps_the_fixed_email_and_the_same_counts(): void
    {
        $this->seed();
        $firstCounts = $this->seededCounts();
        $this->assertDatabaseHas('users', ['email' => 'test@example.com', 'first_name' => 'Test']);
        $this->assertDatabaseCount('users', 15);

        $this->seed();
        $this->assertSame($firstCounts, $this->seededCounts());
        $this->assertSame(1, User::where('email', 'test@example.com')->count());
    }

    public function test_seeding_twice_does_not_duplicate_or_overwrite_an_existing_user(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'first_name' => 'ชื่อที่แก้ไขเอง',
            'role' => 'student',
        ]);
        $originalAttributes = $user->fresh()->getAttributes();

        $this->seed();
        $firstCounts = $this->seededCounts();
        $this->seed();

        $this->assertSame($firstCounts, $this->seededCounts());
        $this->assertDatabaseCount('users', 15);
        $this->assertDatabaseCount('subjects', 8);
        $this->assertDatabaseCount('tutor_profiles', 9);
        $this->assertDatabaseCount('favorites', 3);
        $this->assertSame(0, $user->favorites()->count());
        $this->assertDatabaseCount('report_reasons', 4);
        $this->assertSame($originalAttributes, $user->fresh()->getAttributes());
    }

    public function test_seeding_preserves_a_soft_deleted_account_with_the_same_email(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $user->delete();

        $this->seed();

        $this->assertDatabaseCount('users', 15);
        $this->assertSoftDeleted('users', ['user_id' => $user->user_id]);
        $this->assertDatabaseCount('report_reasons', 4);
    }
}
