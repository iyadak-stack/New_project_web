<x-layouts::app :title="__('เวลาว่างของฉัน')">
    <div class="mx-auto max-w-4xl space-y-6 p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="section-title">เวลาว่างของฉัน</h1>
            <div class="flex gap-4">
                <a class="underline" href="{{ route('availabilities.history') }}">ดูประวัติ</a>
                <a class="underline" href="{{ route('schedule.check') }}">เช็กเวลาตรงกัน</a>
            </div>
        </div>

        @if (session('success'))
            <p class="rounded border border-green-300 bg-green-50 p-3 text-green-800">{{ session('success') }}</p>
        @endif

        <form action="{{ route('availabilities.store') }}" method="POST" class="profile-card space-y-4 p-5">
            @csrf
            <h2 class="text-lg font-medium">เพิ่มเวลาว่าง</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="grid gap-1">
                    <span>เริ่ม</span>
                    <input class="rounded border px-3 py-2" type="datetime-local" name="start_datetime" value="{{ old('start_datetime') }}" required>
                    @error('start_datetime') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                </label>
                <label class="grid gap-1">
                    <span>สิ้นสุด</span>
                    <input class="rounded border px-3 py-2" type="datetime-local" name="end_datetime" value="{{ old('end_datetime') }}" required>
                    @error('end_datetime') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                </label>
            </div>
            <button class="rounded bg-black px-4 py-2 text-white" type="submit">เพิ่มเวลาว่าง</button>
        </form>

        <section class="space-y-3">
            <h2 class="text-lg font-medium">เวลาว่างที่บันทึกไว้</h2>
            @forelse ($availabilities as $availability)
                <div class="profile-card flex flex-wrap items-center justify-between gap-3 p-4">
                    <p>{{ $availability->start_datetime->format('d/m/Y H:i') }} – {{ $availability->end_datetime->format('d/m/Y H:i') }}</p>
                    <div class="flex items-center gap-4">
                        <a class="underline" href="{{ route('availabilities.edit', $availability) }}">แก้ไข</a>
                        <form action="{{ route('availabilities.destroy', $availability) }}" method="POST" onsubmit="return confirm('ลบเวลาว่างรายการนี้ไหม?')">
                            @csrf
                            @method('DELETE')
                            <button class="text-red-600 underline" type="submit">ลบ</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="schedule-placeholder"><p>ยังไม่มีเวลาว่างที่บันทึกไว้</p></div>
            @endforelse
        </section>
    </div>
</x-layouts::app>
