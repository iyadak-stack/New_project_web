<!DOCTYPE html>
<html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Admin - PeerTutor</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <style>body{font-family:'Prompt',sans-serif;background:#f8fafc}.admin-nav{background:#172554}.admin-nav a{color:white;text-decoration:none}</style>
    </head>
    <body>
        <x-admin-navbar />
        <main class="container py-5">
            <h1 class="h3 fw-bold mb-4">แผงควบคุม Admin</h1>
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-secondary">
                                รายงานรอตรวจ
                            </div>
                            <div class="display-6">
                                {{ $pendingReports }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-secondary">
                                รายงานทั้งหมด
                            </div>
                            <div class="display-6">
                                {{ $totalReports }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-secondary">
                                บัญชีผู้ใช้
                            </div>
                            <div class="display-6">
                                {{ $totalUsers }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-secondary">
                                บัญชีปิดใช้งาน
                            </div>
                            <div class="display-6">
                                {{ $inactiveUsers }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a class="btn btn-primary" href="{{ route('admin.reports.index') }}">จัดการรายงาน</a>
                <a class="btn btn-outline-primary" href="{{ route('admin.users.index') }}">จัดการผู้ใช้</a>
            </div>
        </main>
    </body>
</html>
