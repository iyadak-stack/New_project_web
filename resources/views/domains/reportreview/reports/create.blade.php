<!DOCTYPE html>
<html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>รายงานติวเตอร์ - PeerTutor</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <style>body{font-family:'Prompt',sans-serif;background:#f8fafc}</style>
    </head>
    <body>
        <main class="container py-5" style="max-width:760px">
            <a href="{{ route('tutor.show', $tutor) }}" class="text-decoration-none">← กลับไปยังโปรไฟล์ติวเตอร์</a>
            <div class="card shadow-sm border-0 mt-3">
                <div class="card-body p-4 p-md-5">
                    <h1 class="h3 fw-bold">รายงานติวเตอร์</h1>
                    <p class="text-secondary">รายงานโปรไฟล์ของ {{ $tutor->user?->first_name }} {{ $tutor->user?->last_name }} ให้ทีมงานตรวจสอบ</p>
                    <form action="{{ route('reports.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="tutor_id" value="{{ $tutor->tutor_id }}">
                        <div class="mb-3">
                            <label for="reason_id" class="form-label">
                                เหตุผลที่รายงาน
                            </label>
                            <select id="reason_id" name="reason_id" class="form-select" required>
                                <option value="" disabled selected>เลือกเหตุผล</option>
                                @foreach($reasons as $reason)
                                    <option value="{{ $reason->Reason_id }}" @selected(old('reason_id') === $reason->Reason_id)>{{ $reason->reason_name }}</option>
                                @endforeach
                            </select>
                            @error('reason_id')
                            <div class="text-danger small mt-1">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label for="description" class="form-label">
                                รายละเอียด
                            </label>
                            <textarea id="description" name="description" class="form-control" rows="5" minlength="10" maxlength="3000" required>{{ old('description') }}</textarea>
                            @error('description')
                            <div class="text-danger small mt-1">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        @error('tutor_id')
                        <div class="alert alert-danger">
                            {{ $message }}
                        </div>
                        @enderror
                        <button class="btn btn-danger" type="submit">ส่งรายงาน</button>
                        <a class="btn btn-outline-secondary" href="{{ route('tutor.show', $tutor) }}">ยกเลิก</a>
                    </form>
                </div>
            </div>
        </main>
    </body>
</html>
