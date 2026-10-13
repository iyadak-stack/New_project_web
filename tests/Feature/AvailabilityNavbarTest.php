<?php

namespace Tests\Feature;

use App\Domains\Auth\Models\User;
use Database\Seeders\AccountSeeder;
use Database\Seeders\SubjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilityNavbarTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_and_tutor_use_the_home_navbar_on_availability_pages(): void
    {
        // phpunit.xml ใช้ SQLite ในหน่วยความจำ ไม่เปลี่ยนฐานข้อมูลจริง
        $this->seed([SubjectSeeder::class, AccountSeeder::class]);
        $student = User::where('email', 'favorites.demo@example.com')->firstOrFail();
        $tutor = User::where('email', 'tutor1.demo@example.com')->firstOrFail();
        $tutor->tutorProfile->update(['approval_status' => 'approved']);

        foreach ([$student, $tutor] as $user) {
            $this->actingAs($user);
            $home = $this->get(route('home'))->assertOk();
            $home->assertSee('toggleProfileBtn', false);

            foreach (['availabilities.index', 'availabilities.history'] as $routeName) {
                $page = $this->get(route($routeName))->assertOk();
                $page->assertSee('toggleProfileBtn', false);
                $page->assertDontSee('sharedProfileToggle', false);
                $page->assertSee(
                    'href="'.route('availabilities.index').'" class="menu-btn active">จัดเวลา</a>',
                    false
                );
                $page->assertSee(route('appointments.index'), false);

                if ($user->current_role === 'tutor') {
                    $page->assertSee('วิชาของฉัน');
                    $page->assertDontSee('ติวเตอร์ทั้งหมด');
                } else {
                    $page->assertSee('รายวิชาทั้งหมด');
                    $page->assertSee('ติวเตอร์ทั้งหมด');
                    $page->assertSee('รายการโปรด');
                }
            }
        }

        // กลับหน้า home หลังสลับบทบาท ต้องใช้บทบาทที่เลือกไว้
        $this->actingAs($tutor)->post(route('role.switch'), ['role' => 'student'])->assertRedirect();
        $this->get(route('availabilities.index'))->assertOk()->assertSee('ติวเตอร์ทั้งหมด');
        $this->get(route('home'))->assertOk();
        $this->assertSame('student', $tutor->fresh()->current_role);

        $this->post(route('role.switch'), ['role' => 'tutor'])->assertRedirect();
        $this->get(route('availabilities.index'))->assertOk()->assertSee('วิชาของฉัน');
        $this->get(route('home'))->assertOk();
        $this->assertSame('tutor', $tutor->fresh()->current_role);
    }

    public function test_guest_cannot_open_availability_pages(): void
    {
        $this->get(route('availabilities.index'))->assertRedirect();
        $this->get(route('availabilities.history'))->assertRedirect();
    }
}
