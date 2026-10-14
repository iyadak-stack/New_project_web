<?php

namespace Tests\Feature;

use App\Domains\Auth\Models\User;
use App\Domains\Booking\Models\Notification;
use App\Domains\Reportreview\Models\Report;
use App\Domains\Reportreview\Services\ReportNotification;
use Database\Seeders\AccountSeeder;
use Database\Seeders\ReportReasonSeeder;
use Database\Seeders\SubjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // ทดสอบด้วยฐานข้อมูลแยก ไม่แก้ข้อมูลจริงในเว็บ
        $this->seed([SubjectSeeder::class, AccountSeeder::class, ReportReasonSeeder::class]);
        \App\Domains\TutorProfile\Models\TutorProfile::query()->update(['approval_status' => 'approved']);
    }

    public function test_both_navbars_count_only_the_owners_unread_notifications(): void
    {
        $student = User::where('email', 'favorites.demo@example.com')->firstOrFail();
        $tutor = User::where('email', 'tutor1.demo@example.com')->firstOrFail();
        ReportNotification::send($student->user_id, 'รายการของนักเรียน');
        ReportNotification::send($student->user_id, 'รายการที่อ่านแล้ว');
        Notification::where('message', 'รายการที่อ่านแล้ว')->update(['is_read' => true]);
        ReportNotification::send($tutor->user_id, 'รายการของติวเตอร์หนึ่ง');
        ReportNotification::send($tutor->user_id, 'รายการของติวเตอร์สอง');

        $this->actingAs($student)->get(route('notifications.index'))->assertOk()
            ->assertSee('การแจ้งเตือน ยังไม่อ่าน 1 รายการ')
            ->assertSee('notification-count')->assertDontSee('รายการของติวเตอร์หนึ่ง');
        $this->post(route('notifications.read-all'))->assertRedirect();
        $this->get(route('notifications.index'))->assertSee('การแจ้งเตือน ยังไม่อ่าน 0 รายการ')
            ->assertDontSee('<span class="notification-count">', false);
        $this->actingAs($tutor)->get(route('tutor.subjects.index'))->assertOk()
            ->assertSee('การแจ้งเตือน ยังไม่อ่าน 2 รายการ');
        $this->get(route('notifications.index'))->assertSee('notification-bell active')
            ->assertDontSee('รายการของนักเรียน');
    }

    public function test_report_notifies_sender_and_admin_then_notifies_sender_when_status_changes(): void
    {
        $student = User::where('email', 'favorites.demo@example.com')->firstOrFail();
        $admin = User::where('email', 'admin.demo@example.com')->firstOrFail();
        $tutor = User::where('email', 'tutor1.demo@example.com')->firstOrFail();
        $inactiveAdmin = User::factory()->create(['role' => 'admin', 'current_role' => 'admin', 'is_active' => false]);

        $this->actingAs($student)->post(route('reports.store'), [
            'reported_tutor_id' => 'DEMOTUT001', 'reason_type' => 'RPTTYPE002',
            'description' => 'รายละเอียดรายงานทดสอบ',
        ])->assertSessionHasNoErrors()->assertRedirect();
        $report = Report::firstOrFail();
        $this->assertDatabaseHas('notifications', ['Users_user_id' => $student->user_id, 'message' => 'ส่งรายงานแล้ว รหัส '.$report->Report_id.' รอแอดมินตรวจสอบ']);
        $this->assertDatabaseHas('notifications', ['Users_user_id' => $admin->user_id, 'NotificationType_notification_type_id' => 'REPORT0001']);
        $this->assertDatabaseMissing('notifications', ['Users_user_id' => $tutor->user_id]);
        $this->assertDatabaseMissing('notifications', ['Users_user_id' => $inactiveAdmin->user_id]);
        $this->get(route('notifications.index'))->assertSee($report->Report_id)->assertDontSee('ดูนัดหมาย')->assertDontSee('รายละเอียดรายงานทดสอบ');

        $this->actingAs($admin)->get(route('notifications.index'))->assertSee('ดูรายงาน');
        $this->patch(route('admin.reports.status', $report->Report_id), ['status' => 'in_progress'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('notifications', ['Users_user_id' => $student->user_id, 'message' => 'รายงานรหัส '.$report->Report_id.' เปลี่ยนสถานะเป็น กำลังตรวจสอบ']);
        $count = Notification::count();
        $this->patch(route('admin.reports.status', $report->Report_id), ['status' => 'in_progress'])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('notifications', $count);
        $this->patch(route('admin.reports.status', $report->Report_id), ['status' => 'resolved'])->assertSessionHasNoErrors();
        $this->actingAs($student)->get(route('notifications.index'))->assertSee('ตรวจสอบเสร็จแล้ว');
        $this->assertDatabaseCount('notification_types', 1);
    }

    public function test_tutor_can_receive_report_submission_notifications_too(): void
    {
        $tutor = User::where('email', 'tutor2.demo@example.com')->firstOrFail();
        $this->actingAs($tutor)->post(route('reports.store'), [
            'reported_tutor_id' => 'DEMOTUT001', 'reason_type' => 'RPTTYPE002',
            'description' => 'รายงานจากติวเตอร์',
        ])->assertSessionHasNoErrors();
        $this->get(route('notifications.index'))->assertOk()->assertSee('ส่งรายงานแล้ว')
            ->assertSee('การแจ้งเตือน ยังไม่อ่าน 1 รายการ');
    }
}
