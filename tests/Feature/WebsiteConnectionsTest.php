<?php

namespace Tests\Feature;

use App\Domains\Auth\Models\User;
use App\Domains\Booking\Models\Appointment;
use App\Domains\Reportreview\Models\Review;
use App\Domains\TutorProfile\Models\TutorProfile;
use Database\Seeders\AccountSeeder;
use Database\Seeders\SubjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WebsiteConnectionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([SubjectSeeder::class, AccountSeeder::class]);
        TutorProfile::query()->update(['approval_status' => 'approved']);
    }

    private function student(): User
    {
        return User::where('email', 'favorites.demo@example.com')->firstOrFail();
    }

    private function appointment(string $status = 'completed', bool $legacyId = false): Appointment
    {
        $student = $this->student();

        return Appointment::create([
            'Appointment_id' => 'AUDITAPP01',
            'status' => $status,
            'start_datetime' => now()->subDays(2),
            'end_datetime' => now()->subDay(),
            'Subject_subject_id' => 'SUB-001',
            'Tutor_profiles_tutor_id' => 'DEMOTUT001',
            'Student_profiles_student_id' => $legacyId ? $student->studentProfile->id : $student->user_id,
        ]);
    }

    public function test_password_reset_requires_a_token_and_stores_a_hash(): void
    {
        $student = $this->student();
        $originalHash = $student->password;
        $data = [
            'email' => $student->email,
            'password' => 'Changed12345!',
            'password_confirmation' => 'Changed12345!',
        ];
        $this->post(route('password.update'), $data)->assertSessionHasErrors('token');
        $this->post(route('password.update'), $data + ['token' => 'direct-reset'])->assertSessionHasErrors();
        $this->assertSame($originalHash, $student->fresh()->password);
        $token = Password::createToken($student);
        $this->post(route('password.update'), $data + ['token' => $token])->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('Changed12345!', $student->fresh()->password));
        $this->assertNotSame('Changed12345!', $student->fresh()->password);
    }

    public function test_search_works_and_suspended_tutors_are_not_listed(): void
    {
        $this->get(route('tutor.search', ['search' => 'ณัฐ']))->assertOk()->assertSee('ณัฐ');
        $tutor = User::where('email', 'tutor1.demo@example.com')->firstOrFail();
        $tutor->update(['is_active' => false]);
        $this->get(route('tutor.search', ['search' => 'ณัฐ']))->assertOk()
            ->assertDontSee(route('tutor.show', 'DEMOTUT001'), false);
    }

    public function test_student_profile_updates_interests_and_contact_using_real_keys(): void
    {
        $student = User::factory()->create(['current_role' => 'student']);
        $this->actingAs($student)->get(route('student.profile.edit'))->assertOk();
        $this->post(route('student.profile.update'), [
            'first_name' => 'ชื่อใหม่', 'last_name' => 'สกุลใหม่',
            'bio' => 'สนใจคณิตศาสตร์', 'subjects' => ['SUB-001'], 'line_id' => 'student-line',
        ])->assertSessionHasNoErrors();
        $student = $student->fresh();
        $this->assertSame('ชื่อใหม่', $student->first_name);
        $this->assertSame(['SUB-001'], $student->studentProfile->subjects->pluck('subject_id')->all());
        $this->assertSame('student-line', $student->contact->line_id);
        $this->get(route('student.profile'))->assertOk()->assertSee('ชื่อใหม่');
    }

    public function test_contact_routes_only_allow_the_owner(): void
    {
        $student = $this->student();
        $other = User::where('email', 'student2.demo@example.com')->firstOrFail();
        $this->actingAs($student)->get(route('student-contacts.edit', $student->studentProfile->id))
            ->assertRedirect(route('student.profile.edit'));
        $this->put(route('student-contacts.update', $student->studentProfile->id), ['line_id' => 'owner-line'])
            ->assertSessionHasNoErrors();
        $this->assertSame('owner-line', $student->fresh()->contact->line_id);
        $this->put(route('student-contacts.update', $other->studentProfile->id), ['line_id' => 'fake-line'])
            ->assertForbidden();
        $this->assertDatabaseMissing('contacts', ['Users_user_id' => $other->user_id, 'line_id' => 'fake-line']);
    }

    public function test_subject_writes_require_admin_and_linked_subjects_cannot_be_deleted(): void
    {
        $this->appointment();
        $this->actingAs($this->student())->post(route('subjects.store'), [
            'subject_id' => 'NEW0000001', 'subject_name' => 'วิชาใหม่',
        ])->assertForbidden();
        $this->put(route('subjects.update', 'SUB-001'), ['subject_name' => 'แก้ชื่อโดยไม่มีสิทธิ์'])->assertForbidden();
        $this->delete(route('subjects.destroy', 'SUB-001'))->assertForbidden();
        $admin = User::where('email', 'admin.demo@example.com')->firstOrFail();
        $this->actingAs($admin)->delete(route('subjects.destroy', 'SUB-001'))->assertSessionHasErrors('subject');
        $this->assertDatabaseHas('subjects', ['subject_id' => 'SUB-001']);
    }

    public function test_legacy_review_route_checks_owner_completion_and_duplicates_even_when_hidden(): void
    {
        $appointment = $this->appointment('pending');
        $data = [
            'Appointment_Appointment_id' => $appointment->Appointment_id,
            'Tutor_profiles_tutor_id' => $appointment->Tutor_profiles_tutor_id,
            'rating' => 5, 'Comment' => 'เข้าใจง่าย',
        ];
        $this->actingAs($this->student())->post(route('reviews.store'), $data)->assertSessionHasErrors('appointment');
        $appointment->update(['status' => 'completed']);
        $other = User::where('email', 'student2.demo@example.com')->firstOrFail();
        $this->actingAs($other)->post(route('reviews.store'), $data)->assertForbidden();
        $this->actingAs($this->student())->post(route('reviews.store'), $data)->assertSessionHasNoErrors();
        $review = Review::firstOrFail();
        $this->get(route('reviews.index'))->assertOk()->assertSee('เข้าใจง่าย');
        $this->put(route('reviews.update', $review->Review_id), ['rating' => 4, 'Comment' => 'แก้ไขรีวิว'])->assertSessionHasNoErrors();
        $this->assertEquals(4, $review->tutorProfile->fresh()->average_rating);
        $this->delete(route('reviews.destroy', $review->Review_id))->assertSessionHasNoErrors();
        $this->post(route('reviews.store'), $data)->assertSessionHasErrors('appointment');
        // ฟอร์มรีวิวบนหน้านัดหมายต้องกันซ้ำเหมือน route เก่า
        $this->post(route('appointments.review', $appointment), ['rating' => 5, 'Comment' => 'ส่งซ้ำ'])->assertUnprocessable();
        $this->assertDatabaseCount('reviews', 1);
    }

    public function test_legacy_profile_id_reviews_still_have_correct_ownership(): void
    {
        $appointment = $this->appointment('completed', true);
        $review = Review::create([
            'Review_id' => 'AUDITREV01', 'rating' => 3, 'Comment' => 'รีวิวเก่า',
            'Appointment_Appointment_id' => $appointment->Appointment_id,
            'Tutor_profiles_tutor_id' => $appointment->Tutor_profiles_tutor_id,
        ]);
        $this->actingAs($this->student())->get(route('reviews.edit', $review->Review_id))->assertOk();
        $other = User::where('email', 'student2.demo@example.com')->firstOrFail();
        $this->actingAs($other)->get(route('reviews.edit', $review->Review_id))->assertForbidden();
    }

    public function test_admin_searches_and_displays_students_in_new_user_id_appointments(): void
    {
        $this->appointment('confirmed');
        $admin = User::where('email', 'admin.demo@example.com')->firstOrFail();
        $student = $this->student();
        $this->actingAs($admin)->get(route('admin.appointments.index', ['search' => $student->first_name]))
            ->assertOk()->assertSee($student->first_name)->assertDontSee('ไม่พบผู้เรียน');
    }

    public function test_tutor_profile_passes_only_shared_times_to_the_existing_form(): void
    {
        $student = $this->student();
        $tutor = TutorProfile::findOrFail('DEMOTUT001');
        $day = now()->addDays(2)->startOfDay();
        DB::table('availabilities')->insert([
            [
                'availability_id' => 'AUDITAV001',
                'user_id' => $student->user_id,
                'start_datetime' => $day->copy()->setHour(10),
                'end_datetime' => $day->copy()->setHour(11),
            ],
            [
                'availability_id' => 'AUDITAV002',
                'user_id' => $tutor->Users_user_id,
                'start_datetime' => $day->copy()->setHour(9),
                'end_datetime' => $day->copy()->setHour(12),
            ],
        ]);

        $this->actingAs($student)->get(route('tutor.show', $tutor))
            ->assertOk()
            ->assertViewHas('availabilities', function ($times) {
                return count($times) === 1
                    && $times[0]['start_time'] === '10:00'
                    && $times[0]['end_time'] === '11:00';
            });

        DB::table('availabilities')->where('user_id', $student->user_id)->delete();
        $this->get(route('tutor.show', $tutor))
            ->assertOk()
            ->assertViewHas('availabilities', []);
    }

    public function test_tutor_home_displays_student_in_new_user_id_appointments(): void
    {
        $appointment = $this->appointment('pending');
        $teacher = $appointment->tutorProfile->user;
        $teacher->update(['current_role' => 'tutor']);

        $this->actingAs($teacher)->get(route('home'))
            ->assertOk()
            ->assertSee($this->student()->first_name)
            ->assertViewHas('pendingAppointments', function ($appointments) {
                return $appointments->first()->studentProfile->user_id === $this->student()->user_id;
            });
    }

    public function test_uploaded_public_files_are_accessible_and_other_paths_are_rejected(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('avatars/audit.txt', 'public-avatar-file');
        $this->get(route('storage.public', ['path' => 'avatars/audit.txt']))->assertOk();
        $this->get(route('storage.public', ['path' => '../.env']))->assertNotFound();
        $this->get(route('storage.public', ['path' => 'missing.png']))->assertNotFound();
    }
}
