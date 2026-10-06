<x-layouts::app :title="'Report Management'">
    <main>
        <h1>จัดการรายงาน</h1>
        <p>ตรวจสอบรายงานรีวิวและบันทึกผลการดำเนินการ</p>

        <form method="GET" action="{{ route('admin.reports.index') }}">
            <label for="q">ค้นหา Report ID, Review ID, ผู้รายงาน หรือรายละเอียด</label>
            <input id="q" name="q" value="{{ request('q') }}">
            <label for="status">สถานะ</label>
            <select id="status" name="status">
                <option value="">ทุกสถานะ</option>
                @foreach (['pending' => 'รอตรวจสอบ', 'investigating' => 'กำลังตรวจสอบ', 'resolved' => 'ดำเนินการแล้ว', 'rejected' => 'ปฏิเสธรายงาน'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <label for="reason">เหตุผล</label>
            <select id="reason" name="reason">
                <option value="">ทุกเหตุผล</option>
                @foreach ($reasons as $reason)
                    <option value="{{ $reason->Reason_id }}" @selected(request('reason') === $reason->Reason_id)>{{ $reason->reason_name }}</option>
                @endforeach
            </select>
            <button type="submit">ค้นหา</button>
            <a href="{{ route('admin.reports.index') }}">ล้างตัวกรอง</a>
        </form>

        <table>
            <thead><tr><th>Report ID</th><th>Review ID</th><th>Reporter</th><th>Reason</th><th>Status</th><th>วันที่ส่ง</th><th></th></tr></thead>
            <tbody>
                @forelse ($reports as $report)
                    <tr>
                        <td>{{ $report->Report_id }}</td>
                        <td>{{ $report->review_id ?? '—' }}</td>
                        <td>{{ $report->reporter?->name ?? 'ไม่พบข้อมูล' }}</td>
                        <td>{{ $report->reason?->reason_name ?? 'ไม่ระบุ' }}</td>
                        <td>{{ ['pending' => 'รอตรวจสอบ', 'investigating' => 'กำลังตรวจสอบ', 'resolved' => 'ดำเนินการแล้ว', 'rejected' => 'ปฏิเสธรายงาน'][$report->status] ?? $report->status }}</td>
                        <td>{{ $report->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td><a href="{{ route('admin.reports.show', $report) }}">เปิดรายละเอียด</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7">ไม่มีรายงานที่ตรงกับเงื่อนไข</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $reports->links() }}
    </main>
</x-layouts::app>
