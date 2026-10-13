<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตารางนัดหมายและการสอน - PeerTutor</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('CSS/home.css') }}">
    <link rel="stylesheet" href="{{ asset('CSS/tutor-navbar.css') }}">
    <link rel="stylesheet" href="{{ asset('CSS/profile-card.css') }}">
    <link rel="stylesheet" href="{{ asset('CSS/appointment-list.css') }}">
</head>
<body class="booking-body">
    <x-navbar />

    @php
        $isTutor = auth()->check() && auth()->user()->current_role === 'tutor';
        $title = $isTutor ? 'ตารางนัดหมายและการสอน' : 'นัดหมายการเรียนของฉัน';
        $subtitle = $isTutor
            ? 'ตรวจสอบ ตอบรับคำขอจองเรียน และจัดการตารางสอนของคุณได้ในที่เดียว'
            : 'ติดตามสถานะคำขอเรียน รายละเอียดห้องเรียน และประวัติการเรียน';

        $allAppointments = $appointments ?? collect();

        $statusBadges = [
            'confirmed' => ['ยืนยันแล้ว', 'bg-success-subtle text-success'],
            'pending'   => ['รอติวเตอร์ยืนยัน', 'bg-warning-subtle text-warning-emphasis'],
            'completed' => ['เรียนเสร็จแล้ว', 'bg-info-subtle text-info-emphasis'],
            'cancelled' => ['ยกเลิกแล้ว', 'bg-danger-subtle text-danger'],
        ];

        $tabs = [
            [
                'key' => 'all', 'label' => 'ทั้งหมด', 'emoji' => '📅', 'badge' => 'bg-secondary',
                'items' => $allAppointments,
                'empty_title' => 'ยังไม่มีรายการนัดหมายในหมวดนี้',
                'empty_text' => 'เมื่อมีรายการนัดหมายเข้ามาจะแสดงผลในหน้านี้ทันที',
            ],
            [
                'key' => 'pending', 'label' => 'รอการยืนยัน', 'emoji' => '⏳', 'badge' => 'bg-warning text-dark',
                'items' => $allAppointments->where('status', 'pending'),
                'empty_title' => 'ไม่มีรายการนัดหมายที่รอการยืนยัน',
                'empty_text' => 'รายการที่รอการตอบรับจะแสดงในหมวดนี้',
            ],
            [
                'key' => 'confirmed', 'label' => 'ยืนยันแล้ว', 'emoji' => '✅', 'badge' => 'bg-success',
                'items' => $allAppointments->where('status', 'confirmed'),
                'empty_title' => 'ไม่มีรายการนัดหมายที่ยืนยันแล้ว',
                'empty_text' => 'รายการนัดหมายที่ได้รับการยืนยันแล้วจะแสดงในหมวดนี้',
            ],
            [
                'key' => 'completed', 'label' => 'เรียนเสร็จแล้ว', 'emoji' => '🎓', 'badge' => 'bg-info text-dark',
                'items' => $allAppointments->where('status', 'completed'),
                'empty_title' => 'ยังไม่มีประวัติการเรียนที่เสร็จสิ้น',
                'empty_text' => 'คลาสที่เรียนจบแล้วจะถูกเก็บไว้ในหมวดนี้',
            ],
            [
                'key' => 'cancelled', 'label' => 'ยกเลิกแล้ว', 'emoji' => '❌', 'badge' => 'bg-danger',
                'items' => $allAppointments->where('status', 'cancelled'),
                'empty_title' => 'ไม่มีรายการนัดหมายที่ยกเลิก',
                'empty_text' => 'รายการนัดหมายที่ถูกปฏิเสธหรือยกเลิกจะแสดงที่นี่',
            ],
        ];
    @endphp

    <main class="booking-page container my-4" style="max-width: 1000px;">
        <!-- Header -->
        <header class="d-flex align-items-center gap-3 mb-4">
            <span class="fs-2">📅</span>
            <div>
                <h1 class="h3 fw-bold mb-1" style="color: #1E293B;">{{ $title }}</h1>
                <p class="text-muted mb-0 small">{{ $subtitle }}</p>
            </div>
        </header>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Status Filter Tabs -->
        <ul class="nav nav-status-tabs gap-2 mb-4 border-bottom pb-3" id="appointmentTabs" role="tablist">
            @foreach ($tabs as $tab)
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                            id="{{ $tab['key'] }}-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#tab-{{ $tab['key'] }}"
                            type="button" role="tab">
                        @if (! $loop->first){{ $tab['emoji'] }} @endif{{ $tab['label'] }}
                        <span class="badge rounded-pill {{ $tab['badge'] }} ms-1">{{ $tab['items']->count() }}</span>
                    </button>
                </li>
            @endforeach
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="appointmentTabsContent">
            @foreach ($tabs as $tab)
                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="tab-{{ $tab['key'] }}" role="tabpanel">
                    @forelse ($tab['items'] as $appointment)
                        @php
                            $status = $appointment->status;
                            [$statusText, $statusClass] = $statusBadges[$status] ?? [$status, 'bg-secondary-subtle text-secondary'];

                            // นักเรียนเห็นชื่อติวเตอร์ / ติวเตอร์เห็นชื่อนักเรียน
                            if ($isTutor) {
                                $person = $appointment->studentProfile?->user ?? $appointment->studentUser ?? null;
                                $personLabel = 'นักเรียน';
                            } else {
                                $person = $appointment->tutorProfile?->user ?? null;
                                $personLabel = 'ติวเตอร์';
                            }
                            $personName = trim(($person?->first_name ?? '') . ' ' . ($person?->last_name ?? ''));
                            $personName = $personName !== '' ? $personName : 'ไม่พบข้อมูล';

                            $subjectName = $appointment->subject?->subject_name ?? 'ไม่ระบุวิชา';

                            $start = $appointment->start_datetime ? \Illuminate\Support\Carbon::parse($appointment->start_datetime) : null;
                            $end = $appointment->end_datetime ? \Illuminate\Support\Carbon::parse($appointment->end_datetime) : null;

                            $detailUrl = \Illuminate\Support\Facades\Route::has('appointments.show')
                                ? route('appointments.show', $appointment)
                                : url('/appointments/' . $appointment->Appointment_id);

                            $needsReview = ! $isTutor && $status === 'completed' && ! $appointment->review;
                        @endphp

                        <article class="card border-0 shadow-sm rounded-4 mb-3 position-relative" id="review-{{ $appointment->Appointment_id }}">
                            <div class="card-body p-4">
                                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                                    <div>
                                        <h2 class="h5 fw-bold mb-1" style="color: #1E293B;">{{ $subjectName }}</h2>
                                        <p class="mb-0 fw-semibold" style="color: #334155;">👤 {{ $personLabel }}: {{ $personName }}</p>
                                    </div>
                                    <div class="d-flex flex-column align-items-end gap-2">
                                        <span class="badge {{ $statusClass }} px-3 py-2 rounded-pill fw-medium">{{ $statusText }}</span>
                                        @if ($needsReview)
                                            <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill fw-medium">⭐ ยังไม่ได้รีวิว</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                    <div class="d-flex flex-wrap gap-4 small text-secondary">
                                        <span>📆 {{ $start ? $start->format('d/m/Y') : '-' }}</span>
                                        <span>🕐 {{ $start ? $start->format('H:i') : '-' }}{{ $end ? ' - ' . $end->format('H:i') : '' }}</span>
                                    </div>
                                    <a href="{{ $detailUrl }}" class="stretched-link small fw-semibold text-decoration-none" style="color: #E07A5F;">
                                        ดูรายละเอียด →
                                    </a>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="empty-state-box my-4">
                            <div class="fs-1 mb-2">{{ $tab['emoji'] }}</div>
                            <h2 class="h5 fw-bold text-dark">{{ $tab['empty_title'] }}</h2>
                            <p class="text-muted small mb-0">{{ $tab['empty_text'] }}</p>
                        </div>
                    @endforelse
                </div>
            @endforeach
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>