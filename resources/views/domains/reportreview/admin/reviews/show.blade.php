<x-layouts::app :title="'Review Detail'">
    <main>
        <h1>รายละเอียดรีวิว</h1>
        <p><a href="{{ route('admin.reviews.index') }}">กลับไปรายการรีวิว</a></p>

        <dl>
            <dt>Review ID</dt>
            <dd>{{ $review->Review_id }}</dd>
            <dt>คะแนน</dt>
            <dd>{{ $review->rating }}/5</dd>
            <dt>ความคิดเห็น</dt>
            <dd>{{ $review->Comment }}</dd>
            <dt>ผู้รีวิว</dt>
            <dd>{{ $review->appointment?->studentProfile?->user?->name ?? 'ไม่พบข้อมูล' }}</dd>
            <dt>Tutor</dt>
            <dd>{{ $review->tutorProfile?->user?->name ?? 'ไม่พบข้อมูล' }}</dd>
            <dt>Appointment</dt>
            <dd>{{ $review->appointment?->Appointment_id ?? 'ไม่พบข้อมูล' }}</dd>
            <dt>Created</dt>
            <dd>{{ $review->created_at?->format('d/m/Y H:i') ?? '—' }}</dd>
            <dt>Updated</dt>
            <dd>{{ $review->updated_at?->format('d/m/Y H:i') ?? '—' }}</dd>
        </dl>

        <h2>รายงานที่เกี่ยวข้อง</h2>
        @forelse ($review->reports as $report)
            <p><a href="{{ route('admin.reports.show', $report) }}">{{ $report->Report_id }} · {{ $report->status }}</a></p>
        @empty
            <p>ยังไม่มีรายงานรีวิวนี้</p>
        @endforelse

        <p><a href="{{ route('reviews.edit', $review) }}">แก้ไขรีวิว</a></p>
        <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}">
            @csrf
            @method('DELETE')
            <button type="submit">ลบรีวิวแบบเก็บประวัติ</button>
        </form>
    </main>
</x-layouts::app>
