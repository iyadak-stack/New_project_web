# บัญชีจาก TutorSeeder

Seeder นี้เป็นข้อมูลเริ่มต้นของโปรเจกต์ เรียกผ่าน `DatabaseSeeder` ได้ และไม่จำกัดเฉพาะ environment สำหรับทดลอง

รหัสผ่านเริ่มต้นของบัญชีใหม่ทั้ง 6 คน: `password` (บันทึกเป็น hash ใน DB)

| ชื่อ | อีเมลเข้าสู่ระบบ | ผลอนุมัติ | รูปต้นฉบับที่นำมาใช้ |
| --- | --- | --- | --- |
| เนเน่ ศิริกุล | nene@example.com | อนุมัติแล้ว | profile1.jpg → เนเน่.jpg |
| แพรว วัฒนกุล | praew@example.com | อนุมัติแล้ว | profile2.jpg → แพรว.jpg |
| กาย ธนากร | kai@example.com | ปฏิเสธ | profile3.jpg → กาย.jpg |
| ต้น ปัญญาวัฒน์ | ton@example.com | ปฏิเสธ | profile4.jpg → ต้น.jpg |
| นนท์ เจริญชัย | non@example.com | รอตรวจสอบ | profile5.jpg → นนท์.jpg |
| เฟิร์น สุขสวัสดิ์ | fern@example.com | รอตรวจสอบ | profile6.jpg → เฟิร์น.jpg |

ผู้ที่อนุมัติแล้วเริ่มด้วยบทบาทติวเตอร์ ส่วนผู้ที่ปฏิเสธหรือรอตรวจสอบใช้บทบาทนักเรียนจนกว่าแอดมินอนุมัติ ผู้ที่ถูกปฏิเสธมีเหตุผลให้แก้ไขและส่งคำขอใหม่ผ่านระบบเดิม

รูปต้นฉบับในโปรเจกต์: `public/images/tutor-profiles` โดย Seeder คัดลอกเข้า `storage/app/public/tutor-profiles` แล้วบันทึกตำแหน่งใน `Users.profile_picture`

รันเฉพาะชุดนี้:

```sh
php artisan db:seed --class=TutorSeeder
```

หรือรันชุดปกติซึ่งรวม Seeder นี้แล้ว:

```sh
php artisan db:seed
```

การรันซ้ำไม่สร้างบัญชีหรือโปรไฟล์ซ้ำ ไม่เปลี่ยนรหัสผ่าน ชื่อ รูป หรือผลอนุมัติที่ถูกแก้ภายหลัง ไม่เปิดบัญชีที่ soft delete แล้ว และไม่ลบบัญชีเดิม การเชื่อมวิชาใช้ `syncWithoutDetaching` จึงเพิ่มวิชาที่กำหนดไว้โดยไม่ลบวิชาอื่นของติวเตอร์

Seeder นี้ไม่สร้างหรือเปลี่ยน schema และไม่สร้างข้อมูล schedule/availability
