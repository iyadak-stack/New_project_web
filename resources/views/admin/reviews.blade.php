@extends('admin.layout')
@section('title', 'ตรวจสอบรีวิว')
@section('description', 'ดูความคิดเห็นจากผู้เรียน และจัดการรีวิวที่ไม่เหมาะสม')
@section('content')
    {{-- ปุ่มนำรีวิวออกใช้ soft delete จึงยังเปิดรีวิวเดิมกลับมาได้ --}}
    <form class="admin-filters" method="GET">
        <label>ค้นหาติวเตอร์<input name="search" value="{{ request('search') }}" placeholder="ชื่อหรือนามสกุลติวเตอร์">
        </label>
        <label>คะแนน<select name="rating">
                <option value="">ทุกคะแนน</option>
                @for ($rating = 1; $rating <= 5; $rating++)
                    <option value="{{ $rating }}" @selected(request('rating') == (string) $rating)>{{ $rating }} ดาว</option>
                @endfor
            </select>
        </label>
        <label>การแสดงผล<select name="visibility">
                <option value="">ทั้งหมด</option>
                <option value="visible" @selected(request('visibility') === 'visible')>แสดงอยู่</option>
                <option value="hidden" @selected(request('visibility') === 'hidden')>นำออกแล้ว</option>
            </select>
        </label>
        <button class="admin-button" type="submit">ค้นหา</button>
        <a class="admin-reset" href="{{ route('admin.reviews.index') }}">ล้างตัวกรอง</a>
    </form>
    <div class="admin-section-heading">
        <h2>รีวิว <span>{{ $reviews->total() }} รายการ</span>
        </h2>
    </div>
    @forelse ($reviews as $review)
        <article class="admin-panel">
            <div class="admin-section-heading">
                <h2>{{ $review->tutorProfile?->user?->first_name }} {{ $review->tutorProfile?->user?->last_name }} <span
                        class="admin-rating">★ {{ $review->rating }} / 5</span>
                </h2>
                <span
                    class="admin-badge {{ $review->trashed() ? 'red' : 'green' }}">{{ $review->trashed() ? 'นำออกจากการแสดงผลแล้ว' : 'แสดงอยู่' }}</span>
            </div>
            <p class="admin-muted">ผู้รีวิว:
                {{ $review->appointment?->studentProfile?->user?->first_name ?? 'ไม่พบผู้เรียน' }} ·
                {{ $review->created_at?->timezone('Asia/Bangkok')->format('d/m/Y H:i') }}</p>
            <div class="admin-description">{{ $review->Comment }}</div>
            <div class="admin-report-footer">
                <span class="admin-muted">รหัสรีวิว {{ $review->Review_id }}</span>
                <form method="POST" action="{{ route('admin.reviews.visibility', $review->Review_id) }}"
                    onsubmit="return confirm('ยืนยันเปลี่ยนการแสดงผลรีวิวนี้ใช่ไหม?')">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="visible" value="{{ $review->trashed() ? '1' : '0' }}">
                    <button class="admin-button {{ $review->trashed() ? 'secondary' : 'danger' }}"
                        type="submit">{{ $review->trashed() ? 'แสดงรีวิวอีกครั้ง' : 'นำรีวิวออก' }}</button>
                </form>
            </div>
        </article>
    @empty
        <div class="admin-panel admin-empty">ไม่พบรีวิวตามเงื่อนไข</div>
    @endforelse
    @include('admin.partials.pagination', ['items' => $reviews])
@endsection
