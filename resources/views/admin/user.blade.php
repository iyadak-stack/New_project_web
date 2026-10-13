@extends('admin.layout')
@section('title', 'โปรไฟล์ผู้ใช้')
@section('description', 'ข้อมูลบัญชี โปรไฟล์นักเรียน และข้อมูลการสอนของผู้ใช้')
@section('content')
    {{-- member เป็นผู้ใช้ที่ Controller ค้นหาจาก user_id พร้อมข้อมูลโปรไฟล์ --}}
    <a class="admin-button secondary" href="{{ route('admin.users.index') }}">
        ← กลับรายชื่อผู้ใช้</a>
    <section class="admin-panel admin-profile">
        <div class="admin-profile-avatar">
            @if ($member->profile_picture)
                <img src="{{ asset('storage/' . $member->profile_picture) }}" alt="รูปโปรไฟล์">
                @else{{ mb_substr($member->first_name, 0, 1) }}
            @endif
        </div>
        <div>
            <h2>{{ $member->first_name }} {{ $member->last_name }}</h2>
            <p>{{ $member->email }}</p>
            <p>รหัสผู้ใช้ {{ $member->user_id }}</p>
            <span
                class="admin-badge {{ $member->is_active ? 'green' : 'red' }}">{{ $member->is_active ? 'ใช้งานอยู่' : 'พักบัญชี' }}</span>
        </div>
    </section>
    <div class="admin-two-columns">
        <section class="admin-panel">
            <h2>ข้อมูลนักเรียน</h2>
            <p class="admin-description">{{ $member->studentProfile?->bio ?: 'ยังไม่ระบุข้อความแนะนำตัว' }}</p>
        </section>
        <section class="admin-panel">
            <h2>ข้อมูลการสอน</h2>
            @if ($member->tutorProfile)
                <p class="admin-description">{{ $member->tutorProfile->bio }}</p>
                <p>ประสบการณ์ {{ $member->tutorProfile->experience_years }} ปี</p>
                <p>วิชา: {{ $member->tutorProfile->subjects->pluck('subject_name')->implode(', ') ?: 'ยังไม่ระบุ' }}</p>
                <p>รูปแบบ:
                    {{ ['online' => 'ออนไลน์', 'onsite' => 'นัดเจอ', 'both' => 'ทั้งสองแบบ'][$member->tutorProfile->teaching_mode] ?? $member->tutorProfile->teaching_mode }}
                </p>
                <a class="admin-button secondary"
                    href="{{ route('tutor.show', $member->tutorProfile->tutor_id) }}">เปิดโปรไฟล์ติวเตอร์ →</a>
            @else<p>ยังไม่มีโปรไฟล์ติวเตอร์</p>
            @endif
        </section>
    </div>
    <section class="admin-panel">
        <h2>ช่องทางติดต่อ</h2>
        <p>Line: {{ $member->contact?->line_id ?: 'ไม่ระบุ' }} · Discord: {{ $member->contact?->discord_id ?: 'ไม่ระบุ' }}
        </p>
    </section>
@endsection
