<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จองนัดหมายเรียน - PeerTutor</title>

    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('css/home.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/tutor-navbar.css') }}?v={{ time() }}">
    <!-- ลิงก์ไฟล์ CSS แยกของหน้าจองเรียน -->
    <link rel="stylesheet" href="{{ asset('css/create-appointment.css') }}?v={{ time() }}">
</head>
<body>

    <div class="container">
        
        <!-- ปุ่มย้อนกลับ -->
        <div class="booking-wrapper">
            <a href="{{ route('home') }}" class="btn-back-link">
                ← กลับหน้าหลัก
            </a>
        </div>

        <!-- การ์ดฟอร์มจองเรียน -->
        <div class="booking-card">
            <h2 class="booking-title">
                📅 จองนัดหมายเรียน
            </h2>

            <form action="{{ route('appointments.store') }}" method="POST">
                @csrf

                <!-- เลือกวิชาที่ต้องการเรียน -->
                <div class="mb-3">
                    <label class="form-label">วิชาที่ต้องการเรียน</label>
                    <select name="subject_id" class="form-select" required>
                        <option value="" disabled selected>-- เลือกวิชาที่ต้องการเรียน --</option>
                        @if(isset($subjects) && $subjects->count() > 0)
                            @foreach($subjects as $sub)
                                <option value="{{ $sub->subject_id }}">{{ $sub->subject_name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <!-- วัน-เวลา เริ่มต้น -->
                <div class="mb-3">
                    <label class="form-label">วัน-เวลา เริ่มต้น</label>
                    <input type="datetime-local" name="start_datetime" class="form-control" required>
                </div>

                <!-- วัน-เวลา สิ้นสุด -->
                <div class="mb-3">
                    <label class="form-label">วัน-เวลา สิ้นสุด</label>
                    <input type="datetime-local" name="end_datetime" class="form-control" required>
                </div>

                <!-- Hidden inputs สำหรับส่งค่า ID -->
                <input type="hidden" name="tutor_id" value="{{ request('tutor_id', $tutor_id ?? 'T001') }}">
                <input type="hidden" name="student_id" value="{{ Auth::id() ?? 'S001' }}">

                <!-- ปุ่มดำเนินการ -->
                <div class="d-flex flex-column gap-2 mt-4">
                    <button type="submit" class="btn-submit-booking">บันทึกการจอง</button>
                    <a href="{{ route('appointments.index') }}" class="btn-cancel-booking">ยกเลิก</a>
                </div>
            </form>
        </div>

    </div>

</body>
</html>