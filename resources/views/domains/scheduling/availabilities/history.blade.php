@extends('domains.scheduling.layout')

@section('title', 'ประวัติเวลาว่าง')

@section('content')
    <div class="welcome-box">
        <h1>ประวัติเวลาว่าง</h1>
        <p>แสดงรายการปัจจุบัน พร้อมเวลาสร้างและแก้ไขล่าสุด ไม่เก็บรายการที่ลบแล้ว</p>
    </div>
    @forelse ($availabilities as $availability)
        <div class="detail-card">
            <p>{{ $availability->start_datetime->format('d/m/Y H:i') }} – {{ $availability->end_datetime->format('d/m/Y H:i') }}</p>
            <p class="section-sub">สร้างเมื่อ {{ $availability->created_at->format('d/m/Y H:i') }}</p>
            <p class="section-sub">แก้ไขล่าสุด {{ $availability->updated_at->format('d/m/Y H:i') }}</p>
        </div>
    @empty
        <div class="detail-card"><p class="empty-text">ยังไม่มีรายการเวลาว่าง</p></div>
    @endforelse
    <a class="btn-back" href="{{ route('availabilities.index') }}">← กลับไปจัดการเวลาว่าง</a>
@endsection
