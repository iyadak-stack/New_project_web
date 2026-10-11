<!DOCTYPE html>
<html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>การแจ้งเตือน - PeerTutor</title>
        <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('css/home.css') }}?v={{ time() }}">
        <link rel="stylesheet" href="{{ asset('css/tutor-navbar.css') }}?v={{ time() }}">
        <link rel="stylesheet" href="{{ asset('css/profile-card.css') }}?v={{ time() }}">
        <!-- ลิงก์ไฟล์ CSS แจ้งเตือนแยก --><link rel="stylesheet" href="{{ asset('css/notifications.css') }}?v={{ time() }}">
    </head>
    <body>
        <!-- Header / Navbar ด้านบน --><x-home-navbar />
        <!-- Main Notification Area --><main class="notif-container">
            <!-- ปุ่มย้อนกลับ --><div class="notif-back-box">
                <a href="{{ route('home') }}" style="background: #e07a5f; color: #ffffff; padding: 10px 24px; border-radius: 10px; text-decoration: none; font-size: 14px; font-weight: 600; display: inline-block;">← กลับหน้าหลัก</a>
            </div>
            <div class="notif-header">
                <h1 class="notif-title">🔔 การแจ้งเตือน</h1>
            </div>
            <div class="notif-list">
                @forelse($notifications as $notification)
                    <div class="notif-card {{ $notification->is_read ? '' : 'unread' }}">
                        <div class="notif-icon icon-system">
                            🔔
                        </div>
                        <div class="notif-body">
                            <p class="notif-message">{{ $notification->message }}</p>
                            <span class="notif-time">{{ $notification->created_at->diffForHumans() }}</span>
                        </div>
                        @if(!$notification->is_read)
                            <div class="notif-actions">
                                <form action="{{ url('/notifications/'.$notification->notification_id.'/read') }}" method="POST">
                                    @csrf
                                    <button class="btn-notif-action" type="submit">ทำเครื่องหมายว่าอ่านแล้ว</button>
                                </form>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="notif-card">
                        <div class="notif-body">
                            <p class="notif-message">ยังไม่มีการแจ้งเตือน</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </main>
        <!-- JS สำหรับ Pop-over Card Navbar --><script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggleBtn = document.getElementById('toggleProfileBtn');
            const dropdown = document.getElementById('profileDropdown');

            if (toggleBtn && dropdown) {
                toggleBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    dropdown.classList.toggle('show');
                });

                document.addEventListener('click', (e) => {
                    if (!dropdown.contains(e.target) && !toggleBtn.contains(e.target)) {
                        dropdown.classList.remove('show');
                    }
                });
            }
        });
    </script>
    </body>
</html>
