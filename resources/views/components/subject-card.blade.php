@props([
    'title' => 'คณิตศาสตร์',
    'description' => 'ปูพื้นฐานและติวสอบเข้ามหาลัย ม.ปลาย/มหาวิทยาลัย'
])

<div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
    <h3 class="font-bold text-gray-800 text-base mb-1">{{ $title }}</h3>
    <p class="text-xs text-gray-500 mb-4 line-clamp-2">{{ $description }}</p>
    <button class="bg-[#E06D53] hover:bg-[#c85a42] text-white px-4 py-1.5 rounded-xl text-xs font-medium transition-colors">
        ดูติวเตอร์
    </button>
</div>