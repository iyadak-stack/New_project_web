<?php

namespace Tests\Feature;

use App\Domains\Auth\Models\User;
use App\Domains\Booking\Models\Appointment;
use App\Domains\Booking\Models\Subject;
use App\Domains\Reportreview\Models\Report;
use App\Domains\Reportreview\Models\ReportReason;
use App\Domains\Reportreview\Models\Review;
use App\Domains\TutorProfile\Models\TutorProfile;
use Database\Seeders\AccountSeeder;
use Database\Seeders\ReportReasonSeeder;
use Database\Seeders\SubjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([SubjectSeeder::class, AccountSeeder::class, ReportReasonSeeder::class]);
        \App\Domains\TutorProfile\Models\TutorProfile::query()->update(['approval_status' => 'approved']);
        $this->admin = User::where('email', 'admin.demo@example.com')->firstOrFail();
        $this->student = User::where('email', 'favorites.demo@example.com')->firstOrFail();
    }

    private function report(): Report
    {
        return Report::create([
            'Report_id' => 'REPORT0001', 'description' => 'ข้อความปัญหาสำหรับตรวจสอบ',
            'status' => 'pending', 'ReportReason_Reason_id' => 'RPTTYPE002',
            'Users_user_id' => $this->student->user_id, 'Tutor_profiles_tutor_id' => 'DEMOTUT001',
        ]);
    }

    private function appointment(): Appointment
    {
        return Appointment::create([
            'Appointment_id' => 'APPOINT001', 'status' => 'confirmed',
            'start_datetime' => '2026-10-20 10:00:00', 'end_datetime' => '2026-10-20 11:00:00',
            'Subject_subject_id' => 'SUB-001', 'Tutor_profiles_tutor_id' => 'DEMOTUT001',
            'Student_profiles_student_id' => $this->student->studentProfile->id,
        ]);
    }

    public function test_all_admin_pages_render_with_actual_data_and_blue_layout(): void
    {
        $this->report();
        $this->appointment();
        Review::create(['Review_id' => 'REVIEW0001', 'rating' => 4, 'Comment' => 'ทบทวนเข้าใจง่าย', 'Appointment_Appointment_id' => 'APPOINT001', 'Tutor_profiles_tutor_id' => 'DEMOTUT001']);
        foreach (['admin.dashboard', 'admin.users.index', 'admin.reports.index', 'admin.reasons.index', 'admin.subjects.index', 'admin.reviews.index', 'admin.appointments.index'] as $route) {
            $this->actingAs($this->admin)->get(route($route))->assertOk()->assertSee('CSS/admin.css', false)->assertSee('จัดการรายวิชา');
        }
        $this->get(route('admin.users.show', $this->student->user_id))->assertOk()->assertSee($this->student->email);
        $this->get(route('admin.appointments.index'))->assertSee('APPOINT001')->assertSee('ณัฐ')->assertSee('นักเรียน');
        $this->get(route('admin.reviews.index'))->assertSee('ทบทวนเข้าใจง่าย');
        $this->get(route('admin.dashboard'))->assertViewHas('stats', fn ($stats) => $stats['pendingReports'] === 1 && $stats['appointments'] === 1);
    }

    public function test_guests_and_students_cannot_read_or_mutate_admin_data(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->actingAs($this->student)->withSession(['active_role' => 'admin']);
        foreach (['admin.dashboard', 'admin.users.index', 'admin.reports.index', 'admin.subjects.index', 'admin.reviews.index', 'admin.appointments.index', 'admin.reasons.index'] as $route) {
            $this->get(route($route))->assertForbidden();
        }
        $this->patch(route('admin.users.status', $this->admin->user_id), ['is_active' => 0])->assertForbidden();
        $this->post(route('admin.subjects.store'), ['subject_name' => 'วิชาปลอม'])->assertForbidden();
        $this->post(route('subjects.store'), ['subject_name' => 'วิชาปลอม'])->assertForbidden();
        $this->report();
        $this->patch(route('admin.reports.status', 'REPORT0001'), ['status' => 'resolved'])->assertForbidden();
        $this->post(route('admin.reasons.store'), ['reason_name' => 'เหตุผลปลอม'])->assertForbidden();
        $this->assertDatabaseMissing('subjects', ['subject_name' => 'วิชาปลอม']);
    }

    public function test_reports_can_filter_and_save_only_latest_status(): void
    {
        $report = $this->report();
        $this->actingAs($this->admin)->patch(route('admin.reports.status', $report->Report_id), ['status' => 'in_progress'])->assertRedirect();
        $this->assertSame('in_progress', $report->fresh()->status);
        $this->get(route('admin.reports.index', ['status' => 'pending']))->assertDontSee('ข้อความปัญหาสำหรับตรวจสอบ');
        $this->get(route('admin.reports.index', ['status' => 'in_progress', 'reason' => 'RPTTYPE002', 'search' => 'ปัญหา']))->assertSee('ข้อความปัญหาสำหรับตรวจสอบ');
        $this->patch(route('admin.reports.status', $report->Report_id), ['status' => 'resolved'])->assertRedirect();
        $this->patch(route('admin.reports.status', $report->Report_id), ['status' => 'fake'])->assertSessionHasErrors('status');
        $this->assertSame('resolved', $report->fresh()->status);
        $this->assertDatabaseCount('reports', 1);
    }

    public function test_suspension_blocks_existing_session_and_login_and_can_be_reversed(): void
    {
        $this->actingAs($this->admin)->patch(route('admin.users.status', $this->student->user_id), ['is_active' => 0])->assertRedirect();
        $this->assertFalse((bool) $this->student->fresh()->is_active);
        $this->actingAs($this->student->fresh())->get('/favorites')->assertRedirect(route('home'));
        $this->assertGuest();
        $this->post('/login', ['email' => $this->student->email, 'password' => 'Demo12345!'])->assertSessionHasErrors();
        $this->assertGuest();
        $this->actingAs($this->admin)->patch(route('admin.users.status', $this->student->user_id), ['is_active' => 1])->assertRedirect();
        $this->assertTrue((bool) $this->student->fresh()->is_active);
        $this->post('/logout');
        $this->post('/login', ['email' => $this->student->email, 'password' => 'Demo12345!'])->assertSessionHasNoErrors();
        $this->assertAuthenticatedAs($this->student);
    }

    public function test_admin_cannot_suspend_self_or_another_admin(): void
    {
        $other = User::factory()->create(['role' => 'admin', 'current_role' => 'admin']);
        $this->actingAs($this->admin)->patch(route('admin.users.status', $this->admin->user_id), ['is_active' => 0])->assertForbidden();
        $this->patch(route('admin.users.status', $other->user_id), ['is_active' => 0])->assertForbidden();
        $this->assertTrue((bool) $other->fresh()->is_active);
    }

    public function test_unused_subjects_can_be_created_renamed_deleted_but_linked_subjects_are_protected(): void
    {
        $this->actingAs($this->admin)->post(route('admin.subjects.store'), ['subject_name' => 'วิชาใหม่'])->assertRedirect();
        $subject = Subject::where('subject_name', 'วิชาใหม่')->firstOrFail();
        $this->put(route('admin.subjects.update', $subject->subject_id), ['subject_name' => 'วิชาปรับชื่อ'])->assertRedirect();
        $this->assertSame('วิชาปรับชื่อ', $subject->fresh()->subject_name);
        $this->delete(route('admin.subjects.destroy', $subject->subject_id))->assertRedirect();
        $this->assertDatabaseMissing('subjects', ['subject_id' => $subject->subject_id]);
        $this->delete(route('admin.subjects.destroy', 'SUB-003'))->assertSessionHasErrors('subject');
        $this->appointment();
        $this->delete(route('admin.subjects.destroy', 'SUB-001'))->assertSessionHasErrors('subject');
        $this->assertDatabaseHas('appointments', ['Appointment_id' => 'APPOINT001']);
    }

    public function test_used_report_reasons_cannot_be_deleted_and_new_types_appear_in_user_form(): void
    {
        $this->report();
        $this->actingAs($this->admin)->delete(route('admin.reasons.destroy', 'RPTTYPE002'))->assertSessionHasErrors('reason');
        $this->post(route('admin.reasons.store'), ['reason_name' => 'หัวข้อใหม่'])->assertRedirect();
        $reason = ReportReason::where('reason_name', 'หัวข้อใหม่')->firstOrFail();
        $this->get(route('tutor.show', 'DEMOTUT001'))->assertOk()->assertSee('หัวข้อใหม่');
        $this->put(route('admin.reasons.update', $reason->Reason_id), ['reason_name' => 'เปลี่ยนหัวข้อ'])->assertRedirect();
        $this->delete(route('admin.reasons.destroy', $reason->Reason_id))->assertRedirect();
        $this->assertDatabaseMissing('report_reasons', ['Reason_id' => $reason->Reason_id]);
    }

    public function test_review_visibility_changes_rating_and_keeps_review_record(): void
    {
        $this->appointment();
        $review = Review::create(['Review_id' => 'REVIEW0001', 'rating' => 4, 'Comment' => 'รีวิวตัวอย่าง', 'Appointment_Appointment_id' => 'APPOINT001', 'Tutor_profiles_tutor_id' => 'DEMOTUT001']);
        $this->actingAs($this->admin)->patch(route('admin.reviews.visibility', $review->Review_id), ['visible' => 0])->assertRedirect();
        $this->assertSoftDeleted('reviews', ['Review_id' => $review->Review_id]);
        $this->assertEquals(0, TutorProfile::find('DEMOTUT001')->average_rating);
        $this->get(route('tutor.show', 'DEMOTUT001'))->assertOk()->assertDontSee('รีวิวตัวอย่าง');
        $this->patch(route('admin.reviews.visibility', $review->Review_id), ['visible' => 1])->assertRedirect();
        $this->assertNull(Review::findOrFail($review->Review_id)->deleted_at);
        $this->assertEquals(4, TutorProfile::find('DEMOTUT001')->average_rating);
        $this->get(route('tutor.show', 'DEMOTUT001'))->assertOk()->assertSee('รีวิวตัวอย่าง');
    }

    public function test_searches_filter_users_reviews_and_appointments(): void
    {
        $this->appointment();
        Review::create(['Review_id' => 'REVIEW0001', 'rating' => 4, 'Comment' => 'รีวิวตัวอย่าง', 'Appointment_Appointment_id' => 'APPOINT001', 'Tutor_profiles_tutor_id' => 'DEMOTUT001']);
        $this->actingAs($this->admin)->get(route('admin.users.index', ['search' => $this->student->email]))->assertSee($this->student->email)->assertDontSee('student2.demo@example.com');
        $this->get(route('admin.users.index', ['role' => 'tutor']))->assertSee('tutor1.demo@example.com')->assertDontSee('admin.demo@example.com');
        $this->get(route('admin.reviews.index', ['search' => 'ณัฐ', 'rating' => 4]))->assertSee('รีวิวตัวอย่าง');
        $this->get(route('admin.reviews.index', ['rating' => 1]))->assertDontSee('รีวิวตัวอย่าง');
        $this->get(route('admin.appointments.index',['search' => 'คณิตศาสตร์', 'date' => '2026-10-20', 'status' => 'confirmed']))->assertSee('APPOINT001');
        $this->get(route('admin.appointments.index',['date' => '2026-10-21']))->assertDontSee('APPOINT001');
    }
}
