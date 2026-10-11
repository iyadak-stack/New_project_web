<!DOCTYPE html>
<html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>ตรวจสอบติวเตอร์</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
        <style>.admin-nav{background:#172554}.admin-nav a{color:white;text-decoration:none}</style>
    </head>
    <body>
        <x-admin-navbar />
        <main class="container py-4">
            <h1 class="h3">ตรวจสอบใบสมัครติวเตอร์</h1>
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif
            @forelse($applications as $profile)
                <article class="card p-4 my-3">
                    <h2 class="h5">{{ $profile->user?->first_name }} {{ $profile->user?->last_name }} · {{ $profile->user?->email }}</h2>
                    <p>สถานะ: {{ ['pending'=>'รอตรวจสอบ','approved'=>'อนุมัติแล้ว','rejected'=>'ไม่ผ่าน'][$profile->approval_status] ?? $profile->approval_status }}</p>
                    @if($profile->user?->profile_picture)
                        <img src="{{ asset('storage/'.$profile->user->profile_picture) }}" alt="รูปผู้สมัคร" width="100">
                    @endif
                    <p class="mt-2" style="white-space:pre-wrap">{{ $profile->bio }}</p>
                    <p>ประสบการณ์ {{ $profile->experience_years }} ปี · {{ $profile->teaching_mode }}</p>
                    <p>วิชา: {{ $profile->subjects->pluck('subject_name')->join(', ') }}</p>
                    <p>LINE: {{ $profile->user?->contact?->line_id ?: '-' }} · Discord: {{ $profile->user?->contact?->discord_id ?: '-' }}</p>
                    <p>
                        Zoom: {{ $profile->user?->contact?->zoom_link ?: '-' }}
                        <br>
                        Meet: {{ $profile->user?->contact?->google_meet_link ?: '-' }}
                        <br>
                        สถานที่: {{ $profile->user?->contact?->meeting_location ?: '-' }}
                    </p>
                    @if($profile->reject_reason)
                        <p>เหตุผล: {{ $profile->reject_reason }}</p>
                    @endif
                    @if($profile->approval_status === 'pending')
                        <form method="POST" action="{{ route('admin.tutors.update',$profile) }}">
                            @csrf
                            @method('PATCH')
                            <label class="form-label">
                                เหตุผลกรณีไม่อนุมัติ
                                <textarea class="form-control" name="reject_reason" maxlength="2000"></textarea>
                            </label>
                            <div class="d-flex gap-2">
                                <button class="btn btn-success" name="decision" value="approved" @disabled(!$profile->isComplete())>อนุมัติ</button>
                                <button class="btn btn-outline-danger" name="decision" value="rejected">ไม่อนุมัติ</button>
                            </div>
                        </form>
                    @endif
                </article>
            @empty
                <p>ยังไม่มีใบสมัคร</p>
            @endforelse
            {{ $applications->links() }}
        </main>
    </body>
</html>
