<?php

namespace Tests\Feature\Scheduling;

use App\Domains\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SchedulingIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $student;

    private User $teacher;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:'.base64_encode(str_repeat('a', 32))]);
        $this->travelTo(Carbon::parse('2026-10-13 12:00:00'));
        $this->student = User::create([
            'user_id' => 'USER000001', 'first_name' => 'Student', 'last_name' => 'Test',
            'email' => 'student-integration@scheduling.test', 'password' => 'Password123!',
            'role' => 'student', 'current_role' => 'student', 'is_active' => true,
        ]);
        $this->teacher = User::create([
            'user_id' => 'USER000002', 'first_name' => 'Tutor', 'last_name' => 'Test',
            'email' => 'tutor-integration@scheduling.test', 'password' => 'Password123!',
            'role' => 'tutor', 'current_role' => 'tutor', 'is_active' => true,
        ]);
        DB::table('student_profiles')->insert(['id' => 'STUDENT001', 'user_id' => $this->student->user_id]);
        DB::table('tutor_profiles')->insert([
            'tutor_id' => 'TUTOR00001', 'Users_user_id' => $this->teacher->user_id,
            'bio' => 'Test tutor', 'experience_years' => 1,
            'teaching_mode' => 'online', 'approval_status' => 'approved',
        ]);
        DB::table('subjects')->insert(['subject_id' => 'SUBJECT001', 'subject_name' => 'คณิตศาสตร์']);
        DB::table('tutor_profiles_has_subjects')->insert([
            'Tutor_profiles_tutor_id' => 'TUTOR00001', 'Subject_subject_id' => 'SUBJECT001',
        ]);
        DB::table('availabilities')->insert([
            ['availability_id' => 'AVAIL00001', 'user_id' => 'USER000001', 'start_datetime' => '2026-10-14 15:00:00', 'end_datetime' => '2026-10-14 17:00:00'],
            ['availability_id' => 'AVAIL00002', 'user_id' => 'USER000002', 'start_datetime' => '2026-10-14 15:00:00', 'end_datetime' => '2026-10-14 16:00:00'],
            ['availability_id' => 'AVAIL00003', 'user_id' => 'USER000002', 'start_datetime' => '2026-10-14 16:00:00', 'end_datetime' => '2026-10-14 17:00:00'],
        ]);
        $this->actingAs($this->student);
    }

    public function test_actual_controller_books_across_two_rows_and_tutor_page_keeps_both_remainders(): void
    {
        $data = $this->bookingData('2026-10-14 15:30:00', '2026-10-14 16:30:00');
        $data['student_id'] = 'OTHERUSER1';
        $this->post(route('appointments.store'), $data)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('tutor.show', 'TUTOR00001'));
        $this->assertDatabaseCount('appointments', 1);
        $this->assertDatabaseHas('appointments', [
            'Student_profiles_student_id' => 'USER000001',
            'start_datetime' => '2026-10-14 15:30:00',
            'end_datetime' => '2026-10-14 16:30:00',
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('notifications', ['Users_user_id' => 'USER000002']);
        $this->get(route('appointments.index'))->assertOk()->assertSee('CSS/edit-profile.css');
        $appointmentId = DB::table('appointments')->value('Appointment_id');
        $this->get(route('appointments.show', $appointmentId))->assertOk()->assertSee('CSS/edit-profile.css');
        $remaining = [
            ['start' => '2026-10-14 15:00', 'end' => '2026-10-14 15:30'],
            ['start' => '2026-10-14 16:30', 'end' => '2026-10-14 17:00'],
        ];
        $this->get(route('tutor.show', 'TUTOR00001'))
            ->assertOk()
            ->assertViewHas('availableTimes', $remaining)
            ->assertViewHas('bookableTimes', $remaining)
            ->assertSee('name="start_datetime"', false)
            ->assertSee('data-end="2026-10-14T17:00"', false)
            ->assertDontSee('name="availability_id"', false);
    }

    public function test_actual_controller_rejects_a_one_minute_gap_without_creating_a_notification(): void
    {
        DB::table('availabilities')->where('availability_id', 'AVAIL00003')
            ->update(['start_datetime' => '2026-10-14 16:01:00']);
        $this->post(route('appointments.store'), $this->bookingData('2026-10-14 15:30:00', '2026-10-14 16:30:00'))
            ->assertSessionHasErrors('start_datetime');
        $this->assertDatabaseCount('appointments', 0);
        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_tutor_page_only_offers_booking_times_shared_with_the_student(): void
    {
        DB::table('availabilities')->where('availability_id', 'AVAIL00001')
            ->update(['end_datetime' => '2026-10-14 16:00:00']);
        $this->get(route('tutor.show', 'TUTOR00001'))
            ->assertOk()
            ->assertViewHas('availableTimes', [['start' => '2026-10-14 15:00', 'end' => '2026-10-14 17:00']])
            ->assertViewHas('bookableTimes', [['start' => '2026-10-14 15:00', 'end' => '2026-10-14 16:00']]);
    }

    public function test_old_availability_id_requests_still_work(): void
    {
        $this->post(route('appointments.store'), [
            'subject_id' => 'SUBJECT001', 'tutor_id' => 'TUTOR00001', 'availability_id' => 'AVAIL00002',
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('appointments', [
            'start_datetime' => '2026-10-14 15:00:00', 'end_datetime' => '2026-10-14 16:00:00',
        ]);
    }

    public function test_actual_controller_rejects_a_second_overlapping_booking(): void
    {
        $this->post(route('appointments.store'), $this->bookingData('2026-10-14 15:00:00', '2026-10-14 16:00:00'))
            ->assertSessionHasNoErrors();
        $this->post(route('appointments.store'), $this->bookingData('2026-10-14 15:30:00', '2026-10-14 16:30:00'))
            ->assertSessionHasErrors('start_datetime');
        $this->assertDatabaseCount('appointments', 1);
        $this->assertDatabaseCount('notifications', 1);
    }

    public function test_booking_keeps_tutor_approval_and_self_booking_checks(): void
    {
        DB::table('tutor_profiles')->where('tutor_id', 'TUTOR00001')->update(['approval_status' => 'pending']);
        $data = $this->bookingData('2026-10-14 15:00:00', '2026-10-14 16:00:00');
        $this->post(route('appointments.store'), $data)->assertStatus(422);
        DB::table('tutor_profiles')->where('tutor_id', 'TUTOR00001')->update(['approval_status' => 'approved']);
        $this->actingAs($this->teacher);
        $this->post(route('appointments.store'), $data)->assertStatus(422);
        $this->assertDatabaseCount('appointments', 0);
    }

    private function bookingData(string $start, string $end): array
    {
        return [
            'subject_id' => 'SUBJECT001', 'tutor_id' => 'TUTOR00001',
            'start_datetime' => $start, 'end_datetime' => $end,
        ];
    }
}
