<?php

namespace Tests\Feature;

use App\Domains\Auth\Models\Contact;
use App\Domains\Auth\Models\User;
use App\Domains\Booking\Models\Appointment;
use App\Domains\Booking\Models\Notification;
use App\Domains\Scheduling\Models\Availability;
use Database\Seeders\AccountSeeder;
use Database\Seeders\SubjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TutorTeachingTest extends TestCase
{
    use RefreshDatabase;

    private User $tutor;

    private User $student;

    private string $date;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([SubjectSeeder::class, AccountSeeder::class]);
        \App\Domains\TutorProfile\Models\TutorProfile::query()->update(['approval_status' => 'approved']);
        $this->tutor = User::where('email', 'tutor1.demo@example.com')->firstOrFail();
        $this->student = User::where('email', 'favorites.demo@example.com')->firstOrFail();
        $this->date = now('Asia/Bangkok')->addDays(2)->format('Y-m-d');
    }

    private function openSlot(): Availability
    {
        // ระบบของเพื่อนต้องมีเวลาว่างทั้งผู้เรียนและติวเตอร์
        Availability::create([
            'availability_id' => 'STUSLOT001', 'user_id' => $this->student->user_id,
            'start_datetime' => $this->date.' 10:00:00', 'end_datetime' => $this->date.' 11:00:00',
        ]);
        return Availability::create([
            'availability_id' => 'SLOT000001', 'user_id' => $this->tutor->user_id,
            'start_datetime' => $this->date.' 10:00:00', 'end_datetime' => $this->date.' 11:00:00',
        ]);
    }

    private function requestData(): array
    {
        return [
            'tutor_id' => $this->tutor->tutorProfile->tutor_id,
            'subject_id' => $this->tutor->tutorProfile->subjects->first()->subject_id,
            'start_datetime' => $this->date.' 10:00:00', 'end_datetime' => $this->date.' 11:00:00',
        ];
    }

    private function book(): Appointment
    {
        $this->actingAs($this->student)->post(route('appointments.store'), $this->requestData())
            ->assertSessionHasNoErrors()->assertRedirect(route('tutor.show', $this->tutor->tutorProfile->tutor_id));

        return Appointment::firstOrFail();
    }

    public function test_tutor_menu_has_active_my_subjects_and_students_cannot_use_it(): void
    {
        $tutorPage = $this->actingAs($this->tutor)->get(route('tutor.subjects.index'))->assertOk()
            ->assertSee('menu-btn active', false)->assertSee('วิชาของฉัน')
            ->assertDontSee('รายวิชาทั้งหมด')->assertDontSee('ติวเตอร์ทั้งหมด')
            ->assertDontSee('name="start_time"', false);
        $tutorPage->assertSee(route('availabilities.index'), false);
        $this->actingAs($this->student)->get(route('tutor.subjects.index'))->assertRedirect(route('home'));
        $this->get(route('tutor.profile'))->assertRedirect(route('home'));
        $this->put(route('tutor.subjects.update'), ['teaching_mode' => 'online'])->assertRedirect(route('home'));
    }

    public function test_settings_update_only_the_logged_in_tutor_and_do_not_delete_global_subjects(): void
    {
        $other = User::where('email', 'tutor2.demo@example.com')->firstOrFail();
        $otherSubjects = $other->tutorProfile->subjects->pluck('subject_id')->all();
        $this->actingAs($this->tutor)->put(route('tutor.subjects.update'), [
            'subjects' => ['SUB-002'], 'teaching_mode' => 'both', 'line_id' => 'lesson-line',
            'google_meet_link' => 'https://meet.google.com/demo-room',
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame(['SUB-002'], $this->tutor->fresh()->tutorProfile->subjects->pluck('subject_id')->all());
        $this->assertSame('lesson-line', $this->tutor->fresh()->contact->line_id);
        $this->assertSame($otherSubjects, $other->fresh()->tutorProfile->subjects->pluck('subject_id')->all());
        $this->put(route('tutor.subjects.update'), ['teaching_mode' => 'online'])->assertRedirect();
        $this->assertCount(0, $this->tutor->fresh()->tutorProfile->subjects);
        $this->assertDatabaseCount('subjects', 8);
        $this->assertFalse(Schema::hasColumn('appointments', 'teaching_mode'));
    }

    public function test_availability_uses_user_id_blocks_overlap_and_protects_other_tutors_slots(): void
    {
        $this->actingAs($this->tutor)->post(route('availabilities.store'), [
            'start_datetime' => $this->date.' 10:00:00', 'end_datetime' => $this->date.' 11:00:00',
        ])->assertSessionHasNoErrors()->assertRedirect();
        $slot = Availability::firstOrFail();
        $this->assertSame($this->tutor->user_id, $slot->user_id);
        $this->post(route('availabilities.store'), [
            'start_datetime' => $this->date.' 10:30:00', 'end_datetime' => $this->date.' 11:30:00',
        ])->assertSessionHasErrors('start_datetime');
        $this->assertDatabaseCount('availabilities', 1);
        $other = User::where('email', 'tutor2.demo@example.com')->firstOrFail();
        $this->actingAs($other)->delete(route('availabilities.destroy', $slot->availability_id))->assertForbidden();
        $this->actingAs($this->tutor)->delete(route('availabilities.destroy', $slot->availability_id))->assertRedirect();
        $this->assertDatabaseCount('availabilities', 0);
    }

    public function test_booking_confirmation_appears_for_the_correct_student_with_current_channels(): void
    {
        $this->openSlot();
        Contact::create(['contact_id' => 'CONTACT001', 'Users_user_id' => $this->tutor->user_id, 'line_id' => 'lesson-line', 'google_meet_link' => 'https://meet.google.com/demo-room']);
        $this->get(route('tutor.show', $this->tutor->tutorProfile->tutor_id))->assertOk()
            ->assertViewHas('availabilities', fn ($slots) => count($slots) === 1 && $slots[0]['available_date'] === $this->date);
        $appointment = $this->book();
        $this->assertSame($this->student->user_id, $appointment->Student_profiles_student_id);
        $this->assertDatabaseHas('notifications', ['Users_user_id' => $this->tutor->user_id, 'NotificationType_notification_type_id' => 'TYPE01']);
        $this->get(route('appointments.show', $appointment->Appointment_id))->assertOk()->assertDontSee('https://meet.google.com/demo-room');
        $this->actingAs($this->tutor)->get(route('appointments.index'))->assertSee('รอการยืนยัน');
        $this->post(route('appointments.confirm', $appointment->Appointment_id))->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('confirmed', $appointment->fresh()->status);
        $this->assertDatabaseHas('notifications', ['Users_user_id' => $this->student->user_id, 'NotificationType_notification_type_id' => 'TYPE02']);
        $this->actingAs($this->student)->get(route('appointments.index', ['status' => 'confirmed']))->assertOk()->assertSee(route('appointments.show', $appointment->Appointment_id), false);
        $this->get(route('appointments.show', $appointment->Appointment_id))->assertOk()->assertSee('https://meet.google.com/demo-room')->assertSee('lesson-line');
        $this->get('/')->assertOk()->assertSee('การเรียนที่ติวเตอร์ยืนยันแล้ว')->assertSee(route('appointments.show', $appointment->Appointment_id), false);
        $this->get(route('notifications.index'))->assertSee('ยืนยันแล้ว');
        $this->get(route('tutor.show', $this->tutor->tutorProfile->tutor_id))->assertViewHas('availabilities', fn ($slots) => count($slots) === 0);
        $this->actingAs($this->tutor)->post(route('appointments.confirm', $appointment->Appointment_id))->assertUnprocessable();
        $this->assertDatabaseCount('notifications', 2);
        $this->delete(route('availabilities.destroy', 'SLOT000001'))->assertSessionHasErrors('start_datetime');
        $this->assertSame('confirmed', $appointment->fresh()->status);
    }

    public function test_notifications_can_only_be_read_or_deleted_by_the_owner(): void
    {
        $this->openSlot();
        $this->book();
        $notification = Notification::firstOrFail();
        $this->actingAs($this->student)->post(route('notifications.read', $notification->notification_id))->assertNotFound();
        $this->delete(route('notifications.destroy', $notification->notification_id))->assertNotFound();
        $this->actingAs($this->tutor)->post(route('notifications.read', $notification->notification_id))->assertRedirect();
        $this->assertTrue((bool) $notification->fresh()->is_read);
        $this->delete(route('notifications.destroy', $notification->notification_id))->assertRedirect();
        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_other_students_and_tutors_cannot_read_or_change_an_appointment(): void
    {
        $this->openSlot();
        $appointment = $this->book();
        $otherStudent = User::where('email', 'student2.demo@example.com')->firstOrFail();
        $this->actingAs($otherStudent)->get(route('appointments.show', $appointment->Appointment_id))->assertNotFound();
        $this->get(route('appointments.index'))->assertDontSee(route('appointments.show', $appointment->Appointment_id), false);
        $this->post(route('appointments.confirm', $appointment->Appointment_id))->assertForbidden();
        $otherTutor = User::where('email', 'tutor2.demo@example.com')->firstOrFail();
        $this->actingAs($otherTutor)->post(route('appointments.cancel', $appointment->Appointment_id))->assertForbidden();
        $this->assertSame('pending', $appointment->fresh()->status);
    }

    public function test_booking_checks_subject_availability_and_student_identity(): void
    {
        $this->openSlot();
        $this->actingAs($this->student)->post(route('appointments.store'), array_replace($this->requestData(), ['subject_id' => 'SUB-007']))->assertUnprocessable();
        $this->post(route('appointments.store'), array_replace($this->requestData(), ['start_datetime' => $this->date.' 12:00:00', 'end_datetime' => $this->date.' 13:00:00']))->assertSessionHasErrors('start_datetime');
        $this->assertDatabaseCount('appointments', 0);
        $this->post(route('appointments.store'), $this->requestData() + ['student_id' => 'FAKEUSER01'])->assertSessionHasNoErrors();
        $this->assertSame($this->student->user_id, Appointment::firstOrFail()->Student_profiles_student_id);
        $this->post(route('appointments.store'), $this->requestData())->assertSessionHasErrors('start_datetime');
        $this->assertDatabaseCount('appointments', 1);
    }

    public function test_pending_booking_reserves_time_and_repeated_decision_is_rejected(): void
    {
        $this->openSlot();
        $first = $this->book();
        $other = User::where('email', 'student2.demo@example.com')->firstOrFail();
        Availability::create([
            'availability_id' => 'OTHERSLOT1', 'user_id' => $other->user_id,
            'start_datetime' => $this->date.' 10:00:00', 'end_datetime' => $this->date.' 11:00:00',
        ]);
        // pending จองเวลาไว้แล้วตามกฎของ branch เพื่อน
        $this->actingAs($other)->post(route('appointments.store'), $this->requestData())->assertSessionHasErrors('start_datetime');
        $this->assertDatabaseCount('appointments', 1);
        $this->actingAs($this->tutor)->post(route('appointments.confirm', $first->Appointment_id))->assertSessionHasNoErrors();
        $this->post(route('appointments.confirm', $first->Appointment_id))->assertUnprocessable();
        $this->assertSame('confirmed', $first->fresh()->status);
        $this->assertDatabaseCount('notifications', 2);
    }
}
