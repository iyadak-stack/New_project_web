<?php

namespace App\Domains\Booking\Http\Controllers;

use App\Domains\Booking\Models\Notification;
use App\Http\Controllers\Controller;

class NotificationController extends Controller
{
    // แสดงรายการแจ้งเตือนทั้งหมด
    public function index()
    {
        // ใช้บัญชีที่ล็อกอินจริง ไม่ใช้รหัสตัวอย่างตายตัว
        $userId = auth()->id();

        $notifications = Notification::with('notificationType')->where('Users_user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('domains.booking.notifications.index', compact('notifications'));
    }

    // กดอ่านแจ้งเตือน
    public function markAsRead($id)
    {
        $notification = Notification::where('Users_user_id', auth()->id())->findOrFail($id);
        $notification->is_read = 1;
        $notification->save();

        return redirect()->back();
    }

    public function markAllAsRead()
    {
        Notification::where('Users_user_id', auth()->id())->update(['is_read' => true]);

        return back()->with('success', 'อ่านการแจ้งเตือนทั้งหมดแล้ว');
    }

    public function destroy($id)
    {
        Notification::where('Users_user_id', auth()->id())->findOrFail($id)->delete();

        return back()->with('success', 'ลบการแจ้งเตือนแล้ว');
    }
}
