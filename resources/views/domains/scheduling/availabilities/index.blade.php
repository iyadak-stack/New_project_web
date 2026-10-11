<x-tutor-page title="จัดการวันเวลาว่าง">
    <a href="{{ route('tutor.profile') }}">กลับหน้าติวเตอร์</a>
    <form class="card p-3 my-3" method="POST" action="{{ route('availabilities.store') }}">
        @csrf
        <h2 class="h5">เพิ่มช่วงเวลาสอน</h2>
        <label>
            เริ่ม
            <input class="form-control" type="datetime-local" name="start_datetime" value="{{ old('start_datetime') }}" required>
        </label>
        <label>
            สิ้นสุด
            <input class="form-control" type="datetime-local" name="end_datetime" value="{{ old('end_datetime') }}" required>
        </label>
        <button class="btn btn-primary mt-3">เพิ่มเวลาสอน</button>
    </form>
    @forelse($availabilities as $slot)
        <div class="card p-3 mb-2">
            <p>{{ $slot->start_datetime->format('d/m/Y H:i') }}–{{ $slot->end_datetime->format('d/m/Y H:i') }}</p>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-primary" href="{{ route('availabilities.edit',$slot) }}">แก้ไข</a>
                <form method="POST" action="{{ route('availabilities.destroy',$slot) }}">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger">ลบ</button>
                </form>
            </div>
        </div>
    @empty
        <p>ยังไม่มีเวลาสอน</p>
    @endforelse
</x-tutor-page>
