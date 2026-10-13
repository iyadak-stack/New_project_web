<!DOCTYPE html>
<html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>นัดหมายของฉัน - PeerTutor</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <style>body{font-family:'Prompt',sans-serif;background:#f8fafc}.navbar-peer{background:#fff;padding:14px 4vw;box-shadow:0 1px 8px #00000012}.navbar-peer a{color:#334155;text-decoration:none;margin-right:18px}.navbar-peer .brand{font-size:21px;font-weight:700;color:#e07a5f}.appointment-card{border:0;border-radius:16px;box-shadow:0 4px 20px #0f172a0d}</style>
        {{-- ปกป้องแก้ให้: path เดิม css/home.css ต้องเป็น CSS/home.css ให้ตรงกับโฟลเดอร์บน Linux --}}
        <link rel="stylesheet" href="{{ asset('CSS/home.css') }}">
        {{-- ปกป้องแก้ให้: path เดิม css/profile-card.css ต้องเป็น CSS/profile-card.css ให้ตรงกับโฟลเดอร์บน Linux --}}
        <link rel="stylesheet" href="{{ asset('CSS/profile-card.css') }}">
        <link rel="stylesheet" href="{{ asset('CSS/edit-profile.css') }}">
    </head>
    <body>
        <x-home-navbar />
        <main class="container py-4 py-md-5">
            <h1 class="h3 fw-bold mb-1">นัดหมายของฉัน</h1>
            <p class="text-secondary mb-4">รายการจองเรียนที่คุณส่งและคำขอที่ได้รับ</p>
            @if($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="ปิด"></button>
                </div>
            @endif
            @forelse($appointments as $appointment)
                @php
                $isStudentBooking = (string) $appointment->Student_profiles_student_id === $userId
                    || (string) $appointment->studentProfile?->user_id === $userId;
                $otherPerson = $isStudentBooking
                    ? $appointment->tutorProfile?->user
                    : ($appointment->studentProfile?->user ?? $appointment->studentUser);
                $statusLabels = ['pending' => 'รอติวเตอร์ยืนยัน', 'confirmed' => 'ยืนยันแล้ว', 'completed' => 'เรียนเสร็จแล้ว', 'cancelled' => 'ยกเลิกแล้ว'];
                $statusClasses = ['pending' => 'warning', 'confirmed' => 'success', 'completed' => 'primary', 'cancelled' => 'secondary'];
            @endphp
                <article class="card appointment-card mb-3">
                    <div class="card-body p-4">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                            <div>
                                <div class="text-secondary small">
                                    รหัสนัดหมาย {{ $appointment->Appointment_id }}
                                </div>
                                <h2 class="h5 fw-bold mt-1 mb-1">{{ $appointment->subject?->subject_name ?? 'ไม่ระบุวิชา' }}</h2>
                                <div>
                                    {{ $isStudentBooking ? 'ติวเตอร์' : 'ผู้เรียน' }}: {{ trim(($otherPerson?->first_name ?? '') . ' ' . ($otherPerson?->last_name ?? '')) ?: 'ไม่พบข้อมูล' }}
                                </div>
                            </div>
                            <span class="badge text-bg-{{ $statusClasses[$appointment->status] ?? 'secondary' }}">
                                {{ $statusLabels[$appointment->status] ?? $appointment->status }}
                            </span>
                        </div>
                        @if($isStudentBooking && $appointment->status === 'pending')
                            <p class="text-secondary mt-2 mb-0">กรุณารอติวเตอร์ยืนยันก่อนเข้าเรียนและยืนยัน session</p>
                        @endif
                        <hr>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <span class="text-secondary">วันเรียน</span>
                                <br>
                                {{ $appointment->start_datetime?->locale('th')->translatedFormat('lที่ j F Y') ?? 'ไม่ระบุ' }}
                            </div>
                            <div class="col-sm-6">
                                <span class="text-secondary">เวลาเรียน</span>
                                <br>
                                {{ $appointment->start_datetime?->format('H:i') ?? '--:--' }}–{{ $appointment->end_datetime?->format('H:i') ?? '--:--' }} น.
                            </div>
                            <div class="col-sm-6">
                                <span class="text-secondary">รูปแบบการเรียน</span>
                                <br>
                                {{ $appointment->tutorProfile?->teaching_mode ?? 'ตามที่ตกลงกับติวเตอร์' }}
                            </div>
                            <div class="col-sm-6">
                                <span class="text-secondary">ส่งคำขอเมื่อ</span>
                                <br>
                                {{ $appointment->created_at?->locale('th')->translatedFormat('j F Y H:i') ?? 'ไม่ระบุ' }}
                            </div>
                        </div>
                        @if(in_array($appointment->status, ['confirmed', 'completed'], true))
                            <x-lesson-details :appointment="$appointment" />
                        @endif
                        <div class="mt-3 d-flex flex-wrap align-items-start gap-2">
                            {{-- โค้ดเดิมของส่วน Appointment (ทับทิม): ปุ่ม Bootstrap
                            <a href="{{ route('appointments.show', $appointment->Appointment_id) }}" class="btn btn-outline-primary btn-sm">
                                ดูรายละเอียด
                            </a>
                            --}}
                            {{-- ปกป้องปรับเฉพาะคลาสปุ่มให้ใช้ CSS เดิมของเพื่อน ไม่เปลี่ยนการทำงาน --}}
                            <a href="{{ route('appointments.show', $appointment->Appointment_id) }}" class="home-card-button">
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
                                    {{-- โค้ดเดิมของส่วน Appointment (ทับทิม): ปุ่ม Bootstrap
                                    <button class="btn btn-primary btn-sm">ยืนยันว่าเรียนเสร็จแล้ว</button>
                                    --}}
                                    {{-- ปกป้องปรับเฉพาะคลาสปุ่มให้ใช้ CSS เดิมของเพื่อน ไม่เปลี่ยนการทำงาน --}}
                                    <button class="pe-save">ยืนยันว่าเรียนเสร็จแล้ว</button>
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
                                        {{-- โค้ดเดิมของส่วน Appointment (ทับทิม): ปุ่ม Bootstrap
                                        <button class="btn btn-primary">ส่งรีวิว</button>
                                        --}}
                                        {{-- ปกป้องปรับเฉพาะคลาสปุ่มให้ใช้ CSS เดิมของเพื่อน ไม่เปลี่ยนการทำงาน --}}
                                        <button class="pe-save">ส่งรีวิว</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            @empty
                <div class="alert alert-info">
                    ยังไม่มีนัดหมายที่เกี่ยวข้องกับบัญชีนี้
                </div>
                {{-- โค้ดเดิมของส่วน Appointment (ทับทิม): ปุ่ม Bootstrap
                <a href="{{ route('tutor.search') }}" class="btn btn-primary">ค้นหาติวเตอร์</a>
                --}}
                {{-- ปกป้องปรับเฉพาะคลาสปุ่มให้ใช้ CSS เดิมของเพื่อน ไม่เปลี่ยนการทำงาน --}}
                <a href="{{ route('tutor.search') }}" class="home-card-button">ค้นหาติวเตอร์</a>
            @endforelse
        </main>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
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
