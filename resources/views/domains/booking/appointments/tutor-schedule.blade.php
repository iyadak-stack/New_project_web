<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตารางการสอนของฉัน - PeerTutor</title>
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
        $allAppointments = $appointments ?? collect();

        $statusBadges = [
            'confirmed' => ['ยืนยันแล้ว', 'bg-success-subtle text-success'],
            'pending'   => ['รอตอบรับ', 'bg-warning-subtle text-warning-emphasis'],
            'completed' => ['การสอนเสร็จสิ้น', 'bg-info-subtle text-info-emphasis'],
            'cancelled' => ['ยกเลิกแล้ว', 'bg-danger-subtle text-danger'],
        ];


        $tabs = [
            [
                'key' => 'all',
                'label' => 'ทั้งหมด',
                'emoji' => '📅',
                'badge' => 'bg-secondary',
                'items' => $allAppointments,
                'empty_title' => 'ยังไม่มีรายการนัดหมายในหมวดนี้',
                'empty_text' => 'เมื่อมีคำขอจองเรียนจากนักเรียนจะแสดงผลที่นี่ทันที',
            ],
            [
                'key' => 'pending',
                'label' => 'รอการยืนยัน',
                'emoji' => '⏳',
                'badge' => 'bg-warning text-dark',
                'items' => $allAppointments->where('status', 'pending'),
                'empty_title' => 'ไม่มีคำขอที่รอการยืนยัน',
                'empty_text' => 'คำขอจองเรียนใหม่จากนักเรียนจะแสดงในหมวดนี้',
            ],
            [
                'key' => 'confirmed',
                'label' => 'ยืนยันแล้ว',
                'emoji' => '✅',
                'badge' => 'bg-success',
                'items' => $allAppointments->where('status', 'confirmed'),
                'empty_title' => 'ไม่มีคลาสสอนที่ยืนยันแล้ว',
                'empty_text' => 'รายการนัดหมายที่คุณตอบรับแล้วจะแสดงที่นี่',
            ],
            [
                'key' => 'completed',
                'label' => 'สอนเสร็จสิ้น',
                'emoji' => '🎓',
                'badge' => 'bg-info text-dark',
                'items' => $allAppointments->where('status', 'completed'),
                'empty_title' => 'ยังไม่มีประวัติคลาสสอนที่เสร็จสิ้น',
                'empty_text' => 'คลาสเรียนที่เรียนจบแล้วจะถูกเก็บบันทึกในหมวดนี้',
            ],
            [
                'key' => 'cancelled',
                'label' => 'ยกเลิกแล้ว',
                'emoji' => '❌',
                'badge' => 'bg-danger',
                'items' => $allAppointments->where('status', 'cancelled'),
                'empty_title' => 'ไม่มีรายการนัดหมายที่ถูกยกเลิก',
                'empty_text' => 'รายการคำขอที่ปฏิเสธหรือยกเลิกจะแสดงในหมวดนี้',
            ],
        ];
    @endphp

    <main class="booking-page container my-4" style="max-width: 1000px;">
        <!-- Header -->
        <header class="d-flex align-items-center gap-3 mb-4">
            <span class="fs-2">📅</span>
            <div>
                <h1 class="h3 fw-bold mb-1" style="color: #1E293B;">ตารางการสอนของฉัน</h1>
                <p class="text-muted mb-0 small">รายการตารางสอนและคำขอจองเรียนจากนักเรียน</p>
            </div>
        </header>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Status Filter Tabs -->
        <ul class="nav nav-status-tabs gap-2 mb-4 border-bottom pb-3" id="tutorScheduleTabs" role="tablist">
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
        <div class="tab-content" id="tutorScheduleTabsContent">
            @foreach ($tabs as $tab)
                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="tab-{{ $tab['key'] }}" role="tabpanel">
                    @forelse ($tab['items'] as $appointment)
                        @php
                            $status = $appointment->status;
                            [$statusText, $statusClass] = $statusBadges[$status] ?? [$status, 'bg-secondary-subtle text-secondary'];

                            // ชื่อนักเรียน (ใช้ความสัมพันธ์เดียวกับหน้ารายละเอียด)
                            $studentUser = $appointment->studentProfile?->user ?? $appointment->studentUser ?? null;
                            $studentName = trim(($studentUser?->first_name ?? '') . ' ' . ($studentUser?->last_name ?? ''));
                            $studentName = $studentName !== '' ? $studentName : 'ไม่ระบุชื่อนักเรียน';

                            $subjectName = $appointment->subject?->subject_name ?? 'ไม่ระบุวิชา';

                            $start = $appointment->start_datetime ? \Illuminate\Support\Carbon::parse($appointment->start_datetime) : null;
                            $end = $appointment->end_datetime ? \Illuminate\Support\Carbon::parse($appointment->end_datetime) : null;

                            // ลิงก์ไปหน้ารายละเอียดนัดหมาย
                            $detailUrl = \Illuminate\Support\Facades\Route::has('appointments.show')
                                ? route('appointments.show', $appointment)
                                : url('/appointments/' . $appointment->Appointment_id);
                        @endphp

                        <article class="card border-0 shadow-sm rounded-4 mb-3 position-relative">
                            <div class="card-body p-4">
                                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                                    <div>
                                        <h2 class="h5 fw-bold mb-1" style="color: #1E293B;">{{ $subjectName }}</h2>
                                        <p class="mb-0 fw-semibold" style="color: #334155;">👤 {{ $studentName }}</p>
                                    </div>
                                    <span class="badge {{ $statusClass }} px-3 py-2 rounded-pill fw-medium">{{ $statusText }}</span>
                                </div>

                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                    <div class="d-flex flex-wrap gap-4 small text-secondary">
                                        <span>📆 {{ $start ? $start->format('d/m/Y') : '-' }}</span>
                                        <span>🕐 {{ $start ? $start->format('H:i') : '-' }}{{ $end ? ' - ' . $end->format('H:i') : '' }}</span>
                                    </div>
                                    {{-- stretched-link ทำให้คลิกได้ทั้งการ์ด --}}
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