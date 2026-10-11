# 2. CheckSchedule & Time Matching

## 2.1 ต้องแยกสองเส้นทางให้ชัด

ระบบปัจจุบันมีสองชุดอยู่ร่วมกัน: (ก) ทางหลักให้ติวเตอร์เปิดช่วงเวลาและนักเรียนเลือกช่วงนั้นจากโปรไฟล์ (ข) โค้ดเดิมค้นช่วงเวลาที่นักเรียนกับติวเตอร์ว่างร่วมกัน ทั้งสองใช้ availabilities และ appointments แต่ทางหลักไม่ได้เรียก ScheduleMatcher หรือ ScheduleBookingService

## 2.2 ทางหลักที่ใช้กับการจองปัจจุบัน

1. tutor ที่อนุมัติแล้ว → /availabilities → AvailabilityController::index() → availabilities/index.blade.php
2. POST /availabilities → StoreAvailabilityRequest ตรวจเริ่มในอนาคตและจบหลังเริ่ม → AvailabilityController::store() ตรวจช่วงทับก่อนบันทึก Availability
3. เวลาแก้/ลบ Controller ตรวจ ensureOwner และ ensureUnbooked เพื่อไม่ให้คนอื่นแก้ และไม่ให้เวลาที่มีการจองเปลี่ยนเงียบ ๆ
4. นักเรียนเปิด TutorController::show() → อ่าน Availability ของ Users_user_id ของติวเตอร์ → ตัดช่วงทับกับ appointments ที่ไม่ cancelled → ส่ง $availabilities ไป tutor/show.blade.php
5. ผู้เรียนเลือก availability_id → AppointmentController::store() อ่านเวลาจาก DB อีกครั้ง ตรวจช่วงยังเป็นอนาคตและยังไม่มีการจอง → สร้าง pending โดยใช้เวลาจาก DB

ผล: วันเวลาที่แสดงและวันที่บันทึกอิงช่วงเดียวกัน ฝั่ง server ไม่เชื่อวันเวลาที่กรอกเองเพื่อเลี่ยงการตรวจ

## 2.3 อัลกอริทึม Time Matching เดิม

CheckScheduleController::check() → CheckScheduleRequest → AvailabilityService::findSharedTimes() → อ่านเวลาทั้งสองฝ่ายและนัดหมาย → ScheduleMatcher::match() → คืน start/end → check-schedule/index.blade.php

ตัวอย่าง: นักเรียนว่าง 09:00–12:00 ติวเตอร์ว่าง 10:00–13:00 จึงมีช่วงร่วม 10:00–12:00 ถ้ามีนัด 10:30–11:00 ผลคือ 10:00–10:30 และ 11:00–12:00 หลักคือเลือกเวลาเริ่มที่ช้ากว่า เวลาเลิกที่เร็วกว่า แล้วหักช่วงไม่ว่างออก

เงื่อนไขช่วงทับ: เวลาเริ่ม A < เวลาจบ B และเวลาจบ A > เวลาเริ่ม B ดังนั้นช่วงที่ติดกันพอดี เช่น 09:00–10:00 กับ 10:00–11:00 ไม่ถือว่าทับ

## 2.4 ส่วนเพิ่ม เปลี่ยน และตัดจากขอบเขต

- เปลี่ยนทางหลัก: เลือกวันว่างของติวเตอร์แทนกรอกวันเอง; ตรวจข้อมูลซ้ำก่อนจองและห้ามเปลี่ยนเวลาที่มีนัด
- เก็บไว้แต่ยังไม่เชื่อมครบ: CheckScheduleController, AvailabilityService, ScheduleBookingService และ LearningHistoryService ยังอิง tutor_profiles.user_id/id ขณะที่ Model ใช้ Users_user_id/tutor_id
- หน้า check-schedule/booking.blade.php ระบุชัดว่าการจองผ่านหน้านี้ยังไม่เปิด และปุ่ม disabled จึงไม่ใช่หน้าจองที่ใช้เดโมหลัก
- ประวัติการสอนถูกตัดจากขอบเขตหน้าติเตอร์ใหม่ตามที่ตกลง แต่ LearningHistoryService, หน้า history และ routes เดิมยังอยู่ ไม่ใช่ลบไฟล์/route ทั้งหมดแล้ว
- ขอบเขตปัจจุบัน: store ทางหลักเช็กเวลาของติวเตอร์เป็นหลัก อย่าอ้างว่าตรวจนัดซ้อนฝั่งนักเรียนข้ามติวเตอร์ครบแล้วเพียงเพราะ Service เดิมมีโค้ดส่วนนั้น

