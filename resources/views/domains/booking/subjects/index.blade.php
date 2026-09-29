<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการวิชาที่สอน</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="p-4">
    <div class="container">
        <h2>จัดการวิชาที่สอน</h2>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <!-- ฟอร์มเพิ่มวิชา -->
        <form action="{{ url('/subjects') }}" method="POST" class="mb-4">
            @csrf
            <div class="row">
                <div class="col">
                    <input type="text" name="subject_id" class="form-control" placeholder="รหัสวิชา (เช่น SUB01)" required>
                </div>
                <div class="col">
                    <input type="text" name="subject_name" class="form-control" placeholder="ชื่อวิชา" required>
                </div>
                <div class="col">
                    <button type="submit" class="btn btn-primary">เพิ่มวิชา</button>
                </div>
            </div>
        </form>

        <!-- ตารางแสดงวิชา -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>รหัสวิชา</th>
                    <th>ชื่อวิชา</th>
                    <th>การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                @foreach($subjects as $subject)
                <tr>
                    <td>{{ $subject->subject_id }}</td>
                    <td>{{ $subject->subject_name }}</td>
                    <td>
                        <form action="{{ url('/subjects/'.$subject->subject_id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('ยืนยันการลบ?')">ลบ</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>