<x-layouts::app :title="__('แก้ไขเวลาว่าง')">
    <div class="mx-auto max-w-2xl space-y-6 p-6">
        <div class="flex items-center justify-between gap-3">
            <h1 class="section-title">แก้ไขเวลาว่าง</h1>
            <a class="underline" href="{{ route('availabilities.index') }}">กลับ</a>
        </div>

        <form action="{{ route('availabilities.update', $availability) }}" method="POST" class="profile-card space-y-4 p-5">
            @csrf
            @method('PUT')
            <label class="grid gap-1">
                <span>เริ่ม</span>
                <input class="rounded border px-3 py-2" type="datetime-local" name="start_datetime" value="{{ old('start_datetime', $availability->start_datetime->format('Y-m-d\TH:i')) }}" required>
                @error('start_datetime') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </label>
            <label class="grid gap-1">
                <span>สิ้นสุด</span>
                <input class="rounded border px-3 py-2" type="datetime-local" name="end_datetime" value="{{ old('end_datetime', $availability->end_datetime->format('Y-m-d\TH:i')) }}" required>
                @error('end_datetime') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </label>
            <button class="rounded bg-black px-4 py-2 text-white" type="submit">บันทึกการแก้ไข</button>
        </form>
    </div>
</x-layouts::app>
