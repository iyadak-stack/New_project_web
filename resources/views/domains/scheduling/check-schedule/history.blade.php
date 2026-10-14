@extends('domains.scheduling.layout')

@section('title', 'ประวัติการเรียนและการสอน')

@section('content')
    <div class="welcome-box">
        <h1>ประวัติการเรียนและการสอน</h1>
        <p>แสดงนัดที่ผ่านมา พร้อมสถานะของแต่ละนัด</p>
    </div>

        @forelse ($appointments as $appointment)
            <div class="detail-card">
                <h2 class="card-title">นัด {{ $appointment['id'] }}</h2>
                <p>บทบาท: {{ $appointment['role'] }}</p>
                <p>วิชา: {{ $appointment['subject'] }}</p>
                <p>{{ $appointment['start'] }} – {{ $appointment['end'] }}</p>
                <p>สถานะ: {{ ['pending' => 'รอยืนยัน', 'confirmed' => 'ยืนยันแล้ว', 'cancelled' => 'ยกเลิกแล้ว', 'completed' => 'เรียนเสร็จแล้ว'][$appointment['status']] ?? $appointment['status'] }}</p>
            </div>
        @empty
            <div class="detail-card"><p class="empty-text">ยังไม่มีนัดที่ผ่านมา</p></div>
        @endforelse
    <a class="btn-back" href="{{ route('schedule.check') }}">← กลับไปเช็กเวลาว่าง</a>
@endsection
