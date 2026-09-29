<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายการนัดหมายทั้งหมด</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="p-4">
    <div class="container">
        <h2>รายการนัดหมายทั้งหมด</h2>
        <a href="{{ route('appointments.create') }}" class="btn btn-success mb-3">+ สร้างนัดหมายใหม่</a>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>รหัสนัดหมาย</th>
                    <th>วิชา</th>
                    <th>เริ่มเวลา</th>
                    <th>สิ้นสุดเวลา</th>
                    <th>สถานะ</th>
                    <th>ปุ่มจัดการ</th>
                </tr>
            </thead>
            <tbody>
                @foreach($appointments as $app)
                <tr>
                    <td>{{ $app->Appointment_id }}</td>
                    <td>{{ $app->subject->subject_name ?? '-' }}</td>
                    <td>{{ $app->start_datetime }}</td>
                    <td>{{ $app->end_datetime }}</td>
                    <td>
                        <span class="badge bg-info">{{ $app->status }}</span>
                    </td>
                    <td>
                        <a href="{{ route('appointments.show', $app->Appointment_id) }}" class="btn btn-info btn-sm">ดูรายละเอียด</a>
                        
                        <!-- ฟอร์มอัปเดตสถานะ -->
                        <form action="{{ url('/appointments/'.$app->Appointment_id.'/status') }}" method="POST" style="display:inline;">
                            @csrf
                            <input type="hidden" name="status" value="confirmed">
                            <button class="btn btn-success btn-sm">ยืนยัน</button>
                        </form>

                        <form action="{{ url('/appointments/'.$app->Appointment_id.'/status') }}" method="POST" style="display:inline;">
                            @csrf
                            <input type="hidden" name="status" value="cancelled">
                            <button class="btn btn-warning btn-sm">ยกเลิก</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>