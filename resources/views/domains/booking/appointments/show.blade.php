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
    {{-- ใช้ Navbar เดียวกับหน้าหลัก ไม่สร้างเมนูซ้ำอีกชุด --}}
    <x-navbar />
    @php
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
        <a href="{{ route('appointments.index') }}" class="booking-button booking-button-outline appointment-back">
            <span aria-hidden="true">←</span> กลับนัดหมายของฉัน
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
            {{-- ใช้ข้อมูลเดิม แสดงชื่อช่องทางซ้ายและค่าทางขวา --}}
            <dl class="appointment-detail-rows">
                <dt><x-appointment-icon name="book" /> วิชา</dt>
                <dd>{{ $appointment->subject?->subject_name ?? 'ไม่ระบุ' }}</dd>
                <dt><x-appointment-icon name="user" /> ติวเตอร์</dt>
                <dd>{{ $appointment->tutorProfile?->user?->first_name }} {{ $appointment->tutorProfile?->user?->last_name }}</dd>
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
            {{-- คงเงื่อนไขเดิม ให้เห็นช่องทางเรียนเมื่อยืนยันแล้วหรือเรียนเสร็จแล้ว --}}
            @if (in_array($appointment->status, ['confirmed', 'completed'], true))
                <section class="booking-lesson">
                    <span class="booking-icon"><x-appointment-icon name="link" /></span>
                    <div class="booking-lesson-content">
                        <x-lesson-details :appointment="$appointment" />
                    </div>
                </section>
            @endif
            {{-- ฟอร์มด้านล่างคง route, CSRF และสิทธิ์ของระบบเดิม --}}
                @if($errors->any())
                    <div class="alert alert-danger">
                        {{ $errors->first() }}
                    </div>
                @endif
                @if($appointment->rejection_reason)
                    <p class="alert alert-warning">เหตุผลที่ปฏิเสธ: {{ $appointment->rejection_reason }}</p>
                @endif
                @if($isTutorBooking && auth()->user()->current_role === 'tutor' && $appointment->tutorProfile->approval_status === 'approved' && in_array($appointment->status,['pending','confirmed'],true))

                    <form class="appointment-edit-form" method="POST" action="{{ route('appointments.lesson',$appointment) }}">
                        @csrf
                        @method('PATCH')
                        <h2 class="h5">ห้องเรียนสำหรับนัดหมายนี้</h2>
                        <p>เริ่มต้นจากข้อมูลในโปรไฟล์ แก้ไขได้เฉพาะนัดหมายนี้</p>
                        @foreach(['meeting_zoom'=>'Zoom','meeting_meet'=>'Google Meet','meeting_location'=>'สถานที่เรียน'] as $field=>$label)

                            <label class="form-label">
                                {{ $label }}
                                <input class="form-control" type="{{ $field === 'meeting_location' ? 'text' : 'url' }}" name="{{ $field }}" value="{{ old($field, $appointment->lessonValue($field)) }}">
                                <button type="button" class="btn btn-sm btn-outline-secondary mt-1" onclick="this.previousElementSibling.value=''">ล้างช่องนี้</button>
                            </label>
                        @endforeach
                        <label class="form-label">
                            รายละเอียดเพิ่มเติม
                            <textarea class="form-control" name="lesson_notes" maxlength="3000">{{ old('lesson_notes',$appointment->lesson_notes) }}</textarea>
                        </label>
                        <p class="small text-secondary">ล้างช่องที่ต้องการลบแล้วกดบันทึก การเปลี่ยนแปลงมีผลเฉพาะนัดหมายนี้</p>
                        <div class="d-flex gap-2 flex-wrap">
                            <button class="booking-button booking-button-primary">บันทึกรายละเอียดการเรียน</button>
                            <button class="btn btn-outline-secondary" name="use_profile_defaults" value="1" formnovalidate>ใช้ข้อมูลจากโปรไฟล์อีกครั้ง</button>
                        </div>
                    </form>
                @endif
                <div class="d-flex flex-wrap align-items-start gap-2">
                    @if($isTutorBooking && $appointment->status === 'pending' && auth()->user()->current_role === 'tutor' && $appointment->tutorProfile->approval_status === 'approved')
                        <form method="POST" action="{{ url('/appointments/'.$appointment->Appointment_id.'/status') }}">
                            @csrf
                            <input type="hidden" name="status" value="confirmed">
                            <button class="btn btn-success">รับคำขอจอง</button>
                        </form>
                        <form method="POST" action="{{ url('/appointments/'.$appointment->Appointment_id.'/status') }}">
                            @csrf
                            <input type="hidden" name="status" value="cancelled">
                            <input class="form-control mb-2" name="rejection_reason" placeholder="เหตุผลที่ปฏิเสธ" required maxlength="2000">
                            <button class="btn btn-outline-danger">ปฏิเสธคำขอ</button>
                        </form>
                    @endif
                    @if($isStudentBooking && $appointment->canComplete())
                        <form method="POST" action="{{ route('appointments.complete', $appointment) }}">
                            @csrf
                            <button class="booking-button booking-button-primary">ยืนยันว่าเรียนเสร็จแล้ว</button>
                        </form>
                    @endif
                    @if($isStudentBooking && $appointment->status === 'completed' && !$appointment->review)
                        <a class="btn btn-warning" href="{{ route('appointments.index') }}#review-{{ $appointment->Appointment_id }}">
                            เขียนรีวิว
                        </a>
                    @endif
                    @if($appointment->tutorProfile)
                        <a href="{{ route('tutor.show', $appointment->tutorProfile) }}" class="appointment-profile-link">
                                <x-appointment-icon name="user" />
                            ดูโปรไฟล์ติวเตอร์
                        </a>
                    @endif
                </div>
        </article>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // เปิดและปิดเมนูโปรไฟล์ของ Navbar เดียวกับหน้าหลัก
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
