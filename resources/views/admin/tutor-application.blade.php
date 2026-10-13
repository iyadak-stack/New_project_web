@extends('admin.layout')
@section('title', 'ตรวจคำขอติวเตอร์')
@section('description', 'ตรวจข้อมูลการสอน แล้วอนุมัติหรือแจ้งเหตุผลที่ต้องแก้ไข')
@section('content')
    <a class="admin-button secondary" href="{{ route('admin.tutor-applications.index') }}">กลับรายการคำขอ</a>
    <section class="admin-panel">
        @if ($application->user->profile_picture)
            <img src="{{ asset('storage/'.$application->user->profile_picture) }}" width="80" height="80" alt="รูปผู้สมัคร" style="object-fit: cover; border-radius: 10px;">
        @endif
        <h2>{{ $application->user->first_name }} {{ $application->user->last_name }}</h2>
        <p>{{ $application->user->email }}</p>
        <p>แนะนำตัว: {{ $application->bio ?: 'ยังไม่ระบุ' }}</p>
        <p>ประสบการณ์ {{ $application->experience_years }} ปี</p>
        <p>วิชาที่สอน: {{ $application->subjects->pluck('subject_name')->implode(', ') ?: 'ยังไม่ระบุ' }}</p>
        <p>รูปแบบ: {{ ['online'=>'ออนไลน์', 'onsite'=>'นัดเจอ', 'both'=>'ทั้งสองแบบ'][$application->teaching_mode] ?? $application->teaching_mode }}</p>
        <p>Line: {{ $application->user->contact?->line_id ?: 'ไม่ระบุ' }}</p>
        <p>Discord: {{ $application->user->contact?->discord_id ?: 'ไม่ระบุ' }}</p>
        <p>Zoom: {{ $application->user->contact?->zoom_link ?: 'ไม่ระบุ' }}</p>
        <p>Google Meet: {{ $application->user->contact?->google_meet_link ?: 'ไม่ระบุ' }}</p>
        <p>สถานะ: {{ ['pending'=>'รออนุมัติ','approved'=>'อนุมัติแล้ว','rejected'=>'ไม่อนุมัติ','draft'=>'ยังไม่ส่งคำขอ'][$application->approval_status] ?? $application->approval_status }}</p>
        @if ($application->reject_reason)<p>เหตุผล: {{ $application->reject_reason }}</p>@endif
    </section>
    @if ($application->approval_status === 'pending')
        {{-- แยกฟอร์มสองปุ่ม เพื่อให้การไม่อนุมัติต้องระบุเหตุผล --}}
        <section class="admin-panel">
            <form action="{{ route('admin.tutor-applications.decide', $application->tutor_id) }}" method="POST" onsubmit="return confirm('ยืนยันอนุมัติผู้สมัครคนนี้ใช่ไหม?')">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="approved">
                <button class="admin-button">อนุมัติ</button>
            </form>
            <hr>
            <form action="{{ route('admin.tutor-applications.decide', $application->tutor_id) }}" method="POST" onsubmit="return confirm('ยืนยันไม่อนุมัติคำขอนี้ใช่ไหม?')">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="rejected">
                <label for="reject_reason">เหตุผลที่ไม่อนุมัติ</label>
                <textarea id="reject_reason" name="reject_reason" rows="3" maxlength="1000" required>{{ old('reject_reason') }}</textarea>
                <button class="admin-button secondary">ไม่อนุมัติ</button>
            </form>
        </section>
    @endif
@endsection
