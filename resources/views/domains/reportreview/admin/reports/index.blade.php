<!DOCTYPE html>
<html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>รายงาน - Admin</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <style>body{font-family:'Prompt',sans-serif;background:#f8fafc}.admin-nav{background:#172554}.admin-nav a{color:white;text-decoration:none}</style>
    </head>
    <body>
        <x-admin-navbar />
        <main class="container py-5">
            <h1 class="h3 fw-bold mb-4">รายงานจากผู้ใช้</h1>
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            <div class="table-responsive bg-white rounded shadow-sm">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>
                                วันที่
                            </th>
                            <th>
                                เหตุผล
                            </th>
                            <th>
                                ผู้ถูกรายงาน
                            </th>
                            <th>
                                ผู้รายงาน
                            </th>
                            <th>
                                สถานะ
                            </th>
                            <th>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $report)
                            <tr>
                                <td>
                                    {{ $report->created_at?->format('d/m/Y H:i') }}
                                </td>
                                <td>
                                    {{ $report->reason?->reason_name ?? 'ไม่ระบุ' }}
                                </td>
                                <td>
                                    {{ $report->reportedUser?->first_name }} {{ $report->reportedUser?->last_name }}
                                </td>
                                <td>
                                    {{ $report->reporter?->first_name }} {{ $report->reporter?->last_name }}
                                </td>
                                <td>
                                    <span class="badge text-bg-{{ $report->status === 'pending' ? 'warning' : ($report->status === 'resolved' ? 'success' : 'secondary') }}">
                                        {{ $report->status }}
                                    </span>
                                </td>
                                <td>
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.reports.show', $report) }}">ตรวจสอบ</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-5">
                                    ยังไม่มีรายงาน
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $reports->links() }}
            </div>
        </main>
    </body>
</html>
