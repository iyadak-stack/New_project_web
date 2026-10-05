@extends('domains.scheduling.layout')

@section('title', 'ประวัติการเรียนและการสอน')

@section('content')
    <div class="container py-4">
        <h1 class="section-title">ประวัติการเรียนและการสอน</h1>
        <a href="{{ route('schedule.check') }}">กลับไปเช็กเวลาว่าง</a>
        <p class="mt-3">แสดงนัดที่ผ่านมา พร้อมสถานะของแต่ละนัด</p>

        @forelse ($appointments as $appointment)
            <div class="profile-card p-4 mb-3">
                <h2 class="h5">นัด {{ $appointment['id'] }}</h2>
                <p>บทบาท: {{ $appointment['role'] }}</p>
                <p>วิชา: {{ $appointment['subject'] }}</p>
                <p>{{ $appointment['start'] }} – {{ $appointment['end'] }}</p>
                <p>สถานะ: {{ ['pending' => 'รอยืนยัน', 'confirmed' => 'ยืนยันแล้ว', 'cancelled' => 'ยกเลิกแล้ว'][$appointment['status']] ?? $appointment['status'] }}</p>
            </div>
        @empty
            <div class="schedule-placeholder"><p>ยังไม่มีนัดที่ผ่านมา</p></div>
        @endforelse
    </div>
@endsection
