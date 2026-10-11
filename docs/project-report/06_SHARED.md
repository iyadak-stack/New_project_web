# 6. ไฟล์ส่วนกลาง เครื่องมือ และข้อมูลทดลอง

## 6.1 ส่วนกลางเชื่อมทุกงานอย่างไร

public/index.php เป็นประตู HTTP ส่วน artisan เป็นประตูคำสั่ง CLI ทั้งสองโหลด bootstrap/app.php. bootstrap/providers.php โหลด AppServiceProvider และ FortifyServiceProvider. routes/web.php จัด URL งานหลัก ส่วน routes/settings.php เป็นชุด settings เดิม. composer.json/lock ติดตั้ง PHP packages; package.json/lock และ vite.config.js จัด assets

home-navbar เป็นหน้าตาร่วม แต่ไม่ใช่ตัวอนุญาตสิทธิ์ สิทธิ์จริงอยู่ middleware และ Controller. CSS ใน public/CSS ถูก Blade อ้าง asset('css/...'); บน Windows มักเข้าถึงได้แม้ตัวพิมพ์ต่างกัน แต่เครื่องที่แยกตัวพิมพ์เล็ก/ใหญ่ต้องตรวจชื่อโฟลเดอร์ก่อน deploy

## 6.2 Seeder ชุดปัจจุบัน

DatabaseSeeder → SubjectSeeder → ReportReasonSeeder → DemoWebsiteSeeder. DemoWebsiteSeeder เรียก SubjectSeeder ซ้ำได้เพราะ firstOrCreate แล้วสร้างข้อมูลธุรกิจเชื่อมกันใน transaction เฉพาะ local/testing ไม่ล้างฐานข้อมูล ไม่รีเซ็ตรหัสผ่านหรือสถานะของแถวเดิมที่ทดลองแก้แล้ว แต่บางส่วน เช่นเชื่อมวิชา ใช้ syncWithoutDetaching และไฟล์หลักฐานตัวอย่างถูกเขียนใหม่

| บัญชี | บทบาท/กรณี |
| --- | --- |
| demo.admin@example.com | admin ตรวจใบสมัคร รายงาน ปิดบัญชี |
| demo.learner@example.com | นักเรียนจองและรีวิว |
| demo.tutor@example.com | ติวเตอร์คณิต approved |
| demo.english@example.com | ติวเตอร์อังกฤษ approved |
| demo.pending@example.com | ใบสมัคร pending |
| demo.rejected@example.com | ใบสมัคร rejected พร้อมเหตุผล |
| demo.disabled@example.com | บัญชีปิดใช้งานพร้อมเหตุผล |

รหัสผ่านเริ่มต้นของบัญชีชุดนี้คือ password ใช้สำหรับเครื่องทดลอง ลิงก์ห้องเรียนเป็น example.com สำหรับแสดง UI ไม่ใช่ห้องจริง หากทีมเปลี่ยนรหัสผ่านไปแล้ว การรัน Seeder ซ้ำไม่บังคับกลับเป็น password

นัดหมาย DEMOAPP001 รอรับ, DEMOAPP002 ยืนยันอนาคต, DEMOAPP003 จบและมีรีวิว, DEMOAPP004 จบแต่ยังไม่มีรีวิว, DEMOAPP005 ยกเลิก, DEMOAPP006 ยืนยันและผ่านเวลาจบแล้วเพื่อกดยืนยัน session. วันเวลาอิงวันที่สร้างข้อมูลครั้งแรก

คำสั่งเพิ่มข้อมูลในเครื่องที่ตั้งค่าแล้ว: php artisan migrate จากนั้น php artisan db:seed. ห้ามใช้ migrate:fresh เพื่อเพิ่มตัวอย่างในฐานข้อมูลทีมที่มีข้อมูลสำคัญ เพราะคำสั่งนั้นล้างตาราง

TutorDemoSeeder/AdminDemoSeeder/DemoBookingSeeder เป็นชุดเดิมที่ยังอยู่ แต่ DatabaseSeeder ปัจจุบันไม่เรียก ไม่ควรสับสนบัญชี demo.student@example.com รุ่นเดิมกับ demo.learner@example.com ชุดใหม่

