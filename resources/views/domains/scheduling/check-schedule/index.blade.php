@extends('domains.scheduling.layout')

@section('title', 'เช็กเวลาว่างตรงกัน')

@section('content')
    <div class="welcome-box">
        <h1>เช็กเวลาว่างตรงกัน</h1>
        <p>เลือกติวเตอร์และช่วงเวลาที่ต้องการ ระบบจะหาเวลาที่ทั้งสองคนว่าง</p>
    </div>
    <form action="{{ route('schedule.check.results') }}" method="POST" class="detail-card">
        @csrf
        <div class="form-group">
            <label for="tutor_id">รหัสบัญชีติวเตอร์</label>
            <input id="tutor_id" class="form-control" type="text" name="tutor_id" maxlength="10" value="{{ old('tutor_id', ($filters ?? [])['tutor_id'] ?? '') }}" required>
            @error('tutor_id') <p class="login-error">{{ $message }}</p> @enderror
        </div>
        <div class="form-group">
            <label for="start_datetime">ค้นหาตั้งแต่</label>
            <input id="start_datetime" class="form-control" type="datetime-local" name="start_datetime" value="{{ old('start_datetime', ($filters ?? [])['start_datetime'] ?? '') }}" required>
            @error('start_datetime') <p class="login-error">{{ $message }}</p> @enderror
        </div>
        <div class="form-group">
            <label for="end_datetime">ถึง</label>
            <input id="end_datetime" class="form-control" type="datetime-local" name="end_datetime" value="{{ old('end_datetime', ($filters ?? [])['end_datetime'] ?? '') }}" required>
            @error('end_datetime') <p class="login-error">{{ $message }}</p> @enderror
        </div>
        <button class="pe-save" type="submit">ค้นหาเวลาว่างตรงกัน</button>
    </form>
    <p><a class="btn-back" href="{{ route('availabilities.index') }}">จัดการเวลาว่างของฉัน →</a></p>

    @isset($times)
        <section>
            <h2 class="card-title">ช่วงเวลาที่ว่างตรงกัน</h2>
            @forelse ($times as $time)
                <div class="detail-card">
                    <p>{{ \Illuminate\Support\Carbon::parse($time['start'])->format('d/m/Y H:i') }} – {{ \Illuminate\Support\Carbon::parse($time['end'])->format('d/m/Y H:i') }}</p>
                    <a class="home-card-button" href="{{ route('schedule.booking', ['tutor_id' => $filters['tutor_id'], 'start_datetime' => $time['start'], 'end_datetime' => $time['end']]) }}">เลือกวิชาและจอง</a>
                </div>
            @empty
                <div class="detail-card"><p class="empty-text">ไม่พบช่วงเวลาว่างตรงกันในช่วงที่เลือก</p></div>
            @endforelse
        </section>
    @endisset
@endsection
