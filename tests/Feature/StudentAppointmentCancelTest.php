<?php

namespace Tests\Feature;

use App\Domains\Auth\Models\User;
use App\Domains\Booking\Models\Appointment;
use Database\Seeders\AccountSeeder;
use Database\Seeders\SubjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class StudentAppointmentCancelTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_cancel_pending_and_confirmed_appointments(): void
    {
        // ใช้ข้อมูลทดสอบใน SQLite ชั่วคราว ไม่แก้ข้อมูลจริง
        $this->seed([SubjectSeeder::class, AccountSeeder::class]);
        $student = User::where('email', 'favorites.demo@example.com')->firstOrFail();
        $tutor = User::where('email', 'tutor1.demo@example.com')->firstOrFail();
        $otherStudent = User::where('email', 'student2.demo@example.com')->firstOrFail();
        $tutor->tutorProfile->update(['approval_status' => 'approved']);
        $date = now()->addDays(2)->format('Y-m-d');
        $times = [
            'start_datetime' => $date.' 10:00:00',
            'end_datetime' => $date.' 11:00:00',
        ];

        foreach ([$student, $tutor] as $user) {
            $this->actingAs($user)->post(route('availabilities.store'), $times)
                ->assertSessionHasNoErrors()->assertRedirect();
        }

        foreach (['pending', 'confirmed'] as $status) {
            $this->actingAs($student)->post(route('appointments.store'), $times + [
                'tutor_id' => $tutor->tutorProfile->tutor_id,
                'subject_id' => $tutor->tutorProfile->subjects->first()->subject_id,
            ])->assertSessionHasNoErrors()->assertRedirect();
            $appointment = Appointment::where('status', 'pending')->firstOrFail();

            if ($status === 'confirmed') {
                $this->actingAs($tutor)->post(route('appointments.status', $appointment), [
                    'status' => 'confirmed',
                ])->assertSessionHasNoErrors();
            }

            $cancelUrl = route('appointments.cancel', $appointment);
            $this->actingAs($student)->get(route('appointments.show', $appointment))->assertOk()
                ->assertSee('ยกเลิกนัดหมาย')->assertSee($cancelUrl, false);
            $this->actingAs($otherStudent)->post($cancelUrl)->assertForbidden();
            $this->assertSame($status, $appointment->fresh()->status);

            $this->actingAs($student)->post($cancelUrl)->assertRedirect(route('appointments.index'));
            $this->assertSame('cancelled', $appointment->fresh()->status);
            $this->assertDatabaseHas('notifications', [
                'Users_user_id' => $tutor->user_id,
                'message' => 'การนัดหมาย '.$appointment->Appointment_id.' ถูกยกเลิก',
            ]);
            $this->get(route('appointments.show', $appointment))->assertOk()->assertDontSee('ยกเลิกนัดหมาย');
            $this->post($cancelUrl)->assertUnprocessable();
            $this->get(route('tutor.show', $tutor->tutorProfile->tutor_id))
                ->assertViewHas('availabilities', fn ($times) => count($times) === 1);
        }
    }

    public function test_availability_validation_uses_thai_time(): void
    {
        $this->assertSame('Asia/Bangkok', config('app.timezone'));
        $this->assertSame('Asia/Bangkok', date_default_timezone_get());
        $this->travelTo(Carbon::parse('2026-10-20 14:00:00', 'Asia/Bangkok'));
        $student = User::factory()->create(['role' => 'student', 'current_role' => 'student']);

        // เวลา 13:00 ผ่านไปแล้วตามเวลาไทย ต้องไม่รับเป็นเวลาว่างใหม่
        $this->actingAs($student)->post(route('availabilities.store'), [
            'start_datetime' => '2026-10-20 13:00:00',
            'end_datetime' => '2026-10-20 14:00:00',
        ])->assertSessionHasErrors('start_datetime');

        $this->post(route('availabilities.store'), [
            'start_datetime' => '2026-10-20 15:00:00',
            'end_datetime' => '2026-10-20 16:00:00',
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('availabilities', [
            'user_id' => $student->user_id,
            'start_datetime' => '2026-10-20 15:00:00',
            'end_datetime' => '2026-10-20 16:00:00',
        ]);
    }
}
