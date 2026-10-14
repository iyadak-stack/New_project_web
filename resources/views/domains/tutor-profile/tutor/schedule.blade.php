<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดเวลา - PeerTutor</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('CSS/home.css') }}">
    <link rel="stylesheet" href="{{ asset('CSS/tutor-navbar.css') }}">
    <link rel="stylesheet" href="{{ asset('CSS/profile-card.css') }}">
    <link rel="stylesheet" href="{{ asset('CSS/tutor-teaching.css') }}">
</head>
<body class="teaching-body">
<x-navbar />
<main class="teaching-page">
    <header class="teaching-banner">
        <h1>จัดเวลา</h1>
        <p>กำหนดวันที่และช่วงเวลาที่สะดวกสอน เพื่อให้นักเรียนส่งคำขอเรียน</p>
    </header>
    @if (session('success'))<p class="teaching-message" role="status">{{ session('success') }}</p>@endif
    @if ($errors->any())
        <div class="teaching-errors" role="alert">
            @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    {{-- เพิ่มและนำเวลาว่างออก โดยไม่ยกเลิกนัดหมายเดิม --}}
    <section class="teaching-panel">
        <h2>เวลาว่างสำหรับรับคำขอเรียน</h2>
        <p class="teaching-hint">เพิ่มแต่ละช่วงตามเวลาที่สะดวกสอน เมื่อนักเรียนจองจะมารอให้ยืนยันในตารางนัดหมาย</p>
        <form action="{{ route('tutor.schedule.store') }}" method="POST">
            @csrf
            <div class="teaching-times">
                <label class="teaching-field">วันที่<input type="date" name="date" min="{{ now('Asia/Bangkok')->format('Y-m-d') }}" value="{{ old('date') }}" required></label>
                <label class="teaching-field">เวลาเริ่ม<input type="time" name="start_time" value="{{ old('start_time') }}" required></label>
                <label class="teaching-field">เวลาสิ้นสุด<input type="time" name="end_time" value="{{ old('end_time') }}" required></label>
            </div>
            <button type="submit" class="teaching-button">+ เพิ่มเวลาว่าง</button>
        </form>
        <div class="teaching-table-wrap">
            <table class="teaching-table">
                <thead><tr><th>วันที่</th><th>เวลา</th><th>จัดการ</th></tr></thead>
                <tbody>
                    @forelse ($availabilities as $slot)
                        <tr>
                            <td>{{ $slot->start_datetime->format('d/m/Y') }}</td>
                            <td>{{ $slot->start_datetime->format('H:i') }} – {{ $slot->end_datetime->format('H:i') }}</td>
                            <td>
                                <form action="{{ route('tutor.schedule.destroy', $slot->availability_id) }}" method="POST" onsubmit="return confirm('นำเวลานี้ออกจากช่วงรับคำขอใหม่ใช่ไหม? นัดหมายเดิมจะยังอยู่')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="teaching-button secondary" type="submit">นำเวลาออก</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3">ยังไม่มีเวลาว่าง กรุณาเพิ่มช่วงเวลาที่สะดวกสอน</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <a class="teaching-link" href="{{ route('appointments.index', ['status'=>'pending']) }}">ดูคำขอเรียนที่รอยืนยัน →</a>
    </section>
</main>
</body>
</html>
