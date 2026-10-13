<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดนัดหมาย - PeerTutor</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('CSS/home.css') }}">
    <link rel="stylesheet" href="{{ asset('CSS/tutor-navbar.css') }}">
    <link rel="stylesheet" href="{{ asset('CSS/profile-card.css') }}">
    <link rel="stylesheet" href="{{ asset('CSS/appointment-list.css') }}">
    <link rel="stylesheet" href="{{ asset('CSS/appointment-detail.css') }}">
</head>
<body class="booking-body">
    {{-- ใช้ Navbar เดียวกับหน้าหลัก --}}
    <x-navbar />

    @php
        $isTutorRole = auth()->check() && auth()->user()->current_role === 'tutor';

        // เช็คการสลับข้อมูลตาม Role
        if ($isTutorRole) {
            $otherPerson = $appointment->studentProfile?->user ?? $appointment->studentUser;
            $personRoleLabel = 'ผู้เรียน';
            $backRoute = \Illuminate\Support\Facades\Route::has('tutor.schedule') ? route('tutor.schedule') : route('appointments.index');
            $backText = 'กลับตารางการสอนของฉัน';
        } else {
            $otherPerson = $appointment->tutorProfile?->user;
            $personRoleLabel = 'ติวเตอร์';
            $backRoute = route('appointments.index');
            $backText = 'กลับนัดหมายของฉัน';
        }

        $otherPersonName = trim(($otherPerson?->first_name ?? '') . ' ' . ($otherPerson?->last_name ?? '')) ?: 'ไม่พบข้อมูล';

        $statusLabels = [
            'pending' => 'รอติวเตอร์ยืนยัน',
            'confirmed' => 'ยืนยันแล้ว',
            'completed' => 'เรียนเสร็จแล้ว',
            'cancelled' => 'ยกเลิกแล้ว',
        ];
        $modeLabels = [
            'online' => 'ออนไลน์',
            'onsite' => 'นัดเจอ',
            'both' => 'ออนไลน์และนัดเจอ',
        ];
        $statusLabel = $statusLabels[$appointment->status] ?? $appointment->status;
        $teachingMode = $appointment->tutorProfile?->teaching_mode;
    @endphp

    <main class="booking-page appointment-detail-page">
        <a href="{{ $backRoute }}" class="booking-button booking-button-outline appointment-back">
            <span aria-hidden="true">←</span> {{ $backText }}
        </a>

        <article class="booking-card appointment-detail-card">
            <header class="appointment-detail-header">
                <span class="booking-icon"><x-appointment-icon name="calendar" /></span>
                <div class="appointment-detail-title">
                    <h1>รายละเอียดนัดหมาย</h1>
                    <p>รหัส {{ $appointment->Appointment_id }}</p>
                </div>
                <span class="booking-status appointment-header-status {{ $appointment->status }}">
                    @if ($appointment->status === 'confirmed')
                        <span aria-hidden="true">✓</span>
                    @endif
                    {{ $statusLabel }}
                </span>
            </header>

            @if (session('success'))
                <div class="alert alert-success" role="status">{{ session('success') }}</div>
            @endif
            @if ($isStudentBooking && $appointment->status === 'pending')
                <div class="alert alert-warning" role="status">ส่งคำขอแล้ว กรุณารอติวเตอร์ยืนยันก่อนเข้าเรียนและยืนยันว่าเรียนเสร็จแล้ว</div>
            @endif

            {{-- แสดงข้อมูลนัดหมายแบบสลับบทบาท ผู้เรียน / ติวเตอร์ --}}
            <dl class="appointment-detail-rows">
                <dt><x-appointment-icon name="book" /> วิชา</dt>
                <dd>{{ $appointment->subject?->subject_name ?? 'ไม่ระบุ' }}</dd>

                <dt><x-appointment-icon name="user" /> {{ $personRoleLabel }}</dt>
                <dd>{{ $otherPersonName }}</dd>

                <dt><x-appointment-icon name="calendar" /> วัน</dt>
                <dd>{{ $appointment->start_datetime?->locale('th')->translatedFormat('lที่ j F Y') ?? 'ไม่ระบุ' }}</dd>

                <dt><x-appointment-icon name="clock" /> เวลา</dt>
                <dd>{{ $appointment->start_datetime?->format('H:i') ?? '--:--' }}–{{ $appointment->end_datetime?->format('H:i') ?? '--:--' }} น.</dd>

                <dt><x-appointment-icon name="pin" /> รูปแบบ</dt>
                <dd>{{ $modeLabels[$teachingMode] ?? 'ตามที่ตกลงกัน' }}</dd>

                <dt><x-appointment-icon name="check" /> สถานะ</dt>
                <dd>
                    <span class="booking-status {{ $appointment->status }}">
                        @if ($appointment->status === 'confirmed')
                            <span aria-hidden="true">✓</span>
                        @endif
                        {{ $statusLabel }}
                    </span>
                </dd>

                <dt><x-appointment-icon name="calendar" /> สร้างเมื่อ</dt>
                <dd>{{ $appointment->created_at?->format('d/m/Y H:i') ?? 'ไม่ระบุ' }}</dd>
            </dl>

            {{-- ให้เห็นช่องทางเรียนเมื่อยืนยันแล้วหรือเรียนเสร็จแล้ว --}}
            @if (in_array($appointment->status, ['confirmed', 'completed'], true))
                <section class="booking-lesson">
                    <span class="booking-icon"><x-appointment-icon name="link" /></span>
                    <div class="booking-lesson-content">
                        <x-lesson-details :appointment="$appointment" />
                    </div>
                </section>
            @endif

            @if($errors->any())
                <div class="alert alert-danger mt-2">
                    {{ $errors->first() }}
                </div>
            @endif

            @if($appointment->rejection_reason)
                <p class="alert alert-warning mt-2">เหตุผลที่ปฏิเสธ: {{ $appointment->rejection_reason }}</p>
            @endif

            {{-- ฟอร์มการจัดการของติวเตอร์ (แก้ไขข้อมูลห้องเรียน) --}}
            @if($isTutorBooking && auth()->user()->current_role === 'tutor' && $appointment->tutorProfile->approval_status === 'approved' && in_array($appointment->status, ['pending', 'confirmed'], true))
                <form class="appointment-edit-form my-3 p-3 border rounded-3 bg-light" method="POST" action="{{ route('appointments.lesson', $appointment) }}">
                    @csrf
                    @method('PATCH')
                    <h2 class="h5 font-weight-bold mb-2">ห้องเรียนสำหรับนัดหมายนี้</h2>
                    <p class="small text-muted mb-3">เริ่มต้นจากข้อมูลในโปรไฟล์ แก้ไขได้เฉพาะนัดหมายนี้</p>
                    
                    @foreach(['meeting_zoom' => 'Zoom', 'meeting_meet' => 'Google Meet', 'meeting_location' => 'สถานที่เรียน'] as $field => $label)
                        <div class="mb-3">
                            <label class="form-label small fw-semibold mb-1">{{ $label }}</label>
                            <input class="form-control form-control-sm" type="{{ $field === 'meeting_location' ? 'text' : 'url' }}" name="{{ $field }}" value="{{ old($field, $appointment->lessonValue($field)) }}">
                            <button type="button" class="btn btn-sm btn-link text-muted p-0 text-decoration-none mt-1" onclick="this.previousElementSibling.value=''">ล้างช่องนี้</button>
                        </div>
                    @endforeach

                    <div class="mb-3">
                        <label class="form-label small fw-semibold mb-1">รายละเอียดเพิ่มเติม</label>
                        <textarea class="form-control form-control-sm" name="lesson_notes" rows="2" maxlength="3000">{{ old('lesson_notes', $appointment->lesson_notes) }}</textarea>
                    </div>

                    <p class="small text-secondary">ล้างช่องที่ต้องการลบแล้วกดบันทึก การเปลี่ยนแปลงมีผลเฉพาะนัดหมายนี้</p>
                    <div class="d-flex gap-2 flex-wrap">
                        <button class="booking-button booking-button-primary">บันทึกรายละเอียดการเรียน</button>
                        <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" name="use_profile_defaults" value="1" formnovalidate>ใช้ข้อมูลจากโปรไฟล์อีกครั้ง</button>
                    </div>
                </form>
            @endif

            {{-- ปุ่มกดการกระทำต่างๆ --}}
            <div class="d-flex flex-wrap align-items-center justify-content-end gap-2 mt-3">
                @if($isTutorBooking && $appointment->status === 'pending' && auth()->user()->current_role === 'tutor' && $appointment->tutorProfile->approval_status === 'approved')
                    <form method="POST" action="{{ url('/appointments/'.$appointment->Appointment_id.'/status') }}">
                        @csrf
                        <input type="hidden" name="status" value="confirmed">
                        <button class="booking-button booking-button-primary">รับคำขอจอง</button>
                    </form>
                    <form method="POST" action="{{ url('/appointments/'.$appointment->Appointment_id.'/status') }}">
                        @csrf
                        <input type="hidden" name="status" value="cancelled">
                        <button class="btn-reject-action">ปฏิเสธคำขอ</button>
                    </form>
                @endif

                @if($isStudentBooking && $appointment->canComplete())
                    <form method="POST" action="{{ route('appointments.complete', $appointment) }}">
                        @csrf
                        <button class="booking-button booking-button-primary">ยืนยันว่าเรียนเสร็จแล้ว</button>
                    </form>
                @endif

                @if($isStudentBooking && $appointment->status === 'completed' && !$appointment->review)
                    <a class="btn-review-action text-decoration-none" href="{{ route('appointments.index') }}#review-{{ $appointment->Appointment_id }}">
                        เขียนรีวิว
                    </a>
                @endif

                {{-- แสดงปุ่มดูโปรไฟล์ติวเตอร์เฉพาะฝั่งนักเรียนเท่านั้น --}}
                @if(!$isTutorRole && $appointment->tutorProfile)
                    <a href="{{ route('tutor.show', $appointment->tutorProfile) }}" class="btn-tutor-profile text-decoration-none">
                        <x-appointment-icon name="user" /> ดูโปรไฟล์ติวเตอร์
                    </a>
                @endif
            </div>
        </article>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var profileButton = document.getElementById('toggleProfileBtn');
        var profileDropdown = document.getElementById('profileDropdown');
        if (profileButton && profileDropdown) {
            profileButton.addEventListener('click', function () {
                profileDropdown.classList.toggle('show');
            });
            document.addEventListener('click', function (event) {
                if (!profileButton.contains(event.target) && !profileDropdown.contains(event.target)) {
                    profileDropdown.classList.remove('show');
                }
            });
        }
    </script>
</body>
</html>