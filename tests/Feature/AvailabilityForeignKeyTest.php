<?php

namespace Tests\Feature;

use App\Domains\Auth\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AvailabilityForeignKeyTest extends TestCase
{
    use RefreshDatabase;

    private function addAvailability(string $userId): void
    {
        DB::table('availabilities')->insert([
            'availability_id' => 'FKTEST0001',
            'user_id' => $userId,
            'start_datetime' => now()->addDay(),
            'end_datetime' => now()->addDay()->addHour(),
        ]);
    }

    public function test_existing_user_can_create_update_and_delete_availability(): void
    {
        $user = User::factory()->create();
        $this->addAvailability($user->user_id);
        DB::table('availabilities')->where('availability_id', 'FKTEST0001')->update([
            'end_datetime' => now()->addDay()->addHours(2),
        ]);
        $this->assertDatabaseHas('availabilities', ['user_id' => $user->user_id]);
        DB::table('availabilities')->where('availability_id', 'FKTEST0001')->delete();
        $this->assertDatabaseCount('availabilities', 0);
    }

    public function test_missing_user_cannot_own_availability(): void
    {
        $this->expectException(QueryException::class);
        $this->addAvailability('MISSING001');
    }

    public function test_soft_delete_keeps_the_user_and_availability(): void
    {
        $user = User::factory()->create();
        $this->addAvailability($user->user_id);
        $user->delete();
        $this->assertSoftDeleted('users', ['user_id' => $user->user_id]);
        $this->assertDatabaseHas('availabilities', ['user_id' => $user->user_id]);
    }

    public function test_hard_delete_is_restricted_when_availability_exists(): void
    {
        $user = User::factory()->create();
        $this->addAvailability($user->user_id);
        $this->expectException(QueryException::class);
        $user->forceDelete();
    }
}
