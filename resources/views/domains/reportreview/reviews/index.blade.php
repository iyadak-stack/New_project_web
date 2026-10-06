<x-layouts::app :title="'Reviews'">
    <main>
        <h1>รีวิวทั้งหมด</h1>

        @if (session('success'))
            <p>{{ session('success') }}</p>
        @endif

        <p><a href="{{ route('reviews.create') }}">เขียนรีวิวจากนัดหมายของฉัน</a></p>

        @forelse ($reviews as $review)
            <article>
                <h2>รีวิว {{ $review->Review_id }} · {{ $review->rating }}/5</h2>
                <p>{{ $review->Comment }}</p>
                <dl>
                    <dt>ผู้รีวิว</dt>
                    <dd>{{ $review->appointment?->studentProfile?->user?->name ?? 'ไม่พบข้อมูล' }}</dd>
                    <dt>ติวเตอร์</dt>
                    <dd>{{ $review->tutorProfile?->user?->name ?? 'ไม่พบข้อมูล' }}</dd>
                    <dt>นัดหมาย</dt>
                    <dd>{{ $review->appointment?->Appointment_id ?? 'ไม่พบข้อมูล' }}</dd>
                    <dt>วันที่สร้าง</dt>
                    <dd>{{ $review->created_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                </dl>

                @can('update', $review)
                    <a href="{{ route('reviews.edit', $review) }}">แก้ไขรีวิว</a>
                @endcan

                @can('delete', $review)
                    <form action="{{ route('reviews.destroy', $review) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit">ลบรีวิว</button>
                    </form>
                @endcan

                @if (auth()->user()->role !== 'admin' && $review->appointment?->studentProfile?->user_id !== auth()->user()->user_id)
                    <a href="{{ route('reports.create', $review) }}">รายงานรีวิวนี้</a>
                @endif
            </article>
        @empty
            <p>ยังไม่มีรีวิว</p>
        @endforelse

        {{ $reviews->links() }}
    </main>
</x-layouts::app>