## 6.3 ข้อมูลธุรกิจกับข้อมูลระบบ

ข้อมูลตัวอย่างครอบคลุม users, student_profiles, tutor_profiles, subjects, tutor_profiles_has_subjects, contacts, availabilities, appointments, reviews, favorites, notification_types, notifications, report_reasons, reports และ report_evidences. ส่วน sessions, cache, cache_locks, jobs, job_batches, failed_jobs, password_reset_tokens และ passkeys เป็นข้อมูลที่ Laravel/ผู้ใช้สร้างขณะใช้งาน ไม่จำเป็นต้องยัดแถวจำลองให้เต็มทุกตาราง

## 6.4 ความสัมพันธ์หลักที่ต้องจำ

| ต้นทาง | เชื่อมไป | ความหมาย |
| --- | --- | --- |
| users.user_id | student_profiles.user_id / tutor_profiles.Users_user_id / contacts.Users_user_id | บัญชีหนึ่งมีข้อมูลแต่ละบทบาท/ติดต่อ |
| tutor_profiles.tutor_id | tutor_profiles_has_subjects.Tutor_profiles_tutor_id | ผู้สอนเลือกหลายวิชา |
| subjects.subject_id | ตารางกลาง.Subject_subject_id และ appointments.Subject_subject_id | วิชาที่สอนและวิชาในนัด |
| users.user_id | availabilities.user_id | เจ้าของช่วงเวลาว่าง |
| tutor_profiles.tutor_id | appointments.Tutor_profiles_tutor_id | ผู้สอนในนัด |
| users.user_id หรือ student_profiles.id ในข้อมูลเก่า | appointments.Student_profiles_student_id | ผู้เรียนในนัด ต้องตรวจรูปแบบก่อนเขียน query |
| appointments.Appointment_id | reviews.Appointment_Appointment_id | รีวิวอ้างนัดที่เรียนจบ |
| users.user_id | notifications.Users_user_id / favorites.Users_user_id | ผู้รับแจ้งเตือน/เจ้าของรายการโปรด |
| users.user_id | reports.Users_user_id / Reported_users_user_id | ผู้รายงาน/ผู้ถูกรายงาน คนละหน้าที่ |
| reports.Report_id | report_evidences.Report_Report_id | หลักฐานของรายงาน |

นี่คือความสัมพันธ์ตาม Model และการใช้คอลัมน์ ไม่ได้หมายความว่าทุกเส้นมี foreign key บังคับใน DB แล้ว

## รายไฟล์: หน้าที่ การส่งต่อ และผลที่เกิด

ชื่อไฟล์อ้างจากรากโปรเจกต์ จุดเชื่อมในตารางอ้างจากซอร์ส ไม่ใช่ทุกจุดที่ถูกเรียกขณะรันจริง ให้ดูคอลัมน์สถานะประกอบ สำหรับ route resource/Fortify ที่ไม่ได้ประกาศ method ทีละรายการให้อ่าน flow ด้านบนร่วมด้วย

