<?php

namespace Tests\Feature\Scheduling;

use App\Domains\Auth\Models\User;
use App\Domains\Scheduling\Services\AvailabilityService;
use App\Domains\Scheduling\Services\LearningHistoryService;
use App\Domains\Scheduling\Services\ScheduleBookingService;
use App\Domains\Scheduling\Services\ScheduleMatcher;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AvailabilityServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:'.base64_encode(str_repeat('a', 32))]);

        Schema::create('availabilities', function (Blueprint $table): void {
            $table->char('availability_id', 10)->primary();
            $table->char('user_id', 10);
            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime');
            $table->timestamps();
        });

        Schema::create('student_profiles', function (Blueprint $table): void {
            $table->char('id', 10)->primary();
            $table->char('user_id', 10);
        });

        Schema::create('tutor_profiles', function (Blueprint $table): void {
            $table->char('id', 10)->primary();
            $table->char('user_id', 10);
        });

        Schema::create('subjects', function (Blueprint $table): void {
            $table->char('subject_id', 10)->primary();
            $table->string('subject_name');
        });

        Schema::create('notifications', function (Blueprint $table): void {
            $table->string('notification_id')->primary();
            $table->string('message');
            $table->boolean('is_read');
            $table->string('Users_user_id');
            $table->string('NotificationType_notification_type_id');
            $table->timestamps();
        });

        Schema::create('appointments', function (Blueprint $table): void {
            $table->string('Appointment_id', 10)->primary();
            $table->string('status', 45);
            $table->dateTime('start_datetime')->nullable();
            $table->dateTime('end_datetime')->nullable();
            $table->string('Subject_subject_id', 10);
            $table->string('Tutor_profiles_tutor_id', 10);
            $table->string('Student_profiles_student_id', 10);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('tutor_profiles');
        Schema::dropIfExists('student_profiles');
        Schema::dropIfExists('availabilities');

        parent::tearDown();
    }

    public function test_it_uses_real_appointment_times_and_ignores_cancelled_appointments(): void
    {
        DB::table('student_profiles')->insert(['id' => 1, 'user_id' => 101]);
        DB::table('tutor_profiles')->insert(['id' => 2, 'user_id' => 202]);

        DB::table('availabilities')->insert([
            [
                'availability_id' => 'AVAIL00001',
                'user_id' => '101',
                'start_datetime' => '2026-09-30 15:00:00',
                'end_datetime' => '2026-09-30 17:00:00',
            ],
            [
                'availability_id' => 'AVAIL00002',
                'user_id' => '202',
                'start_datetime' => '2026-09-30 15:00:00',
                'end_datetime' => '2026-09-30 17:00:00',
            ],
        ]);

        DB::table('appointments')->insert([
            [
                'Appointment_id' => 'APP0000001',
                'status' => 'confirmed',
                'start_datetime' => '2026-09-30 15:00:00',
                'end_datetime' => '2026-09-30 16:00:00',
                'Subject_subject_id' => 'SUBJECT001',
                'Tutor_profiles_tutor_id' => '2',
                'Student_profiles_student_id' => '1',
            ],
            [
                'Appointment_id' => 'APP0000002',
                'status' => 'cancelled',
                'start_datetime' => '2026-09-30 16:00:00',
                'end_datetime' => '2026-09-30 17:00:00',
                'Subject_subject_id' => 'SUBJECT001',
                'Tutor_profiles_tutor_id' => '2',
                'Student_profiles_student_id' => '1',
            ],
        ]);

        $service = new AvailabilityService(new ScheduleMatcher);

        $times = $service->findSharedTimes(
            '101',
            '202',
            Carbon::parse('2026-09-30 15:00'),
            Carbon::parse('2026-09-30 17:00'),
        );

        $this->assertSame([
            ['start' => '2026-09-30 16:00', 'end' => '2026-09-30 17:00'],
        ], $times);
    }

    public function test_history_only_contains_past_appointments_for_the_users_profiles(): void
    {
        $this->travelTo(Carbon::parse('2026-10-06 12:00:00'));
        DB::table('student_profiles')->insert(['id' => 1, 'user_id' => 101]);
        DB::table('tutor_profiles')->insert(['id' => 2, 'user_id' => 101]);

        foreach ([
            ['APP0000001', '1', '9', 'confirmed', '2026-10-05', null],
            ['APP0000002', '9', '2', 'cancelled', '2026-10-04', null],
            ['APP0000003', '9', '9', 'confirmed', '2026-10-05', null],
            ['APP0000004', '1', '9', 'confirmed', '2026-10-07', null],
            ['APP0000005', '1', '9', 'confirmed', '2026-10-05', '2026-10-05 18:00:00'],
        ] as [$id, $student, $tutor, $status, $date, $deletedAt]) {
            DB::table('appointments')->insert([
                'Appointment_id' => $id,
                'Student_profiles_student_id' => $student,
                'Tutor_profiles_tutor_id' => $tutor,
                'Subject_subject_id' => 'SUBJECT001',
                'status' => $status,
                'start_datetime' => $date.' 15:00:00',
                'end_datetime' => $date.' 16:00:00',
                'deleted_at' => $deletedAt,
            ]);
        }

        $history = (new LearningHistoryService)->forUser('101');

        $this->assertSame(['APP0000001', 'APP0000002'], $history->pluck('id')->all());
        $this->assertSame(['นักเรียน', 'ติวเตอร์'], $history->pluck('role')->all());
        $this->assertSame('cancelled', $history->last()['status']);
        $this->assertEmpty((new LearningHistoryService)->forUser('999'));
    }

    public function test_booking_rejects_student_and_tutor_conflicts_but_allows_adjacent_times(): void
    {
        DB::table('student_profiles')->insert([
            ['id' => 1, 'user_id' => 101], ['id' => 3, 'user_id' => 303],
        ]);
        DB::table('tutor_profiles')->insert([
            ['id' => 2, 'user_id' => 202], ['id' => 4, 'user_id' => 404],
        ]);

        foreach ([101, 202, 303, 404] as $userId) {
            DB::table('availabilities')->insert([
                'availability_id' => 'AVAIL'.$userId,
                'user_id' => (string) $userId,
                'start_datetime' => '2026-10-07 14:00:00',
                'end_datetime' => '2026-10-07 18:00:00',
            ]);
        }

        $service = new ScheduleBookingService;
        $data = [
            'Appointment_id' => 'APP0000001',
            'status' => 'pending',
            'Student_profiles_student_id' => '1',
            'Tutor_profiles_tutor_id' => '2',
            'Subject_subject_id' => 'SUBJECT001',
            'start_datetime' => '2026-10-07 15:00:00',
            'end_datetime' => '2026-10-07 16:00:00',
        ];
        $first = $service->save($data);

        foreach ([['1', '4'], ['3', '2']] as [$student, $tutor]) {
            try {
                $service->save(array_replace($data, [
                    'Appointment_id' => 'APP0000002',
                    'Student_profiles_student_id' => $student,
                    'Tutor_profiles_tutor_id' => $tutor,
                ]));
                $this->fail('An overlapping appointment was accepted.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('start_datetime', $exception->errors());
            }
        }

        $this->assertSame(1, DB::table('appointments')->count());
        $service->save(array_replace($data, [
            'Appointment_id' => 'APP0000003',
            'start_datetime' => '2026-10-07 16:00:00',
            'end_datetime' => '2026-10-07 17:00:00',
        ]));
        $this->assertSame(2, DB::table('appointments')->count());

        $service->save(array_replace($data, ['status' => 'cancelled']), $first);
        $service->save(array_replace($data, ['Appointment_id' => 'APP0000004']));
        $this->assertSame(3, DB::table('appointments')->count());

        try {
            $service->save(array_replace($data, ['status' => 'confirmed']), $first);
            $this->fail('A cancelled appointment was restored into a booked time.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('start_datetime', $exception->errors());
        }

        $this->assertSame('cancelled', $first->fresh()->status);
    }

    public function test_student_can_add_availability_prepare_booking_and_view_service_history(): void
    {
        $this->withoutVite();
        $this->travelTo(Carbon::parse('2026-10-06 12:00:00'));
        $user = new User;
        $user->forceFill(['user_id' => 'USER000001', 'first_name' => 'Student', 'last_name' => 'Test', 'name' => 'Student Test', 'email' => 'student@example.test', 'current_role' => 'student']);
        $this->actingAs($user);
        DB::table('student_profiles')->insert(['id' => 'STUDENT001', 'user_id' => 'USER000001']);
        DB::table('tutor_profiles')->insert(['id' => 'TUTOR00001', 'user_id' => 'USER000002']);
        DB::table('subjects')->insert(['subject_id' => 'SUBJECT001', 'subject_name' => 'คณิตศาสตร์']);
        DB::table('availabilities')->insert([
            'availability_id' => 'AVAIL00001', 'user_id' => 'USER000002',
            'start_datetime' => '2026-10-07 15:00:00', 'end_datetime' => '2026-10-07 17:00:00',
        ]);

        $this->post(route('availabilities.store'), [
            'start_datetime' => '2026-10-07 15:00:00', 'end_datetime' => '2026-10-07 17:00:00',
        ])->assertRedirect(route('availabilities.index'));
        $this->get(route('availabilities.index'))->assertOk()->assertSee('07/10/2026 15:00');

        $filters = ['tutor_id' => 'USER000002', 'start_datetime' => '2026-10-07 15:00:00', 'end_datetime' => '2026-10-07 17:00:00'];
        $this->post(route('schedule.check.results'), $filters)->assertOk()->assertSee('เลือกวิชาและจอง');
        $this->get(route('schedule.booking', $filters))->assertOk()->assertSee('คณิตศาสตร์')->assertSee('STUDENT001')->assertSee('TUTOR00001');

        $booking = ['subject_id' => 'SUBJECT001', 'student_id' => 'STUDENT001', 'tutor_id' => 'TUTOR00001', 'start_datetime' => '2026-10-07 15:00:00', 'end_datetime' => '2026-10-07 16:00:00'];
        $service = app(ScheduleBookingService::class);
        $service->save([
            'Appointment_id' => 'APP0000001', 'status' => 'pending',
            'Subject_subject_id' => $booking['subject_id'],
            'Student_profiles_student_id' => $booking['student_id'],
            'Tutor_profiles_tutor_id' => $booking['tutor_id'],
            'start_datetime' => $booking['start_datetime'], 'end_datetime' => $booking['end_datetime'],
        ]);
        $this->assertSame(1, DB::table('appointments')->count());
        $this->post(route('schedule.check.results'), $filters)->assertOk()->assertViewHas('times', [
            ['start' => '2026-10-07 16:00', 'end' => '2026-10-07 17:00'],
        ]);

        $this->travelTo(Carbon::parse('2026-10-08 12:00:00'));
        $this->get(route('schedule.history'))->assertOk()->assertSee('นักเรียน')->assertSee('07/10/2026 15:00');
    }

    public function test_matching_subtracts_bookings_even_when_the_user_switches_roles(): void
    {
        DB::table('student_profiles')->insert(['id' => 'STUDENT001', 'user_id' => 'USER000002']);
        DB::table('tutor_profiles')->insert(['id' => 'TUTOR00001', 'user_id' => 'USER000001']);
        foreach (['USER000001', 'USER000002'] as $index => $userId) {
            DB::table('availabilities')->insert([
                'availability_id' => 'AVAIL0000'.$index, 'user_id' => $userId,
                'start_datetime' => '2026-10-07 15:00:00', 'end_datetime' => '2026-10-07 17:00:00',
            ]);
        }
        DB::table('appointments')->insert([
            'Appointment_id' => 'APP0000001', 'status' => 'pending',
            'Student_profiles_student_id' => 'STUDENT001', 'Tutor_profiles_tutor_id' => 'TUTOR00001',
            'Subject_subject_id' => 'SUBJECT001', 'start_datetime' => '2026-10-07 15:00:00', 'end_datetime' => '2026-10-07 16:00:00',
        ]);
        $times = app(AvailabilityService::class)->findSharedTimes('USER000001', 'USER000002', Carbon::parse('2026-10-07 15:00'), Carbon::parse('2026-10-07 17:00'));
        $this->assertSame([['start' => '2026-10-07 16:00', 'end' => '2026-10-07 17:00']], $times);
    }

    public function test_missing_appointment_schema_does_not_appear_as_free_time(): void
    {
        Schema::drop('appointments');
        $this->expectException(ValidationException::class);
        app(AvailabilityService::class)->findSharedTimes('101', '202', Carbon::parse('2026-10-07 15:00'), Carbon::parse('2026-10-07 17:00'));
    }

    public function test_availability_crud_rejects_overlap_and_other_users_edits(): void
    {
        $this->withoutVite();
        $user = new User;
        $user->forceFill(['user_id' => 'USER000001', 'first_name' => 'Student', 'current_role' => 'student']);
        $this->actingAs($user);
        $data = ['start_datetime' => '2026-10-07 15:00:00', 'end_datetime' => '2026-10-07 17:00:00'];
        $this->post(route('availabilities.store'), $data)->assertRedirect(route('availabilities.index'));
        $this->post(route('availabilities.store'), $data)->assertSessionHasErrors('start_datetime');
        $id = DB::table('availabilities')->value('availability_id');
        $this->put(route('availabilities.update', $id), array_replace($data, ['end_datetime' => '2026-10-07 18:00:00']))->assertRedirect();
        $this->assertSame('2026-10-07 18:00:00', DB::table('availabilities')->value('end_datetime'));
        $other = new User;
        $other->forceFill(['user_id' => 'USER000002', 'first_name' => 'Other', 'current_role' => 'student']);
        $this->actingAs($other);
        $this->get(route('availabilities.edit', $id))->assertForbidden();
        $this->put(route('availabilities.update', $id), $data)->assertForbidden();
        $this->delete(route('availabilities.destroy', $id))->assertForbidden();
        $this->actingAs($user);
        $this->delete(route('availabilities.destroy', $id))->assertRedirect();
        $this->assertSame(0, DB::table('availabilities')->count());
    }
}
