@extends('domains.scheduling.layout')

@section('title', 'จองเวลาเรียน')

@section('content')
    <div class="welcome-box">
        <h1>จองเวลาเรียน</h1>
        <p>เลือกวิชาและเวลาที่ต้องการเรียนในช่วงที่ว่างตรงกัน</p>
    </div>

    <form id="schedule-booking" class="detail-card" method="POST" action="{{ route('appointments.store') }}">
        @csrf
        @unless ($bookingEnabled)
            <p>เลือกวิชาและเวลาไว้ก่อนได้ การจองผ่านหน้านี้รอเชื่อมกับระบบนัดหมาย</p>
        @endunless
        <p id="schedule-message" class="section-sub" role="status" aria-live="polite">กำลังตรวจเวลาล่าสุด</p>
        @if ($errors->any())
            <div class="pe-errors" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <input type="hidden" name="student_id" value="{{ $student->getRawOriginal('id') }}">
        <input type="hidden" name="tutor_id" value="{{ $tutor->getRawOriginal('tutor_id') }}">
        <input type="hidden" name="appointment_date" id="appointment-date">
        <input type="hidden" name="appointment_time" id="appointment-time">
        <div class="form-group">
            <label for="teaching_mode">รูปแบบการเรียน</label>
            <select id="teaching_mode" name="teaching_mode" class="form-control" required>
                <option value="online">ออนไลน์</option>
                <option value="onsite">พบกัน</option>
            </select>
        </div>
        <div class="form-group">
            <label for="subject_id">วิชา</label>
            <select id="subject_id" name="subject_id" class="form-control" required>
                <option value="">เลือกวิชา</option>
                @foreach ($subjects as $subject)
                    <option value="{{ $subject->getKey() }}" @selected(old('subject_id') === (string) $subject->getKey())>{{ $subject->subject_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="start_datetime">เริ่ม</label>
            <input id="start_datetime" type="datetime-local" name="start_datetime" class="form-control" value="{{ old('start_datetime', \Illuminate\Support\Carbon::parse($filters['start_datetime'])->format('Y-m-d\TH:i')) }}" min="{{ \Illuminate\Support\Carbon::parse($filters['start_datetime'])->format('Y-m-d\TH:i') }}" max="{{ \Illuminate\Support\Carbon::parse($filters['end_datetime'])->format('Y-m-d\TH:i') }}" required>
        </div>
        <div class="form-group">
            <label for="end_datetime">สิ้นสุด</label>
            <input id="end_datetime" type="datetime-local" name="end_datetime" class="form-control" value="{{ old('end_datetime', \Illuminate\Support\Carbon::parse($filters['end_datetime'])->format('Y-m-d\TH:i')) }}" min="{{ \Illuminate\Support\Carbon::parse($filters['start_datetime'])->format('Y-m-d\TH:i') }}" max="{{ \Illuminate\Support\Carbon::parse($filters['end_datetime'])->format('Y-m-d\TH:i') }}" required>
        </div>
        <button id="schedule-submit" type="submit" class="btn-booking-submit" disabled>จองเวลาเรียน</button>
    </form>
    <a class="btn-back" href="{{ route('schedule.check') }}">← กลับไปเช็กเวลาว่าง</a>
    <script>
        const form = document.getElementById('schedule-booking');
        const start = document.getElementById('start_datetime');
        const end = document.getElementById('end_datetime');
        const button = document.getElementById('schedule-submit');
        const message = document.getElementById('schedule-message');
        const bookingEnabled = @json((bool) $bookingEnabled);
        let checking = false;
        let inputVersion = 0;

        async function checkTime() {
            if (checking) {
                return false;
            }
            const version = inputVersion;
            button.disabled = true;
            if (!start.value || !end.value || start.value >= end.value) {
                message.textContent = 'เลือกเวลาเริ่มและสิ้นสุดให้ถูกต้อง';
                return false;
            }
            checking = true;
            try {
                const query = new URLSearchParams({
                    tutor_id: @json($filters['tutor_id']),
                    start_datetime: start.value,
                    end_datetime: end.value
                });
                const response = await fetch(@json(route('schedule.availability')) + '?' + query, {
                    headers: { Accept: 'application/json' },
                    cache: 'no-store'
                });
                if (!response.ok) {
                    throw new Error('ไม่สามารถตรวจเวลาได้ กรุณาลองใหม่หรือเข้าสู่ระบบอีกครั้ง');
                }
                const result = await response.json();
                if (version !== inputVersion) {
                    return false;
                }
                message.textContent = result.message;
                button.disabled = !bookingEnabled || !result.available;
                document.getElementById('appointment-date').value = start.value.slice(0, 10);
                document.getElementById('appointment-time').value = start.value.slice(11, 16) + ' - ' + end.value.slice(11, 16);
                return result.available;
            } catch (error) {
                message.textContent = error.message;
                return false;
            } finally {
                checking = false;
                if (version !== inputVersion) {
                    checkTime();
                }
            }
        }

        for (const input of [start, end]) {
            input.addEventListener('input', () => {
                inputVersion++;
                button.disabled = true;
            });
            input.addEventListener('change', checkTime);
        }
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (bookingEnabled && form.reportValidity() && await checkTime()) {
                button.disabled = true;
                form.submit();
            }
        });
        checkTime();
        setInterval(checkTime, 10000);
        window.addEventListener('focus', checkTime);
    </script>
@endsection
