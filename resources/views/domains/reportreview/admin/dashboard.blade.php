<x-layouts::app :title="'Admin Dashboard'">
    <main>
        <h1>Admin Dashboard</h1>
        <p>ภาพรวมการดูแลรีวิวและรายงานในระบบ</p>
        <section>
            <h2>สรุป</h2>
            <dl>
                <dt>รีวิวทั้งหมด</dt><dd>{{ $reviewCount }}</dd>
                <dt>รายงานทั้งหมด</dt><dd>{{ $reportCount }}</dd>
                <dt>รายงานที่รอตรวจสอบ</dt><dd>{{ $pendingReportCount }}</dd>
            </dl>
        </section>
        <section>
            <h2>รายงานล่าสุด</h2>
            <p><a href="{{ route('admin.reports.index') }}">ดูรายงานทั้งหมด</a></p>
            @forelse ($recentReports as $report)
                <article>
                    <h3><a href="{{ route('admin.reports.show', $report) }}">{{ $report->Report_id }} · {{ $report->status }}</a></h3>
                    <p>{{ $report->reason?->reason_name ?? 'ไม่ระบุเหตุผล' }} · {{ $report->reporter?->name ?? 'ไม่ทราบผู้รายงาน' }}</p>
                    <p>{{ $report->description }}</p>
                </article>
            @empty
                <p>ยังไม่มีรายงาน</p>
            @endforelse
        </section>
        <section>
            <h2>รีวิวล่าสุด</h2>
            <p><a href="{{ route('admin.reviews.index') }}">ดูรีวิวทั้งหมด</a></p>
            @forelse ($recentReviews as $review)
                <article>
                    <h3><a href="{{ route('admin.reviews.show', $review) }}">{{ $review->Review_id }} · {{ $review->rating }}/5</a></h3>
                    <p>{{ $review->appointment?->studentProfile?->user?->name ?? 'ไม่พบผู้รีวิว' }} · {{ $review->tutorProfile?->user?->name ?? 'ไม่พบติวเตอร์' }}</p>
                    <p>{{ $review->Comment }}</p>
                </article>
            @empty
                <p>ยังไม่มีรีวิว</p>
            @endforelse
        </section>
    </main>
</x-layouts::app>
