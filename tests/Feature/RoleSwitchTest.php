<?php

namespace Tests\Feature;

use App\Domains\Auth\Models\User;
use App\Domains\Booking\Models\Notification;
use App\Domains\TutorProfile\Models\TutorProfile;
use Database\Seeders\AccountSeeder;
use Database\Seeders\SubjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleSwitchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([SubjectSeeder::class, AccountSeeder::class]);
    }

    private function applicationData(): array
    {
        return ['first_name' => 'ผู้สมัคร', 'last_name' => 'ทดสอบ', 'bio' => 'สอนคณิตศาสตร์แบบเข้าใจง่าย',
            'experience_years' => 1, 'teaching_mode' => 'online', 'subjects' => ['SUB-001'], 'line_id' => 'myline'];
    }

    public function test_student_must_submit_application_and_wait_for_admin_before_switching(): void
    {
        $student = User::where('email', 'favorites.demo@example.com')->firstOrFail();
        $admin = User::where('email', 'admin.demo@example.com')->firstOrFail();
        $this->actingAs($student)->post(route('role.switch'), ['role' => 'tutor'])->assertRedirect(route('tutor.application.index'));
        $this->assertSame('student', $student->fresh()->current_role);
        $this->get(route('tutor.application.index'))->assertOk()->assertSee('ยืนยันและส่งคำขอ');
        $this->post(route('tutor.application.submit'), $this->applicationData() + ['approval_status' => 'approved'])
            ->assertSessionHasNoErrors()->assertRedirect(route('tutor.application.index'));
        $profile = $student->fresh()->tutorProfile;
        $this->assertSame('pending', $profile->approval_status);
        $this->assertNotNull($profile->submitted_at);
        $this->assertSame('myline', $student->fresh()->contact->line_id);
        $this->get(route('tutor.profile.edit'))->assertRedirect(route('home'));
        $this->get(route('tutor.subjects.index'))->assertRedirect(route('home'));
        $this->get(route('tutor.application.index'))->assertSee('รอแอดมินอนุมัติ')->assertSee('disabled', false);
        $count = Notification::count();
        $this->post(route('tutor.application.submit'), $this->applicationData())->assertSessionHas('error');
        $this->assertDatabaseCount('notifications', $count);
        $this->get(route('admin.tutor-applications.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.tutor-applications.index'))->assertOk()->assertSee('ผู้สมัคร');
        $this->get(route('admin.tutor-applications.show', $profile->tutor_id))->assertOk()->assertSee('myline');
        $this->patch(route('admin.tutor-applications.decide', $profile->tutor_id), ['status' => 'approved'])->assertSessionHasNoErrors();
        $this->assertSame('approved', $profile->fresh()->approval_status);
        $this->actingAs($student->fresh())->post(route('role.switch'), ['role' => 'tutor'])->assertSessionHas('active_role', 'tutor');
        $this->assertSame('tutor', $student->fresh()->current_role);
        $this->get(route('tutor.profile.edit'))->assertOk();
    }

    public function test_tutor_registration_sends_pending_request_and_student_registration_does_not(): void
    {
        $this->post(route('register'), $this->applicationData() + ['role' => 'tutor', 'email' => 'newtutor@example.com',
            'password' => 'Demo12345!', 'password_confirmation' => 'Demo12345!'])->assertSessionHasNoErrors()->assertRedirect(route('tutor.application.index'));
        $user = User::where('email', 'newtutor@example.com')->firstOrFail();
        $this->assertSame('student', $user->current_role);
        $this->assertNotNull($user->studentProfile);
        $this->assertSame('pending', $user->tutorProfile->approval_status);
        $this->assertDatabaseHas('notifications', ['Users_user_id' => 'DEMOADM001', 'NotificationType_notification_type_id' => 'TUTORAPP01']);
        $this->get(route('tutor.subjects.index'))->assertRedirect(route('home'));
        $this->post(route('logout'));
        $this->post(route('register'), ['first_name' => 'นักเรียน', 'last_name' => 'ใหม่', 'role' => 'student',
            'email' => 'newstudent@example.com', 'password' => 'Demo12345!', 'password_confirmation' => 'Demo12345!'])->assertSessionHasNoErrors();
        $student = User::where('email', 'newstudent@example.com')->firstOrFail();
        $this->assertNull($student->tutorProfile);
        $this->assertNotNull($student->studentProfile);
    }

    public function test_rejected_application_needs_reason_and_can_be_corrected_and_resubmitted(): void
    {
        $student = User::where('email', 'favorites.demo@example.com')->firstOrFail();
        $admin = User::where('email', 'admin.demo@example.com')->firstOrFail();
        $this->actingAs($student)->post(route('tutor.application.submit'), $this->applicationData())->assertSessionHasNoErrors();
        $profile = $student->fresh()->tutorProfile;
        $this->actingAs($admin)->patch(route('admin.tutor-applications.decide', $profile->tutor_id), ['status' => 'rejected'])->assertSessionHasErrors('reject_reason');
        $this->patch(route('admin.tutor-applications.decide', $profile->tutor_id), ['status' => 'rejected', 'reject_reason' => 'กรุณาเพิ่มรายละเอียดการสอน'])->assertSessionHasNoErrors();
        $this->actingAs($student)->get(route('tutor.application.index'))->assertSee('กรุณาเพิ่มรายละเอียดการสอน');
        $this->post(route('tutor.application.submit'), $this->applicationData())->assertSessionHasNoErrors();
        $this->assertSame('pending', $profile->fresh()->approval_status);
        $this->assertNull($profile->fresh()->reject_reason);
    }

    public function test_pending_old_tutor_and_stale_session_cannot_use_tutor_role_or_public_profile(): void
    {
        $tutor = User::where('email', 'tutor1.demo@example.com')->firstOrFail();
        $tutor->tutorProfile->update(['approval_status' => 'pending', 'submitted_at' => now()]);
        $this->actingAs($tutor)->withSession(['active_role' => 'tutor'])->get(route('tutor.subjects.index'))->assertRedirect(route('home'));
        $this->assertSame('student', $tutor->fresh()->current_role);
        $this->assertNotNull($tutor->fresh()->studentProfile);
        $student = User::where('email', 'favorites.demo@example.com')->firstOrFail();
        $this->actingAs($student)->get(route('tutor.show', $tutor->tutorProfile->tutor_id))->assertNotFound();
        $this->get(route('student.tutors.index'))->assertDontSee(route('tutor.show', $tutor->tutorProfile->tutor_id), false);
    }

    public function test_invalid_application_and_non_admin_decision_cannot_change_status(): void
    {
        $student = User::where('email', 'favorites.demo@example.com')->firstOrFail();
        $this->actingAs($student)->post(route('tutor.application.submit'), ['first_name' => 'ชื่อ', 'last_name' => 'สกุล'])->assertSessionHasErrors(['bio', 'subjects']);
        $this->assertNull($student->fresh()->tutorProfile);
        $profile = TutorProfile::firstOrFail();
        $this->patch(route('admin.tutor-applications.decide', $profile->tutor_id), ['status' => 'approved'])->assertForbidden();
        $this->assertSame('draft',$profile->fresh()->approval_status);
    }
}
