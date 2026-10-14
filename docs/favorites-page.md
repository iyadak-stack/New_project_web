# หน้ารายการโปรด: โน้ตสำหรับพรีเซนต์

หน้านี้ใช้ PHP ของ Laravel, Blade (HTML) และ CSS ไม่มี JavaScript เพิ่มสำหรับหน้ารายการโปรด

## ลำดับการทำงาน

1. ผู้ใช้เปิด `/favorites` หรือ `/tutors/favorites` โดยต้องเข้าสู่ระบบก่อน
2. Route เรียกเมธอด `tutorFavorites()` ใน `FavoriteController`
3. Controller ใช้ `auth()->id()` เพื่อดึงเฉพาะรายการโปรดของผู้ใช้ปัจจุบัน ไม่แสดงรายการของคนอื่น
4. `with()` โหลดข้อมูลผู้ใช้และวิชาของติวเตอร์มาพร้อมกัน ก่อนส่งข้อมูลให้ View
5. Blade ใช้ `@forelse` วนสร้างการ์ด และแสดงข้อความแนะนำหากไม่มีรายการ
6. ปุ่มดูโปรไฟล์ใช้ `route('tutor.show', $tutor->tutor_id)` จึงเปิดหน้าโปรไฟล์ของติวเตอร์ตรงคน

## ไฟล์หลัก

- `app/Domains/TutorProfile/Http/Controllers/FavoriteController.php`: ดึงข้อมูล
- `resources/views/domains/tutor-profile/tutor/favorites.blade.php`: โครงสร้างหน้าและการแสดงข้อมูล
- `public/CSS/favorites.css`: สี ขนาดตัวอักษร การ์ด และการปรับหน้าเมื่อจอเล็ก
- `database/seeders/FavoritePageSeeder.php`: ข้อมูลตัวอย่างสำหรับสาธิต

รูปโปรไฟล์ใช้มุมมน 10px หากไม่มีรูปจะแสดงตัวอักษรแรกของชื่อ คะแนนใช้ข้อมูลจริงจากโปรไฟล์ และแสดงข้อความเมื่อยังไม่มีคะแนน ส่วนประสบการณ์ใช้จำนวนปีที่ติวเตอร์ระบุ

## ข้อมูลตัวอย่าง

เรียกเฉพาะ Seeder นี้ได้ด้วยคำสั่ง:

```bash
php artisan db:seed --class=FavoritePageSeeder
```

Seeder ทำงานเฉพาะ environment `local` และ `testing` สร้างติวเตอร์ 3 คนและบัญชีนักเรียนสาธิต อีเมล `favorites.demo@example.com` รหัสผ่าน `Demo12345!`

เรียก `php artisan db:seed` ได้เช่นกัน เพราะ DatabaseSeeder เรียก FavoritePageSeeder ให้แล้ว

DatabaseSeeder เรียกผ่าน AccountSeeder เพื่อสร้างบัญชีนักเรียนและแอดมินตัวอย่างด้วย ติวเตอร์ใช้งานได้ทันทีโดยไม่ต้องรออนุมัติ

บัญชีสาธิตและนักเรียนเดิมที่ยังไม่เคยบันทึกรายการโปรดจะได้รายการตัวอย่าง 3 คน โดยตรวจทั้ง studentProfile และบทบาท student จึงรองรับบัญชีที่เพิ่งสร้างและยังไม่มีโปรไฟล์ด้วย บัญชีที่มีรายการอยู่แล้วไม่ถูกเปลี่ยนแปลง ใช้ `firstOrCreate()` ป้องกันข้อมูลซ้ำเมื่อเรียก Seeder อีกครั้ง

## ตรวจสอบการทำงาน

```bash
php artisan test --compact tests/Feature/TutorFavoritesTest.php
```

ตรวจการบังคับล็อกอิน การแยกรายการของแต่ละคน รายการที่นำออกแล้ว กรณีไม่มีรายการ การเรียก Seeder ซ้ำ และการเปิดหน้าโปรไฟล์
