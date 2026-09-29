<x-layouts::app :title="__('ประวัติเวลาว่าง')">
    <div class="mx-auto max-w-4xl space-y-6 p-6">
        <div class="flex items-center justify-between gap-3">
            <h1 class="section-title">ประวัติเวลาว่าง</h1>
            <a class="underline" href="{{ route('availabilities.index') }}">กลับไปจัดการเวลาว่าง</a>
        </div>

        <div class="space-y-3">
            @forelse ($availabilities as $availability)
                <div class="profile-card p-4">
                    <p class="font-medium">{{ $availability->start_datetime->format('d/m/Y H:i') }} – {{ $availability->end_datetime->format('d/m/Y H:i') }}</p>
                    <p class="mt-2 text-sm text-neutral-600">สร้างเมื่อ {{ $availability->created_at->format('d/m/Y H:i') }}</p>
                    <p class="text-sm text-neutral-600">แก้ไขล่าสุด {{ $availability->updated_at->format('d/m/Y H:i') }}</p>
                </div>
            @empty
                <div class="schedule-placeholder"><p>ยังไม่มีรายการในประวัติ</p></div>
            @endforelse
        </div>
    </div>
</x-layouts::app>
