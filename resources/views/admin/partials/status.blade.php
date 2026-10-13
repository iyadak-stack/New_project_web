@php
    $statusLabels = ['pending' => 'รอตรวจสอบ', 'in_progress' => 'กำลังตรวจสอบ', 'resolved' => 'ปิดเรื่อง', 'confirmed' => 'ยืนยันแล้ว', 'cancelled' => 'ยกเลิก', 'completed' => 'เรียนเสร็จแล้ว'];
@endphp
<span class="admin-badge {{ in_array($status, ['resolved', 'confirmed', 'completed']) ? 'green' : ($status === 'cancelled' ? 'red' : '') }}">{{ $statusLabels[$status] ?? $status }}</span>
