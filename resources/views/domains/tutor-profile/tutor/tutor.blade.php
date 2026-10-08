<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าหลักติวเตอร์ - PeerTutor</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts (Kanit) -->
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- External Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/tutor.css') }}">
</head>
<body>

    <!-- Header Navbar -->
    <nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top py-2">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}">
                <span class="navbar-brand-text">PeerTutor</span>
            </a>
            
            <div class="d-flex align-items-center gap-3 ms-auto">
                <!-- ปุ่มสลับบทบาท -->
                <form action="{{ route('role.switch') }}" method="POST" class="m-0">
                    @csrf
                    <input type="hidden" name="role" value="student">
                    <button type="submit" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                        🔄 สลับเป็นนักเรียน
                    </button>
                </form>

                <!-- โปรไฟล์ติวเตอร์ -->
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-warning text-white d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px;">
                        {{ strtoupper(substr(auth()->user()->name ?? 'T', 0, 1)) }}
                    </div>
                    <span class="fw-medium small">{{ auth()->user()->name ?? 'ติวเตอร์' }}</span>
                </div>
            </div>
        </div>
    </nav>

    <!-- เนื้อหาหลัก -->
    <div class="container py-4">
        <!-- Banner ต้อนรับ -->
        <div class="card card-custom p-4 mb-4 bg-banner">
            <h4 class="fw-bold mb-1 text-dark-custom">
                ยินดีต้อนรับ คุณ<span class="text-orange">{{ auth()->user()->first_name ?? auth()->user()->name }}</span> สู่ PeerTutor
            </h4>
            <p class="text-muted small mb-0">จัดการคำขอจอง ตารางสอน จากหน้านี้ พร้อมตัดสินใจตอบรับหรือปฏิเสธคำขอจองได้ทันที</p>
        </div>

        <div class="row g-4">
            <!-- ฝั่งซ้าย: คำขอจอง & ตารางรายการคำขอจอง -->
            <div class="col-lg-8">
                <!-- สรุปการ์ดสถานะ -->
                <div class="card card-custom p-3 mb-4 bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0">คำขอจอง</h6>
                        <span class="badge bg-danger rounded-pill px-3">3 รายการ</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-4">
                            <div class="p-3 rounded-3 text-center bg-stat-box">
                                <small class="text-muted d-block mb-1">รอตรวจสอบ</small>
                                <span class="fs-4 fw-bold text-dark">3</span>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 rounded-3 text-center bg-light">
                                <small class="text-muted d-block mb-1">ตอบรับแล้ว</small>
                                <span class="fs-4 fw-bold text-dark">8</span>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 rounded-3 text-center bg-light">
                                <small class="text-muted d-block mb-1">ปฏิเสธ</small>
                                <span class="fs-4 fw-bold text-dark">2</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ตารางรายการคำขอจอง -->
                <div class="card card-custom p-3 bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <h6 class="fw-bold mb-0">ตารางคำขอจอง</h6>
                        <span class="badge bg-danger-subtle text-danger rounded-pill px-3">ทั้งหมด 3 รายการ</span>
                    </div>
                    <p class="text-muted small mb-3">ตรวจสอบรายละเอียดนักเรียน วันเวลา วิชา และสถานะ เพื่อตัดสินใจตอบรับหรือปฏิเสธ</p>

                    <div class="table-responsive">
                        <table class="table align-middle border-top mb-0">
                            <thead class="text-muted small">
                                <tr>
                                    <th>นักเรียน</th>
                                    <th>วันเวลา</th>
                                    <th>วิชา</th>
                                    <th>สถานะ</th>
                                    <th class="text-center">การตัดสินใจ</th>
                                </tr>
                            </thead>
                            <tbody class="small">
                                <tr>
                                    <td><strong>น้องฟ้า</strong><br><span class="text-muted">ม.4</span></td>
                                    <td><strong>วันจันทร์</strong><br><span class="text-muted">16:00–17:30</span></td>
                                    <td><strong>คณิตศาสตร์</strong><br><span class="text-muted">ออนไลน์</span></td>
                                    <td><span class="badge rounded-pill border border-warning text-warning bg-light px-2 py-1">รอตรวจสอบ</span></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-orange rounded-pill px-3 me-1">ตอบรับ</button>
                                        <button class="btn btn-sm btn-outline-secondary rounded-pill px-3">ปฏิเสธ</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>น้องมิว</strong><br><span class="text-muted">ม.5</span></td>
                                    <td><strong>วันพุธ</strong><br><span class="text-muted">18:00–19:30</span></td>
                                    <td><strong>ฟิสิกส์</strong><br><span class="text-muted">ออนไลน์</span></td>
                                    <td><span class="badge rounded-pill border border-warning text-warning bg-light px-2 py-1">รอตรวจสอบ</span></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-orange rounded-pill px-3 me-1">ตอบรับ</button>
                                        <button class="btn btn-sm btn-outline-secondary rounded-pill px-3">ปฏิเสธ</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>น้องปัน</strong><br><span class="text-muted">ม.6</span></td>
                                    <td><strong>วันศุกร์</strong><br><span class="text-muted">17:00–18:30</span></td>
                                    <td><strong>คอมพิวเตอร์</strong><br><span class="text-muted">ออนไลน์</span></td>
                                    <td><span class="badge rounded-pill border border-warning text-warning bg-light px-2 py-1">รอตรวจสอบ</span></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-orange rounded-pill px-3 me-1">ตอบรับ</button>
                                        <button class="btn btn-sm btn-outline-secondary rounded-pill px-3">ปฏิเสธ</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ฝั่งขวา: ตารางสอนวันนี้ -->
            <div class="col-lg-4">
                <div class="card card-custom p-3 bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold mb-0">ตารางสอนวันนี้</h6>
                        <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1">2 คาบ</span>
                    </div>
                    <p class="text-muted small mb-3">มี 2 คาบ และ 1 คาบรอการยืนยัน</p>

                    <!-- รายการคาบสอน -->
                    <div class="p-3 mb-2 rounded-3 bg-stat-box">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong class="small">คณิตศาสตร์</strong>
                            <span class="badge bg-danger-subtle text-danger rounded-pill x-small">ยืนยันแล้ว</span>
                        </div>
                        <span class="text-muted d-block small mt-1">น้องฟ้า • วันจันทร์ 16:00–17:30</span>
                    </div>

                    <div class="p-3 mb-2 rounded-3 bg-stat-box">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong class="small">ฟิสิกส์</strong>
                            <span class="badge bg-danger-subtle text-danger rounded-pill x-small">ยืนยันแล้ว</span>
                        </div>
                        <span class="text-muted d-block small mt-1">น้องมิว • วันพุธ 18:00–19:30</span>
                    </div>

                    <div class="p-3 mb-3 rounded-3 bg-light border">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong class="small">คอมพิวเตอร์</strong>
                            <span class="badge bg-secondary-subtle text-secondary rounded-pill x-small">รอการยืนยัน</span>
                        </div>
                        <span class="text-muted d-block small mt-1">น้องปัน • วันศุกร์ 17:00–18:30</span>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="#" class="btn btn-outline-orange btn-sm w-100 rounded-pill">ดูรายละเอียด</a>
                        <a href="#" class="btn btn-outline-secondary btn-sm w-100 rounded-pill">แจ้งเตือน</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>