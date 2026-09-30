@extends('layouts.tutor')

@section('title', 'แก้ไขโปรไฟล์ติวเตอร์')

@section('content')

<div>
    <h1>แก้ไขโปรไฟล์ติวเตอร์</h1>

    <p>อัปเดตข้อมูลการสอนและโปรไฟล์ติวเตอร์ของคุณ</p>
</div>

@if ($errors->any())

    <div>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>

@endif

<div>
    <form action="{{ route('tutor.profile.update') }}" method="POST">
        @csrf
        <div>
            <label for="bio">แนะนำตัว (Bio)</label>

            <textarea id="bio" name="bio" rows="5">{{ old('bio', $tutorProfile->bio) }}</textarea>
        </div>

        <div>
            <label for="experience_years">ประสบการณ์ (ปี)</label>
            <input type="number" id="experience_years" name="experience_years" min="0" value="{{ old('experience_years', $tutorProfile->experience_years) }}">
        </div>

        <div>
            <label for="teaching_mode">รูปแบบการสอน</label>

            <select id="teaching_mode" name="teaching_mode">
                <option value="online" {{ old('teaching_mode', $tutorProfile->teaching_mode) === 'online' ? 'selected' : '' }}>ออนไลน์ (Online)</option>
                <option value="onsite" {{ old('teaching_mode', $tutorProfile->teaching_mode) === 'onsite' ? 'selected' : '' }}>นัดเจอ (Onsite)</option>
                <option value="both" {{ old('teaching_mode', $tutorProfile->teaching_mode) === 'both' ? 'selected' : '' }}>ทั้งสองแบบ (Both)</option>
            </select>
        </div>

        <div>
            <button type="submit">บันทึก</button>

            <a href="{{ route('tutor.profile') }}">ยกเลิก</a>
        </div>
    </form>
</div>

@endsection
