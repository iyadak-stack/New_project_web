@extends('domains.scheduling.layout')

@section('title', 'ยืนยันการจอง')

@section('content')
    <div class="container py-4">
        <h1 class="section-title">ยืนยันการจอง</h1>
        <a href="{{ route('schedule.check') }}">กลับไปเลือกเวลา</a>

        <div class="profile-card p-4 mt-3">
            <p>ตรวจสอบข้อมูลการเรียนก่อนยืนยันการจอง</p>

            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('schedule.booking.store') }}" method="POST">
                @csrf

                <input type="hidden" name="Student_profiles_student_id" value="{{ $student->getRawOriginal('id') }}">
                <input type="hidden" name="Tutor_profiles_tutor_id" value="{{ $tutor->getRawOriginal('tutor_id') }}">

                <label for="subject_id" class="form-label">วิชา</label>
                <select id="subject_id" name="Subject_subject_id" class="form-select mb-3" required>
                    <option value="">เลือกวิชา</option>
                    @foreach ($subjects as $subject)
                        <option value="{{ $subject->getKey() }}" @selected(old('Subject_subject_id') === (string) $subject->getKey())>{{ $subject->subject_name }}</option>
                    @endforeach
                </select>

                <label for="start_datetime" class="form-label">เวลาเริ่ม</label>
                <input id="start_datetime" type="datetime-local" name="start_datetime" class="form-control mb-3" value="{{ old('start_datetime', \Illuminate\Support\Carbon::parse($filters['start_datetime'])->format('Y-m-d\TH:i')) }}" readonly required>

                <label for="end_datetime" class="form-label">เวลาสิ้นสุด</label>
                <input id="end_datetime" type="datetime-local" name="end_datetime" class="form-control mb-3" value="{{ old('end_datetime', \Illuminate\Support\Carbon::parse($filters['end_datetime'])->format('Y-m-d\TH:i')) }}" readonly required>

                <button type="submit" class="btn btn-primary">ยืนยันการจอง</button>
            </form>
        </div>
    </div>
@endsection