| ไฟล์ | หน้าที่ / ผลที่เกิด | รับจาก / ส่งต่อ | สถานะ |
| --- | --- | --- | --- |
| `.editorconfig` | กำหนดรูปแบบไฟล์/การเยื้องให้ editor ของทีมสอดคล้องกัน | กำหนดรูปแบบไฟล์/การเยื้องให้ editor ของทีมสอดคล้องกัน | โค้ดในระบบ |
| `.env` | ค่าจริงของเครื่อง เช่นฐานข้อมูล APP_URL และกุญแจแอป; รายงานไม่อ่านหรือเผยแพร่ค่าในไฟล์ | config/*.php อ่านผ่าน env() → ค่าเฉพาะเครื่อง; ไม่คัดลอกเนื้อหาหรือรหัสลับลงรายงาน | ข้อมูลเฉพาะเครื่อง ห้ามแนบค่าไปกับรายงาน |
| `.gitattributes` | กำหนดพฤติกรรม Git เช่นข้อความและ line ending | กำหนดพฤติกรรม Git เช่นข้อความและ line ending | โค้ดในระบบ |
| `.gitignore` | ระบุไฟล์ที่ไม่บันทึกใน Git เช่น dependencies และไฟล์สภาพแวดล้อม | ระบุไฟล์ที่ไม่บันทึกใน Git เช่น dependencies และไฟล์สภาพแวดล้อม | โค้ดในระบบ |
| `.npmrc` | ค่าการทำงานของ npm ในโปรเจกต์ | ค่าการทำงานของ npm ในโปรเจกต์ | โค้ดในระบบ |
| `AGENTS.md` | ข้อตกลงเขียนโค้ดให้เหมาะกับนักศึกษาปี 2 และข้อกำหนดสำหรับผู้ช่วยแก้โค้ด | ผู้พัฒนาอ่าน → ใช้เป็นแนวทางทำงาน/ทดลอง ไม่ถูก HTTP route เรียก | โค้ดในระบบ |
| `app/Console/Commands/InstallFeaturesCommand.php` | คำสั่งเลือกฟีเจอร์ Starter Kit ผ่าน Chisel อาจแก้หรือลบไฟล์ตามตัวเลือก | คำสั่งเลือกฟีเจอร์ Starter Kit ผ่าน Chisel อาจแก้หรือลบไฟล์ตามตัวเลือก | เครื่องมือติดตั้ง ไม่ใช่ flow ผู้ใช้ |
| `app/Http/Controllers/Controller.php` | คลาสแม่ให้ Controller แต่ละงานสืบทอด ไม่ใช่หน้าที่ผู้ใช้เปิดโดยตรง | คลาสแม่ให้ Controller แต่ละงานสืบทอด ไม่ใช่หน้าที่ผู้ใช้เปิดโดยตรง | โค้ดในระบบ |
| `app/Providers/AppServiceProvider.php` | ค่าเริ่มต้นวันเวลา/กฎรหัสผ่าน และลงทะเบียน namespace layouts ของ Auth Blade | ค่าเริ่มต้นวันเวลา/กฎรหัสผ่าน และลงทะเบียน namespace layouts ของ Auth Blade | โค้ดในระบบ |
| `artisan` | จุดเข้า CLI ของ Laravel เช่น migrate, db:seed, serve โหลด bootstrap/app | จุดเข้า CLI ของ Laravel เช่น migrate, db:seed, serve โหลด bootstrap/app | โค้ดในระบบ |
| `bootstrap/app.php` | เริ่มแอป โหลด routes ตั้ง EnsureActiveAccount ใน web middleware และ alias role/active | เชื่อม EnsureActiveAccount.php, RoleMiddleware.php | โค้ดในระบบ |
| `bootstrap/providers.php` | รายชื่อ service providers ที่โหลดขณะเริ่มแอป | เชื่อม AppServiceProvider.php, FortifyServiceProvider.php | โค้ดในระบบ |
| `chisel-paths.php` | รายการ path ที่ Chisel ใช้แก้ฟีเจอร์ ยังมี resources/views/pages แบบเดิม | รายการ path ที่ Chisel ใช้แก้ฟีเจอร์ ยังมี resources/views/pages แบบเดิม | path เดิมต้องตรวจ |
| `chisel.php` | สคริปต์เลือกลบ/คงฟีเจอร์ Starter Kit ระหว่างติดตั้ง อ้างเส้นทางดั้งเดิมบางส่วน | สคริปต์เลือกลบ/คงฟีเจอร์ Starter Kit ระหว่างติดตั้ง อ้างเส้นทางดั้งเดิมบางส่วน | เครื่องมือติดตั้งเดิม |
| `composer.json` | รายการแพ็กเกจ PHP, autoload App/Database/Tests และคำสั่ง setup/dev/test | composer install → vendor/autoload.php → public/index.php / artisan | โค้ดในระบบ |
| `composer.lock` | ล็อกเวอร์ชันแพ็กเกจ PHP ที่ composer install ใช้ ให้เครื่องทีมติดตั้งตรงกัน | composer install → vendor/autoload.php → public/index.php / artisan | โค้ดในระบบ |
| `config/app.php` | กำหนด ชื่อแอป environment timezone locale และ encryption key → Laravel และส่วนที่เรียก config ใช้ค่านี้ | bootstrap/Laravel โหลดค่า → config() → บริการที่เกี่ยวข้อง (ชื่อค่าเป็นจุดเชื่อม ไม่ใช่ redirect) | โค้ดในระบบ |
| `config/cache.php` | กำหนด ที่เก็บ cache และ lock ของ Laravel → Laravel และส่วนที่เรียก config ใช้ค่านี้ | bootstrap/Laravel โหลดค่า → config() → บริการที่เกี่ยวข้อง (ชื่อค่าเป็นจุดเชื่อม ไม่ใช่ redirect) | โค้ดในระบบ |
| `config/database.php` | กำหนด connection ฐานข้อมูลและข้อมูลอ้างอิงจาก environment → Laravel และส่วนที่เรียก config ใช้ค่านี้ | bootstrap/Laravel โหลดค่า → config() → บริการที่เกี่ยวข้อง (ชื่อค่าเป็นจุดเชื่อม ไม่ใช่ redirect) | โค้ดในระบบ |
| `config/filesystems.php` | กำหนด disk public/local ที่เก็บรูปและไฟล์; local ใช้ URL private-storage แยกจาก public storage → Laravel และส่วนที่เรียก config ใช้ค่านี้ | bootstrap/Laravel โหลดค่า → config() → บริการที่เกี่ยวข้อง (ชื่อค่าเป็นจุดเชื่อม ไม่ใช่ redirect) | โค้ดในระบบ |
| `config/logging.php` | กำหนด ช่องทางและระดับบันทึก log → Laravel และส่วนที่เรียก config ใช้ค่านี้ | bootstrap/Laravel โหลดค่า → config() → บริการที่เกี่ยวข้อง (ชื่อค่าเป็นจุดเชื่อม ไม่ใช่ redirect) | โค้ดในระบบ |
| `config/mail.php` | กำหนด ตัวส่งอีเมลและผู้ส่งจาก environment → Laravel และส่วนที่เรียก config ใช้ค่านี้ | bootstrap/Laravel โหลดค่า → config() → บริการที่เกี่ยวข้อง (ชื่อค่าเป็นจุดเชื่อม ไม่ใช่ redirect) | โค้ดในระบบ |
| `config/queue.php` | กำหนด ตัวขับคิวงานและ failed jobs → Laravel และส่วนที่เรียก config ใช้ค่านี้ | bootstrap/Laravel โหลดค่า → config() → บริการที่เกี่ยวข้อง (ชื่อค่าเป็นจุดเชื่อม ไม่ใช่ redirect) | โค้ดในระบบ |
| `config/services.php` | กำหนด ค่าบริการภายนอกที่ Laravel อ่านจาก environment → Laravel และส่วนที่เรียก config ใช้ค่านี้ | bootstrap/Laravel โหลดค่า → config() → บริการที่เกี่ยวข้อง (ชื่อค่าเป็นจุดเชื่อม ไม่ใช่ redirect) | โค้ดในระบบ |
| `config/session.php` | กำหนด ที่เก็บ session อายุ cookie และพฤติกรรมจำผู้ล็อกอิน → Laravel และส่วนที่เรียก config ใช้ค่านี้ | bootstrap/Laravel โหลดค่า → config() → บริการที่เกี่ยวข้อง (ชื่อค่าเป็นจุดเชื่อม ไม่ใช่ redirect) | โค้ดในระบบ |
| `database/.gitignore` | กำหนดไฟล์ฐานข้อมูลที่ไม่ควรเก็บใน Git | กำหนดไฟล์ฐานข้อมูลที่ไม่ควรเก็บใน Git | ไฟล์ประกอบ repository |
| `database/factories/UserFactory.php` | สร้างข้อมูลผู้ใช้สำหรับชุดทดสอบ อ้าง Model User; ไม่ใช่บัญชี demo จาก Seeder | เชื่อม User.php | ไฟล์ช่วยทดสอบ |
| `database/migrations/0001_01_01_000001_create_cache_table.php` | สร้าง cache/cache_locks ให้ Laravel เก็บค่าชั่วคราวและ lock | artisan migrate → cache, cache_locks → Models/Controllers ที่อ่านตาราง; ไม่ส่งผู้ใช้ไปหน้าเว็บโดยตรง | ไฟล์เปลี่ยนโครงสร้าง DB |
| `database/migrations/0001_01_01_000002_create_jobs_table.php` | สร้าง jobs/job_batches/failed_jobs สำหรับคิวงาน | artisan migrate → jobs, job_batches, failed_jobs → Models/Controllers ที่อ่านตาราง; ไม่ส่งผู้ใช้ไปหน้าเว็บโดยตรง | ไฟล์เปลี่ยนโครงสร้าง DB |
| `database/seeders/AdminDemoSeeder.php` | บัญชี admin@example.com และ demo.student@example.com พร้อมรายงานตัวอย่างรุ่นเดิม | DatabaseSeeder หรือระบุ --class → Models/DB → เพิ่มข้อมูลเริ่มต้นในตาราง | Seeder เก่ายังเรียกเองได้ |
| `database/seeders/DatabaseSeeder.php` | จุดเริ่ม db:seed เรียก SubjectSeeder → ReportReasonSeeder → DemoWebsiteSeeder | artisan db:seed → DatabaseSeeder.php → SubjectSeeder, ReportReasonSeeder, DemoWebsiteSeeder | โค้ดในระบบ |
| `database/seeders/DemoBookingSeeder.php` | นัดหมายเก่า APPDEMO001 และ Contact ตัวอย่าง อาศัยบัญชี/ติวเตอร์จาก Seeder เก่า | DatabaseSeeder หรือระบุ --class → Models/DB → เพิ่มข้อมูลเริ่มต้นในตาราง | Seeder เก่ายังเรียกเองได้ |
| `database/seeders/DemoWebsiteSeeder.php` | ข้อมูลตัวอย่างชุดหลักที่เชื่อมตารางงานทั้งหมด: 7 บัญชี เวลาว่าง 6 นัดหมาย รีวิว โปรด แจ้งเตือน รายงานและหลักฐาน; ทำงานเฉพาะ local/testing | artisan db:seed → DemoWebsiteSeeder.php → SubjectSeeder | โค้ดในระบบ |
| `database/seeders/ReportReasonSeeder.php` | เพิ่มเหตุผลรายงาน 5 ข้อ สำหรับตัวเลือกในฟอร์ม; มีหัวข้อชำระเงินแต่ไม่ได้หมายความว่ามีระบบจ่ายเงิน | DatabaseSeeder หรือระบุ --class → Models/DB → เพิ่มข้อมูลเริ่มต้นในตาราง | โค้ดในระบบ |
| `database/seeders/SubjectSeeder.php` | เพิ่ม 7 วิชาพื้นฐานด้วยรหัสคงที่ ใช้ซ้ำได้โดยไม่สร้างแถวซ้ำ | DatabaseSeeder หรือระบุ --class → Models/DB → เพิ่มข้อมูลเริ่มต้นในตาราง | โค้ดในระบบ |
| `database/seeders/TutorDemoSeeder.php` | ชุดติวเตอร์ตัวอย่างเดิม mintra/thanakrit/pimchanok พร้อมวิชาและเวลาว่าง; ไม่ได้ถูก DatabaseSeeder ล่าสุดเรียก และไม่มีการอนุมัติอัตโนมัติ | artisan db:seed → TutorDemoSeeder.php → SubjectSeeder | Seeder เก่ายังเรียกเองได้ |
| `docs/CODE_GUIDE_TH.md` | คู่มือเริ่มอ่านโค้ดและคำศัพท์สำหรับทีม | ผู้พัฒนาอ่าน → ใช้เป็นแนวทางทำงาน/ทดลอง ไม่ถูก HTTP route เรียก | โค้ดในระบบ |
| `docs/DEMO_ACCOUNTS_TH.md` | รายการบัญชีตัวอย่าง รหัสผ่านทดลอง สถานะนัดหมาย และวิธีเรียก Seeder | ผู้พัฒนาอ่าน → ใช้เป็นแนวทางทำงาน/ทดลอง ไม่ถูก HTTP route เรียก | โค้ดในระบบ |
| `package-lock.json` | ล็อกเวอร์ชันแพ็กเกจ JavaScript สำหรับติดตั้งให้ตรงกัน | npm install / npm run dev หรือ build → Vite → assets ที่ Blade โหลด | โค้ดในระบบ |
| `package.json` | รายการ Vite/Tailwind/passkeys และคำสั่ง npm dev/build | npm install / npm run dev หรือ build → Vite → assets ที่ Blade โหลด | โค้ดในระบบ |
| `phpstan.neon` | ตั้งค่าตรวจชนิดข้อมูลด้วย PHPStan/Larastan | ตั้งค่าตรวจชนิดข้อมูลด้วย PHPStan/Larastan | โค้ดในระบบ |
| `phpunit.xml` | กำหนด PHPUnit ชุดทดสอบและ environment ขณะทดสอบ ไม่ใช่ค่ารันหน้าเว็บปกติ | กำหนด PHPUnit ชุดทดสอบและ environment ขณะทดสอบ ไม่ใช่ค่ารันหน้าเว็บปกติ | โค้ดในระบบ |
| `pint.json` | ตั้งกฎจัดรูปแบบ PHP ด้วย Laravel Pint | ตั้งกฎจัดรูปแบบ PHP ด้วย Laravel Pint | โค้ดในระบบ |
| `public/.htaccess` | กฎ Apache ส่ง URL เข้า public/index.php | กฎ Apache ส่ง URL เข้า public/index.php | โค้ดในระบบ |
| `public/favicon.svg` | ไอคอนแท็บเว็บที่ head โหลด | ไอคอนแท็บเว็บที่ head โหลด | โค้ดในระบบ |
| `public/index.php` | รับ HTTP request โหลด vendor/autoload และ bootstrap/app แล้วส่งคำขอเข้า Laravel | รับ HTTP request โหลด vendor/autoload และ bootstrap/app แล้วส่งคำขอเข้า Laravel | โค้ดในระบบ |
| `public/robots.txt` | คำแนะนำให้ crawler; ไม่ใช่ระบบตรวจสิทธิ์ | คำแนะนำให้ crawler; ไม่ใช่ระบบตรวจสิทธิ์ | โค้ดในระบบ |
| `resources/js/app.js` | ไฟล์ JavaScript entry ของ Vite ปัจจุบันว่าง; JavaScript หน้าหลักส่วนใหญ่อยู่ใน Blade | ไฟล์ JavaScript entry ของ Vite ปัจจุบันว่าง; JavaScript หน้าหลักส่วนใหญ่อยู่ใน Blade | โค้ดในระบบ |
| `resources/views/components/app-logo-icon.blade.php` | SVG ของโลโก้ที่ app-logo เรียกใช้ | รับจาก app-logo.blade.php, card.blade.php, simple.blade.php; แสดง HTML/ส่วนประกอบแก่หน้าแม่ ไม่บันทึก DB เอง | ส่วนประกอบหน้าตา |
| `resources/views/components/app-logo.blade.php` | แสดงโลโก้กับชื่อใน layout Starter Kit | รับจาก header.blade.php, sidebar.blade.php; ใช้ app-logo-icon.blade.php | ส่วนประกอบหน้าตา |
| `resources/views/components/desktop-user-menu.blade.php` | เมนูบัญชีบน layout Starter Kit เชื่อม settings และ logout | รับจาก header.blade.php, sidebar.blade.php; ปุ่ม/ฟอร์มไป route profile.edit, logout | ส่วนประกอบ Starter Kit |
| `resources/views/components/home-navbar.blade.php` | navbar ร่วม แยกเมนูตามบทบาท เปิดเมนูบัญชี สลับ role และ logout; ใช้ notification-nav-link สำหรับผู้ล็อกอิน | รับจาก tutor-page.blade.php, index.blade.php, show.blade.php; ปุ่ม/ฟอร์มไป route home, tutor.profile.edit, availabilities.index, appointments.index, tutor.application.status, tutor.search, tutor.ranking, student.profile.edit, role.switch, logout และ route อื่นในไฟล์; ใช้ nav-user-links.blade.php, notification-nav-link.blade.php | โค้ดในระบบ |
| `resources/views/components/nav-user-links.blade.php` | เมนูรายการโปรด นัดหมาย และ admin เฉพาะผู้ล็อกอิน | รับจาก home-navbar.blade.php, welcome.blade.php; ปุ่ม/ฟอร์มไป route tutor.favorites, appointments.index, admin.dashboard | โค้ดในระบบ |
| `resources/views/components/placeholder-pattern.blade.php` | ลายพื้นที่ตัวอย่างสำหรับ layout เริ่มต้น | รับข้อมูลจาก layout/Controller ตามสถานะที่ระบุ; แสดง HTML/ส่วนประกอบแก่หน้าแม่ ไม่บันทึก DB เอง | ส่วนประกอบหน้าตา |
| `resources/views/flux/icon/book-open-text.blade.php` | SVG ไอคอนหนังสือสำหรับเมนู Flux | รับข้อมูลจาก layout/Controller ตามสถานะที่ระบุ; แสดง HTML/ส่วนประกอบแก่หน้าแม่ ไม่บันทึก DB เอง | ส่วนประกอบหน้าตา |
| `resources/views/flux/icon/chevrons-up-down.blade.php` | SVG ไอคอนลูกศรเมนู Flux | รับข้อมูลจาก layout/Controller ตามสถานะที่ระบุ; แสดง HTML/ส่วนประกอบแก่หน้าแม่ ไม่บันทึก DB เอง | ส่วนประกอบหน้าตา |
| `resources/views/flux/icon/folder-git-2.blade.php` | SVG ไอคอน repository ในเมนู Flux | รับข้อมูลจาก layout/Controller ตามสถานะที่ระบุ; แสดง HTML/ส่วนประกอบแก่หน้าแม่ ไม่บันทึก DB เอง | ส่วนประกอบหน้าตา |
| `resources/views/flux/icon/layout-grid.blade.php` | SVG ไอคอน dashboard ในเมนู Flux | รับข้อมูลจาก layout/Controller ตามสถานะที่ระบุ; แสดง HTML/ส่วนประกอบแก่หน้าแม่ ไม่บันทึก DB เอง | ส่วนประกอบหน้าตา |
| `resources/views/flux/navlist/group.blade.php` | ปรับกลุ่มเมนูของ Flux ให้ใช้ใน layout | รับข้อมูลจาก layout/Controller ตามสถานะที่ระบุ; แสดง HTML/ส่วนประกอบแก่หน้าแม่ ไม่บันทึก DB เอง | ส่วนประกอบ Starter Kit |
| `resources/views/layouts/app.blade.php` | layout app ชั้นนอกของ Starter Kit ส่งเนื้อหาไป layout ภายใน | รับข้อมูลจาก layout/Controller ตามสถานะที่ระบุ; แสดง HTML/ส่วนประกอบแก่หน้าแม่ ไม่บันทึก DB เอง | ส่วนประกอบ Starter Kit |
| `resources/views/layouts/app/header.blade.php` | layout แบบ header/navigation ของ Starter Kit | รับข้อมูลจาก layout/Controller ตามสถานะที่ระบุ; ปุ่ม/ฟอร์มไป route dashboard; ใช้ app-logo.blade.php, desktop-user-menu.blade.php, head.blade.php | ส่วนประกอบ Starter Kit |
| `resources/views/layouts/app/sidebar.blade.php` | layout แบบ sidebar และเมนูบัญชีของ Starter Kit | รับข้อมูลจาก layout/Controller ตามสถานะที่ระบุ; ปุ่ม/ฟอร์มไป route dashboard, profile.edit, logout; ใช้ app-logo.blade.php, desktop-user-menu.blade.php, head.blade.php | ส่วนประกอบ Starter Kit |
| `resources/views/partials/head.blade.php` | meta title icon และ @vite สำหรับ layout Starter Kit; อ้าง resources/css/app.css ที่ไม่พบใน snapshot นี้ | รับจาก card.blade.php, simple.blade.php, split.blade.php; แสดง HTML/ส่วนประกอบแก่หน้าแม่ ไม่บันทึก DB เอง | มี asset อ้างอิงที่ขาด |
| `resources/views/partials/settings-heading.blade.php` | หัวข้อร่วมของหน้า settings | รับจาก ⚡appearance.blade.php, ⚡profile.blade.php, ⚡security.blade.php; แสดง HTML/ส่วนประกอบแก่หน้าแม่ ไม่บันทึก DB เอง | ส่วนประกอบ Starter Kit |
| `routes/console.php` | คำสั่ง inspire ตัวอย่างใน terminal ไม่ใช่ระบบนัดหมาย | คำสั่ง inspire ตัวอย่างใน terminal ไม่ใช่ระบบนัดหมาย | ตัวอย่าง Laravel |
| `routes/web.php` | จุดเชื่อม URL กับ Controller รวม home/บทบาท/จอง/รีวิว/admin และ public tutors; มี reset-password แบบตรงและ storage fallback | เชื่อม ContactController.php, RoleController.php, User.php, AppointmentController.php, NotificationController.php, SubjectController.php, ReportController.php, ReviewController.php, AvailabilityController.php, CheckScheduleController.php, AdminTutorController.php, FavoriteController.php, StudentProfileController.php, TutorController.php | โค้ดในระบบ |
| `tests/Feature/DashboardTest.php` | กรณี guest และผู้ล็อกอินเปิด dashboard; อธิบายสิ่งที่ไฟล์ตั้งใจตรวจ ไม่ใช่หลักฐานว่ารันผ่านล่าสุด | phpunit.xml → PHPUnit → คลาส/route ที่ระบุในไฟล์; ไม่มีผลต่อเว็บจนกว่าจะสั่งรัน | ชุดทดสอบเดิม ไม่ได้รันในงานรายงาน |
| `tests/Feature/ExampleTest.php` | กรณีเปิด home แล้วได้ HTTP สำเร็จ; อธิบายสิ่งที่ไฟล์ตั้งใจตรวจ ไม่ใช่หลักฐานว่ารันผ่านล่าสุด | phpunit.xml → PHPUnit → คลาส/route ที่ระบุในไฟล์; ไม่มีผลต่อเว็บจนกว่าจะสั่งรัน | ชุดทดสอบเดิม ไม่ได้รันในงานรายงาน |
| `tests/TestCase.php` | คลาสแม่ทดสอบ สร้าง Laravel app และ helper ข้ามกรณีที่ Fortify ไม่เปิดฟีเจอร์; อธิบายสิ่งที่ไฟล์ตั้งใจตรวจ ไม่ใช่หลักฐานว่ารันผ่านล่าสุด | phpunit.xml → PHPUnit → คลาส/route ที่ระบุในไฟล์; ไม่มีผลต่อเว็บจนกว่าจะสั่งรัน | ชุดทดสอบเดิม ไม่ได้รันในงานรายงาน |
| `tests/Unit/ExampleTest.php` | ตัวอย่าง assertion true ของ PHPUnit; อธิบายสิ่งที่ไฟล์ตั้งใจตรวจ ไม่ใช่หลักฐานว่ารันผ่านล่าสุด | phpunit.xml → PHPUnit → คลาส/route ที่ระบุในไฟล์; ไม่มีผลต่อเว็บจนกว่าจะสั่งรัน | ชุดทดสอบเดิม ไม่ได้รันในงานรายงาน |
| `vite.config.js` | กำหนด entry JS/CSS ฟอนต์และ dev server; มี resources/css/app.css ที่ยังไม่พบ | npm install / npm run dev หรือ build → Vite → assets ที่ Blade โหลด | ต้องตรวจ build input |

