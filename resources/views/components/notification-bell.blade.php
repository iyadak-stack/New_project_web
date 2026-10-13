@php
    // นับเฉพาะแจ้งเตือนที่ยังไม่อ่านของบัญชีที่ล็อกอิน
    $unreadCount = 0;
    if (Auth::check()) {
        $unreadCount = \App\Domains\Booking\Models\Notification::where('Users_user_id', Auth::id())
            ->where('is_read', false)->count();
    }
@endphp

<link rel="stylesheet" href="{{ asset('CSS/notification-bell.css') }}">
<a href="{{ route('notifications.index') }}"
   class="notification-bell {{ request()->routeIs('notifications.*') ? 'active' : '' }}"
   aria-label="การแจ้งเตือน ยังไม่อ่าน {{ $unreadCount }} รายการ" title="การแจ้งเตือน">
    {{-- วาดรูประฆังด้วย SVG จึงไม่ต้องติดตั้งชุดไอคอนเพิ่มเติม --}}
    <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
        <path d="M12 2a1 1 0 0 1 1 1v1.1A6 6 0 0 1 18 10v4l2 3v1H4v-1l2-3v-4a6 6 0 0 1 5-5.9V3a1 1 0 0 1 1-1Z" />
        <path d="M9 20h6a3 3 0 0 1-6 0Z" />
    </svg>
    @if ($unreadCount > 0)
        <span class="notification-count">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
    @endif
</a>
