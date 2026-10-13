@extends('domains.scheduling.layout')

@section('title', 'แก้ไขเวลาว่าง')

@section('content')
    <div class="welcome-box">
        <h1>แก้ไขเวลาว่าง</h1>
        <p>เปลี่ยนเวลาเริ่มและสิ้นสุดของช่วงเวลานี้</p>
    </div>
    @if ($errors->any())
        <div class="pe-errors" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form class="detail-card" method="POST" action="{{ route('availabilities.update', $availability) }}">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="start_datetime">เริ่ม</label>
            <input id="start_datetime" class="form-control" type="datetime-local" name="start_datetime" value="{{ old('start_datetime', $availability->start_datetime->format('Y-m-d\TH:i')) }}" required>
        </div>
        <div class="form-group">
            <label for="end_datetime">สิ้นสุด</label>
            <input id="end_datetime" class="form-control" type="datetime-local" name="end_datetime" value="{{ old('end_datetime', $availability->end_datetime->format('Y-m-d\TH:i')) }}" required>
        </div>
        <div class="pe-actions">
            <button class="pe-save" type="submit">บันทึก</button>
            <a class="pe-cancel" href="{{ route('availabilities.index') }}">กลับ</a>
        </div>
    </form>
@endsection
