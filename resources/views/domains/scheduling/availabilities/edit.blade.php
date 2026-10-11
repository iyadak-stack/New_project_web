<x-tutor-page title="แก้ไขช่วงเวลาสอน">
    <form method="POST" action="{{ route('availabilities.update',$availability) }}">
        @csrf
        @method('PUT')
        <label class="d-block">
            เริ่ม
            <input class="form-control" type="datetime-local" name="start_datetime" value="{{ old('start_datetime',$availability->start_datetime->format('Y-m-d\TH:i')) }}" required>
        </label>
        <label class="d-block">
            สิ้นสุด
            <input class="form-control" type="datetime-local" name="end_datetime" value="{{ old('end_datetime',$availability->end_datetime->format('Y-m-d\TH:i')) }}" required>
        </label>
        <button class="btn btn-primary mt-3">บันทึก</button>
        <a class="btn btn-light" href="{{ route('availabilities.index') }}">กลับ</a>
    </form>
</x-tutor-page>
