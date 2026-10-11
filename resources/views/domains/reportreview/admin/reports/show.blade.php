<!DOCTYPE html>
<html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>ตรวจสอบรายงาน - Admin</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <style>body{font-family:'Prompt',sans-serif;background:#f8fafc}.admin-nav{background:#172554}.admin-nav a{color:white;text-decoration:none}</style>
    </head>
    <body>
        <x-admin-navbar />
        <main class="container py-5" style="max-width:850px">
            <a href="{{ route('admin.reports.index') }}">← กลับรายการรายงาน</a>
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body p-4">
                    <h1 class="h3 fw-bold">รายงาน {{ $report->Report_id }}</h1>
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    <dl class="row mt-4">
                        <dt class="col-sm-4">เหตุผล</dt>
                        <dd class="col-sm-8">{{ $report->reason?->reason_name ?? 'ไม่ระบุ' }}</dd>
                        <dt class="col-sm-4">ผู้ถูกรายงาน</dt>
                        <dd class="col-sm-8">{{ $report->reportedUser?->first_name }} {{ $report->reportedUser?->last_name }} ({{ $report->reportedUser?->email }})</dd>
                        <dt class="col-sm-4">ผู้รายงาน</dt>
                        <dd class="col-sm-8">{{ $report->reporter?->first_name }} {{ $report->reporter?->last_name }} ({{ $report->reporter?->email }})</dd>
                        <dt class="col-sm-4">วันที่ส่ง</dt>
                        <dd class="col-sm-8">{{ $report->created_at?->format('d/m/Y H:i') }}</dd>
                        <dt class="col-sm-4">รายละเอียด</dt>
                        <dd class="col-sm-8" style="white-space:pre-wrap">{{ $report->description }}</dd>
                    </dl>
                    <form action="{{ route('admin.reports.update', $report) }}" method="POST" class="row g-2 align-items-end">
                        @csrf
                        @method('PATCH')
                        <div class="col-sm-5">
                            <label class="form-label" for="status">
                                เปลี่ยนสถานะ
                            </label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="pending" @selected($report->status === 'pending')>รอตรวจสอบ</option>
                                <option value="investigating" @selected($report->status === 'investigating')>กำลังตรวจสอบ</option>
                                <option value="resolved" @selected($report->status === 'resolved')>ดำเนินการแล้ว</option>
                                <option value="rejected" @selected($report->status === 'rejected')>ไม่พบการกระทำผิด</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-primary">บันทึกสถานะ</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </body>
</html>
