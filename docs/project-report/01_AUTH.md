# 1. Auth / Register / Permission / Role

## 1.1 หน้าที่และข้อมูล

รับผิดชอบว่า “คนนี้เป็นใคร และใช้หน้าไหนได้” ข้อมูลหลักอยู่ใน users: user_id, email, password, first_name, last_name, role, current_role, is_active, disabled_reason และ profile_picture โดย role เป็นข้อมูลบทบาทของบัญชี ส่วน current_role คือบทบาทที่ใช้อยู่ตอนนี้ ทั้งคู่ไม่ควรถูกอธิบายว่าเป็นฟิลด์เดียวกัน

StudentProfile เก็บรายละเอียดนักเรียน และ TutorProfile เก็บใบสมัครผู้สอน บัญชีเดียวมีทั้งสองโปรไฟล์ได้ Contact เก็บข้อมูลติดต่อที่ผูกบัญชีด้วย Users_user_id

## 1.2 การสมัครสมาชิกทีละขั้น

1. welcome.blade.php เรียก register-modal.blade.php ผู้ใช้กรอกชื่อ อีเมล รหัสผ่าน และเลือก student/tutor
2. ถ้าเลือก tutor: tutor-registration-fields.blade.php เปิด tutor-fields.blade.php ให้กรอกประวัติ วิชา ประสบการณ์ รูปแบบสอน และข้อมูลติดต่อ
3. ฟอร์ม POST /register → Fortify → FortifyServiceProvider → CreateNewUser::create()
4. CreateNewUser ตรวจข้อมูล/อีเมลไม่ซ้ำ/รหัสผ่าน แล้วสร้าง users และ student_profiles ใน transaction
5. กรณีติวเตอร์เรียก TutorApplication::rules()/save() ซึ่งบันทึก tutor_profiles ตารางกลางวิชา contacts และรูปที่ public disk; ใบสมัครใหม่เป็น pending
6. ส่งแจ้งเตือน admin ผ่าน UserNotification; RegisterResponse พากลับ / แล้ว TutorController::profile() พาไปหน้าใบสมัครเมื่อยังไม่อนุมัติ

ผล: ผู้สมัครมีบัญชีเข้าสู่ระบบ แต่การสมัคร tutor ไม่ได้แปลว่าเปิดสอนได้ทันที

## 1.3 เข้าสู่ระบบ ออกจากระบบ และปิดบัญชี

1. GET /login ถูก provider redirect กลับ home พร้อม open_login เพื่อเปิด login-modal
2. POST /login → Fortify authenticateUsing ใน FortifyServiceProvider → User หา email → Hash ตรวจรหัสผ่าน
3. ถ้ารหัสผ่านถูกแต่ is_active=false: โยน validation error ด้วย User::disabledMessage() ให้ modal แสดงเหตุผล โดยไม่ยอมเข้าสู่ระบบ
4. ถ้าผ่าน: สร้าง session และกลับ /; routes/web.php เลือกหน้า admin/tutor/home ตาม current_role
5. ถ้า admin ปิดบัญชีระหว่างมี session: EnsureActiveAccount ที่ผูกกับ web middleware ออกจากระบบ invalidate session แล้วกลับ home พร้อมเหตุผล; เกิดตอนผู้ใช้ส่งคำขอถัดไป ไม่ใช่ push แบบทันที
6. ปุ่ม logout ใน navbar ส่ง POST route logout ของ Fortify ส่วน Logout.php เป็น Action สำหรับ Livewire เดิม

## 1.4 สลับบทบาทและขอบเขตสิทธิ์

home-navbar → POST /switch-role → RoleController::switchRole(). หากเลือก tutor และข้อมูลไม่ครบให้ไป tutor.onboarding; หากข้อมูลครบแต่ยังไม่ approved ให้ไป tutor.application.status; ผ่านแล้วจึงบันทึก current_role=tutor. หากเลือก student ให้มี StudentProfile ก่อน แล้วบันทึก role ปัจจุบันและกลับ home

