<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>วิชาของฉัน - PeerTutor</title>
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
        <h1>วิชาของฉัน</h1>
        <p>เลือกวิชาที่สอน ตั้งรูปแบบการสอน และจัดการช่องทางเรียน</p>
    </header>
    @if (session('success'))<p class="teaching-message" role="status">{{ session('success') }}</p>@endif
    @if ($errors->any())
        <div class="teaching-errors" role="alert">
            @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    {{-- ฟอร์มนี้แก้เฉพาะวิชาและข้อมูลการสอนของคนที่ล็อกอิน --}}
    <form action="{{ route('tutor.subjects.update') }}" method="POST" class="teaching-panel">
        @csrf
        @method('PUT')
        <h2>วิชาที่เปิดสอน</h2>
        <p class="teaching-hint">เลือกจากวิชาที่มีในระบบ การนำวิชาออกจะไม่ลบนัดหมายเดิม</p>
        <div class="teaching-subjects">
            @forelse ($subjects as $subject)
                <label>
                    <input type="checkbox" name="subjects[]" value="{{ $subject->subject_id }}"
                           @checked(in_array($subject->subject_id, old('subjects', $tutor->subjects->pluck('subject_id')->all())))>
                    {{ $subject->subject_name }}
                </label>
            @empty
                <p>ยังไม่มีรายวิชา กรุณาติดต่อแอดมินเพื่อเพิ่มรายวิชา</p>
            @endforelse
        </div>

        <h2>รูปแบบและช่องทางเรียน</h2>
        <p class="teaching-hint">นัดหมายจะแสดงข้อมูลปัจจุบันจากโปรไฟล์นี้ นักเรียนดูช่องทางเรียนได้เมื่อคุณยืนยันคำขอแล้ว</p>
        <label class="teaching-field">รูปแบบการสอน
            <select name="teaching_mode" required>
                <option value="online" @selected(old('teaching_mode', $tutor->teaching_mode)==='online')>ออนไลน์</option>
                <option value="onsite" @selected(old('teaching_mode', $tutor->teaching_mode)==='onsite')>นัดเจอ</option>
                <option value="both" @selected(old('teaching_mode', $tutor->teaching_mode)==='both')>ออนไลน์และนัดเจอ</option>
            </select>
        </label>
        <div class="teaching-fields">
            <label class="teaching-field">Line ID<input name="line_id" maxlength="45" value="{{ old('line_id', $contact?->line_id) }}"></label>
            <label class="teaching-field">Discord ID<input name="discord_id" maxlength="255" value="{{ old('discord_id', $contact?->discord_id) }}"></label>
            <label class="teaching-field">ลิงก์ Zoom<input type="url" name="zoom_link" maxlength="255" placeholder="https://zoom.us/..." value="{{ old('zoom_link', $contact?->zoom_link) }}"></label>
            <label class="teaching-field">ลิงก์ Google Meet<input type="url" name="google_meet_link" maxlength="255" placeholder="https://meet.google.com/..." value="{{ old('google_meet_link', $contact?->google_meet_link) }}"></label>
        </div>
        <button type="submit" class="teaching-button">บันทึกข้อมูลการสอน</button>
    </form>

</main>
</body>
</html>
