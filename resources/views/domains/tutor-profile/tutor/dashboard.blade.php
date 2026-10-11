<x-tutor-page title="หน้าหลักติวเตอร์">
    <div class="alert alert-success">
        โปรไฟล์ได้รับการอนุมัติแล้ว
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card p-3">
                คำขอรอตอบรับ
                <strong class="fs-3">{{ $appointments->where('status','pending')->count() }}</strong>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3">
                นัดหมายที่ยืนยันแล้ว
                <strong class="fs-3">{{ $appointments->where('status','confirmed')->count() }}</strong>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3">
                คะแนนเฉลี่ย
                <strong class="fs-3">{{ number_format($reviews->avg('rating') ?? 0,1) }}/5</strong>
            </div>
        </div>
    </div>
    <h2 class="h4">คำขอและนัดหมายที่ต้องดูแล</h2>
    @forelse($appointments as $appointment)
        <div class="card p-3 mb-2">
            <div>
                {{ $appointment->subject?->subject_name }} · ผู้เรียน {{ $appointment->studentUser?->first_name ?? $appointment->studentProfile?->user?->first_name }} · {{ $appointment->start_datetime?->format('d/m/Y H:i') }}–{{ $appointment->end_datetime?->format('H:i') }}
            </div>
            <div>
                {{ $appointment->status === 'pending' ? 'รอตอบรับ' : 'ยืนยันแล้ว' }}
            </div>
            <a href="{{ route('appointments.show',$appointment) }}">ดูรายละเอียด / จัดการ</a>
        </div>
    @empty
        <p>ยังไม่มีคำขอหรือนัดหมาย</p>
    @endforelse
    <h2 class="h4 mt-4">วันเวลาที่เปิดสอน</h2>
    @forelse($availabilities as $slot)
        <p>{{ $slot->start_datetime->format('d/m/Y H:i') }}–{{ $slot->end_datetime->format('H:i') }}</p>
    @empty
        <p>ยังไม่มีเวลาสอน กรุณาเพิ่มวันเวลาว่างเพื่อให้ผู้เรียนจอง</p>
    @endforelse
    <h2 id="reviews" class="h4 mt-4">รีวิวจากผู้เรียน</h2>
    @forelse($reviews as $review)
        <article class="card p-3 mb-2">
            <strong>{{ $review->rating }}/5</strong>
            <p>{{ $review->Comment }}</p>
            <small>{{ $review->created_at?->format('d/m/Y') }}</small>
        </article>
    @empty
        <p>ยังไม่มีรีวิว</p>
    @endforelse
</x-tutor-page>
