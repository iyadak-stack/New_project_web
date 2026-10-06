<x-layouts::app :title="__('Dashboard')">
    <div class="mx-auto flex w-full max-w-7xl flex-col gap-6 p-6">
        <header class="rounded-2xl p-6 sm:p-8" style="background:var(--mint-soft); border:1px solid var(--card-border)">
            <p class="text-sm font-semibold uppercase tracking-wide" style="color:var(--mint)">PeerTutor</p>
            <h1 class="mt-2 text-3xl font-bold" style="color:var(--ink)">สวัสดี {{ auth()->user()->first_name }}</h1>
            <p class="mt-2 max-w-2xl" style="color:var(--ink-soft)">
                {{ $isTutor ? 'จัดการตารางสอนและติดตามการนัดหมายของคุณได้ที่นี่' : 'ค้นหาเพื่อนติว นัดหมายการเรียน และติดตามกิจกรรมของคุณได้ที่นี่' }}
            </p>
            <div class="mt-5 flex flex-wrap gap-3">
                @if ($isTutor)
                    <a href="{{ route('availabilities.index') }}" class="rounded-lg px-4 py-2 font-semibold text-white" style="background:var(--accent)">จัดการเวลาว่าง</a>
                    <a href="{{ route('tutor.profile') }}" class="rounded-lg border px-4 py-2 font-semibold" style="border-color:var(--card-border); color:var(--ink)">โปรไฟล์ติวเตอร์</a>
                @else
                    <a href="{{ route('tutor.search') }}" class="rounded-lg px-4 py-2 font-semibold text-white" style="background:var(--accent)">ค้นหาติวเตอร์</a>
                    <a href="{{ route('appointments.index') }}" class="rounded-lg border px-4 py-2 font-semibold" style="border-color:var(--card-border); color:var(--ink)">การนัดหมายของฉัน</a>
                @endif
            </div>
        </header>

        <section class="grid gap-4 sm:grid-cols-3">
            @foreach ([
                ['การนัดหมาย', $appointmentCount, route('appointments.index')],
                ['รีวิว', $reviewCount, route('reviews.index')],
                ['รายงานที่ส่ง', $reportCount, route('reviews.index')],
            ] as [$label, $count, $url])
                <a href="{{ $url }}" class="rounded-xl p-5 transition hover:-translate-y-0.5" style="background:var(--surface); border:1px solid var(--card-border); box-shadow:var(--shadow-sm)">
                    <p class="text-sm" style="color:var(--ink-soft)">{{ $label }}</p>
                    <p class="mt-2 text-3xl font-bold" style="color:var(--ink)">{{ number_format($count) }}</p>
                </a>
            @endforeach
        </section>

        <section class="rounded-xl p-5 sm:p-6" style="background:var(--surface); border:1px solid var(--card-border)">
            <div class="mb-4 flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold" style="color:var(--ink)">นัดหมายล่าสุด</h2>
                    <p class="mt-1 text-sm" style="color:var(--ink-soft)">รายการนัดหมายล่าสุดของคุณ</p>
                </div>
                <a href="{{ route('appointments.index') }}" class="text-sm font-semibold" style="color:var(--accent-dark)">ดูทั้งหมด</a>
            </div>

            @forelse ($appointments as $appointment)
                <article class="flex flex-wrap items-center justify-between gap-3 border-t py-4" style="border-color:var(--card-border)">
                    <div>
                        <p class="font-semibold" style="color:var(--ink)">
                            {{ $isTutor ? ($appointment->studentProfile?->user?->name ?? 'ผู้เรียน') : ($appointment->tutorProfile?->user?->name ?? 'ติวเตอร์') }}
                        </p>
                        <p class="text-sm" style="color:var(--ink-soft)">
                            {{ $appointment->subject?->subject_name ?? 'นัดหมายติว' }}
                            @if ($appointment->start_datetime) · {{ $appointment->start_datetime->format('d/m/Y H:i') }} @endif
                        </p>
                    </div>
                    <span class="rounded-full px-3 py-1 text-sm" style="background:var(--accent-soft); color:var(--accent-dark)">{{ $appointment->status }}</span>
                </article>
            @empty
                <div class="rounded-lg border border-dashed p-6 text-center" style="border-color:var(--card-border)">
                    <p class="font-medium" style="color:var(--ink)">ยังไม่มีนัดหมาย</p>
                    <p class="mt-1 text-sm" style="color:var(--ink-soft)">
                        {{ $isTutor ? 'เพิ่มเวลาว่างเพื่อให้ผู้เรียนค้นหาและนัดหมายกับคุณ' : 'เริ่มจากค้นหาติวเตอร์ที่ตรงกับวิชาที่คุณสนใจ' }}
                    </p>
                </div>
            @endforelse
        </section>
    </div>
</x-layouts::app>
