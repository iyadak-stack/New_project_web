<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>นัดหมายของฉัน - PeerTutor</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('CSS/home.css') }}">
    <link rel="stylesheet" href="{{ asset('CSS/tutor-navbar.css') }}">
    <link rel="stylesheet" href="{{ asset('CSS/profile-card.css') }}">
    <link rel="stylesheet" href="{{ asset('CSS/appointment-list.css') }}">
</head>
<body class="booking-body">
    {{-- ใช้ Navbar ของหน้าหลัก เมนูและ active จึงเหมือนหน้าอื่น --}}
    <x-navbar />
    <main class="booking-page">
        <header class="booking-page-header">
            <span class="booking-icon"><x-appointment-icon name="user" /></span>
            <div>
                <h1>นัดหมายของฉัน</h1>
                <p>รายการจองเรียนที่คุณส่งและคำขอที่ได้รับ</p>
            </div>
        </header>
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
        @endif
        @if (session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif
        @forelse ($appointments as $appointment)
            @php
                // ใช้เงื่อนไขเจ้าของเดิม รองรับทั้งรหัสผู้ใช้และรหัสโปรไฟล์
                $isStudentBooking = (string) $appointment->Student_profiles_student_id === $userId
                    || (string) $appointment->studentProfile?->user_id === $userId;
                if ($isStudentBooking) {
                    $otherPerson = $appointment->tutorProfile?->user;
                } else {
                    $otherPerson = $appointment->studentProfile?->user ?? $appointment->studentUser;
                }
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
                $teachingMode = $appointment->tutorProfile?->teaching_mode;
                // แสดงทางเข้าห้องเรียนเฉพาะนัดหมายที่ยืนยันแล้ว
                $classroomLink = '';
                if ($appointment->status === 'confirmed') {
                    $classroomLink = $appointment->lessonValue('meeting_zoom');
                    if ($classroomLink === '') {
                        $classroomLink = $appointment->lessonValue('meeting_meet');
                    }
                }
            @endphp
            <article class="booking-card">
                <div class="booking-card-header">
                    <div>
                        <span class="booking-code">รหัสนัดหมาย {{ $appointment->Appointment_id }}</span>
                        <div class="booking-person">
                            <span class="booking-icon"><x-appointment-icon name="user" /></span>
                            <div>
                                <h2>{{ $appointment->subject?->subject_name ?? 'ไม่ระบุวิชา' }}</h2>
                                <p>{{ $isStudentBooking ? 'ติวเตอร์' : 'ผู้เรียน' }}: {{ trim(($otherPerson?->first_name ?? '') . ' ' . ($otherPerson?->last_name ?? '')) ?: 'ไม่พบข้อมูล' }}</p>
                            </div>
                        </div>
                    </div>
                    @if ($classroomLink !== '')
                        <a class="booking-button booking-button-primary" href="{{ $classroomLink }}" target="_blank" rel="noopener">
                            <x-appointment-icon name="video" /> เริ่มชั้นเรียน
                        </a>
                    @else
                        <span class="booking-status {{ $appointment->status }}">{{ $statusLabels[$appointment->status] ?? $appointment->status }}</span>
                    @endif
                </div>
                @if ($isStudentBooking && $appointment->status === 'pending')
                    <p class="booking-hint">กรุณารอติวเตอร์ยืนยันก่อนเข้าเรียนและยืนยันว่าเรียนเสร็จแล้ว</p>
                @endif
                <div class="booking-details">
                    <div class="booking-detail">
                        <span class="booking-icon"><x-appointment-icon name="calendar" /></span>
                        <div>
                            <span>วันเรียน</span>
                            <p>{{ $appointment->start_datetime?->locale('th')->translatedFormat('lที่ j F Y') ?? 'ไม่ระบุ' }}</p>
                        </div>
                    </div>
                    <div class="booking-detail">
                        <span class="booking-icon"><x-appointment-icon name="clock" /></span>
                        <div>
                            <span>เวลาเรียน</span>
                            <p>{{ $appointment->start_datetime?->format('H:i') ?? '--:--' }}–{{ $appointment->end_datetime?->format('H:i') ?? '--:--' }} น.</p>
                        </div>
                    </div>
                    <div class="booking-detail">
                        <span class="booking-icon"><x-appointment-icon name="screen" /></span>
                        <div>
                            <span>รูปแบบการเรียน</span>
                            <p>{{ $modeLabels[$teachingMode] ?? 'ตามที่ตกลงกับติวเตอร์' }}</p>
                        </div>
                    </div>
                    <div class="booking-detail">
                        <span class="booking-icon"><x-appointment-icon name="calendar" /></span>
                        <div>
                            <span>ส่งคำขอเมื่อ</span>
                            <p>{{ $appointment->created_at?->locale('th')->translatedFormat('j F Y H:i') ?? 'ไม่ระบุ' }}</p>
                        </div>
                    </div>
                </div>
                {{-- คง component รายละเอียดและสิทธิ์เดิม เปลี่ยนเฉพาะกล่องด้านนอก --}}
                @if (in_array($appointment->status, ['confirmed', 'completed'], true))
                    <section class="booking-lesson">
                        <span class="booking-icon"><x-appointment-icon name="link" /></span>
                        <div class="booking-lesson-content">
                            <x-lesson-details :appointment="$appointment" />
                        </div>
                    </section>
                @endif
                <div class="booking-actions">
                    <a href="{{ route('appointments.show', $appointment->Appointment_id) }}" class="booking-button booking-button-outline">
                        <span aria-hidden="true">←</span>
                        ดูรายละเอียด
                    </a>
                    @if(!$isStudentBooking && $appointment->status === 'pending' && auth()->user()->current_role === 'tutor' && $appointment->tutorProfile->approval_status === 'approved')
                        <form method="POST" action="{{ url('/appointments/'.$appointment->Appointment_id.'/status') }}">
                            @csrf
                            <input type="hidden" name="status" value="confirmed">
                            <button class="btn btn-success btn-sm">รับคำขอ</button>
                        </form>
                        <form method="POST" action="{{ url('/appointments/'.$appointment->Appointment_id.'/status') }}">
                            @csrf
                            <input type="hidden" name="status" value="cancelled">
                            <input class="form-control mb-2" name="rejection_reason" placeholder="เหตุผลที่ปฏิเสธ" required maxlength="2000">
                            <button class="btn btn-outline-danger btn-sm">ปฏิเสธ</button>
                        </form>
                    @endif
                    @if($isStudentBooking && $appointment->canComplete())
                        <form method="POST" action="{{ route('appointments.complete', $appointment) }}">
                            @csrf
                            <button class="booking-button booking-button-primary">ยืนยันว่าเรียนเสร็จแล้ว</button>
                        </form>
                    @endif
                    @if($isStudentBooking && $appointment->status === 'completed' && !$appointment->review)
                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#review-{{ $appointment->Appointment_id }}">
                            เขียนรีวิว
                        </button>
                    @elseif($appointment->review)
                        <span class="badge text-bg-light align-self-center">รีวิวแล้ว {{ $appointment->review->rating }}/5</span>
                    @endif
                    @if($isStudentBooking && $appointment->tutorProfile)
                        <a href="{{ route('tutor.show', $appointment->tutorProfile) }}" class="btn btn-outline-secondary btn-sm">
                            ดูโปรไฟล์ติวเตอร์
                        </a>
                    @endif
                </div>
            </article>
            @if($isStudentBooking && $appointment->status === 'completed' && !$appointment->review)
                <div class="modal fade" id="review-{{ $appointment->Appointment_id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST" action="{{ route('appointments.review', $appointment) }}">
                                @csrf
                                <div class="modal-header">
                                    <h2 class="modal-title fs-5">รีวิวติวเตอร์</h2>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
                                </div>
                                <div class="modal-body">
                                    <label class="form-label" for="rating-{{ $appointment->Appointment_id }}">
                                        คะแนน
                                    </label>
                                    <select class="form-select mb-3" id="rating-{{ $appointment->Appointment_id }}" name="rating" required>
                                        <option value="5">5 - ดีมาก</option>
                                        <option value="4">4 - ดี</option>
                                        <option value="3">3 - ปานกลาง</option>
                                        <option value="2">2 - ควรปรับปรุง</option>
                                        <option value="1">1 - ไม่พอใจ</option>
                                    </select>
                                    <label class="form-label" for="comment-{{ $appointment->Appointment_id }}">
                                        ความคิดเห็น
                                    </label>
                                    <textarea class="form-control" id="comment-{{ $appointment->Appointment_id }}" name="Comment" rows="4" required maxlength="2000"></textarea>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                                    <button class="booking-button booking-button-primary">ส่งรีวิว</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            @empty
                <section class="booking-empty">
                    <span class="booking-icon"><x-appointment-icon name="calendar" /></span>
                    <h2>ยังไม่มีนัดหมาย</h2>
                    <p>เมื่อส่งคำขอเรียนหรือได้รับคำขอ นัดหมายจะแสดงที่นี่</p>
                    <a class="booking-button booking-button-outline" href="{{ route('tutor.search') }}">ค้นหาติวเตอร์</a>
                </section>
            @endforelse
    </main>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // ใช้เมนูโปรไฟล์ component เดียวกับหน้าหลัก
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

                // เปิดฟอร์มหลังยืนยันเรียนจบ หรือเมื่อกดลิงก์เขียนรีวิวจากหน้ารายละเอียด
                const completedAppointmentId = @json(session('review_appointment'));
                let reviewModalId = window.location.hash.substring(1);

                if (completedAppointmentId) {
                    reviewModalId = 'review-' + completedAppointmentId;
                }

                if (reviewModalId.startsWith('review-')) {
                    const reviewModal = document.getElementById(reviewModalId);
                    if (reviewModal) {
                        bootstrap.Modal.getOrCreateInstance(reviewModal).show();
                    }
                }
            });
        </script>
    </body>
</html>
