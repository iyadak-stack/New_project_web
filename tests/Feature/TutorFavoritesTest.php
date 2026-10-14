<?php

namespace Tests\Feature;

use App\Domains\Auth\Models\User;
use App\Domains\TutorProfile\Models\Favorite;
use App\Domains\TutorProfile\Models\TutorProfile;
use Database\Seeders\FavoritePageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TutorFavoritesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_must_log_in_to_view_favorites(): void
    {
        $this->get('/favorites')->assertRedirect(route('login'));
        $this->get('/tutors/favorites')->assertRedirect(route('login'));
    }

    public function test_guest_can_view_tutor_profiles_even_with_a_stale_role_session(): void
    {
        $this->seed(FavoritePageSeeder::class);
        TutorProfile::query()->update(['approval_status' => 'approved']);

        foreach (['DEMOTUT001', 'DEMOTUT002', 'DEMOTUT003'] as $tutorId) {
            // บทบาทใน Session อาจค้างอยู่ แต่ต้องตรวจล็อกอินก่อนอ่านข้อมูลผู้ใช้
            $this->withSession(['active_role' => 'tutor'])
                ->get(route('tutor.show', $tutorId))
                ->assertOk()
                ->assertSee('เข้าสู่ระบบ')
                ->assertSee(route('tutor.search'), false)
                ->assertDontSee('id="toggleProfileBtn"', false);
        }
    }

    public function test_favorites_show_only_the_current_users_tutors_and_profile_links(): void
    {
        $this->seed(FavoritePageSeeder::class);
        TutorProfile::query()->update(['approval_status' => 'approved']);
        $student = User::where('email', 'favorites.demo@example.com')->firstOrFail();

        foreach (['/favorites', '/tutors/favorites'] as $url) {
            $this->actingAs($student)->get($url)
                ->assertOk()
                ->assertSee('พี่ณัฐ ใจดี')
                ->assertSee('คณิตศาสตร์')
                ->assertSee(route('tutor.show', 'DEMOTUT001'), false)
                ->assertSee('ประสบการณ์การติว')
                ->assertDontSee('ยินดีต้อนรับคุณ');
        }

        // ผู้ใช้อีกคนไม่ควรเห็นรายการที่บัญชีสาธิตบันทึกไว้
        $otherStudent = User::factory()->create();
        $this->actingAs($otherStudent)->get('/favorites')
            ->assertOk()
            ->assertSee('ยังไม่มีติวเตอร์ในรายการโปรด')
            ->assertDontSee('พี่ณัฐ ใจดี');

        // รายการที่นำออกแล้ว ต้องไม่แสดงบนหน้า
        Favorite::where('Users_user_id', $student->user_id)
            ->where('Tutor_profiles_tutor_id', 'DEMOTUT001')->delete();
        $this->actingAs($student)->get('/favorites')
            ->assertOk()->assertDontSee('พี่ณัฐ ใจดี');
    }

    public function test_demo_seeder_can_run_twice_and_profile_link_opens(): void
    {
        $this->seed(FavoritePageSeeder::class);
        $this->seed(FavoritePageSeeder::class);
        TutorProfile::query()->update(['approval_status' => 'approved']);
        $this->assertDatabaseCount('favorites', 3);
        $this->assertDatabaseCount('tutor_profiles', 3);

        $student = User::where('email', 'favorites.demo@example.com')->firstOrFail();
        $this->actingAs($student)->get(route('tutor.show', 'DEMOTUT001'))
            ->assertOk()->assertSee('ณัฐ');
    }

    public function test_seeder_does_not_add_favorites_to_other_students(): void
    {
        $student = User::factory()->create(['role' => 'student', 'current_role' => 'student']);
        $this->assertNull($student->studentProfile);

        $this->seed(FavoritePageSeeder::class);
        $this->seed(FavoritePageSeeder::class);

        $this->assertSame(0, $student->favorites()->count());
        $this->actingAs($student)->get('/favorites')
            ->assertOk()->assertSee('ยังไม่มีติวเตอร์ในรายการโปรด');
    }
}
