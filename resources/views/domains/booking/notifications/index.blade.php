<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>การแจ้งเตือน - PeerTutor</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('CSS/home.css') }}">
    <link rel="stylesheet" href="{{ asset('CSS/tutor-navbar.css') }}">
    <link rel="stylesheet" href="{{ asset('CSS/profile-card.css') }}">
    <link rel="stylesheet" href="{{ asset('CSS/notifications.css') }}">
</head>
<body>
<x-navbar />
<main class="notif-container">
    @if (session('success'))<p role="status">{{ session('success') }}</p>@endif
    <div class="notif-header">
        <h1 class="notif-title">การแจ้งเตือน</h1>
        {{-- ไม่มีรายการก็ไม่ต้องแสดงปุ่มอ่านทั้งหมด --}}
        @if ($notifications->isNotEmpty())
        <form action="{{ route('notifications.read-all') }}" method="POST">
            @csrf
            <button type="submit" class="btn-mark-all">อ่านทั้งหมด</button>
        </form>
        @endif
    </div>
    {{-- แสดงเฉพาะข้อมูลของคนที่ล็อกอิน ไม่ใส่ตัวอย่างแจ้งเตือนปะปน --}}
    <div class="notif-list">
        @forelse ($notifications as $notification)
            {{-- ใช้ is_read เดิมเพื่อแยกรูปแบบการ์ดที่อ่านแล้วกับยังไม่อ่าน --}}
            <article class="notif-card {{ $notification->is_read ? 'read' : 'unread' }}">
                <div class="notif-icon icon-system" aria-hidden="true">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2a1 1 0 0 1 1 1v1.1A6 6 0 0 1 18 10v4l2 3v1H4v-1l2-3v-4a6 6 0 0 1 5-5.9V3a1 1 0 0 1 1-1Z" />
                        <path d="M9 20h6a3 3 0 0 1-6 0Z" />
                    </svg>
                </div>
                <div class="notif-body">
                    <p class="notif-message">{{ $notification->message }}</p>
                    <span class="notif-time">{{ $notification->notificationType?->type_name }}</span>
                    <span class="notif-time">{{ $notification->created_at->timezone('Asia/Bangkok')->format('d/m/Y H:i') }}</span>
                </div>
                <div class="notif-actions">
                    {{-- แต่ละประเภทมีทางเข้าที่ตรงกับเรื่องนั้น ไม่พารายงานไปหน้านัดหมาย --}}
                    @if ($notification->NotificationType_notification_type_id === 'TUTORAPP01')
                        <a class="btn-notif-action notif-details" href="{{ route(auth()->user()->role === 'admin' || auth()->user()->current_role === 'admin' ? 'admin.tutor-applications.index' : 'tutor.application.index') }}">ดูคำขอ</a>
                    @elseif ($notification->NotificationType_notification_type_id === 'REPORT0001')
                        @if (auth()->user()->role === 'admin' || auth()->user()->current_role === 'admin')
                            <a class="btn-notif-action notif-details" href="{{ route('admin.reports.index') }}">ดูรายงาน</a>
                        @endif
                    @elseif (in_array($notification->NotificationType_notification_type_id, ['BOOKREQ001', 'BOOKANS001', 'TYPE01', 'TYPE02']))
                        <a class="btn-notif-action notif-details" href="{{ route('appointments.index') }}">ดูนัดหมาย</a>
                    @endif
                    @if (! $notification->is_read)
                        <form action="{{ route('notifications.read', $notification->notification_id) }}" method="POST">
                            @csrf
                            <button class="btn-notif-action notif-mark-read" type="submit">ทำเครื่องหมายว่าอ่านแล้ว</button>
                        </form>
                    @else
                        {{-- เป็นป้ายข้อความ ไม่ใช่ปุ่ม จึงกดซ้ำไม่ได้ --}}
                        <span class="notif-read-status">✓ อ่านแล้ว</span>
                    @endif
                    <form action="{{ route('notifications.destroy', $notification->notification_id) }}" method="POST" onsubmit="return confirm('ลบการแจ้งเตือนนี้ใช่ไหม?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn-delete" type="submit" aria-label="ลบการแจ้งเตือน">&times;</button>
                    </form>
                </div>
            </article>
        @empty
            <section class="empty-notif">
                <div class="empty-notif-icon" aria-hidden="true">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2a1 1 0 0 1 1 1v1.1A6 6 0 0 1 18 10v4l2 3v1H4v-1l2-3v-4a6 6 0 0 1 5-5.9V3a1 1 0 0 1 1-1Z" />
                        <path d="M9 20h6a3 3 0 0 1-6 0Z" />
                    </svg>
                </div>
                <h2 class="empty-notif-title">ยังไม่มีการแจ้งเตือน</h2>
                <p class="empty-notif-sub">เมื่อมีความคืบหน้าเกี่ยวกับนัดหมายหรือรายงาน ระบบจะแจ้งให้คุณทราบที่นี่</p>
            </section>
        @endforelse
    </div>
</main>
<script>
    // เปิดและปิดเมนูโปรไฟล์เดิมของ Navbar
    var profileButton = document.getElementById('toggleProfileBtn');
    var profileMenu = document.getElementById('profileDropdown');
    if (profileButton && profileMenu) {
        profileButton.addEventListener('click', function () {
            profileMenu.classList.toggle('show');
        });
    }
</script>
</body>
</html>
