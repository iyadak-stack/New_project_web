<?php

namespace Tests\Feature;

use App\Domains\Auth\Models\User;
use App\Domains\Reportreview\Models\Report;
use App\Domains\Reportreview\Models\ReportReason;
use Database\Seeders\FavoritePageSeeder;
use Database\Seeders\ReportReasonSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TutorReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // ใช้ฐานข้อมูลทดสอบแยกจากข้อมูลจริงของผู้ใช้
        $this->seed([FavoritePageSeeder::class, ReportReasonSeeder::class]);
        \App\Domains\TutorProfile\Models\TutorProfile::query()->update(['approval_status' => 'approved']);

    }

    private function reportData(): array
    {
        return [
            'reported_tutor_id' => 'DEMOTUT001',
            'reason_type' => 'RPTTYPE002',
            'description' => 'ข้อความตัวอย่างสำหรับทดสอบรายงาน',
        ];
    }

    public function test_reason_options_come_from_database_and_seeder_is_repeatable(): void
    {
        $this->seed(ReportReasonSeeder::class);
        $this->assertDatabaseCount('report_reasons', 4);
        ReportReason::create(['Reason_id' => 'CUSTOM0001', 'reason_name' => 'ประเภทใหม่จากฐานข้อมูล']);

        $user = User::factory()->create();
        $this->actingAs($user)->get(route('tutor.show', 'DEMOTUT001'))
            ->assertOk()->assertSee('ประเภทใหม่จากฐานข้อมูล')
            ->assertSee('value="CUSTOM0001"', false)
            ->assertSee('confirmReportSubmission(this)', false);
    }

    public function test_submitted_report_is_saved_and_visible_to_admin(): void
    {
        $student = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin', 'current_role' => 'admin']);

        $this->actingAs($student)->post(route('reports.store'), $this->reportData() + [
            // ฟอร์มที่ปลอม user_id หรือ status ต้องไม่มีผลต่อข้อมูลที่บันทึก
            'Users_user_id' => $admin->user_id,
            'status' => 'resolved',
        ])->assertRedirect(route('tutor.show', 'DEMOTUT001'))->assertSessionHas('success');

        $report = Report::firstOrFail();
        $this->assertDatabaseHas('reports', [
            'Report_id' => $report->Report_id,
            'Users_user_id' => $student->user_id,
            'Tutor_profiles_tutor_id' => 'DEMOTUT001',
            'ReportReason_Reason_id' => 'RPTTYPE002',
            'description' => $this->reportData()['description'],
            'status' => 'pending',
        ]);

        $this->actingAs($admin)->get(route('admin.reports.index'))
            ->assertOk()->assertSee($report->Report_id)
            ->assertSee($student->first_name)->assertSee('ณัฐ ใจดี')
            ->assertSee('พฤติกรรมหรือภาษาไม่เหมาะสม')
            ->assertSee($this->reportData()['description'])->assertSee('รอตรวจสอบ');
    }

    public function test_normal_users_cannot_view_reports_even_with_admin_session_role(): void
    {
        $user = User::factory()->create(['role' => 'student', 'current_role' => 'student']);
        $this->actingAs($user)->withSession(['active_role' => 'admin'])
            ->get(route('admin.reports.index'))->assertForbidden();
    }

    public function test_guests_cannot_submit_or_view_reports(): void
    {
        $this->post(route('reports.store'), $this->reportData())->assertRedirect(route('login'));
        $this->get(route('admin.reports.index'))->assertRedirect(route('login'));
        $this->assertDatabaseCount('reports', 0);
    }

    public function test_invalid_input_does_not_create_reports_and_preserves_form_input(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->from(route('tutor.show', 'DEMOTUT001'))
            ->post(route('reports.store'), [
                'reported_tutor_id' => 'UNKNOWN',
                'reason_type' => 'fake_info',
                'description' => 'รายละเอียดที่กรอกไว้',
            ])->assertRedirect(route('tutor.show', 'DEMOTUT001'))
            ->assertSessionHasErrors(['reported_tutor_id', 'reason_type'])
            ->assertSessionHasInput('description', 'รายละเอียดที่กรอกไว้');

        $this->actingAs($user)->post(route('reports.store'), array_replace($this->reportData(), [
            'description' => '   ',
        ]))->assertSessionHasErrors('description');

        $this->assertDatabaseCount('reports', 0);
    }

    public function test_report_description_is_escaped_in_admin_view(): void
    {
        $student = User::factory()->create();
        $this->actingAs($student)->post(route('reports.store'), array_replace($this->reportData(), [
            'description' => '<script>alert("test")</script>',
        ]));

        $admin = User::factory()->create(['role' => 'admin', 'current_role' => 'admin']);
        $this->actingAs($admin)->get(route('admin.reports.index'))
            ->assertOk()->assertSee('&lt;script&gt;', false)
            ->assertDontSee('<script>alert("test")</script>', false);
    }
}
