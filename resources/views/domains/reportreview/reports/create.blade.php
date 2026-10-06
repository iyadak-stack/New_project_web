<x-layouts::app :title="'ส่งรายงานรีวิว'">
    <main>
        <h1>รายงานรีวิว</h1>
        <p>รายงานรีวิวที่คุณเห็นว่าไม่เหมาะสมให้ผู้ดูแลตรวจสอบ</p>

        <section>
            <h2>รีวิวที่ถูกรายงาน</h2>
            <p>รหัสรีวิว: {{ $review->Review_id }}</p>
            <p>คะแนน: {{ $review->rating }}/5</p>
            <p>ความคิดเห็น: {{ $review->Comment }}</p>
            <p>ผู้รีวิว: {{ $review->appointment?->studentProfile?->user?->name ?? 'ไม่พบข้อมูล' }}</p>
            <p>ติวเตอร์: {{ $review->tutorProfile?->user?->name ?? 'ไม่พบข้อมูล' }}</p>
            <p>นัดหมาย: {{ $review->appointment?->Appointment_id ?? 'ไม่พบข้อมูล' }}</p>
        </section>

        <form method="POST" action="{{ route('reports.store', $review) }}">
            @csrf
            <div>
                <label for="reason">เหตุผล</label>
                <select id="reason" name="ReportReason_Reason_id" required>
                    <option value="">เลือกเหตุผล</option>
                    @foreach ($reasons as $reason)
                        <option value="{{ $reason->Reason_id }}" @selected(old('ReportReason_Reason_id') === $reason->Reason_id)>
                            {{ $reason->reason_name }}
                        </option>
                    @endforeach
                </select>
                @error('ReportReason_Reason_id') <p>{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="description">รายละเอียดเพิ่มเติม (ไม่บังคับ)</label>
                <textarea id="description" name="description" rows="5" maxlength="5000">{{ old('description') }}</textarea>
                @error('description') <p>{{ $message }}</p> @enderror
            </div>
            <button type="submit">ส่งรายงาน</button>
        </form>
        <p><a href="{{ route('reviews.index') }}">กลับไปหน้ารายการรีวิว</a></p>
    </main>
</x-layouts::app>
