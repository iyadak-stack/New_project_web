<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายวิชา {{ $subject->subject_name }} - PeerTutor</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('css/home.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/tutor-navbar.css') }}?v={{ time() }}">
    <!-- ลิงก์ไฟล์ CSS แยกของหน้ารายวิชา -->
    <link rel="stylesheet" href="{{ asset('css/subject-show.css') }}?v={{ time() }}">
</head>
<body>

    <!-- Header / Banner วิชา -->
    <div class="subject-hero">
        <div class="container">
            <a href="{{ route('home') }}" class="btn-back-home">
                ← กลับหน้าหลัก
            </a>
            <h1 class="fw-bold text-dark mb-1" style="font-size: 28px;">
                📘 {{ $subject->subject_name }}
            </h1>
            <p class="text-muted mb-0" style="font-size: 15px;">
                รวมรายชื่อติวเตอร์ที่เปิดสอนวิชา {{ $subject->subject_name }} ทั้งหมด ({{ $subject->tutors->count() }} ท่าน)
            </p>
        </div>
    </div>

    <!-- รายชื่อติวเตอร์ที่สอนวิชานี้ -->
    <div class="container mb-5">
        <div class="row g-4">
            @forelse($subject->tutors as $tutor)
                <div class="col-md-6 col-lg-4">
                    <div class="tutor-card h-100 d-flex flex-column justify-content-between">
                        <div>
                            <!-- ข้อมูลส่วนตัวติวเตอร์ -->
                            <div class="d-flex align-items-center gap-3 mb-3">
                                @if(optional($tutor->user)->profile_picture)
                                    <img src="{{ asset('storage/' . $tutor->user->profile_picture) }}" alt="Profile" class="tutor-avatar">
                                @else
                                    <div class="avatar-placeholder">
                                        {{ mb_substr(optional($tutor->user)->first_name ?? 'T', 0, 1) }}
                                    </div>
                                @endif
                                <div>
                                    <h5 class="mb-1 fw-bold text-dark" style="font-size: 17px;">
                                        พี่{{ optional($tutor->user)->first_name }} {{ optional($tutor->user)->last_name }}
                                    </h5>
                                    <small class="text-muted">
                                        ประสบการณ์ {{ $tutor->experience_years ?? 0 }} ปี • {{ ucfirst($tutor->teaching_mode ?? 'ออนไลน์') }}
                                    </small>
                                </div>
                            </div>

                            <!-- คำแนะนำตัว -->
                            <p style="font-size: 14px; color: #64748b; line-height: 1.6;" class="mb-3">
                                {{ Str::limit($tutor->bio ?? 'ไม่มีข้อมูลคำแนะนำตัว', 90, '...') }}
                            </p>
                        </div>

                        <!-- Footer การ์ด / ปุ่มจอง -->
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <div style="font-size: 14px; color: #f59e0b; font-weight: 600;">
                                ⭐ {{ number_format($tutor->average_rating ?? 0, 1) }} 
                                <span class="text-muted fw-normal" style="font-size: 12px;">({{ $tutor->reviews->count() }} รีวิว)</span>
                            </div>
                            <a href="{{ route('tutor.show', $tutor->tutor_id) }}" class="btn-book-now">
                                ดูโปรไฟล์ / จองเรียน
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="empty-box">
                        <h4 class="fw-bold mb-2">ยังไม่มีติวเตอร์เปิดสอนวิชานี้ในขณะนี้</h4>
                        <p class="mb-3">คุณสามารถลองค้นหาวิชาอื่นๆ หรือกลับไปเลือกดูติวเตอร์ทั้งหมดได้ครับ</p>
                        <a href="{{ route('home') }}" class="btn-book-now">กลับสู่หน้าหลัก</a>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

</body>
</html>