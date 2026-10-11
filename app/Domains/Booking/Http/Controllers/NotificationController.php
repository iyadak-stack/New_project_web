<?php

namespace App\Domains\Booking\Http\Controllers;

use App\Domains\Booking\Models\Notification;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    // แสดงรายการแจ้งเตือนทั้งหมด
    public function index()
    {
        $notifications = Notification::where('Users_user_id', Auth::user()->user_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('domains.booking.notifications.index', compact('notifications'));
    }

    // กดอ่านแจ้งเตือน
    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);
        abort_unless((string) $notification->Users_user_id === (string) Auth::user()->user_id, 404);
        $notification->is_read = 1;
        $notification->save();

        return redirect()->back();
    }
}
