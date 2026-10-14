
@if ($items->hasPages())
    <nav class="admin-pagination" aria-label="เปลี่ยนหน้ารายการ">

@if ($items->previousPageUrl())<a class="admin-button secondary" href="{{ $items->previousPageUrl() }}">← ก่อนหน้า</a>
@endif
        <span>หน้า {{ $items->currentPage() }} / {{ $items->lastPage() }}</span>

@if ($items->nextPageUrl())<a class="admin-button secondary" href="{{ $items->nextPageUrl() }}">ถัดไป →</a>
@endif
    </nav>
@endif
