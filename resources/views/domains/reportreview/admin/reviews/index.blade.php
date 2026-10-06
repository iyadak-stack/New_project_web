<x-layouts::app :title="'Review Management'">
    <main>
        <h1>จัดการรีวิว</h1>
        <p>ค้นหา ตรวจสอบรายละเอียด และจัดการรีวิวทั้งหมดในระบบ</p>

        <form method="GET" action="{{ route('admin.reviews.index') }}">
            <label for="q">ค้นหา ID, ความคิดเห็น, ผู้รีวิว หรือติวเตอร์</label>
            <input id="q" name="q" value="{{ request('q') }}">

            <label for="rating">คะแนน</label>
            <select id="rating" name="rating">
                <option value="">ทุกคะแนน</option>
                @for ($rating = 1; $rating <= 5; $rating++)
                    <option value="{{ $rating }}" @selected(request('rating') == $rating)>{{ $rating }}</option>
                @endfor
            </select>

            <label for="from">ตั้งแต่วันที่</label>
            <input id="from" name="from" type="date" value="{{ request('from') }}">
            <label for="to">ถึงวันที่</label>
            <input id="to" name="to" type="date" value="{{ request('to') }}">
            <button type="submit">ค้นหา</button>
            <a href="{{ route('admin.reviews.index') }}">ล้างตัวกรอง</a>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Review ID</th>
                    <th>คะแนน/ความคิดเห็น</th>
                    <th>ผู้รีวิว</th>
                    <th>ติวเตอร์</th>
                    <th>Appointment</th>
                    <th>วันที่สร้าง</th>
                    <th>รายงาน</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reviews as $review)
                    <tr>
                        <td>{{ $review->Review_id }}</td>
                        <td>{{ $review->rating }}/5 — {{ $review->Comment }}</td>
                        <td>{{ $review->appointment?->studentProfile?->user?->name ?? 'ไม่พบข้อมูล' }}</td>
                        <td>{{ $review->tutorProfile?->user?->name ?? 'ไม่พบข้อมูล' }}</td>
                        <td>{{ $review->appointment?->Appointment_id ?? 'ไม่พบข้อมูล' }}</td>
                        <td>{{ $review->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td>{{ $review->reports->count() }}</td>
                        <td>
                            <a href="{{ route('admin.reviews.show', $review) }}">รายละเอียด</a>
                            <a href="{{ route('reviews.edit', $review) }}">แก้ไข</a>
                            <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit">ลบแบบเก็บประวัติ</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8">ไม่พบรีวิว</td></tr>
                @endforelse
            </tbody>
        </table>

        {{ $reviews->links() }}
    </main>
</x-layouts::app>
