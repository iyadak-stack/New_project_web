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
    <style>
        .pt-confirm-overlay { display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.45); z-index: 10000; align-items: center; justify-content: center; padding: 16px; }
        .pt-confirm-overlay.show { display: flex; }
        .pt-confirm-box { background: #fff; border-radius: 16px; padding: 24px; width: 100%; max-width: 380px; box-shadow: 0 10px 30px rgba(0,0,0,.2); font-family: 'Prompt', sans-serif; }
        .pt-confirm-title { margin: 0 0 8px; font-size: 18px; font-weight: 700; color: #1e293b; }
        .pt-confirm-text { margin: 0 0 20px; font-size: 14px; color: #64748b; line-height: 1.5; }
        .pt-confirm-actions { display: flex; gap: 8px; justify-content: flex-end; }
        .pt-confirm-actions button { padding: 9px 18px; border-radius: 999px; font-family: inherit; font-size: 14px; font-weight: 600; cursor: pointer; }
        .pt-btn-cancel { background: #fff; border: 1px solid #cbd5e1; color: #64748b; }
        .pt-btn-ok { background: #ef4444; border: 1px solid #ef4444; color: #fff; }
    </style>
</head>
<body>
<x-navbar />
<main class="notif-container">
    @if (session('success'))<p role="status">{{ session('success') }}</p>@endif
    <div class="notif-header">
        <h1 class="notif-title">การแจ้งเตือน</h1>
        @if ($notifications->isNotEmpty())
        <form action="{{ route('notifications.read-all') }}" method="POST">
            @csrf
            <button type="submit" class="btn-mark-all">อ่านทั้งหมด</button>
        </form>
        @endif
    </div>
    <div class="notif-list">
        @forelse ($notifications as $notification)
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
                        <span class="notif-read-status">✓ อ่านแล้ว</span>
                    @endif
                    {{-- data-confirm ใช้เปิดป๊อปอัพยืนยันของเว็บเอง แทน confirm() ของเบราว์เซอร์ --}}
                    <form action="{{ route('notifications.destroy', $notification->notification_id) }}" method="POST"
                          data-confirm-title="ลบการแจ้งเตือน"
                          data-confirm-text="ต้องการลบการแจ้งเตือนนี้ใช่ไหม? ลบแล้วจะกู้คืนไม่ได้">
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

{{-- ป๊อปอัพยืนยัน --}}
<div id="ptConfirm" class="pt-confirm-overlay" role="dialog" aria-modal="true" aria-labelledby="ptConfirmTitle">
    <div class="pt-confirm-box">
        <h2 id="ptConfirmTitle" class="pt-confirm-title"></h2>
        <p id="ptConfirmText" class="pt-confirm-text"></p>
        <div class="pt-confirm-actions">
            <button type="button" class="pt-btn-cancel" id="ptConfirmCancel">ยกเลิก</button>
            <button type="button" class="pt-btn-ok" id="ptConfirmOk">ลบ</button>
        </div>
    </div>
</div>

<script>
    var profileButton = document.getElementById('toggleProfileBtn');
    var profileMenu = document.getElementById('profileDropdown');
    if (profileButton && profileMenu) {
        profileButton.addEventListener('click', function () {
            profileMenu.classList.toggle('show');
        });
    }

    // ป๊อปอัพยืนยันก่อนส่งฟอร์มที่มี data-confirm-text
    (function () {
        var overlay = document.getElementById('ptConfirm');
        var pendingForm = null;

        function close() { overlay.classList.remove('show'); pendingForm = null; }

        document.addEventListener('submit', function (e) {
            var form = e.target;
            if (!form.dataset || !form.dataset.confirmText) return;
            e.preventDefault();
            pendingForm = form;
            document.getElementById('ptConfirmTitle').textContent = form.dataset.confirmTitle || 'ยืนยันการทำรายการ';
            document.getElementById('ptConfirmText').textContent = form.dataset.confirmText;
            overlay.classList.add('show');
        });

        document.getElementById('ptConfirmCancel').addEventListener('click', close);
        overlay.addEventListener('click', function (e) { if (e.target === overlay) close(); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });

        document.getElementById('ptConfirmOk').addEventListener('click', function () {
            if (pendingForm) {
                var f = pendingForm;
                close();
                f.submit(); // submit() ไม่เรียก event submit ซ้ำ จึงไม่วนลูป
            }
        });
    })();
</script>
</body>
</html>