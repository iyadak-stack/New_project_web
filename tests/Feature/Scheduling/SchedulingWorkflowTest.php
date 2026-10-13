<?php

namespace Tests\Feature\Scheduling;

use App\Domains\Auth\Models\User;
use App\Domains\Scheduling\Services\ScheduleBookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SchedulingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_scheduling_works_with_the_latest_branch_schema(): void
    {
        config(['app.key' => 'base64:'.base64_encode(str_repeat('a', 32))]);
        $this->travelTo(Carbon::parse('2026-10-13 12:00:00'));
        $student = User::create([
            'user_id' => 'USER000001', 'first_name' => 'Student', 'last_name' => 'Test',
            'email' => 'student@scheduling.test', 'password' => 'Password123!',
            'role' => 'student', 'current_role' => 'student', 'is_active' => true,
        ]);
        $teacher = User::create([
            'user_id' => 'USER000002', 'first_name' => 'Tutor', 'last_name' => 'Test',
            'email' => 'tutor@scheduling.test', 'password' => 'Password123!',
            'role' => 'tutor', 'current_role' => 'tutor', 'is_active' => true,
        ]);
        DB::table('student_profiles')->insert(['id' => 'STUDENT001', 'user_id' => $student->user_id]);
        DB::table('tutor_profiles')->insert(['tutor_id' => 'TUTOR00001', 'Users_user_id' => $teacher->user_id]);
        DB::table('subjects')->insert(['subject_id' => 'SUBJECT001', 'subject_name' => 'คณิตศาสตร์']);
        $times = ['start_datetime' => '2026-10-14 15:00:00', 'end_datetime' => '2026-10-14 17:00:00'];
        $this->actingAs($student);
        $this->post(route('availabilities.store'), $times)->assertRedirect(route('availabilities.index'));
        $this->get(route('availabilities.index'))->assertOk()->assertSee('CSS/home.css');
        $this->actingAs($teacher);
        $this->post(route('availabilities.store'), $times)->assertRedirect(route('availabilities.index'));
        $this->actingAs($student);
        $filters = array_merge($times, ['tutor_id' => $teacher->user_id]);
        $this->post(route('schedule.check.results'), $filters)->assertOk()->assertViewHas('times', [
            ['start' => '2026-10-14 15:00', 'end' => '2026-10-14 17:00'],
        ]);
        $this->get(route('schedule.booking', $filters))->assertOk()->assertSee('TUTOR00001');
        $this->getJson(route('schedule.availability', $filters))->assertOk()->assertJson(['available' => true]);
        $data = [
            'Appointment_id' => 'APP0000001', 'status' => 'pending',
            'Subject_subject_id' => 'SUBJECT001',
            'Student_profiles_student_id' => $student->user_id,
            'Tutor_profiles_tutor_id' => 'TUTOR00001',
            'start_datetime' => '2026-10-14 15:00:00', 'end_datetime' => '2026-10-14 16:00:00',
        ];
        $service = app(ScheduleBookingService::class);
        $appointment = $service->save($data);
        $service->save($data, $appointment);
        try {
            $service->save(array_replace($data, [
                'start_datetime' => '2026-10-14 18:00:00',
                'end_datetime' => '2026-10-14 19:00:00',
            ]), $appointment);
            $this->fail('An appointment was moved outside availability.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('start_datetime', $exception->errors());
        }
        $this->post(route('schedule.check.results'), $filters)->assertOk()->assertViewHas('times', [
            ['start' => '2026-10-14 16:00', 'end' => '2026-10-14 17:00'],
        ]);
        $this->getJson(route('schedule.availability', $filters))->assertOk()->assertJson(['available' => false]);
        $remaining = array_replace($filters, ['start_datetime' => '2026-10-14 16:00:00']);
        $this->getJson(route('schedule.availability', $remaining))->assertOk()->assertJson(['available' => true]);
        $this->travelTo(Carbon::parse('2026-10-15 12:00:00'));
        $this->get(route('schedule.history'))->assertOk()->assertSee('นักเรียน')->assertSee('คณิตศาสตร์');
        $this->actingAs($teacher);
        $this->get(route('schedule.history'))->assertOk()->assertSee('ติวเตอร์')->assertSee('คณิตศาสตร์');
        $data['Appointment_id'] = 'APP0000002';
        $this->expectException(ValidationException::class);
        $service->save($data);
    }
}
