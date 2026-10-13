@extends('domains.scheduling.layout')

@section('title', 'จัดการเวลาว่าง')

@section('content')
    <div class="welcome-box">
        <h1>จัดการเวลาว่าง</h1>
        <p>เพิ่มช่วงเวลาที่คุณสะดวก เพื่อใช้ค้นหาเวลาว่างตรงกัน</p>
    </div>
    @if (session('success'))
        <p class="alert alert-success" role="status">{{ session('success') }}</p>
    @endif
    @if ($errors->any())
        <div class="pe-errors" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form class="detail-card" method="POST" action="{{ route('availabilities.store') }}">
        @csrf
        <h2 class="card-title">เพิ่มเวลาว่าง</h2>
        <div class="form-group">
            <label for="start_datetime">เริ่ม</label>
            <input id="start_datetime" class="form-control" type="datetime-local" name="start_datetime" value="{{ old('start_datetime') }}" required>
        </div>
        <div class="form-group">
            <label for="end_datetime">สิ้นสุด</label>
            <input id="end_datetime" class="form-control" type="datetime-local" name="end_datetime" value="{{ old('end_datetime') }}" required>
        </div>
        <button class="pe-save" type="submit">เพิ่มเวลาว่าง</button>
    </form>
    <h2 class="card-title">เวลาว่างของฉัน</h2>
    @forelse ($availabilities as $slot)
        <div class="detail-card">
            <p>{{ $slot->start_datetime->format('d/m/Y H:i') }} – {{ $slot->end_datetime->format('d/m/Y H:i') }}</p>
            <div class="pe-actions">
                <a class="home-card-button" href="{{ route('availabilities.edit', $slot) }}">แก้ไข</a>
                <form method="POST" action="{{ route('availabilities.destroy', $slot) }}">
                    @csrf
                    @method('DELETE')
                    <button class="btn pe-cancel" type="submit">ลบ</button>
                </form>
            </div>
        </div>
    @empty
        <div class="detail-card"><p class="empty-text">ยังไม่มีเวลาว่าง</p></div>
    @endforelse
    <a class="btn-back" href="{{ route('home') }}">← กลับหน้าหลัก</a>
@endsection
