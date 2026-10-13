@extends('admin.layout')
@section('title', 'คำขอติวเตอร์')
@section('description', 'ตรวจข้อมูลผู้สมัคร ก่อนเปิดสิทธิ์ใช้งานบทบาทติวเตอร์')
@section('content')
    <form method="GET" class="admin-panel">
        <label>สถานะคำขอ</label>
        <select name="status">
            <option value="pending" @selected($status === 'pending')>รออนุมัติ</option>
            <option value="approved" @selected($status === 'approved')>อนุมัติแล้ว</option>
            <option value="rejected" @selected($status === 'rejected')>ไม่อนุมัติ</option>
        </select>
        <button class="admin-button">แสดงรายการ</button>
    </form>
    <section class="admin-panel">
        {{-- แสดงผลล่าสุดเท่านั้น ไม่เพิ่มตารางประวัติ --}}
        @forelse ($applications as $application)
            <p>{{ $application->user->first_name }} {{ $application->user->last_name }} · {{ $application->user->email }}</p>
            <p>ส่งเมื่อ {{ $application->submitted_at?->timezone('Asia/Bangkok')->format('d/m/Y H:i') }}</p>
            <a class="admin-button secondary" href="{{ route('admin.tutor-applications.show', $application->tutor_id) }}">ตรวจคำขอ</a>
            <hr>
        @empty
            <p>ไม่มีคำขอในสถานะนี้</p>
        @endforelse
        {{ $applications->links() }}
    </section>
@endsection
