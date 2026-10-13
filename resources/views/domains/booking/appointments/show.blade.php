<!DOCTYPE html>
<html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>รายละเอียดนัดหมาย</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <style>body{font-family:'Prompt',sans-serif;background:#f8fafc}</style>
        {{-- ปกป้องแก้ให้: path เดิม css/home.css ต้องเป็น CSS/home.css ให้ตรงกับโฟลเดอร์บน Linux --}}
        <link rel="stylesheet" href="{{ asset('CSS/home.css') }}">
        {{-- ปกป้องแก้ให้: path เดิม css/profile-card.css ต้องเป็น CSS/profile-card.css ให้ตรงกับโฟลเดอร์บน Linux --}}
        <link rel="stylesheet" href="{{ asset('CSS/profile-card.css') }}">
        <link rel="stylesheet" href="{{ asset('CSS/edit-profile.css') }}">
    </head>
    <body>
        <x-home-navbar />
        <main class="container py-5" style="max-width:850px">
            {{-- โค้ดเดิมของส่วน Appointment (ทับทิม): ลิงก์ใช้สีเริ่มต้นของ Bootstrap
            <a href="{{ route('appointments.index') }}" class="text-decoration-none">← กลับนัดหมายของฉัน</a>
            --}}
            <a href="{{ route('appointments.index') }}" class="pe-cancel">← กลับนัดหมายของฉัน</a>
            <article class="card border-0 shadow-sm mt-3">
                <div class="card-body p-4 p-md-5">
                    <h1 class="h3 fw-bold">รายละเอียดนัดหมาย</h1>
                    <p class="text-secondary">รหัส {{ $appointment->Appointment_id }}</p>
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="ปิด"></button>
                        </div>
                    @endif
                    @if($isStudentBooking && $appointment->status === 'pending')
                        <div class="alert alert-warning">
                            ส่งคำขอแล้ว กรุณารอติวเตอร์ยืนยันก่อนเข้าเรียนและยืนยัน session
                        </div>
                    @endif
                    <dl class="row mt-4">
                        <dt class="col-sm-4">วิชา</dt>
                        <dd class="col-sm-8">{{ $appointment->subject?->subject_name ?? 'ไม่ระบุ' }}</dd>
                        <dt class="col-sm-4">ติวเตอร์</dt>
                        <dd class="col-sm-8">{{ $appointment->tutorProfile?->user?->first_name }} {{ $appointment->tutorProfile?->user?->last_name }}</dd>
                        <dt class="col-sm-4">วัน</dt>
                        <dd class="col-sm-8">{{ $appointment->start_datetime?->locale('th')->translatedFormat('lที่ j F Y') ?? 'ไม่ระบุ' }}</dd>
                        <dt class="col-sm-4">เวลา</dt>
                        <dd class="col-sm-8">{{ $appointment->start_datetime?->format('H:i') ?? '--:--' }}–{{ $appointment->end_datetime?->format('H:i') ?? '--:--' }} น.</dd>
                        <dt class="col-sm-4">รูปแบบ</dt>
                        <dd class="col-sm-8">{{ $appointment->tutorProfile?->teaching_mode ?? 'ตามที่ตกลงกัน' }}</dd>
                        <dt class="col-sm-4">สถานะ</dt>
                        <dd class="col-sm-8">{{ ['pending' => 'รอติวเตอร์ยืนยัน', 'confirmed' => 'ยืนยันแล้ว', 'completed' => 'เรียนเสร็จแล้ว', 'cancelled' => 'ยกเลิกแล้ว'][$appointment->status] ?? $appointment->status }}</dd>
                        <dt class="col-sm-4">สร้างเมื่อ</dt>
                        <dd class="col-sm-8">{{ $appointment->created_at?->format('d/m/Y H:i') }}</dd>
                    </dl>
                    @if(in_array($appointment->status,['confirmed','completed'],true))
                        <x-lesson-details :appointment="$appointment" />
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger">
                            {{ $errors->first() }}
                        </div>
                    @endif
                    @if($appointment->rejection_reason)
                        <p class="alert alert-warning">เหตุผลที่ปฏิเสธ: {{ $appointment->rejection_reason }}</p>
                    @endif
                    @if($isTutorBooking && auth()->user()->current_role === 'tutor' && $appointment->tutorProfile->approval_status === 'approved' && in_array($appointment->status,['pending','confirmed'],true))

                        <form class="card p-3 mb-4" method="POST" action="{{ route('appointments.lesson',$appointment) }}">
                            @csrf
                            @method('PATCH')
                            <h2 class="h5">ห้องเรียนสำหรับนัดหมายนี้</h2>
                            <p>เริ่มต้นจากข้อมูลในโปรไฟล์ แก้ไขได้เฉพาะนัดหมายนี้</p>
                            @foreach(['meeting_zoom'=>'Zoom','meeting_meet'=>'Google Meet','meeting_location'=>'สถานที่เรียน'] as $field=>$label)

                                <label class="form-label">
                                    {{ $label }}
                                    <input class="form-control" type="{{ $field === 'meeting_location' ? 'text' : 'url' }}" name="{{ $field }}" value="{{ old($field, $appointment->lessonValue($field)) }}">
                                    <button type="button" class="btn btn-sm btn-outline-secondary mt-1" onclick="this.previousElementSibling.value=''">ล้างช่องนี้</button>
                                </label>
                            @endforeach
                            <label class="form-label">
                                รายละเอียดเพิ่มเติม
                                <textarea class="form-control" name="lesson_notes" maxlength="3000">{{ old('lesson_notes',$appointment->lesson_notes) }}</textarea>
                            </label>
                            <p class="small text-secondary">ล้างช่องที่ต้องการลบแล้วกดบันทึก การเปลี่ยนแปลงมีผลเฉพาะนัดหมายนี้</p>
                            <div class="d-flex gap-2 flex-wrap">
                                {{-- โค้ดเดิมของส่วน Appointment (ทับทิม): ปุ่ม Bootstrap
                                <button class="btn btn-primary">บันทึกรายละเอียดการเรียน</button>
                                --}}
                                {{-- ปกป้องปรับเฉพาะคลาสปุ่มให้ใช้ CSS เดิมของเพื่อน ไม่เปลี่ยนการทำงาน --}}
                                <button class="pe-save">บันทึกรายละเอียดการเรียน</button>
                                <button class="btn btn-outline-secondary" name="use_profile_defaults" value="1" formnovalidate>ใช้ข้อมูลจากโปรไฟล์อีกครั้ง</button>
                            </div>
                        </form>
                    @endif
                    <div class="d-flex flex-wrap align-items-start gap-2">
                        @if($isTutorBooking && $appointment->status === 'pending' && auth()->user()->current_role === 'tutor' && $appointment->tutorProfile->approval_status === 'approved')
                            <form method="POST" action="{{ url('/appointments/'.$appointment->Appointment_id.'/status') }}">
                                @csrf
                                <input type="hidden" name="status" value="confirmed">
                                <button class="btn btn-success">รับคำขอจอง</button>
                            </form>
                            <form method="POST" action="{{ url('/appointments/'.$appointment->Appointment_id.'/status') }}">
                                @csrf
                                <input type="hidden" name="status" value="cancelled">
                                <input class="form-control mb-2" name="rejection_reason" placeholder="เหตุผลที่ปฏิเสธ" required maxlength="2000">
                                <button class="btn btn-outline-danger">ปฏิเสธคำขอ</button>
                            </form>
                        @endif
                        @if($isStudentBooking && $appointment->canComplete())
                            <form method="POST" action="{{ route('appointments.complete', $appointment) }}">
                                @csrf
                                {{-- โค้ดเดิมของส่วน Appointment (ทับทิม): ปุ่ม Bootstrap
                                <button class="btn btn-primary">ยืนยันว่าเรียนเสร็จแล้ว</button>
                                --}}
                                {{-- ปกป้องปรับเฉพาะคลาสปุ่มให้ใช้ CSS เดิมของเพื่อน ไม่เปลี่ยนการทำงาน --}}
                                <button class="pe-save">ยืนยันว่าเรียนเสร็จแล้ว</button>
                            </form>
                        @endif
                        @if($isStudentBooking && $appointment->status === 'completed' && !$appointment->review)
                            <a class="btn btn-warning" href="{{ route('appointments.index') }}#review-{{ $appointment->Appointment_id }}">
                                เขียนรีวิว
                            </a>
                        @endif
                        @if($appointment->tutorProfile)
                            {{-- โค้ดเดิมของส่วน Appointment (ทับทิม): ปุ่ม Bootstrap
                            <a href="{{ route('tutor.show', $appointment->tutorProfile) }}" class="btn btn-outline-primary">
                                ดูโปรไฟล์ติวเตอร์
                            </a>
                            --}}
                            {{-- ปกป้องปรับเฉพาะคลาสปุ่มให้ใช้ CSS เดิมของเพื่อน ไม่เปลี่ยนการทำงาน --}}
                            <a href="{{ route('tutor.show', $appointment->tutorProfile) }}" class="home-card-button">
                                ดูโปรไฟล์ติวเตอร์
                            </a>
                        @endif
                    </div>
                </div>
            </article>
        </main>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
