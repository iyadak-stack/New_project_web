<x-layouts::app :title="'Report Detail'">
    <main>
        <h1>รายละเอียดรายงาน</h1>
        <p><a href="{{ route('admin.reports.index') }}">กลับไปรายการรายงาน</a></p>
        @if (session('success')) <p>{{ session('success') }}</p> @endif
        @if ($errors->any())
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        @endif

        <section>
            <h2>ข้อมูลรายงาน</h2>
            <dl>
                <dt>Report ID</dt><dd>{{ $report->Report_id }}</dd>
                <dt>สถานะ</dt><dd>{{ $report->status }}</dd>
                <dt>ผู้รายงาน</dt><dd>{{ $report->reporter?->name ?? 'ไม่พบข้อมูล' }}</dd>
                <dt>เหตุผล</dt><dd>{{ $report->reason?->reason_name ?? 'ไม่ระบุ' }}</dd>
                <dt>รายละเอียด</dt><dd>{{ $report->description ?: 'ไม่มีรายละเอียดเพิ่มเติม' }}</dd>
                <dt>วันที่ส่ง</dt><dd>{{ $report->created_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                <dt>ผู้รับเรื่องล่าสุด</dt><dd>{{ $report->handler?->name ?? '—' }}</dd>
                <dt>วันที่รับเรื่อง</dt><dd>{{ $report->handled_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                <dt>ผู้ปิดรายงาน</dt><dd>{{ $report->resolver?->name ?? '—' }}</dd>
                <dt>วันที่ปิดรายงาน</dt><dd>{{ $report->resolved_at?->format('d/m/Y H:i') ?? '—' }}</dd>
            </dl>
        </section>

        <section>
            <h2>รีวิวที่ถูกรายงาน</h2>
            @if ($report->review)
                <p>Review ID: {{ $report->review->Review_id }}</p>
                <p>Rating: {{ $report->review->rating }}/5</p>
                <p>Comment: {{ $report->review->Comment }}</p>
                <p>Reviewer: {{ $report->review->appointment?->studentProfile?->user?->name ?? 'ไม่พบข้อมูล' }}</p>
                <p>Tutor: {{ $report->review->tutorProfile?->user?->name ?? 'ไม่พบข้อมูล' }}</p>
                <p>Appointment: {{ $report->review->appointment?->Appointment_id ?? 'ไม่พบข้อมูล' }}</p>
                <p>วันที่รีวิว: {{ $report->review->created_at?->format('d/m/Y H:i') ?? '—' }}</p>
                <p>สถานะรีวิว: {{ $report->review->trashed() ? 'ถูกลบแบบเก็บประวัติแล้ว' : 'ยังแสดงอยู่' }}</p>
            @else
                <p>รายงานเก่านี้ยังไม่มี Review ID เชื่อมโยง</p>
            @endif
        </section>

        @if ($report->status === 'pending')
            <form method="POST" action="{{ route('admin.reports.investigate', $report) }}">
                @csrf @method('PATCH')
                <button type="submit">รับเรื่องเพื่อตรวจสอบ</button>
            </form>
        @endif

        @if (in_array($report->status, ['pending', 'investigating'], true))
            <form method="POST" action="{{ route('admin.reports.resolve', $report) }}">
                @csrf @method('PATCH')
                <label><input type="checkbox" name="delete_review" value="1"> ลบรีวิวแบบ Soft Delete เมื่อปิดรายงาน</label>
                <button type="submit">ดำเนินการและปิดรายงาน</button>
            </form>
            <form method="POST" action="{{ route('admin.reports.reject', $report) }}">
                @csrf @method('PATCH')
                <button type="submit">ปฏิเสธรายงานและคงรีวิวไว้</button>
            </form>
        @endif
    </main>
</x-layouts::app>
