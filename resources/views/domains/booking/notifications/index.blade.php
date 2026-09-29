<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>การแจ้งเตือน</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="p-4">
    <div class="container">
        <h2>การแจ้งเตือนของคุณ</h2>

        <ul class="list-group mt-3">
            @forelse($notifications as $notif)
                <li class="list-group-item d-flex justify-content-between align-items-center {{ $notif->is_read ? 'bg-light' : '' }}">
                    <div>
                        <p class="mb-1">{{ $notif->message }}</p>
                        <small class="text-muted">{{ $notif->created_at }}</small>
                    </div>
                    @if(!$notif->is_read)
                        <form action="{{ url('/notifications/'.$notif->notification_id.'/read') }}" method="POST">
                            @csrf
                            <button class="btn btn-sm btn-outline-primary">ทำเครื่องหมายว่าอ่านแล้ว</button>
                        </form>
                    @else
                        <span class="badge bg-secondary">อ่านแล้ว</span>
                    @endif
                </li>
            @empty
                <li class="list-group-item text-center">ไม่มีข้อความแจ้งเตือน</li>
            @endforelse
        </ul>
    </div>
</body>
</html>