<?php

namespace Tests\Feature;

use App\Domains\Auth\Models\User;
use App\Domains\TutorProfile\Models\Favorite;
use App\Domains\TutorProfile\Models\TutorProfile;
use Database\Seeders\AccountSeeder;
use Database\Seeders\SubjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RemainingIssuesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([SubjectSeeder::class, AccountSeeder::class]);
    }

    public function test_favorites_hide_pending_rejected_and_suspended_tutors_without_deleting_records(): void
    {
        TutorProfile::where('tutor_id', 'DEMOTUT001')->update(['approval_status' => 'approved']);
        TutorProfile::where('tutor_id', 'DEMOTUT002')->update(['approval_status' => 'pending']);
        TutorProfile::where('tutor_id', 'DEMOTUT003')->update(['approval_status' => 'rejected']);
        $student = User::where('email', 'favorites.demo@example.com')->firstOrFail();

        $this->actingAs($student)->get(route('tutor.favorites'))
            ->assertOk()
            ->assertSee(route('tutor.show', 'DEMOTUT001'), false)
            ->assertDontSee(route('tutor.show', 'DEMOTUT002'), false)
            ->assertDontSee(route('tutor.show', 'DEMOTUT003'), false);

        User::where('email', 'tutor1.demo@example.com')->update(['is_active' => false]);
        $this->get(route('tutor.favorites'))->assertOk()->assertSee('ยังไม่มีติวเตอร์ในรายการโปรด');
        $this->assertSame(3, Favorite::where('Users_user_id', $student->user_id)->count());
    }

    public function test_search_displays_hours_from_seconds_instead_of_years(): void
    {
        TutorProfile::where('tutor_id', 'DEMOTUT001')->update([
            'approval_status' => 'approved',
            'experience_years' => 7,
            'total_teaching_seconds' => 9000,
        ]);
        $this->get(route('tutor.search', ['search' => 'ณัฐ']))
            ->assertOk()
            ->assertSee('สอนพิเศษ 2 ชั่วโมง')
            ->assertDontSee('สอนพิเศษ 7 ชั่วโมง');
    }

    public function test_old_ranking_url_redirects_to_the_existing_tutor_list(): void
    {
        $this->get(route('tutor.ranking'))->assertRedirect(route('tutor.search'));
        $this->get(route('tutor.search'))->assertOk();
    }

    public function test_admin_sidebar_keeps_the_menu_without_numbers(): void
    {
        $admin = User::where('email', 'admin.demo@example.com')->firstOrFail();
        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('nav-number', false)
            ->assertSee('จัดการผู้ใช้')
            ->assertSee('จัดการรายงาน');
    }
}