| ผู้ใช้ | ทำได้ตามเส้นทางหลัก |
| --- | --- |
| ยังไม่ล็อกอิน | home ค้นหา จัดอันดับ และโปรไฟล์สาธารณะ; ไม่เห็นเมนูแจ้งเตือน |
| ล็อกอิน/บัญชีเปิด | ใช้หน้าโปรด จอง และแจ้งเตือนตามด่าน auth/active และตรวจเจ้าของใน Controller |
| tutor ที่ยังไม่ approved | ดู/แก้ใบสมัครและใช้บทบาทนักเรียน; เปิดสอน/รับคำขอไม่ได้ |
| tutor ที่ approved และ current_role=tutor | จัดการโปรไฟล์ เวลาสอน และนัดหมายของตน |
| current_role=admin | เข้าเส้นทาง /admin ที่อยู่หลัง role:admin |

auth เช็กล็อกอิน; active เช็กบัญชี; role เช็กบทบาท; การเช็กเจ้าของใน Controller เช็กข้อมูลแต่ละรายการ การซ่อนปุ่มบนหน้าเว็บอย่างเดียวไม่ใช่การตรวจสิทธิ์

## 1.5 ส่วนเพิ่ม เปลี่ยน และของเดิม

- เพิ่ม: TutorApplication และฟอร์มร่วมสำหรับสมัครติวเตอร์, ขั้นตอนรอตรวจ, ด่าน active และ disabled_reason พร้อมข้อความให้เจ้าของบัญชี
- เปลี่ยน: config/auth.php ใช้ User ใน Domains แก้ปัญหาอ้าง App\Models\User; login/register หลักเปิดเป็น modal ที่ home; สลับ tutor ต้องครบข้อมูลและอนุมัติก่อน
- เก็บไว้: หน้าล็อกอิน/สมัครเต็มและชุด settings/2FA/passkey จาก Starter Kit ยังมีไฟล์ ไม่ใช่หลักฐานว่าตัดทิ้งหรือใช้งานครบแล้ว
- จุดต้องรู้: web.php มี reset-password แบบเปลี่ยนตรงโดยไม่ตรวจ token ใน handler นั้น ไม่ควรอธิบายว่าเป็นระบบรีเซ็ตผ่านอีเมลที่ปลอดภัยครบแล้ว; settings บางไฟล์ยังใช้ App\Concerns และ name แบบเก่า; ContactController ยังใช้ student_id/view ที่ไม่ตรงโครงสร้างปัจจุบัน

## 1.6 แนวพูดพรีเซนต์

“ส่วนของเราจัดการตัวตนและสิทธิ์ สมาชิกสลับบทบาทได้ แต่ติวเตอร์ต้องกรอกโปรไฟล์และผ่านแอดมินก่อน ส่วนการปิดบัญชีมีเหตุผลให้ผู้ใช้รับทราบ และมีด่านตรวจฝั่งเซิร์ฟเวอร์ทุกครั้งที่ส่งคำขอ”

## รายไฟล์: หน้าที่ การส่งต่อ และผลที่เกิด

ชื่อไฟล์อ้างจากรากโปรเจกต์ จุดเชื่อมในตารางอ้างจากซอร์ส ไม่ใช่ทุกจุดที่ถูกเรียกขณะรันจริง ให้ดูคอลัมน์สถานะประกอบ สำหรับ route resource/Fortify ที่ไม่ได้ประกาศ method ทีละรายการให้อ่าน flow ด้านบนร่วมด้วย

