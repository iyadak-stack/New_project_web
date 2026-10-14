@props(['active' => false])
@auth
    @php
        // นับเฉพาะแจ้งเตือนที่เจ้าของบัญชียังไม่ได้อ่าน
        $unreadCount = \App\Domains\Booking\Models\Notification::where('Users_user_id', auth()->id())
            ->where('is_read', false)
            ->count();
    @endphp
    <a href="{{ route('notifications.index') }}" {{ $attributes->class(['menu-btn', 'active' => $active || request()->routeIs('notifications.*')]) }}>
        แจ้งเตือน
        @if($unreadCount > 0)
            <span class="badge bg-danger">{{ $unreadCount }}</span>
        @endif
    </a>
@endauth
