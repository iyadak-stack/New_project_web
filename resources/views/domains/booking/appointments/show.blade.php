<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายละเอียดการนัดหมาย</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="p-4">
    <div class="container">
        <h2>รายละเอียดการนัดหมาย</h2>

        <div class="card p-3 mb-3">
            <p><strong>รหัสการนัดหมาย:</strong> {{ $appointment->Appointment_id }}</p>
            <p><strong>วิชาที่เรียน:</strong> {{ $appointment->subject->subject_name ?? '-' }}</p>
            <p><strong>เวลานัด:</strong> {{ $appointment->start_datetime }} ถึง {{ $appointment->end_datetime }}</p>
            <p><strong>สถานะปัจจุบัน:</strong> {{ $appointment->status }}</p>
            <p><strong>รหัสติวเตอร์:</strong> {{ $appointment->Tutor_profiles_tutor_id }}</p>
            <p><strong>รหัสนักเรียน:</strong> {{ $appointment->Student_profiles_student_id }}</p>
        </div>

        <a href="{{ route('appointments.index') }}" class="btn btn-secondary">ย้อนกลับ</a>
    </div>
</body>
</html>