| ไฟล์ | หน้าที่ / ผลที่เกิด | รับจาก / ส่งต่อ | สถานะ |
| --- | --- | --- | --- |
| `app/Domains/Auth/Actions/Fortify/CreateNewUser.php` | ตรวจฟอร์มสมัคร สร้าง User และ StudentProfile; ถ้าเลือกติวเตอร์เรียก TutorApplication เพื่อสร้างใบสมัครรออนุมัติ | เชื่อม PasswordValidationRules.php, User.php, StudentProfile.php, TutorApplication.php | โค้ดในระบบ |
| `app/Domains/Auth/Actions/Fortify/ResetUserPassword.php` | Action ของ Fortify ตรวจและเปลี่ยนรหัสผ่าน แต่ routes/web.php มีเส้นทางรีเซ็ตแบบตรงซ้อนอยู่ จึงต้องตรวจเส้นทางจริงก่อนนำเสนอ | เชื่อม PasswordValidationRules.php, User.php | มีเส้นทางซ้อน |
| `app/Domains/Auth/Concerns/PasswordValidationRules.php` | รวมกฎตรวจรหัสผ่านและรหัสผ่านปัจจุบันไว้ใช้ซ้ำใน Action สมัครและรีเซ็ต | รวมกฎตรวจรหัสผ่านและรหัสผ่านปัจจุบันไว้ใช้ซ้ำใน Action สมัครและรีเซ็ต | โค้ดในระบบ |
| `app/Domains/Auth/Concerns/ProfileValidationRules.php` | รวมกฎชื่อและอีเมลของหน้า settings เดิม; บางหน้า settings ยัง import namespace เก่า | เชื่อม User.php | Starter Kit/ต้องเชื่อมต่อ |
| `app/Domains/Auth/Http/Controllers/ContactController.php` | Controller ติดต่อเดิมค้นด้วย student_id และส่ง view student-contacts.edit ซึ่งไม่ตรงกับ contacts.Users_user_id และไม่พบ view ดังกล่าว | routes/web.php: /student-contacts/{studentId}/edit → edit(); /student-contacts/{studentId} → update(); ใช้ Contact.php | เส้นทางเดิมต้องแก้ |
| `app/Domains/Auth/Http/Controllers/RoleController.php` | รับ role ที่ขอสลับ ตรวจความครบถ้วนและอนุมัติของติวเตอร์ก่อนเปลี่ยน current_role แล้วส่งกลับหน้าที่เหมาะสม | routes/web.php: /switch-role → switchRole(); ใช้ StudentProfile.php | โค้ดในระบบ |
| `app/Domains/Auth/Http/Middleware/EnsureActiveAccount.php` | ตรวจ is_active ทุกคำขอเว็บ หากถูกปิดจะเก็บเหตุผล ออกจากระบบ ยกเลิก session และส่งข้อความกลับหน้าแรก | ตรวจ is_active ทุกคำขอเว็บ หากถูกปิดจะเก็บเหตุผล ออกจากระบบ ยกเลิก session และส่งข้อความกลับหน้าแรก | โค้ดในระบบ |
| `app/Domains/Auth/Http/Middleware/RoleMiddleware.php` | ตรวจ current_role ให้ตรงกับหน้าที่ขอ และตรวจ approval_status ก่อนใช้บทบาทติวเตอร์ | ตรวจ current_role ให้ตรงกับหน้าที่ขอ และตรวจ approval_status ก่อนใช้บทบาทติวเตอร์ | โค้ดในระบบ |
| `app/Domains/Auth/Livewire/Actions/Logout.php` | ออกจากระบบและยกเลิก session ผ่าน Livewire ของ Starter Kit | ออกจากระบบและยกเลิก session ผ่าน Livewire ของ Starter Kit | ส่วนประกอบ Starter Kit |
| `app/Domains/Auth/Models/Contact.php` | ข้อมูลติดต่อ LINE Discord Zoom Meet และสถานที่เริ่มต้น ผูกผู้ใช้ผ่าน Users_user_id | Controller/Service → Model → ตาราง contacts; ความสัมพันธ์ไป ตารางที่ระบุ | โค้ดในระบบ |
| `app/Domains/Auth/Models/User.php` | บัญชีหลัก users: ชื่อ อีเมล รหัสผ่าน role/current_role สถานะใช้งาน รูป และความสัมพันธ์; disabledMessage สร้างข้อความเหตุผลปิดบัญชี | Controller/Service → Model → ตาราง Users; ความสัมพันธ์ไป Favorite.php, StudentProfile.php, TutorProfile.php | โค้ดในระบบ |
| `app/Providers/FortifyServiceProvider.php` | กำหนดการ login/register/reset ใช้ User ใน Domains ตรวจรหัสผ่านและ is_active เปิด modal ผ่าน redirect และจำกัดความถี่ login | เชื่อม CreateNewUser.php, ResetUserPassword.php, User.php, confirm-password.blade.php, forgot-password.blade.php, reset-password.blade.php, two-factor-challenge.blade.php, verify-email.blade.php | โค้ดในระบบ |
| `config/auth.php` | กำหนด guard/session และ provider ชี้ App\Domains\Auth\Models\User แทน App\Models\User → Laravel และส่วนที่เรียก config ใช้ค่านี้ | เชื่อม User.php | โค้ดในระบบ |
| `config/fortify.php` | กำหนด ฟีเจอร์สมัคร รีเซ็ต email verification 2FA/passkeys และชื่อ field login → Laravel และส่วนที่เรียก config ใช้ค่านี้ | bootstrap/Laravel โหลดค่า → config() → บริการที่เกี่ยวข้อง (ชื่อค่าเป็นจุดเชื่อม ไม่ใช่ redirect) | โค้ดในระบบ |
| `database/migrations/0001_01_01_000000_create_users_table.php` | สร้าง users (PK user_id 10 ตัว) พร้อม password_reset_tokens และ sessions สำหรับ login | artisan migrate → users, password_reset_tokens, sessions → Models/Controllers ที่อ่านตาราง; ไม่ส่งผู้ใช้ไปหน้าเว็บโดยตรง | ไฟล์เปลี่ยนโครงสร้าง DB |
| `database/migrations/2024_01_01_000000_create_passkeys_table.php` | สร้าง passkeys ผูก users สำหรับฟีเจอร์กุญแจเข้าสู่ระบบ | artisan migrate → passkeys → Models/Controllers ที่อ่านตาราง; ไม่ส่งผู้ใช้ไปหน้าเว็บโดยตรง | ไฟล์เปลี่ยนโครงสร้าง DB |
| `database/migrations/2025_08_14_170933_add_two_factor_columns_to_users_table.php` | เพิ่ม secret รหัสสำรองและเวลายืนยัน 2FA ใน users | artisan migrate → users → Models/Controllers ที่อ่านตาราง; ไม่ส่งผู้ใช้ไปหน้าเว็บโดยตรง | ไฟล์เปลี่ยนโครงสร้าง DB |
| `database/migrations/2026_09_22_170954_add_current_role_to_users_table.php` | เพิ่ม current_role สำหรับบทบาทที่กำลังใช้งาน แยกจาก role เดิม | artisan migrate → users → Models/Controllers ที่อ่านตาราง; ไม่ส่งผู้ใช้ไปหน้าเว็บโดยตรง | ไฟล์เปลี่ยนโครงสร้าง DB |
| `database/migrations/2026_10_09_194915_create_contacts_table.php` | สร้าง contacts ผูก Users_user_id และข้อมูลห้องเรียน/ช่องทางติดต่อ | artisan migrate → contacts → Models/Controllers ที่อ่านตาราง; ไม่ส่งผู้ใช้ไปหน้าเว็บโดยตรง | ไฟล์เปลี่ยนโครงสร้าง DB |
| `database/migrations/2026_10_11_000004_add_disabled_reason_to_users.php` | เพิ่ม disabled_reason ใน users ให้แอดมินบันทึกและผู้ใช้เห็นเหตุผลปิดบัญชี | artisan migrate → users → Models/Controllers ที่อ่านตาราง; ไม่ส่งผู้ใช้ไปหน้าเว็บโดยตรง | ไฟล์เปลี่ยนโครงสร้าง DB |
| `public/CSS/auth.css` | หน้าตาของหน้าสมัคร/ลืม/เปลี่ยนรหัสผ่านแบบเต็ม | Blade โหลดผ่าน asset() → browser จัดหน้าตา; ผู้เรียก: forgot-password.blade.php, register.blade.php, reset-password.blade.php | โค้ดในระบบ |
| `resources/js/passkeys.js` | นำเข้า @laravel/passkeys ใส่ window.Passkeys และส่ง event passkeys:ready ให้ component ใช้งาน | นำเข้า @laravel/passkeys ใส่ window.Passkeys และส่ง event passkeys:ready ให้ component ใช้งาน | โค้ดในระบบ |
| `resources/views/components/auth-header.blade.php` | หัวเรื่องและคำอธิบายสำหรับหน้ารับรองตัวตนของ Starter Kit | รับจาก confirm-password.blade.php, login.blade.php, two-factor-challenge.blade.php; แสดง HTML/ส่วนประกอบแก่หน้าแม่ ไม่บันทึก DB เอง | ส่วนประกอบ Starter Kit |
| `resources/views/components/auth-session-status.blade.php` | แสดงข้อความ session เช่นสถานะส่งอีเมล/รีเซ็ต | รับจาก confirm-password.blade.php, login.blade.php; แสดง HTML/ส่วนประกอบแก่หน้าแม่ ไม่บันทึก DB เอง | ส่วนประกอบ Starter Kit |
| `resources/views/components/login-modal.blade.php` | กล่องเข้าสู่ระบบ ส่ง POST login และแสดงข้อผิดพลาดรวมถึงเหตุผลปิดบัญชี พร้อม JavaScript เปิด/ปิด | รับจาก show.blade.php, welcome.blade.php; ปุ่ม/ฟอร์มไป route login, password.request | โค้ดในระบบ |
| `resources/views/components/passkey-registration.blade.php` | เรียก browser passkey เพื่อสมัครกุญแจและแจ้ง Livewire | รับจาก ⚡security.blade.php; แสดง HTML/ส่วนประกอบแก่หน้าแม่ ไม่บันทึก DB เอง | ส่วนประกอบ Starter Kit |
| `resources/views/components/passkey-verify.blade.php` | ใช้ browser passkey เพื่อตรวจตัวตนร่วมกับ passkeys.js | รับจาก confirm-password.blade.php, login.blade.php; แสดง HTML/ส่วนประกอบแก่หน้าแม่ ไม่บันทึก DB เอง | ส่วนประกอบ Starter Kit |
| `resources/views/components/register-modal.blade.php` | กล่องสมัครสมาชิก ส่ง POST register และใช้ฟอร์มติวเตอร์ร่วมเมื่อเลือก tutor | รับจาก show.blade.php, welcome.blade.php; ปุ่ม/ฟอร์มไป route register; ใช้ tutor-registration-fields.blade.php | โค้ดในระบบ |
| `resources/views/components/tutor-registration-fields.blade.php` | เปิด/ปิดและ disable ฟอร์มติวเตอร์ตาม radio role ในหน้าสมัคร เพื่อส่งเฉพาะข้อมูลที่เกี่ยวข้อง | รับจาก register-modal.blade.php, register.blade.php; ใช้ tutor-fields.blade.php | โค้ดในระบบ |
| `resources/views/domains/auth/layouts/auth.blade.php` | wrapper สำหรับหน้ารับรองตัวตนผ่าน namespace layouts | รับข้อมูลจาก layout/Controller ตามสถานะที่ระบุ; แสดง HTML/ส่วนประกอบแก่หน้าแม่ ไม่บันทึก DB เอง | ส่วนประกอบ Starter Kit |
| `resources/views/domains/auth/layouts/auth/card.blade.php` | layout auth แบบการ์ด ใช้ partials.head และโลโก้ | รับข้อมูลจาก layout/Controller ตามสถานะที่ระบุ; ปุ่ม/ฟอร์มไป route home; ใช้ app-logo-icon.blade.php, head.blade.php | ส่วนประกอบ Starter Kit |
| `resources/views/domains/auth/layouts/auth/simple.blade.php` | layout auth แบบเรียบ ใช้ partials.head และ slot | รับข้อมูลจาก layout/Controller ตามสถานะที่ระบุ; ปุ่ม/ฟอร์มไป route home; ใช้ app-logo-icon.blade.php, head.blade.php | ส่วนประกอบ Starter Kit |
| `resources/views/domains/auth/layouts/auth/split.blade.php` | layout auth แบ่งสองส่วน ใช้ partials.head และ slot | รับข้อมูลจาก layout/Controller ตามสถานะที่ระบุ; ปุ่ม/ฟอร์มไป route home; ใช้ app-logo-icon.blade.php, head.blade.php | ส่วนประกอบ Starter Kit |
| `resources/views/domains/auth/pages/auth/confirm-password.blade.php` | หน้ากรอกรหัสผ่านก่อนเข้าหน้าที่ต้องยืนยันตัวตน เช่น security | รับจาก FortifyServiceProvider.php; ปุ่ม/ฟอร์มไป route password.confirm.store; ใช้ auth-header.blade.php, auth-session-status.blade.php, passkey-verify.blade.php | ส่วนประกอบ Starter Kit |
| `resources/views/domains/auth/pages/auth/forgot-password.blade.php` | ฟอร์มกรอกอีเมล ส่ง password.email ให้เส้นทางขอรีเซ็ต | รับจาก FortifyServiceProvider.php; ปุ่ม/ฟอร์มไป route home, password.email, login | โค้ดในระบบ |
| `resources/views/domains/auth/pages/auth/login.blade.php` | หน้าล็อกอินเต็มจาก Starter Kit; provider ปัจจุบันพา GET login กลับ home เพื่อเปิด modal | รับข้อมูลจาก layout/Controller ตามสถานะที่ระบุ; ปุ่ม/ฟอร์มไป route login.store, password.request, register; ใช้ auth-header.blade.php, auth-session-status.blade.php, passkey-verify.blade.php | เก็บไว้ ไม่ใช่หน้าล็อกอินหลัก |
| `resources/views/domains/auth/pages/auth/register.blade.php` | หน้าสมัครเต็มใช้ฟอร์มติวเตอร์ร่วม; GET register ปัจจุบันเปิด modal ที่ home | รับข้อมูลจาก layout/Controller ตามสถานะที่ระบุ; ปุ่ม/ฟอร์มไป route home, register, login; ใช้ tutor-registration-fields.blade.php | เก็บไว้ ไม่ใช่หน้าสมัครหลัก |
| `resources/views/domains/auth/pages/auth/reset-password.blade.php` | ฟอร์มรหัสผ่านใหม่และยืนยัน ส่ง password.update; ต้องแยกเส้นทางตรงใน web.php ออกจาก Fortify token flow | รับจาก FortifyServiceProvider.php; ปุ่ม/ฟอร์มไป route home, password.update, token, login | โค้ดในระบบ |
| `resources/views/domains/auth/pages/auth/two-factor-challenge.blade.php` | กรอกรหัส 2FA หรือรหัสกู้คืนแล้วส่ง Fortify | รับจาก FortifyServiceProvider.php; ปุ่ม/ฟอร์มไป route two-factor.login.store; ใช้ auth-header.blade.php | ฟีเจอร์ Starter Kit ยังต้องตรวจการเชื่อม |
| `resources/views/domains/auth/pages/auth/verify-email.blade.php` | แจ้งให้ยืนยันอีเมล ขอส่งใหม่ และออกจากระบบ | รับจาก FortifyServiceProvider.php; ปุ่ม/ฟอร์มไป route verification.send, logout | ฟีเจอร์ Starter Kit ยังต้องตรวจการเชื่อม |
| `resources/views/domains/auth/pages/settings/layout.blade.php` | เมนู settings profile/security/appearance ที่แต่ละหน้าใช้ร่วม | รับข้อมูลจาก layout/Controller ตามสถานะที่ระบุ; ปุ่ม/ฟอร์มไป route profile.edit, security.edit, appearance.edit | Starter Kit/namespace เดิม |
| `resources/views/domains/auth/pages/settings/two-factor/⚡recovery-codes.blade.php` | โหลด แสดง และสร้างรหัสสำรอง 2FA ใหม่ | รับข้อมูลจาก layout/Controller ตามสถานะที่ระบุ; แสดง HTML/ส่วนประกอบแก่หน้าแม่ ไม่บันทึก DB เอง | ส่วนประกอบ Starter Kit |
| `resources/views/domains/auth/pages/settings/⚡appearance.blade.php` | ตัวเลือกธีมสว่าง/มืด/ตามเครื่องผ่าน Flux | รับข้อมูลจาก layout/Controller ตามสถานะที่ระบุ; ใช้ settings-heading.blade.php | ส่วนประกอบ Starter Kit |
| `resources/views/domains/auth/pages/settings/⚡delete-user-form.blade.php` | ส่วนแบบฟอร์ม/ปุ่มเปิดการลบบัญชี ไปยัง modal ลบ | รับข้อมูลจาก layout/Controller ตามสถานะที่ระบุ; แสดง HTML/ส่วนประกอบแก่หน้าแม่ ไม่บันทึก DB เอง | ส่วนประกอบ Starter Kit |
| `resources/views/domains/auth/pages/settings/⚡delete-user-modal.blade.php` | ยืนยันรหัสผ่านก่อนลบบัญชีและออกจากระบบ | รับข้อมูลจาก layout/Controller ตามสถานะที่ระบุ; แสดง HTML/ส่วนประกอบแก่หน้าแม่ ไม่บันทึก DB เอง | ส่วนประกอบ Starter Kit |
| `resources/views/domains/auth/pages/settings/⚡profile.blade.php` | Livewire แก้ชื่ออีเมลและส่งยืนยันอีเมล แต่ยังใช้ name และ App\Concerns เดิมไม่ตรงโครงสร้างปัจจุบัน | รับข้อมูลจาก layout/Controller ตามสถานะที่ระบุ; ปุ่ม/ฟอร์มไป route dashboard; ใช้ settings-heading.blade.php | ต้องปรับก่อนใช้งาน |
| `resources/views/domains/auth/pages/settings/⚡security.blade.php` | Livewire เปลี่ยนรหัสผ่าน จัดการ passkey/2FA มีการอ้างอิงส่วนประกอบเดิม | รับข้อมูลจาก layout/Controller ตามสถานะที่ระบุ; ใช้ passkey-registration.blade.php, settings-heading.blade.php | ต้องตรวจการเชื่อม Starter Kit |
| `resources/views/domains/auth/pages/settings/⚡two-factor-setup-modal.blade.php` | ตั้งค่า QR/secret และยืนยันรหัสก่อนเปิด 2FA | รับข้อมูลจาก layout/Controller ตามสถานะที่ระบุ; แสดง HTML/ส่วนประกอบแก่หน้าแม่ ไม่บันทึก DB เอง | ส่วนประกอบ Starter Kit |
| `routes/settings.php` | เส้นทาง settings ของ Livewire และ passkey endpoints ยังอ้าง pages::settings ซึ่งต้องตรวจการลงทะเบียน namespace | เส้นทาง settings ของ Livewire และ passkey endpoints ยังอ้าง pages::settings ซึ่งต้องตรวจการลงทะเบียน namespace | Starter Kit/ต้องเชื่อมต่อ |
| `tests/Feature/Auth/AuthenticationTest.php` | กรณีหน้า login เข้าสู่ระบบ รหัสผิด 2FA และ logout; อธิบายสิ่งที่ไฟล์ตั้งใจตรวจ ไม่ใช่หลักฐานว่ารันผ่านล่าสุด | phpunit.xml → PHPUnit → คลาส/route ที่ระบุในไฟล์; ไม่มีผลต่อเว็บจนกว่าจะสั่งรัน | ชุดทดสอบเดิม ไม่ได้รันในงานรายงาน |
| `tests/Feature/Auth/EmailVerificationTest.php` | กรณียืนยันอีเมลและ hash ไม่ถูกต้อง; อธิบายสิ่งที่ไฟล์ตั้งใจตรวจ ไม่ใช่หลักฐานว่ารันผ่านล่าสุด | phpunit.xml → PHPUnit → คลาส/route ที่ระบุในไฟล์; ไม่มีผลต่อเว็บจนกว่าจะสั่งรัน | ชุดทดสอบเดิม ไม่ได้รันในงานรายงาน |
| `tests/Feature/Auth/PasswordConfirmationTest.php` | กรณีเปิดหน้ายืนยันรหัสผ่าน; อธิบายสิ่งที่ไฟล์ตั้งใจตรวจ ไม่ใช่หลักฐานว่ารันผ่านล่าสุด | phpunit.xml → PHPUnit → คลาส/route ที่ระบุในไฟล์; ไม่มีผลต่อเว็บจนกว่าจะสั่งรัน | ชุดทดสอบเดิม ไม่ได้รันในงานรายงาน |
| `tests/Feature/Auth/PasswordResetTest.php` | กรณีขอลิงก์ reset และเปลี่ยนรหัสผ่านด้วย token แบบ Starter Kit; อธิบายสิ่งที่ไฟล์ตั้งใจตรวจ ไม่ใช่หลักฐานว่ารันผ่านล่าสุด | phpunit.xml → PHPUnit → คลาส/route ที่ระบุในไฟล์; ไม่มีผลต่อเว็บจนกว่าจะสั่งรัน | ชุดทดสอบเดิม ไม่ได้รันในงานรายงาน |
| `tests/Feature/Auth/RegistrationTest.php` | กรณีเปิดหน้าสมัครและสร้างสมาชิกตามแบบ Starter Kit; อธิบายสิ่งที่ไฟล์ตั้งใจตรวจ ไม่ใช่หลักฐานว่ารันผ่านล่าสุด | phpunit.xml → PHPUnit → คลาส/route ที่ระบุในไฟล์; ไม่มีผลต่อเว็บจนกว่าจะสั่งรัน | ชุดทดสอบเดิม ไม่ได้รันในงานรายงาน |
| `tests/Feature/Auth/TwoFactorChallengeTest.php` | กรณีเข้าหน้า challenge 2FA ก่อน/หลังล็อกอิน; อธิบายสิ่งที่ไฟล์ตั้งใจตรวจ ไม่ใช่หลักฐานว่ารันผ่านล่าสุด | phpunit.xml → PHPUnit → คลาส/route ที่ระบุในไฟล์; ไม่มีผลต่อเว็บจนกว่าจะสั่งรัน | ชุดทดสอบเดิม ไม่ได้รันในงานรายงาน |
| `tests/Feature/Settings/ProfileUpdateTest.php` | กรณี settings แก้ชื่ออีเมลและลบบัญชี; อธิบายสิ่งที่ไฟล์ตั้งใจตรวจ ไม่ใช่หลักฐานว่ารันผ่านล่าสุด | phpunit.xml → PHPUnit → คลาส/route ที่ระบุในไฟล์; ไม่มีผลต่อเว็บจนกว่าจะสั่งรัน | ชุดทดสอบเดิม ไม่ได้รันในงานรายงาน |
| `tests/Feature/Settings/SecurityTest.php` | กรณี security เปลี่ยนรหัสผ่านและตั้งค่า 2FA; อธิบายสิ่งที่ไฟล์ตั้งใจตรวจ ไม่ใช่หลักฐานว่ารันผ่านล่าสุด | phpunit.xml → PHPUnit → คลาส/route ที่ระบุในไฟล์; ไม่มีผลต่อเว็บจนกว่าจะสั่งรัน | ชุดทดสอบเดิม ไม่ได้รันในงานรายงาน |

