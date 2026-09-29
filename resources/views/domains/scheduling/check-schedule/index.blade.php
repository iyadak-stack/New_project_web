<x-layouts::app :title="__('เช็กเวลาว่างตรงกัน')">
    <div class="mx-auto max-w-4xl space-y-6 p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="section-title">เช็กเวลาว่างตรงกัน</h1>
            <a class="underline" href="{{ route('availabilities.index') }}">จัดการเวลาว่างของฉัน</a>
        </div>

        <form action="{{ route('schedule.check.results') }}" method="POST" class="profile-card space-y-4 p-5">
            @csrf
            <label class="grid gap-1">
                <span>รหัสบัญชีติวเตอร์</span>
                <input class="rounded border px-3 py-2" type="text" name="tutor_id" maxlength="10" value="{{ old('tutor_id', ($filters ?? [])['tutor_id'] ?? '') }}" required>
                @error('tutor_id') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </label>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="grid gap-1">
                    <span>ค้นหาตั้งแต่</span>
                    <input class="rounded border px-3 py-2" type="datetime-local" name="start_datetime" value="{{ old('start_datetime', ($filters ?? [])['start_datetime'] ?? '') }}" required>
                    @error('start_datetime') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                </label>
                <label class="grid gap-1">
                    <span>ถึง</span>
                    <input class="rounded border px-3 py-2" type="datetime-local" name="end_datetime" value="{{ old('end_datetime', ($filters ?? [])['end_datetime'] ?? '') }}" required>
                    @error('end_datetime') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                </label>
            </div>
            <button class="rounded bg-black px-4 py-2 text-white" type="submit">ค้นหาเวลาว่างตรงกัน</button>
        </form>

        @isset($times)
            <section class="space-y-3">
                <h2 class="text-lg font-medium">ช่วงเวลาที่ว่างตรงกัน</h2>
                @forelse ($times as $time)
                    <div class="schedule-placeholder"><p>{{ \Illuminate\Support\Carbon::parse($time['start'])->format('d/m/Y H:i') }} – {{ \Illuminate\Support\Carbon::parse($time['end'])->format('d/m/Y H:i') }}</p></div>
                @empty
                    <div class="schedule-placeholder"><p>ไม่พบช่วงเวลาว่างตรงกันในช่วงที่เลือก</p></div>
                @endforelse
            </section>
        @endisset
    </div>
</x-layouts::app>
