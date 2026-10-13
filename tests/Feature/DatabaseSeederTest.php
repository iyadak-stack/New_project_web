<?php

namespace Tests\Feature;

use App\Domains\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeding_twice_does_not_duplicate_or_overwrite_an_existing_user(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'first_name' => 'ชื่อที่แก้ไขเอง',
            'role' => 'student',
        ]);
        $originalAttributes = $user->fresh()->getAttributes();

        $this->seed();
        $this->seed();

        $this->assertDatabaseCount('users', 8);
        $this->assertDatabaseCount('subjects', 8);
        $this->assertDatabaseCount('tutor_profiles', 3);
        $this->assertDatabaseCount('favorites', 21);
        $this->assertSame(3, $user->favorites()->count());
        $this->assertDatabaseCount('report_reasons', 4);
        $this->assertSame($originalAttributes, $user->fresh()->getAttributes());
    }

    public function test_seeding_preserves_a_soft_deleted_account_with_the_same_email(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $user->delete();

        $this->seed();

        $this->assertDatabaseCount('users', 8);
        $this->assertSoftDeleted('users', ['user_id' => $user->user_id]);
        $this->assertDatabaseCount('report_reasons', 4);
    }
}
