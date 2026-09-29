<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จองนัดหมายเรียน</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="p-4">
    <div class="container">
        <h2>จองนัดหมายเรียน</h2>

        <form action="{{ route('appointments.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label>เลือกวิชาที่ต้องการเรียน:</label>
                <select name="subject_id" class="form-control" required>
                    <option value="">-- เลือกวิชา --</option>
                    @foreach($subjects as $sub)
                        <option value="{{ $sub->subject_id }}">{{ $sub->subject_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label>วัน-เวลา เริ่มต้น:</label>
                <input type="datetime-local" name="start_datetime" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>วัน-เวลา สิ้นสุด:</label>
                <input type="datetime-local" name="end_datetime" class="form-control" required>
            </div>

            <!-- ค่าสมมุติตามไอดี นศ. -->
            <input type="hidden" name="tutor_id" value="T001">
            <input type="hidden" name="student_id" value="S001">

            <button type="submit" class="btn btn-primary">บันทึกการจอง</button>
            <a href="{{ route('appointments.index') }}" class="btn btn-secondary">ยกเลิก</a>
        </form>
    </div>
</body>
</html>