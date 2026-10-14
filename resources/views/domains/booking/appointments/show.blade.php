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
    <style>
        .appointment-review-form { margin-top: 24px; padding: 24px; background: #fff8f5; border: 1px solid #ffe5dc; border-radius: 16px; }
        .appointment-review-form h2 { font-size: 1.25rem; font-weight: 700; margin-bottom: 8px; }
        .appointment-review-form .form-label { font-weight: 600; }
        .appointment-review-form .form-control, .appointment-review-form .form-select { border-radius: 10px; padding: 12px; }
        .appointment-review-form .review-help { color: #777; font-size: 0.9rem; }
        .appointment-review-form .review-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 16px; }
    </style>
</head>

<body class="booking-body {{ auth()->user()?->current_role === 'tutor' ? 'tutor-appointment-body' : '' }}">
    <x-navbar />

    @php
        $isTutorRole = auth()->check() && auth()->user()->current_role === 'tutor';

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
        $statusLabels = ['pending' => 'รอติวเตอร์ยืนยัน', 'confirmed' => 'ยืนยันแล้ว', 'completed' => 'เรียนเสร็จแล้ว', 'cancelled' => 'ยกเลิกแล้ว'];
        $modeLabels = ['online' => 'ออนไลน์', 'onsite' => 'นัดเจอ', 'both' => 'ออนไลน์และนัดเจอ'];
        $statusLabel = $statusLabels[$appointment->status] ?? $appointment->status;
        $teachingMode = $appointment->tutorProfile?->teaching_mode;
    @endphp

    <main class="booking-page appointment-detail-page">
        <a href="{{ $backRoute }}" class="booking-button booking-button-outline appointment-back"><span aria-hidden="true">←</span> {{ $backText }}</a>

        <article class="booking-card appointment-detail-card {{ $isTutorRole && $isTutorBooking ? 'tutor-appointment-card' : '' }}">
            <header class="appointment-detail-header">
                <span class="booking-icon"><x-appointment-icon name="calendar" /></span>
                <div class="appointment-detail-title">
                    <h1>รายละเอียดนัดหมาย</h1>
                    <p>รหัส {{ $appointment->Appointment_id }}</p>
                </div>
                <span class="booking-status appointment-header-status {{ $appointment->status }}">
                    @if ($appointment->status === 'confirmed') <span aria-hidden="true">✓</span> @endif
                    {{ $statusLabel }}
                </span>
            </header>

            @if (session('success')) <div class="alert alert-success" role="status">{{ session('success') }}</div> @endif
            @if (session('error')) <div class="alert alert-danger" role="alert">{{ session('error') }}</div> @endif
            @if ($isStudentBooking && $appointment->status === 'pending')
                <div class="alert alert-warning" role="status">ส่งคำขอแล้ว กรุณารอติวเตอร์ยืนยันก่อนเข้าเรียนและยืนยันว่าเรียนเสร็จแล้ว</div>
            @endif
            @if ($isTutorRole && $isTutorBooking && $appointment->status === 'pending')
                <p class="tutor-request-hint">ตรวจสอบวันเวลาและข้อมูลห้องเรียน ก่อนรับหรือปฏิเสธคำขอจากผู้เรียน</p>
            @endif

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
                        @if ($appointment->status === 'confirmed') <span aria-hidden="true">✓</span> @endif
                        {{ $statusLabel }}
                    </span>
                </dd>

                <dt><x-appointment-icon name="calendar" /> สร้างเมื่อ</dt>
                <dd>{{ $appointment->created_at?->format('d/m/Y H:i') ?? 'ไม่ระบุ' }}</dd>
            </dl>

            @if (in_array($appointment->status, ['confirmed', 'completed'], true))
                <section class="booking-lesson">
                    <span class="booking-icon"><x-appointment-icon name="link" /></span>
                    <div class="booking-lesson-content"><x-lesson-details :appointment="$appointment" /></div>
                </section>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger mt-3" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                    </ul>
                </div>
            @endif

            @if ($appointment->rejection_reason)
                <p class="alert alert-warning mt-2">เหตุผลที่ปฏิเสธ: {{ $appointment->rejection_reason }}</p>
            @endif

            {{-- ฟอร์มจัดการรายละเอียดห้องเรียนของติวเตอร์ --}}
            @if ($isTutorBooking && auth()->user()->current_role === 'tutor' && $appointment->tutorProfile?->approval_status === 'approved' && in_array($appointment->status, ['pending', 'confirmed'], true))
                <form class="tutor-lesson-form" method="POST" action="{{ route('appointments.lesson', $appointment) }}">
                    @csrf
                    @method('PATCH')

                    <section class="tutor-room-panel">
                        <div class="tutor-panel-heading">
                            <span class="booking-icon"><x-appointment-icon name="link" /></span>
                            <div>
                                <h2>ห้องเรียนสำหรับนัดหมายนี้</h2>
                                <p>เมื่อนัดหมายได้รับการยืนยัน ผู้เรียนจะเห็นช่องทางเข้าเรียนนี้</p>
                            </div>
                        </div>

                        @foreach (['meeting_zoom' => 'Zoom', 'meeting_meet' => 'Google Meet'] as $field => $label)
                            <div class="tutor-room-row">
                                <label for="{{ $field }}"><x-appointment-icon name="video" /> {{ $label }}</label>
                                <input class="form-control" id="{{ $field }}" type="url" name="{{ $field }}" value="{{ old($field, $appointment->lessonValue($field)) }}" placeholder="ใส่ลิงก์ห้องเรียน {{ $label }}">
                                <button type="button" class="tutor-copy-button" onclick="copyLessonLink('{{ $field }}', this)">คัดลอกลิงก์</button>
                            </div>
                        @endforeach

                        <div class="tutor-location-field">
                            <label for="meeting_location"><x-appointment-icon name="pin" /> สถานที่เรียน</label>
                            <input class="form-control" id="meeting_location" type="text" name="meeting_location" value="{{ old('meeting_location', $appointment->lessonValue('meeting_location')) }}" placeholder="ระบุสถานที่สำหรับการเรียนแบบนัดเจอ">
                        </div>
                    </section>

                    <section class="tutor-notes-panel">
                        <h2><x-appointment-icon name="book" /> รายละเอียดเพิ่มเติม</h2>
                        <label class="visually-hidden" for="lesson_notes">รายละเอียดเพิ่มเติมเกี่ยวกับการสอน</label>
                        <textarea class="form-control" id="lesson_notes" name="lesson_notes" rows="4" maxlength="3000" placeholder="ใส่รายละเอียดเพิ่มเติมเกี่ยวกับการสอน (ถ้ามี)">{{ old('lesson_notes', $appointment->lesson_notes) }}</textarea>
                        <p>ล้างช่องที่ต้องการลบแล้วกดบันทึก การเปลี่ยนแปลงมีผลเฉพาะนัดหมายนี้</p>

                        <div class="d-flex gap-2 flex-wrap">
                            <button type="submit" class="booking-button booking-button-primary">บันทึกรายละเอียดการเรียน</button>
                            <button type="submit" class="booking-button booking-button-outline" name="use_profile_defaults" value="1" formnovalidate>ใช้ข้อมูลจากโปรไฟล์อีกครั้ง</button>
                        </div>
                    </section>
                </form>
            @endif

            {{-- ปุ่มจัดการนัดหมาย --}}
            <div class="d-flex flex-wrap align-items-center justify-content-end gap-2 mt-3">
                @if ($isStudentBooking && auth()->user()->current_role === 'student' && in_array($appointment->status, ['pending', 'confirmed'], true))
                    <form method="POST" action="{{ route('appointments.cancel', $appointment) }}" onsubmit="return confirm('คุณต้องการยกเลิกนัดหมายนี้ใช่ไหม?')">
                        @csrf
                        <button type="submit" class="btn-reject-action">ยกเลิกนัดหมาย</button>
                    </form>
                @endif

                @if ($isTutorBooking && $appointment->status === 'pending' && auth()->user()->current_role === 'tutor' && $appointment->tutorProfile?->approval_status === 'approved')
                    <section class="tutor-request-actions">
                        <form class="tutor-accept-panel" method="POST" action="{{ route('appointments.status', $appointment->Appointment_id) }}">
                            @csrf
                            <input type="hidden" name="status" value="confirmed">
                            <span class="booking-icon"><x-appointment-icon name="check" /></span>
                            <h2>ตอบรับคำขอเรียน</h2>
                            <p>เมื่อยืนยัน ผู้เรียนจะได้รับแจ้งเตือนและเห็นรายละเอียดห้องเรียน</p>
                            <button type="submit" class="booking-button booking-button-primary">รับคำขอจอง</button>
                        </form>

                        <form class="tutor-reject-panel" method="POST" action="{{ route('appointments.status', $appointment->Appointment_id) }}">
                            @csrf
                            <input type="hidden" name="status" value="cancelled">
                            <label for="rejection_reason" class="form-label">เหตุผลที่ปฏิเสธคำขอ</label>
                            <textarea id="rejection_reason" name="rejection_reason" class="form-control form-control-sm mb-2" rows="2" maxlength="2000" placeholder="ระบุเหตุผลให้นักเรียนทราบ" required>{{ old('rejection_reason') }}</textarea>
                            <button type="submit" class="btn-reject-action">ปฏิเสธคำขอ</button>
                        </form>
                    </section>
                @endif

                @if ($isStudentBooking && $appointment->canComplete())
                    <form method="POST" action="{{ route('appointments.complete', $appointment) }}">
                        @csrf
                        <button type="submit" class="booking-button booking-button-primary">ยืนยันว่าเรียนเสร็จแล้ว</button>
                    </form>
                @endif

                {{-- ปุ่มเปิดฟอร์มรีวิว --}}
                @if ($isStudentBooking && $appointment->status === 'completed' && !$appointment->review)
                    <a class="btn-review-action text-decoration-none" href="#appointment-review-form">เขียนรีวิว</a>
                @endif

                {{-- ดูโปรไฟล์ติวเตอร์ --}}
                @if (!$isTutorRole && $appointment->tutorProfile)
                    <a href="{{ route('tutor.show', $appointment->tutorProfile) }}" class="btn-tutor-profile text-decoration-none">
                        <x-appointment-icon name="user" /> ดูโปรไฟล์ติวเตอร์
                    </a>
                @endif
            </div>

            {{-- ฟอร์มเขียนรีวิวในหน้ารายละเอียดนัดหมาย --}}
            @if ($isStudentBooking && $appointment->status === 'completed' && !$appointment->review)
                <section id="appointment-review-form" class="appointment-review-form">
                    <h2>เขียนรีวิวติวเตอร์</h2>
                    <p class="text-muted mb-4">แบ่งปันประสบการณ์การเรียนของคุณ เพื่อช่วยให้ผู้เรียนคนอื่นตัดสินใจได้ง่ายขึ้น</p>

                    <form method="POST" action="{{ route('appointments.review', $appointment->Appointment_id) }}">
                        @csrf
                        <div class="mb-3">
                            <label for="rating" class="form-label">คะแนนความพึงพอใจ</label>
                            <select id="rating" name="rating" class="form-select" required>
                                <option value="">เลือกคะแนน</option>
                                @for ($score = 5; $score >= 1; $score--)
                                    <option value="{{ $score }}" {{ (string) old('rating') === (string) $score ? 'selected' : '' }}>{{ $score }} / 5 ดาว</option>
                                @endfor
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="Comment" class="form-label">ความคิดเห็น</label>
                            <textarea id="Comment" name="Comment" class="form-control" rows="4" maxlength="2000" placeholder="เล่าประสบการณ์การเรียนของคุณ..." required>{{ old('Comment') }}</textarea>
                        </div>

                        <p class="review-help">กรุณาเขียนรีวิวตามประสบการณ์จริง และใช้ถ้อยคำสุภาพ</p>

                        <div class="review-actions">
                            <button type="submit" class="booking-button booking-button-primary">ส่งรีวิว</button>
                            <a href="{{ $backRoute }}" class="booking-button booking-button-outline text-decoration-none">ยกเลิก</a>
                        </div>
                    </form>
                </section>
            @endif
        </article>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        async function copyLessonLink(inputId, button) {
            var input = document.getElementById(inputId);
            if (!input || input.value.trim() === '') {
                button.textContent = 'ยังไม่มีลิงก์';
                if (input) input.focus();
                return;
            }
            try {
                await navigator.clipboard.writeText(input.value);
                button.textContent = 'คัดลอกแล้ว ✓';
            } catch (error) {
                input.focus();
                input.select();
                button.textContent = 'กด Ctrl+C เพื่อคัดลอก';
            }
        }

        var profileButton = document.getElementById('toggleProfileBtn');
        var profileDropdown = document.getElementById('profileDropdown');
        if (profileButton && profileDropdown) {
            profileButton.addEventListener('click', function () { profileDropdown.classList.toggle('show'); });
            document.addEventListener('click', function (event) {
                if (!profileButton.contains(event.target) && !profileDropdown.contains(event.target)) {
                    profileDropdown.classList.remove('show');
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            if (window.location.hash === '#appointment-review-form') {
                var reviewForm = document.getElementById('appointment-review-form');
                if (reviewForm) reviewForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    </script>
</body>
</html>