## 2.5 แนวพูดพรีเซนต์

“การจองหลักเลือกช่วงที่ผู้สอนเปิดไว้ ระบบตรวจว่าช่วงนั้นยังว่างก่อนบันทึก เพื่อให้วันที่ผู้เรียนเห็นตรงกับข้อมูลผู้สอน ส่วนอัลกอริทึมหาเวลาว่างร่วมเป็นอีกชุดหนึ่ง ซึ่งยังต้องปรับการเชื่อมฐานข้อมูลก่อนใช้แทนทางหลัก”

## รายไฟล์: หน้าที่ การส่งต่อ และผลที่เกิด

ชื่อไฟล์อ้างจากรากโปรเจกต์ จุดเชื่อมในตารางอ้างจากซอร์ส ไม่ใช่ทุกจุดที่ถูกเรียกขณะรันจริง ให้ดูคอลัมน์สถานะประกอบ สำหรับ route resource/Fortify ที่ไม่ได้ประกาศ method ทีละรายการให้อ่าน flow ด้านบนร่วมด้วย

| ไฟล์ | หน้าที่ / ผลที่เกิด | รับจาก / ส่งต่อ | สถานะ |
| --- | --- | --- | --- |
| `app/Domains/Scheduling/Http/Controllers/AvailabilityController.php` | ติวเตอร์ดู เพิ่ม แก้ ลบเวลาว่าง ตรวจเจ้าของ ตรวจช่วงเวลาทับกัน และห้ามแก้เวลาที่มีนัดหมายแล้ว | routes/web.php: /availabilities → index(); /availabilities → store(); /availabilities/history → history(); /availabilities/{availability}/edit → edit(); /availabilities/{availability} → update(); /availabilities/{availability} → destroy(); ใช้ Appointment.php, StoreAvailabilityRequest.php, UpdateAvailabilityRequest.php, Availability.php, edit.blade.php, history.blade.php, index.blade.php | โค้ดในระบบ |
| `app/Domains/Scheduling/Http/Controllers/CheckScheduleController.php` | หน้าค้นเวลาที่นักเรียนกับติวเตอร์ว่างร่วมกัน ส่งต่อ AvailabilityService และหน้าประวัติ; โค้ดบางจุดยังใช้คอลัมน์ติวเตอร์แบบเก่า | routes/web.php: /schedule/check → index(); /schedule/history → history(); /schedule/booking → booking(); /schedule/check → check(); ใช้ Subject.php, CheckScheduleRequest.php, AvailabilityService.php, LearningHistoryService.php, StudentProfile.php, TutorProfile.php, booking.blade.php, history.blade.php, index.blade.php | เส้นทางเดิมต้องแก้ |
| `app/Domains/Scheduling/Http/Requests/CheckScheduleRequest.php` | ตรวจรหัส tutor_id ความยาว 10 และช่วงเริ่ม–จบให้ถูกต้อง ยังไม่ได้ยืนยันตัวตนของติวเตอร์ด้วย exists | ตรวจรหัส tutor_id ความยาว 10 และช่วงเริ่ม–จบให้ถูกต้อง ยังไม่ได้ยืนยันตัวตนของติวเตอร์ด้วย exists | เส้นทางจับเวลาเดิม |
| `app/Domains/Scheduling/Http/Requests/StoreAvailabilityRequest.php` | ตรวจเวลาเริ่มต้องเป็นอนาคตและเวลาจบต้องหลังเวลาเริ่ม ก่อนเพิ่มช่วงเวลาสอน | ตรวจเวลาเริ่มต้องเป็นอนาคตและเวลาจบต้องหลังเวลาเริ่ม ก่อนเพิ่มช่วงเวลาสอน | โค้ดในระบบ |
| `app/Domains/Scheduling/Http/Requests/UpdateAvailabilityRequest.php` | ตรวจวันเวลาชุดใหม่ก่อนแก้ช่วงเวลาสอน ใช้เงื่อนไขเดียวกับการเพิ่ม | ตรวจวันเวลาชุดใหม่ก่อนแก้ช่วงเวลาสอน ใช้เงื่อนไขเดียวกับการเพิ่ม | โค้ดในระบบ |
| `app/Domains/Scheduling/Models/Availability.php` | แทนตาราง availabilities แปลง start_datetime/end_datetime เป็นวันเวลา และเชื่อม User | Controller/Service → Model → ตาราง availabilities; ความสัมพันธ์ไป User.php | โค้ดในระบบ |
| `app/Domains/Scheduling/Services/AvailabilityService.php` | อ่านเวลาว่างทั้งสองฝ่ายและนัดหมาย ส่งให้ ScheduleMatcher หาจุดร่วม; ส่วน query ยังอิง tutor_profiles.user_id/id แบบเก่า | เชื่อม Appointment.php, Availability.php, StudentProfile.php, TutorProfile.php | เส้นทางเดิมต้องแก้ |
| `app/Domains/Scheduling/Services/LearningHistoryService.php` | รวมนัดหมายที่เวลาสิ้นสุดผ่านแล้วให้หน้าประวัติ ยังใช้คอลัมน์โปรไฟล์ติวเตอร์เก่า | เชื่อม Appointment.php, StudentProfile.php, TutorProfile.php | โค้ดประวัติเดิมยังอยู่ |
| `app/Domains/Scheduling/Services/ScheduleBookingService.php` | Service จองแบบเดิม ตรวจทั้งสองฝ่ายและล็อกข้อมูล แต่ไม่พบ Controller หลักเรียกใช้ปัจจุบัน และยังอิงคอลัมน์ติวเตอร์เก่า | เชื่อม Appointment.php, StudentProfile.php, TutorProfile.php | ไม่ได้ใช้ในทางจองหลัก |
| `app/Domains/Scheduling/Services/ScheduleMatcher.php` | คำนวณช่วงเวลาซ้อนกัน ตัดให้อยู่ในช่วงที่ค้น แล้วหักเวลาที่จองออก คืนรายการ start/end โดยไม่บันทึกฐานข้อมูล | คำนวณช่วงเวลาซ้อนกัน ตัดให้อยู่ในช่วงที่ค้น แล้วหักเวลาที่จองออก คืนรายการ start/end โดยไม่บันทึกฐานข้อมูล | อัลกอริทึมของระบบจับเวลาเดิม |
| `database/migrations/2026_09_16_000000_create_availabilities_table.php` | สร้างเวลาว่าง availability_id/user_id/start_datetime/end_datetime | artisan migrate → availabilities → Models/Controllers ที่อ่านตาราง; ไม่ส่งผู้ใช้ไปหน้าเว็บโดยตรง | ไฟล์เปลี่ยนโครงสร้าง DB |
| `resources/views/domains/scheduling/availabilities/edit.blade.php` | แก้วันเวลาเดิม ส่ง availabilities.update และแสดงข้อผิดพลาด | รับจาก AvailabilityController.php; ปุ่ม/ฟอร์มไป route availabilities.update, availabilities.index; ใช้ tutor-page.blade.php | โค้ดในระบบ |
| `resources/views/domains/scheduling/availabilities/history.blade.php` | ประวัติรายการเวลาว่าง ยังมี route อยู่ แต่ไม่ใช่เมนูประวัติการสอนใหม่ | รับจาก AvailabilityController.php; ปุ่ม/ฟอร์มไป route availabilities.index; ใช้ layout.blade.php | ไฟล์และ route เดิมยังอยู่ |
| `resources/views/domains/scheduling/availabilities/index.blade.php` | เพิ่มและแสดงวันเวลาสอน พร้อมปุ่มแก้/ลบ ส่ง AvailabilityController | รับจาก AvailabilityController.php; ปุ่ม/ฟอร์มไป route tutor.profile, availabilities.store, availabilities.edit, availabilities.destroy; ใช้ tutor-page.blade.php | โค้ดในระบบ |
| `resources/views/domains/scheduling/check-schedule/booking.blade.php` | แสดงวิชา/เวลาที่เลือก แต่ระบุว่ายังไม่เปิดการจองและปุ่ม disabled | รับจาก CheckScheduleController.php; ปุ่ม/ฟอร์มไป route schedule.check; ใช้ layout.blade.php | หน้าจองเดิมยังไม่เปิด |
| `resources/views/domains/scheduling/check-schedule/history.blade.php` | แสดงผลจาก LearningHistoryService ของนัดหมายที่ผ่านมา | รับจาก CheckScheduleController.php; ปุ่ม/ฟอร์มไป route schedule.check; ใช้ layout.blade.php | ประวัติเดิมยังอยู่ |
| `resources/views/domains/scheduling/check-schedule/index.blade.php` | กรอกรหัสติวเตอร์และช่วงเวลา ส่ง schedule.check.results และแสดงเวลาว่างร่วม | รับจาก CheckScheduleController.php; ปุ่ม/ฟอร์มไป route availabilities.index, schedule.history, schedule.check.results, schedule.booking; ใช้ layout.blade.php | ระบบจับเวลาเดิมต้องเชื่อม schema |
| `resources/views/domains/scheduling/layout.blade.php` | layout ของหน้าเช็กเวลาร่วมและประวัติ พร้อมเมนูลิงก์เส้นทาง scheduling เดิม | รับจาก history.blade.php, booking.blade.php, history.blade.php; ปุ่ม/ฟอร์มไป route home, availabilities.index, schedule.check, schedule.history | ใช้กับเส้นทางเดิม |
| `tests/Feature/Availability/.gitkeep` | ไฟล์ว่างรักษาโฟลเดอร์ใน Git ไม่มีคำสั่งทำงาน | phpunit.xml → PHPUnit → คลาส/route ที่ระบุในไฟล์; ไม่มีผลต่อเว็บจนกว่าจะสั่งรัน | ไฟล์ประกอบ repository |
| `tests/Feature/Scheduling/AvailabilityServiceTest.php` | กรณีตัดนัดหมายออกจากเวลาว่าง บันทึกจอง ประวัติ และ CRUD เวลาว่าง; บางสมมติฐานอิง schema/สิทธิ์เดิม; อธิบายสิ่งที่ไฟล์ตั้งใจตรวจ ไม่ใช่หลักฐานว่ารันผ่านล่าสุด | phpunit.xml → PHPUnit → คลาส/route ที่ระบุในไฟล์; ไม่มีผลต่อเว็บจนกว่าจะสั่งรัน | ชุดทดสอบเดิม ไม่ได้รันในงานรายงาน |
| `tests/Unit/Scheduling/.gitkeep` | ไฟล์ว่างรักษาโฟลเดอร์ใน Git ไม่มีคำสั่งทำงาน | phpunit.xml → PHPUnit → คลาส/route ที่ระบุในไฟล์; ไม่มีผลต่อเว็บจนกว่าจะสั่งรัน | ไฟล์ประกอบ repository |
| `tests/Unit/Scheduling/ScheduleMatcherTest.php` | กรณีจุดร่วมเวลา ไม่มีช่วงร่วม ตัดเวลาจอง และแบ่งช่วงว่าง; อธิบายสิ่งที่ไฟล์ตั้งใจตรวจ ไม่ใช่หลักฐานว่ารันผ่านล่าสุด | phpunit.xml → PHPUnit → คลาส/route ที่ระบุในไฟล์; ไม่มีผลต่อเว็บจนกว่าจะสั่งรัน | ชุดทดสอบเดิม ไม่ได้รันในงานรายงาน |

