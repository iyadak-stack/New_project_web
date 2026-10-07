@extends('domains.scheduling.layout')

@section('title', 'จองเวลาเรียน')

@section('content')
    <div class="container py-4">
        <h1 class="section-title">จองเวลาเรียน</h1>
        <a href="{{ route('schedule.check') }}">กลับไปเช็กเวลาว่าง</a>

        <div class="profile-card p-4 mt-3">
            <p>เลือกวิชาและเวลาไว้ก่อนได้ การจองผ่านหน้านี้ยังไม่เปิดใช้งาน</p>
            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif
            <input type="hidden" name="student_id" value="{{ $student->getRawOriginal('id') }}">
            <input type="hidden" name="tutor_id" value="{{ $tutor->getRawOriginal('id') }}">
            <label for="subject_id" class="form-label">วิชา</label>
            <select id="subject_id" name="subject_id" class="form-select mb-3" required>
                <option value="">เลือกวิชา</option>
                @foreach ($subjects as $subject)
                    <option value="{{ $subject->getKey() }}" @selected(old('subject_id') === (string) $subject->getKey())>{{ $subject->subject_name }}</option>
                @endforeach
            </select>
            <label for="start_datetime" class="form-label">เริ่ม</label>
            <input id="start_datetime" type="datetime-local" name="start_datetime" class="form-control mb-3" value="{{ old('start_datetime', \Illuminate\Support\Carbon::parse($filters['start_datetime'])->format('Y-m-d\TH:i')) }}" min="{{ \Illuminate\Support\Carbon::parse($filters['start_datetime'])->format('Y-m-d\TH:i') }}" max="{{ \Illuminate\Support\Carbon::parse($filters['end_datetime'])->format('Y-m-d\TH:i') }}" required>
            <label for="end_datetime" class="form-label">สิ้นสุด</label>
            <input id="end_datetime" type="datetime-local" name="end_datetime" class="form-control mb-3" value="{{ old('end_datetime', \Illuminate\Support\Carbon::parse($filters['end_datetime'])->format('Y-m-d\TH:i')) }}" min="{{ \Illuminate\Support\Carbon::parse($filters['start_datetime'])->format('Y-m-d\TH:i') }}" max="{{ \Illuminate\Support\Carbon::parse($filters['end_datetime'])->format('Y-m-d\TH:i') }}" required>
            <button type="button" class="btn btn-primary" disabled>จองเวลาเรียน</button>
        </div>
    </div>
@endsection
