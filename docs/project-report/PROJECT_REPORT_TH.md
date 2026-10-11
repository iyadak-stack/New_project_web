# รายงานโครงสร้างและการทำงานของ PeerTutor

เอกสารส่งต่อทีมและเตรียมพรีโปรเจกต์ · ตรวจจากซอร์สใน workspace วันที่ 11 ตุลาคม 2026

## วิธีใช้รายงาน

อ่านภาพรวมก่อน จากนั้นสมาชิกแต่ละคนอ่านหน้าที่ของตัวเอง พร้อมตารางรายไฟล์ท้ายหัวข้อนั้น ทุกหัวข้อมีลำดับการทำงาน ส่วนที่เพิ่ม/เปลี่ยน/ตัด และประเด็นที่ต้องรู้ก่อนเดโม ไฟล์ CSV ใช้ค้นชื่อไฟล์หรือกรองหมวดใน Excel ได้ ส่วน HTML เปิดในเบราว์เซอร์และพิมพ์เป็น PDF ได้

รายงานนี้อธิบายสถานะโค้ดที่ตรวจพบ ไม่ได้อ้างว่าทุกหน้าทดสอบผ่านหรือพร้อมใช้งานครบทั้งหมด งานครั้งนี้อ่านซอร์สและจัดทำเอกสาร ไม่แก้ระบบและไม่รันทดสอบ การมีไฟล์อยู่ไม่ได้แปลว่าหน้าปัจจุบันเรียกใช้ไฟล์นั้น

## ขอบเขตไฟล์

มีรายการ 234 ไฟล์ แยกเป็น 5 หน้าที่และส่วนกลาง รวม Controllers, Models, Services, Requests, Middleware, Policy, Blade, CSS/JS, migrations, seeders, tests, config และเครื่องมือโปรเจกต์ มีทั้งไฟล์หลักและไฟล์เดิมเพื่อไม่ให้ทีมเข้าใจว่าไฟล์เก่าถูกลบไปแล้ว

ไม่แจกแจงภายใน vendor/node_modules, build/cache/log, session จริง, ไฟล์ฐานข้อมูลจริง และไฟล์ผู้ใช้อัปโหลด เพราะเป็นแพ็กเกจหรือข้อมูลที่สร้างขณะรัน ไม่ใช่ซอร์สหน้าที่ที่ทีมพัฒนา รายงานบอกตำแหน่ง .env แต่ไม่อ่านหรือเผยแพร่ค่าในนั้น ไฟล์รายงานที่สร้างในรอบนี้ไม่รวมในจำนวนซอร์ส 234 ไฟล์

คำว่า “เพิ่ม/เปลี่ยน” อ้างตามงานที่ตกลงและโค้ดปัจจุบัน ไม่ได้ใช้สถานะ Git เพียงอย่างเดียวตัดสินว่าใครเป็นผู้เขียน ไม่มีหลักฐานประวัติครบพอที่จะระบุผู้แต่งทุกไฟล์

## ภาพรวมที่อธิบายให้อาจารย์ฟังได้

PeerTutor ให้ผู้เรียนค้นหาติวเตอร์ ดูโปรไฟล์ เลือกช่วงเวลาที่ผู้สอนเปิดไว้ และส่งคำขอจอง ผู้สอนต้องผ่านแอดมินก่อนจึงรับคำขอได้ เมื่อรับแล้วผู้เรียนจึงเห็นข้อมูลห้องเรียน หลังผ่านเวลาสิ้นสุดผู้เรียนยืนยันเรียนจบและเขียนรีวิวได้ แอดมินตรวจใบสมัคร รายงาน และสถานะบัญชี

ลำดับหลัก: ผู้เยี่ยมชม → สมัคร/เข้าสู่ระบบ → ค้นหาติวเตอร์ → ดูโปรไฟล์ → เลือกเวลา → ส่งคำขอ pending → ติวเตอร์รับ confirmed → เรียน → นักเรียนยืนยัน completed → รีวิว → คะแนนและแจ้งเตือนติวเตอร์

ลำดับไฟล์ของคำขอเว็บ: public/index.php → bootstrap/app.php → middleware → routes/web.php หรือ route ของ Fortify → Controller/Action → Model และฐานข้อมูล → Blade → HTML ที่ browser แสดง

## คำศัพท์ที่ต้องรู้

| คำ | ความหมายแบบสั้น |
| --- | --- |
| Route | บอกว่า URL และวิธี GET/POST/PATCH จะเรียกฟังก์ชันไหน |
| Controller | รับคำขอ ตรวจข้อมูล เรียกข้อมูล แล้วเลือกหน้าที่จะส่งกลับ |
| Model | ตัวแทนตารางและความสัมพันธ์ในฐานข้อมูล |
| Blade/View | ไฟล์หน้าตาเว็บ รับตัวแปรมาแสดงและมีฟอร์มส่งคำขอ |
| Middleware | ด่านตรวจผู้ใช้ก่อนเข้าฟังก์ชัน เช่น ล็อกอิน บทบาท และบัญชีเปิดใช้งาน |
| Request/validate | กฎตรวจข้อมูล เช่น ต้องกรอกและเวลาจบต้องหลังเริ่ม |
| Service | รวมขั้นตอนที่เรียกใช้ร่วมกัน เช่น สมัครติวเตอร์หรือคำนวณเวลาว่าง |
| Migration | คำสั่งสร้าง/เปลี่ยนโครงสร้างตาราง ไม่ใช่ข้อมูลตัวอย่าง |
| Seeder | เพิ่มข้อมูลตัวอย่างหรือข้อมูลพื้นฐาน |
| Policy/Gate | กฎว่าใครมีสิทธิ์แก้หรือลบข้อมูลรายการนั้น |
| Session/flash | ข้อมูลผู้ล็อกอินและข้อความชั่วคราวหลัง redirect |
| Transaction/lock | บันทึกหลายขั้นตอนเป็นชุดและกันคำขอพร้อมกันแก้ข้อมูลซ้ำ |
| Soft delete | ทำเครื่องหมาย deleted_at แทนลบแถวทันที |
| Component/slot | ชิ้นหน้าตาที่ใช้ซ้ำ และตำแหน่งใส่เนื้อหาของหน้า |

## แผนแบ่งงานของทีม

| หมวด | สิ่งที่รับผิดชอบ | ส่งงานต่อให้ |
| --- | --- | --- |
| 1 Auth/Register/Permission/Role | บัญชี การเข้าสู่ระบบ บทบาท และด่านสิทธิ์ | ทุกหมวดใช้ User และผู้ล็อกอินปัจจุบัน |
| 2 CheckSchedule & Time Matching | เวลาสอนและตรรกะเวลาไม่ทับกัน | หมวด 3 แสดงช่องเวลา หมวด 4 ตรวจซ้ำก่อนจอง |
| 3 Tutor Profiles, Search & Ranking | ข้อมูลติวเตอร์ วิชา หน้า home ค้นหา อันดับ รายการโปรด | หมวด 4 รับ tutor_id/subject_id/availability_id; หมวด 5 ตรวจใบสมัคร |
| 4 Appointment, Subject & Notification | การจอง สถานะ ห้องเรียน วิชา แจ้งเตือน | หมวด 5 รับนัดหมายที่เรียนจบเพื่อรีวิว |
| 5 Review, Report & Admin Operation | รีวิว รายงาน อนุมัติ และปิดบัญชี | คะแนนกลับหมวด 3; สิทธิ์/ปิดบัญชีกลับหมวด 1 |


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


# 3. Tutor Profiles, Search & Ranking

## 3.1 หน้าที่

ให้ผู้เรียนเห็นว่าใครสอนอะไร มีประสบการณ์เท่าไร รีวิวเป็นอย่างไร และว่างเมื่อไร ไฟล์แกนคือ TutorController, TutorProfile, FavoriteController และชุด Blade tutor-profile. ข้อมูลผู้ใช้มาจาก users, วิชามาจาก subjects ผ่าน tutor_profiles_has_subjects, คะแนนและความเห็นมาจาก reviews

## 3.2 Home ค้นหา และจัดอันดับ

- GET / → routes/web.php → TutorController::home() เมื่อเป็น guest/student → TutorProfile::approved() โหลด user/subjects จำนวน reviews → เรียง average_rating มากก่อน แล้ว experience_years → welcome หรือ student/student
- home ตัด Users_user_id ที่ตรงกับบัญชีตนเอง เพื่อไม่แสดงการ์ดตัวเอง; อย่าเหมารวมว่าทุกหน้าค้นหา/อันดับตัดตัวเองด้วย
- GET /tutors?search=คำค้น → search() ค้นชื่อ นามสกุล หรือชื่อวิชา → search.blade.php. ไม่ใช่ระบบค้นด้วย AI
- GET /tutors/ranking → ranking() → ranking.blade.php ใช้คะแนนเฉลี่ยและประสบการณ์เป็นเกณฑ์ ไม่ใช่จำนวนผู้ติดตาม/ยอดจอง
- GET /tutors/{tutorProfile} → show() ตรวจ approved/active หรือเป็นเจ้าของ → โหลดวิชา รีวิวและเวลาที่จองได้ → show.blade.php

ทั้งสามหน้าสาธารณะเปิดโดยไม่สมัครได้ แต่ปุ่มที่ทำให้เกิดข้อมูลใหม่ เช่น จอง/โปรด/รายงาน ยังส่งเข้าทาง auth

## 3.3 โปรไฟล์ผู้สอนและการอนุมัติ

editProfile() → edit-profile.blade.php → tutor-fields.blade.php → updateProfile() → TutorApplication::save(). ฟอร์มร่วมกับหน้าสมัครใช้กฎเดียวกัน ลดกรณีสมัครแล้วข้อมูลติวเตอร์ว่าง

บันทึก bio/experience/teaching_mode ใน tutor_profiles, subjects ในตารางกลาง, ลิงก์/สถานที่ใน contacts, รูปใน public disk และ path ใน users.profile_picture. หากยังไม่ approved จะเข้าสู่ pending แล้วให้หมวด 5 ตรวจ; หากผ่านอยู่แล้ว การแก้โปรไฟล์ทั่วไปยังคง approved ตามโค้ดปัจจุบัน

profile() → dashboard.blade.php แสดง pending/confirmed ของตน เวลาว่างข้างหน้าและรีวิว; เมนูจัดการอยู่ home-navbar สำหรับ tutor ที่ approved

## 3.4 รายการโปรดและโปรไฟล์นักเรียน

ปุ่มหัวใจ → POST tutor.favorite.store → FavoriteController ตรวจอนุมัติ แล้วหาแถวเดิมรวม soft delete; ถ้ามีให้ restore ถ้าไม่มีให้ create → redirect กลับพร้อมข้อความ. ปุ่มเอาออกใช้ destroyTutor ลบเฉพาะ user ปัจจุบัน. หน้า tutor.favorites โหลดรายการของบัญชีและกรองติวเตอร์ที่ approved/active

StudentProfileController แยกดู/แก้นักเรียน: ชื่อกับรูปบันทึก users; bio บันทึก student_profiles. รูปโหลด asset('storage/...') โดย web.php มี fallback อ่าน public disk เมื่อเครื่อง Windows ไม่สร้าง storage link

## 3.5 ส่วนเพิ่ม เปลี่ยน และส่วนเดิม

- เพิ่ม: ฟอร์มติวเตอร์ร่วม, หน้า application-status, dashboard ที่ใช้ข้อมูลจริง, รายการโปรด, รูปจาก public disk และ route fallback
- เปลี่ยน: home แสดงติวเตอร์จาก DB และไม่แสดงตัวเอง; รายชื่อสาธารณะเฉพาะ approved/active; guest ดูข้อมูลได้; ช่องค้นหาออกจาก navbar แต่ยังมีแบบฟอร์มค้นหาในหน้าค้นหา
- ย้าย: ปุ่มจัดการโปรไฟล์/วิชา เวลาว่าง และคำขอ ไป navbar เฉพาะ tutor; แจ้งเตือนแสดงเฉพาะผู้ล็อกอิน
- เก็บไว้: tutor/profile.blade.php เดิมไม่ได้เป็นหน้า profile หลักแล้ว; tutor/tutor.blade.php ยังเป็น layout ของ ranking/บาง profile และมี dashboard ตัวอย่างคงที่ใน fallback
- ข้อจำกัดที่ต้องรู้: total_teaching_seconds มีใน DB แต่ complete ปัจจุบันไม่ได้เพิ่มชั่วโมงสะสม จึงไม่ควรพรีเซนต์ว่าเป็นสถิติชั่วโมงที่อัปเดตครบแล้ว

## 3.6 แนวพูดพรีเซนต์

“เราคัดติวเตอร์ที่ผ่านการอนุมัติและยังเปิดบัญชีมาแสดง ค้นได้จากชื่อและวิชา จัดอันดับจากคะแนนเฉลี่ย และใช้ตารางกลางให้หนึ่งติวเตอร์สอนได้หลายวิชา ส่วนรายการโปรดเก็บแยกตามสมาชิก”

## รายไฟล์: หน้าที่ การส่งต่อ และผลที่เกิด

ชื่อไฟล์อ้างจากรากโปรเจกต์ จุดเชื่อมในตารางอ้างจากซอร์ส ไม่ใช่ทุกจุดที่ถูกเรียกขณะรันจริง ให้ดูคอลัมน์สถานะประกอบ สำหรับ route resource/Fortify ที่ไม่ได้ประกาศ method ทีละรายการให้อ่าน flow ด้านบนร่วมด้วย

| ไฟล์ | หน้าที่ / ผลที่เกิด | รับจาก / ส่งต่อ | สถานะ |
| --- | --- | --- | --- |
| `app/Domains/TutorProfile/Http/Controllers/FavoriteController.php` | เพิ่ม คืนรายการเดิม หรือลบรายการโปรดของบัญชีปัจจุบัน และแสดงติวเตอร์ที่ยังอนุมัติ/ใช้งานได้ | routes/web.php: /tutor/{tutorProfile}/favorite → storeTutor(); /tutor/{tutorProfile}/favorite/remove → destroyTutor(); /tutors/favorites → tutorFavorites(); ใช้ Favorite.php, TutorProfile.php, favorites.blade.php | โค้ดในระบบ |
| `app/Domains/TutorProfile/Http/Controllers/StudentProfileController.php` | แสดง/แก้โปรไฟล์นักเรียน บันทึกชื่อใน users ประวัติใน student_profiles และรูปลง public disk | routes/web.php: /student/profile → profile(); /student/profile/edit → editProfile(); /student/profile → updateProfile(); ใช้ StudentProfile.php, edit-profile.blade.php, profile.blade.php | โค้ดในระบบ |
| `app/Domains/TutorProfile/Http/Controllers/TutorController.php` | home/search/ranking/show อ่านติวเตอร์ที่อนุมัติและบัญชีเปิดใช้งาน; profile แสดง dashboard; edit/updateProfile ใช้ TutorApplication; show ตัดเวลาที่ถูกจองออก | routes/web.php: /tutor/application-status → applicationStatus(); /tutor/onboarding → editProfile(); /tutor/onboarding → updateProfile(); /tutor/profile → profile(); /tutor/profile/edit → editProfile(); /tutor/profile → updateProfile(); /tutors → search(); ดู flow ในหัวข้องานสำหรับ method อื่น; ใช้ Contact.php, Appointment.php, Subject.php, Availability.php, Favorite.php, TutorProfile.php, TutorApplication.php, application-status.blade.php, dashboard.blade.php, edit-profile.blade.php, ranking.blade.php, search.blade.php, show.blade.php, welcome.blade.php | โค้ดในระบบ |
| `app/Domains/TutorProfile/Models/Favorite.php` | เชื่อมผู้ใช้ ติวเตอร์ และวิชาใน favorites ลบแบบ soft delete เพื่อคืนรายการเดิมได้ | Controller/Service → Model → ตาราง favorites; ความสัมพันธ์ไป User.php, Subject.php | โค้ดในระบบ |
| `app/Domains/TutorProfile/Models/StudentProfile.php` | โปรไฟล์นักเรียนผูก user_id และเก็บ bio; มี HasUuids ใน Model ต้องระวังเส้นทางสร้างที่ไม่ได้ระบุ id 10 ตัวเอง | Controller/Service → Model → ตาราง student_profiles; ความสัมพันธ์ไป User.php | โค้ดในระบบ |
| `app/Domains/TutorProfile/Models/TutorProfile.php` | โปรไฟล์ติวเตอร์ เชื่อม User วิชา นัดหมาย รีวิว; approved คัดผู้พร้อมสอน และ isComplete ตรวจข้อมูลขั้นต่ำ | Controller/Service → Model → ตาราง tutor_profiles; ความสัมพันธ์ไป User.php, Appointment.php, Subject.php, Review.php | โค้ดในระบบ |
| `app/Domains/TutorProfile/Services/TutorApplication.php` | กฎและขั้นตอนสมัครติวเตอร์ร่วมสำหรับสมัครใหม่/สลับบทบาท/แก้โปรไฟล์ บันทึกวิชา ติดต่อ รูป และแจ้งแอดมินเมื่อรอตรวจ | เชื่อม Contact.php, User.php, UserNotification.php, TutorProfile.php | โค้ดในระบบ |
| `database/migrations/2026_09_14_140329_create_tutor_profiles_table.php` | สร้าง tutor_profiles ใช้ tutor_id และ Users_user_id ไม่ใช่ id/user_id แบบที่โค้ด scheduling เก่าบางตัวใช้ | artisan migrate → tutor_profiles → Models/Controllers ที่อ่านตาราง; ไม่ส่งผู้ใช้ไปหน้าเว็บโดยตรง | ไฟล์เปลี่ยนโครงสร้าง DB |
| `database/migrations/2026_09_14_140446_create_favorites_table.php` | สร้าง favorites ผูก user/tutor/subject มี deleted_at สำหรับ soft delete | artisan migrate → favorites → Models/Controllers ที่อ่านตาราง; ไม่ส่งผู้ใช้ไปหน้าเว็บโดยตรง | ไฟล์เปลี่ยนโครงสร้าง DB |
| `database/migrations/2026_09_18_032630_create_tutor_profiles_has_subject_table.php` | สร้างตารางกลางติวเตอร์–วิชา หนึ่งคนสอนได้หลายวิชา หนึ่งวิชามีหลายคน | artisan migrate → tutor_profiles_has_subjects → Models/Controllers ที่อ่านตาราง; ไม่ส่งผู้ใช้ไปหน้าเว็บโดยตรง | ไฟล์เปลี่ยนโครงสร้าง DB |
| `database/migrations/2026_09_22_163929_create_student_profiles_table.php` | สร้าง student_profiles ใช้ id 10 ตัวและ user_id ไม่ซ้ำ | artisan migrate → student_profiles → Models/Controllers ที่อ่านตาราง; ไม่ส่งผู้ใช้ไปหน้าเว็บโดยตรง | ไฟล์เปลี่ยนโครงสร้าง DB |
| `public/CSS/edit-profile.css` | จัดหน้าฟอร์มแก้โปรไฟล์นักเรียน | Blade โหลดผ่าน asset() → browser จัดหน้าตา; ผู้เรียก: edit-profile.blade.php | โค้ดในระบบ |
| `public/CSS/home.css` | หน้าตาหลัก การ์ด ปุ่ม home-card-button และ navbar; แยกจาก .btn เพื่อไม่ทับ Bootstrap | Blade โหลดผ่าน asset() → browser จัดหน้าตา; ผู้เรียก: tutor-page.blade.php, create.blade.php, index.blade.php, show.blade.php, index.blade.php, show.blade.php | โค้ดในระบบ |
| `public/CSS/profile-card.css` | กรอบเมนูบัญชี รูปโปรไฟล์ และปุ่มสลับบทบาท | Blade โหลดผ่าน asset() → browser จัดหน้าตา; ผู้เรียก: tutor-page.blade.php, index.blade.php, show.blade.php, index.blade.php, edit-profile.blade.php, student.blade.php | โค้ดในระบบ |
| `public/CSS/tutor-dashboard.css` | โครง dashboard/layout ติวเตอร์เดิม | Blade โหลดผ่าน asset() → browser จัดหน้าตา; ผู้เรียก: tutor.blade.php | โค้ดในระบบ |
| `public/CSS/tutor-detail.css` | หน้าโปรไฟล์สาธารณะ การ์ดวิชา รีวิว และพื้นที่จอง | Blade โหลดผ่าน asset() → browser จัดหน้าตา; ผู้เรียก: show.blade.php | โค้ดในระบบ |
| `public/CSS/tutor-navbar.css` | หน้าตา navbar ติวเตอร์เดิม บางหน้ายังโหลดไฟล์ | Blade โหลดผ่าน asset() → browser จัดหน้าตา; ผู้เรียก: create.blade.php, index.blade.php, show.blade.php, favorites.blade.php, search.blade.php, show.blade.php | โค้ดในระบบ |
| `public/CSS/tutor-profile-edit.css` | จัดฟอร์มแก้โปรไฟล์ติวเตอร์ | Blade โหลดผ่าน asset() → browser จัดหน้าตา; ผู้เรียก: ตรวจการโหลดตาม layout ของหน้า | โค้ดในระบบ |
| `public/CSS/tutor-ranking.css` | อันดับและการ์ดติวเตอร์ยอดนิยม | Blade โหลดผ่าน asset() → browser จัดหน้าตา; ผู้เรียก: ranking.blade.php, tutor.blade.php | โค้ดในระบบ |
| `resources/views/components/tutor-card.blade.php` | แสดงข้อมูลการ์ดติวเตอร์ รูป ชื่อ คะแนน รีวิว วิชา และลิงก์ tutor.show | รับจาก student.blade.php, welcome.blade.php; ปุ่ม/ฟอร์มไป route tutor.show | โค้ดในระบบ |
| `resources/views/components/tutor-fields.blade.php` | ฟอร์มติวเตอร์ร่วม: bio ประสบการณ์ รูปแบบ วิชา รูป และข้อมูลติดต่อ/ห้องเรียนเริ่มต้น | รับจาก tutor-registration-fields.blade.php, edit-profile.blade.php; แสดง HTML/ส่วนประกอบแก่หน้าแม่ ไม่บันทึก DB เอง | โค้ดในระบบ |
| `resources/views/components/tutor-navbar.blade.php` | navbar ติวเตอร์เดิม ก่อนย้ายหน้าหลักไปใช้ home-navbar | รับข้อมูลจาก layout/Controller ตามสถานะที่ระบุ; ปุ่ม/ฟอร์มไป route home, tutor.search, tutor.ranking, tutor.favorites, appointments.index, admin.dashboard, notifications.index, role.switch, profile.edit, login | ส่วนประกอบเดิม ไม่พบการเรียกจากหน้าหลัก |
| `resources/views/components/tutor-page.blade.php` | โครงหน้าติเตอร์แบบใหม่ ใช้ navbar ร่วม Bootstrap ข้อความสำเร็จ/ผิดพลาด และ slot เนื้อหา | รับจาก edit.blade.php, index.blade.php, application-status.blade.php; ใช้ home-navbar.blade.php | โค้ดในระบบ |
| `resources/views/domains/tutor-profile/student/edit-profile.blade.php` | ฟอร์มชื่อ ประวัติ รูปนักเรียน ส่ง student.profile.update | รับจาก StudentProfileController.php; ปุ่ม/ฟอร์มไป route student.profile.update, tutor.profile, home; ใช้ home-navbar.blade.php | โค้ดในระบบ |
| `resources/views/domains/tutor-profile/student/profile.blade.php` | หน้าดูโปรไฟล์นักเรียนจาก StudentProfileController และสืบทอด layout tutor เดิม | รับจาก StudentProfileController.php; ปุ่ม/ฟอร์มไป route student.profile.edit; ใช้ tutor.blade.php | โค้ดในระบบ |
| `resources/views/domains/tutor-profile/student/student.blade.php` | หน้า home สำหรับผู้ล็อกอิน แสดงติวเตอร์/วิชายอดนิยมและ navbar ร่วม | รับข้อมูลจาก layout/Controller ตามสถานะที่ระบุ; ปุ่ม/ฟอร์มไป route tutor.search; ใช้ home-navbar.blade.php, tutor-card.blade.php | โค้ดในระบบ |
| `resources/views/domains/tutor-profile/tutor/application-status.blade.php` | หน้ารออนุมัติ/ไม่ผ่าน/ผ่านแล้ว พร้อมส่งแก้ใบสมัครหรือสลับบทบาท | รับจาก TutorController.php; ปุ่ม/ฟอร์มไป route role.switch, tutor.onboarding; ใช้ tutor-page.blade.php | โค้ดในระบบ |
| `resources/views/domains/tutor-profile/tutor/dashboard.blade.php` | หน้าหลักติวเตอร์ปัจจุบัน: จำนวนคำขอ นัดหมาย เวลาสอน รีวิว และลิงก์จัดการ | รับจาก TutorController.php; ปุ่ม/ฟอร์มไป route appointments.show; ใช้ tutor-page.blade.php | โค้ดในระบบ |
| `resources/views/domains/tutor-profile/tutor/edit-profile.blade.php` | ฟอร์มสมัคร/แก้ติวเตอร์ ใช้ tutor-fields และส่ง onboarding หรือ profile.update ตามสถานะ | รับจาก TutorController.php; ปุ่ม/ฟอร์มไป route tutor.onboarding.store, tutor.profile.update, home; ใช้ tutor-fields.blade.php, tutor-page.blade.php | โค้ดในระบบ |
| `resources/views/domains/tutor-profile/tutor/favorites.blade.php` | รายการติวเตอร์โปรดของผู้ใช้ มี navbar ร่วมและลิงก์ไปโปรไฟล์ | รับจาก FavoriteController.php; ปุ่ม/ฟอร์มไป route tutor.show, tutor.favorite.destroy, tutor.search; ใช้ home-navbar.blade.php | โค้ดในระบบ |
| `resources/views/domains/tutor-profile/tutor/profile.blade.php` | หน้าดูโปรไฟล์ติวเตอร์เดิม แต่ profile action ปัจจุบันใช้ dashboard แทน | รับข้อมูลจาก layout/Controller ตามสถานะที่ระบุ; ปุ่ม/ฟอร์มไป route tutor.profile.edit; ใช้ tutor.blade.php | ไม่ใช่หน้าหลักปัจจุบัน |
| `resources/views/domains/tutor-profile/tutor/ranking.blade.php` | แสดงลำดับติวเตอร์ตามข้อมูลที่ ranking ส่งมา และปุ่มดูโปรไฟล์/รายการโปรด | รับจาก TutorController.php; ปุ่ม/ฟอร์มไป route tutor.show, tutor.favorite.destroy, tutor.favorite.store, tutor.search; ใช้ tutor.blade.php | โค้ดในระบบ |
| `resources/views/domains/tutor-profile/tutor/search.blade.php` | ฟอร์มค้นหาติวเตอร์และรายวิชา พร้อมการ์ดผลลัพธ์ กดต่อไปโปรไฟล์สาธารณะ | รับจาก TutorController.php; ปุ่ม/ฟอร์มไป route tutor.search, tutor.show, home; ใช้ home-navbar.blade.php | โค้ดในระบบ |
| `resources/views/domains/tutor-profile/tutor/show.blade.php` | โปรไฟล์สาธารณะ วิชา รีวิว เวลาที่เปิดสอน และฟอร์มเลือก availability_id เพื่อส่งคำขอจอง | รับจาก TutorController.php; ปุ่ม/ฟอร์มไป route home, tutor.favorite.destroy, tutor.favorite.store, reports.create, login, appointments.store; ใช้ home-navbar.blade.php, login-modal.blade.php, register-modal.blade.php | โค้ดในระบบ |
| `resources/views/domains/tutor-profile/tutor/tutor.blade.php` | layout เดิมยังใช้กับ ranking และหน้า profile บางหน้า; ส่วน dashboard สำรองในไฟล์มีข้อมูลคงที่ | รับจาก profile.blade.php, profile.blade.php, ranking.blade.php; ปุ่ม/ฟอร์มไป route appointments.index, notifications.index; ใช้ home-navbar.blade.php | layout ยังใช้ แต่ dashboard สำรองเป็นตัวอย่าง |
| `resources/views/welcome.blade.php` | home ของ guest ดูติวเตอร์และวิชา เปิด login/register modal แสดงเหตุผลปิดบัญชี และไม่มีเมนูแจ้งเตือน | รับจาก TutorController.php; ปุ่ม/ฟอร์มไป route home, tutor.ranking, tutor.search; ใช้ login-modal.blade.php, nav-user-links.blade.php, register-modal.blade.php, tutor-card.blade.php | โค้ดในระบบ |


# 4. Appointment, Subject & Notification

## 4.1 ข้อมูลเข้าและข้อตกลงระหว่างทีม

ฟอร์ม tutor/show ส่ง subject_id, tutor_id, availability_id. AppointmentController ใช้ Auth::id() เป็นผู้เรียนและอ่านวันเวลาจาก Availability เอง. ตาราง appointments เก็บ Subject_subject_id, Tutor_profiles_tutor_id และ Student_profiles_student_id ซึ่งข้อมูลใหม่ใช้รหัส user แม้ชื่อคอลัมน์จะดูเหมือนรหัสโปรไฟล์; บางรายการเก่าใช้ student_profiles.id จึงมีโค้ดรองรับสองแบบ

## 4.2 การจองและเปลี่ยนสถานะ

1. POST /appointments → store() validate → ตรวจ tutor approved/active, ห้ามจองตัวเอง, วิชาอยู่กับติวเตอร์, availability เป็นของติวเตอร์และอยู่อนาคต
2. ล็อก TutorProfile ใน transaction แล้วตรวจ appointments ช่วงทับที่ไม่ cancelled → สร้าง Appointment status=pending → สร้าง Notification ให้ผู้สอน → กลับ tutor.show พร้อมข้อความส่งคำขอสำเร็จ
3. GET /appointments → index() โหลดข้อมูลที่บัญชีเป็นผู้เรียนหรือผู้สอน → index.blade.php; GET รายการเดียว → show() ตรวจเจ้าของ → show.blade.php
4. POST status หรือ confirm → updateStatus() ตรวจเจ้าของติวเตอร์ current_role=tutor และ approved; เปลี่ยนได้จาก pending เท่านั้น → confirmed หรือ cancelled พร้อมเหตุผลปฏิเสธ → แจ้งนักเรียน
5. การยกเลิกผ่าน cancel() อนุญาตเจ้าของนักเรียนหรือผู้สอนที่มีสิทธิ์ เฉพาะ pending/confirmed → แจ้งอีกฝ่าย
6. POST complete → ล็อกนัดหมาย ตรวจเจ้าของนักเรียนและ Appointment::canComplete() ซึ่งต้อง confirmed และผ่าน end_datetime → completed → แจ้งติวเตอร์ → กลับ index พร้อม review_appointment เพื่อเปิด modal

| สถานะ | ความหมาย | การใช้งานสำคัญ |
| --- | --- | --- |
| pending | ส่งคำขอแล้วรอติวเตอร์รับ | นักเรียนยังไม่เห็นห้องเรียน/ยืนยันจบไม่ได้ |
| confirmed | ติวเตอร์รับแล้ว | แสดงข้อมูลห้องเรียน; ยืนยันจบได้เมื่อเวลาจบผ่าน |
| completed | นักเรียนยืนยันจบแล้ว | สร้างรีวิวได้หนึ่งครั้ง |
| cancelled | ปฏิเสธหรือยกเลิก | ไม่กันช่องเวลาเหมือนนัดที่ยังใช้งาน |

index อ่านนัดหมายที่เกี่ยวกับบัญชีทั้งสองบทบาท ไม่ได้กรองรายชื่อทั้งหมดตาม current_role อย่างเดียว ปุ่มจัดการจึงต้องตรวจทั้งเจ้าของและ role อีกชั้น

## 4.3 รายละเอียดห้องเรียนและการแก้เฉพาะนัด

contacts เป็นข้อมูลเริ่มต้นของติวเตอร์ ส่วน appointments เก็บข้อมูลห้องเรียนเฉพาะครั้ง. ตอนสร้างนัดเก็บค่าติดต่อที่มี ณ ตอนนั้น; ถ้าของนัดว่างและยังไม่ปรับเอง lessonValue จึง fallback ไปโปรไฟล์ การแก้โปรไฟล์ภายหลังไม่จำเป็นต้องเปลี่ยนค่าที่ snapshot ลงนัดไว้แล้ว

show.blade.php → PATCH appointments.lesson → updateLesson() → บันทึก meeting_zoom/meeting_meet/meeting_location/lesson_notes และ lesson_details_customized=true. ล้างช่องแล้วบันทึกทำให้ว่างจริง ไม่ดึงค่าโปรไฟล์กลับเอง. ปุ่ม use_profile_defaults ล้างค่าของนัดและ flag เพื่อกลับไปใช้โปรไฟล์ (รวมล้าง lesson_notes ตามโค้ด)

student เห็น lesson-details เฉพาะ confirmed/completed. ลิงก์เป็นค่าที่ผู้สอนกรอก ไม่ได้สร้างห้อง Zoom/Meet ผ่าน API และระบบไม่ได้ตรวจว่าห้องเปิดอยู่จริง

## 4.4 วิชาและแจ้งเตือน

SubjectSeeder → subjects → Subject model → ฟอร์ม tutor-fields เลือกวิชา → TutorApplication sync ตารางกลาง → tutor/show เลือกเฉพาะวิชาของผู้สอน → store ตรวจซ้ำก่อนจอง. SubjectController มี CRUD บางส่วน แต่เส้นทาง resource มี action มากกว่าที่ implement อยู่

แจ้งเตือนการจองสร้างโดย AppointmentController; ใบสมัคร/อนุมัติใช้ UserNotification. NotificationController::index อ่านเฉพาะ Users_user_id ปัจจุบัน; markAsRead ตรวจเจ้าของแล้วตั้ง is_read. notification-nav-link นับที่ยังไม่อ่านและแสดง badge. เป็นการอ่านข้อมูลเมื่อโหลดหน้า ไม่ใช่ WebSocket, browser push, SMS หรืออีเมลอัตโนมัติ

## 4.5 ส่วนเพิ่ม เปลี่ยน และของเดิม

- เพิ่ม: หน้านัดหมาย/รายละเอียด ห้องเรียนเฉพาะนัด การยืนยันจบ modal รีวิว และแจ้งเตือนพร้อม badge
- เปลี่ยน: บังคับ pending ที่ server; ต้องรับและหมดเวลาเรียนก่อนยืนยันจบ; ปุ่ม Bootstrap ไม่ถูก .btn ของหน้า home ทับและใช้ align-items-start กันยืด
- เปลี่ยน: แบบจองหลักเลือก availability แทนวันอิสระ
- เก็บไว้แต่ไม่ตรง flow: appointments/create ยังมี route แต่ไม่ส่ง availability_id; ฟอร์ม tutor/show มี grade_level แต่ store ไม่บันทึกฟิลด์นี้ อย่าอธิบายว่าเก็บระดับชั้นครบแล้ว
- ยังขาด: SubjectController ไม่มี create/show/edit ตาม resource; AppointmentController ไม่มี destroy แม้ resource ลงทะเบียนไว้; NotificationController ไม่มี destroy แม้ route ลบยังอยู่ ต้องแยกจากปุ่ม/flow ที่ทำงานจริง

## 4.6 แนวพูดพรีเซนต์

“การกดจองคือส่งคำขอ ไม่ใช่ยืนยันเรียนทันที เราใช้สถานะควบคุมปุ่มและตรวจซ้ำที่ server ห้องเรียนแก้แยกตามนัดได้ และหลังเรียนจบจึงเปิดรีวิว ข้อความแจ้งเตือนเก็บในฐานข้อมูลเพื่อให้เจ้าของบัญชีกลับมาอ่านได้”

## รายไฟล์: หน้าที่ การส่งต่อ และผลที่เกิด

ชื่อไฟล์อ้างจากรากโปรเจกต์ จุดเชื่อมในตารางอ้างจากซอร์ส ไม่ใช่ทุกจุดที่ถูกเรียกขณะรันจริง ให้ดูคอลัมน์สถานะประกอบ สำหรับ route resource/Fortify ที่ไม่ได้ประกาศ method ทีละรายการให้อ่าน flow ด้านบนร่วมด้วย

| ไฟล์ | หน้าที่ / ผลที่เกิด | รับจาก / ส่งต่อ | สถานะ |
| --- | --- | --- | --- |
| `app/Domains/Booking/Http/Controllers/AppointmentController.php` | ศูนย์กลางนัดหมาย: list/show/store/updateStatus/updateLesson/complete/storeReview; ตรวจสิทธิ์และสถานะก่อนเขียนข้อมูล ส่งข้อความใน notifications | routes/web.php: /appointments/{appointment}/lesson → updateLesson(); /appointments/{id}/status → updateStatus(); /appointments/{appointment}/complete → complete(); /appointments/{appointment}/review → storeReview(); /appointments/{appointment}/confirm → confirm(); /appointments/{appointment}/cancel → cancel(); ใช้ Appointment.php, Notification.php, NotificationType.php, Subject.php, Review.php, Availability.php, StudentProfile.php, TutorProfile.php, create.blade.php, index.blade.php, show.blade.php | โค้ดในระบบ |
| `app/Domains/Booking/Http/Controllers/NotificationController.php` | แสดงแจ้งเตือนของผู้ใช้ปัจจุบัน และ markAsRead ตรวจเจ้าของก่อนบันทึก is_read; route ลบยังไม่มี method destroy | routes/web.php: /notifications → index(); /notifications/{id}/read → markAsRead(); /notifications/{notification} → destroy(); ใช้ Notification.php, index.blade.php | มีส่วนต้องเชื่อมให้ครบ |
| `app/Domains/Booking/Http/Controllers/SubjectController.php` | อ่านรายวิชา เพิ่ม แก้ ลบ subjects; resource route มีบาง action ที่ Controller ยังไม่มี และไม่ได้จำกัด CRUD เฉพาะ admin | เชื่อม Subject.php, index.blade.php | มีส่วนต้องเชื่อมให้ครบ |
| `app/Domains/Booking/Models/Appointment.php` | แทน appointments เชื่อมวิชา ผู้เรียน ติวเตอร์ รีวิว; lessonValue เลือกห้องเรียน และ canComplete ตรวจรับคำขอแล้ว/หมดเวลาเรียน | Controller/Service → Model → ตาราง appointments; ความสัมพันธ์ไป User.php, Review.php, StudentProfile.php, TutorProfile.php | โค้ดในระบบ |
| `app/Domains/Booking/Models/Notification.php` | ข้อความแจ้งเตือน ผู้รับ สถานะอ่าน และความสัมพันธ์ประเภทแจ้งเตือน | Controller/Service → Model → ตาราง notifications; ความสัมพันธ์ไป ตารางที่ระบุ | โค้ดในระบบ |
| `app/Domains/Booking/Models/NotificationType.php` | ประเภทของแจ้งเตือน เชื่อมหนึ่งประเภทกับหลายข้อความ | Controller/Service → Model → ตาราง notification_types; ความสัมพันธ์ไป ตารางที่ระบุ | โค้ดในระบบ |
| `app/Domains/Booking/Models/Subject.php` | ข้อมูลรายวิชา เชื่อมนัดหมายและติวเตอร์ผ่านตารางกลาง โดยรายชื่อติวเตอร์ใช้เงื่อนไข approved | Controller/Service → Model → ตาราง subjects; ความสัมพันธ์ไป TutorProfile.php | โค้ดในระบบ |
| `app/Domains/Booking/Services/UserNotification.php` | ฟังก์ชันส่งแจ้งเตือนงานติวเตอร์ ใช้จากใบสมัครและการอนุมัติ; การจองมี notify ของตัวเองอีกชุด | เชื่อม Notification.php, NotificationType.php | โค้ดในระบบ |
| `database/migrations/2026_09_25_082910_create_subjects_table.php` | สร้าง subjects มี subject_id และ subject_name | artisan migrate → subjects → Models/Controllers ที่อ่านตาราง; ไม่ส่งผู้ใช้ไปหน้าเว็บโดยตรง | ไฟล์เปลี่ยนโครงสร้าง DB |
| `database/migrations/2026_09_25_082922_create_notification_types_table.php` | สร้างประเภทแจ้งเตือน notification_types | artisan migrate → notification_types → Models/Controllers ที่อ่านตาราง; ไม่ส่งผู้ใช้ไปหน้าเว็บโดยตรง | ไฟล์เปลี่ยนโครงสร้าง DB |
| `database/migrations/2026_09_25_082928_create_appointments_table.php` | สร้าง appointments สถานะ เวลา วิชา รหัสติวเตอร์/ผู้เรียน และ soft delete | artisan migrate → appointments → Models/Controllers ที่อ่านตาราง; ไม่ส่งผู้ใช้ไปหน้าเว็บโดยตรง | ไฟล์เปลี่ยนโครงสร้าง DB |
| `database/migrations/2026_09_25_082932_create_notifications_table.php` | สร้างข้อความแจ้งเตือน ผู้รับ ประเภท และ is_read | artisan migrate → notifications → Models/Controllers ที่อ่านตาราง; ไม่ส่งผู้ใช้ไปหน้าเว็บโดยตรง | ไฟล์เปลี่ยนโครงสร้าง DB |
| `database/migrations/2026_09_29_163355_bridge_booking_schema.php` | เพิ่มคอลัมน์นัดหมาย/แจ้งเตือนที่ขาดให้ schema เดิม; down ลบคอลัมน์เหล่านี้ จึงต้องอ่านก่อน rollback | artisan migrate → appointments, notifications → Models/Controllers ที่อ่านตาราง; ไม่ส่งผู้ใช้ไปหน้าเว็บโดยตรง | ไฟล์เปลี่ยนโครงสร้าง DB |
| `database/migrations/2026_10_11_000003_add_lesson_override_flag.php` | เพิ่ม lesson_details_customized แยกการล้างข้อมูลนัดหมายเองออกจากยังไม่เคยตั้งค่า | artisan migrate → appointments → Models/Controllers ที่อ่านตาราง; ไม่ส่งผู้ใช้ไปหน้าเว็บโดยตรง | ไฟล์เปลี่ยนโครงสร้าง DB |
| `public/CSS/notifications.css` | หน้าตารายการแจ้งเตือน | Blade โหลดผ่าน asset() → browser จัดหน้าตา; ผู้เรียก: index.blade.php | โค้ดในระบบ |
| `public/CSS/subject-show.css` | หน้าตารายละเอียดวิชาเดิม | Blade โหลดผ่าน asset() → browser จัดหน้าตา; ผู้เรียก: show.blade.php | โค้ดในระบบ |
| `resources/views/components/lesson-details.blade.php` | อ่าน Appointment.lessonValue แล้วแสดง Zoom/Meet สถานที่ LINE และโน้ตตามที่หน้าหลักอนุญาต | รับจาก index.blade.php, show.blade.php; แสดง HTML/ส่วนประกอบแก่หน้าแม่ ไม่บันทึก DB เอง | โค้ดในระบบ |
| `resources/views/components/notification-nav-link.blade.php` | นับแจ้งเตือนยังไม่อ่านของผู้ใช้ แสดง badge; ไม่แสดงอะไรสำหรับ guest | รับจาก home-navbar.blade.php; ปุ่ม/ฟอร์มไป route notifications.index | โค้ดในระบบ |
| `resources/views/components/subject-card.blade.php` | การ์ดวิชาตัวอย่างใช้ Tailwind และปุ่มดูติวเตอร์ยังไม่ผูก route | รับข้อมูลจาก layout/Controller ตามสถานะที่ระบุ; แสดง HTML/ส่วนประกอบแก่หน้าแม่ ไม่บันทึก DB เอง | ส่วนประกอบตัวอย่าง |
| `resources/views/domains/booking/appointments/create.blade.php` | ฟอร์มจองเดิมเลือกวันเองและมีรหัส fallback แต่ไม่ส่ง availability_id ที่ store ปัจจุบันต้องการ | รับจาก AppointmentController.php; ปุ่ม/ฟอร์มไป route home, appointments.store, appointments.index | มี route แต่ไม่ตรงกับ flow หลัก |
| `resources/views/domains/booking/appointments/index.blade.php` | รายการนัดหมายของบัญชี ปุ่มรับ/ปฏิเสธ/ยืนยันจบ และ Bootstrap modal รีวิว; เปิด modal หลังจบหรือจาก hash link | รับจาก AppointmentController.php; ปุ่ม/ฟอร์มไป route appointments.show, appointments.complete, tutor.show, appointments.review, tutor.search; ใช้ home-navbar.blade.php, lesson-details.blade.php | โค้ดในระบบ |
| `resources/views/domains/booking/appointments/show.blade.php` | รายละเอียดนัดหมาย สถานะ ห้องเรียน ฟอร์มปรับเฉพาะนัดหมาย และปุ่มตามสิทธิ์ | รับจาก AppointmentController.php; ปุ่ม/ฟอร์มไป route appointments.index, appointments.lesson, appointments.complete, tutor.show; ใช้ home-navbar.blade.php, lesson-details.blade.php | โค้ดในระบบ |
| `resources/views/domains/booking/notifications/index.blade.php` | รายการแจ้งเตือนของผู้ใช้ กดอ่านผ่าน NotificationController และใช้ navbar ร่วม | รับจาก NotificationController.php; ปุ่ม/ฟอร์มไป route home; ใช้ home-navbar.blade.php | โค้ดในระบบ |
| `resources/views/domains/booking/subjects/index.blade.php` | หน้ารายวิชาพร้อมฟอร์มจัดการ เรียก SubjectController | รับจาก SubjectController.php; แสดง HTML/ส่วนประกอบแก่หน้าแม่ ไม่บันทึก DB เอง | ต้องทบทวนสิทธิ์ CRUD |
| `resources/views/domains/booking/subjects/show.blade.php` | หน้ารายละเอียดวิชาเดิม แต่ SubjectController ยังไม่มี show | รับข้อมูลจาก layout/Controller ตามสถานะที่ระบุ; ปุ่ม/ฟอร์มไป route home, tutor.show | ยังเชื่อม route ไม่ครบ |


# 5. Review, Report & Admin Operation

## 5.1 รีวิวหลังเรียน

appointments/index.blade.php เปิด Bootstrap modal → POST appointments.review → AppointmentController::storeReview(). ฟังก์ชันล็อกนัดหมาย ตรวจเจ้าของนักเรียน status=completed และยังไม่มี review; validate rating 1–5/Comment; สร้าง Review ผ่านความสัมพันธ์ของนัด ใช้ Tutor_profiles_tutor_id จากนัดจริง ไม่เชื่อรหัสติวเตอร์จากฟอร์ม → คำนวณ average_rating → แจ้งติวเตอร์ → redirect กลับนัดหมาย

ผลแสดงที่ TutorController::profile() → dashboard.blade.php ส่วน reviews และ TutorController::show() → โปรไฟล์สาธารณะ. reviews.index/create เดิมเปลี่ยนเป็น redirect; reviews.store เดิมส่งต่อ storeReview เพื่อใช้กฎเดียวกัน

ReviewController::edit/update/destroy ยังใช้ Gate::authorize กับ ReviewPolicy อนุญาตเจ้าของหรือ current_role=admin. จุดแยกสำคัญ: update/destroy ปัจจุบันยังไม่ได้คำนวณ average_rating ใหม่เหมือนตอนสร้างรีวิว จึงอาจทำให้คะแนนที่เก็บกับรีวิวที่เหลือไม่ตรงกัน

## 5.2 รายงานติวเตอร์

โปรไฟล์ติวเตอร์ → GET reports.create?tutor_id=... → ReportController::create() โหลดติวเตอร์และ ReportReason → reports/create.blade.php → POST reports.store → ตรวจข้อมูล/ห้ามรายงานตัวเอง → Report status=pending

reports.Users_user_id เป็นผู้รายงาน; Reported_users_user_id เป็นผู้ถูกรายงาน; ReportReason_Reason_id เป็นเหตุผล. การแยกผู้รายงานกับผู้ถูกกล่าวถึงช่วยให้หน้า admin ไม่แสดงคนผิด

/admin/reports → index → รายการ; /admin/reports/{report} → show → รายละเอียด; PATCH → updateStatus → investigating/resolved/rejected. เป็นการเปลี่ยนสถานะรายงาน ไม่ได้ปิดบัญชีอัตโนมัติและยังไม่มีข้อความตอบกลับผู้รายงานอัตโนมัติใน handler นี้

## 5.3 แอดมินอนุมัติใบสมัคร

/admin/tutors → AdminTutorController::index → admin/tutors.blade.php ดูชื่อ ประวัติ วิชา ติดต่อ และสถานะ. ปุ่มอนุมัติ/ไม่อนุมัติส่ง PATCH → update(): ล็อกโปรไฟล์ ตรวจ pending และห้ามอนุมัติตนเอง ตรวจข้อมูลครบเมื่ออนุมัติ → บันทึก approval_status/reviewed_at/reviewed_by/reject_reason → UserNotification ส่งผลให้ผู้สมัคร

การตรวจเป็นการพิจารณาข้อมูลที่ผู้ใช้กรอก ยังไม่มีระบบตรวจบัตรประชาชนหรือเอกสารยืนยันตัวตนจากหน่วยงานภายนอก อย่าพรีเซนต์ว่าเป็นการยืนยันตัวบุคคลอัตโนมัติ

## 5.4 แอดมินจัดการบัญชี

/admin/users → ReportController::users() → admin/users/index.blade.php. ปิดบัญชีส่ง disabled_reason ที่บังคับกรอก → toggleUserStatus() ห้ามปิดบัญชี admin ที่กำลังใช้อยู่เอง → is_active=false + เหตุผล; เปิดคืน → true และล้างเหตุผล

ส่งผลไปสองหมวด: หมวด 1 ปฏิเสธ login/ออกจาก session พร้อมเหตุผล และหมวด 3 คัดติวเตอร์ที่ปิดบัญชีออกจากรายชื่อสาธารณะ. เข้าแอดมินผ่าน login ปกติและ /admin ใช้ role:admin; admin-navbar มี logout และลิงก์ไปงานแต่ละส่วน

## 5.5 ส่วนเพิ่ม เปลี่ยน และสิ่งที่ยังไม่มี

- เพิ่ม: Report/ReportReason model และผู้ถูกรายงาน, admin dashboard/รายงาน/ผู้ใช้/ใบสมัคร, เหตุผลปิดบัญชี, การแจ้งผลอนุมัติและรีวิวใหม่
- เปลี่ยน: รีวิวผูกกับนัดหมายจริงแทน hidden ID ตัวอย่าง; หน้าสร้างรีวิวหลักเป็น modal หลังยืนยันจบ
- เก็บไว้: reviews/create/index แบบเดิมยังเป็นไฟล์อยู่ แต่ Controller ปัจจุบันไม่ได้ใช้เป็นหน้าหลัก
- มีเพียงตาราง/ข้อมูลตัวอย่าง: report_evidences และไฟล์หลักฐานจาก Seeder; ยังไม่มี Model/Form/Controller อัปโหลดและหน้า admin ดูหลักฐานครบวงจร
- ไม่ได้อยู่ในระบบที่ทำแล้ว: การจ่ายเงิน คืนเงิน วิดีโอคอลในเว็บ การสร้างห้องประชุมจริง และการตรวจเอกสารตัวตน แม้มีคำเกี่ยวข้องในข้อมูลตัวอย่างหรือเหตุผลรายงาน

## 5.6 แนวพูดพรีเซนต์

“รีวิวเกิดจากนัดที่เรียนจบจริงและสร้างได้ครั้งเดียว แอดมินตรวจใบสมัครก่อนเปิดสิทธิ์ผู้สอน รับรายงาน และระบุเหตุผลเมื่อปิดบัญชี โดยเราแยกสถานะรายงาน สถานะบัญชี และสถานะใบสมัครออกจากกัน”

## รายไฟล์: หน้าที่ การส่งต่อ และผลที่เกิด

ชื่อไฟล์อ้างจากรากโปรเจกต์ จุดเชื่อมในตารางอ้างจากซอร์ส ไม่ใช่ทุกจุดที่ถูกเรียกขณะรันจริง ให้ดูคอลัมน์สถานะประกอบ สำหรับ route resource/Fortify ที่ไม่ได้ประกาศ method ทีละรายการให้อ่าน flow ด้านบนร่วมด้วย

| ไฟล์ | หน้าที่ / ผลที่เกิด | รับจาก / ส่งต่อ | สถานะ |
| --- | --- | --- | --- |
| `app/Domains/Reportreview/Http/Controllers/Admin/ReportController.php` | รับรายงานติวเตอร์และตรวจห้ามรายงานตัวเอง; ฝั่ง admin ดูสถิติ รายงาน ผู้ใช้ เปลี่ยนสถานะ และปิดบัญชีพร้อมเหตุผล | routes/web.php: /reports/create → create(); /reports → store(); / → dashboard(); /reports → index(); /reports/{report} → show(); /reports/{report} → updateStatus(); /users → users(); ดู flow ในหัวข้องานสำหรับ method อื่น; ใช้ User.php, Report.php, ReportReason.php, TutorProfile.php, dashboard.blade.php, index.blade.php, show.blade.php, index.blade.php, create.blade.php | โค้ดในระบบ |
| `app/Domains/Reportreview/Http/Controllers/ReviewController.php` | เส้นทางรีวิวเดิมส่งการสร้างต่อ AppointmentController; index/create เปลี่ยนเป็น redirect; edit/update/delete ตรวจ Gate ก่อนดำเนินการ | routes/web.php: /reviews → index(); /reviews/create → create(); /reviews → store(); /reviews/{review}/edit → edit(); /reviews/{review} → update(); /reviews/{review} → destroy(); ใช้ AppointmentController.php, Appointment.php, Review.php, edit.blade.php | โค้ดในระบบ |
| `app/Domains/Reportreview/Models/Report.php` | รายงานเชื่อมเหตุผล ผู้รายงาน ผู้ถูกรายงาน และโปรไฟล์ติวเตอร์ ทำให้ admin แยกทั้งสองฝ่ายได้ | Controller/Service → Model → ตาราง reports; ความสัมพันธ์ไป User.php, TutorProfile.php | โค้ดในระบบ |
| `app/Domains/Reportreview/Models/ReportReason.php` | รายการเหตุผลรายงาน ไม่มี timestamps; ส่งตัวเลือกให้ฟอร์มรายงาน | Controller/Service → Model → ตาราง report_reasons; ความสัมพันธ์ไป ตารางที่ระบุ | โค้ดในระบบ |
| `app/Domains/Reportreview/Models/Review.php` | รีวิวคะแนน 1–5 และ Comment ผูกนัดหมาย/ติวเตอร์ มี soft delete | Controller/Service → Model → ตาราง reviews; ความสัมพันธ์ไป Appointment.php, TutorProfile.php | โค้ดในระบบ |
| `app/Domains/Reportreview/Policies/ReviewPolicy.php` | กำหนดเจ้าของรีวิวหรือบทบาท admin แก้/ลบได้ ใช้ผ่าน Gate ใน ReviewController | เชื่อม User.php, Review.php | โค้ดในระบบ |
| `app/Domains/TutorProfile/Http/Controllers/AdminTutorController.php` | แอดมินดูใบสมัคร เรียงรอตรวจก่อน อนุมัติหรือปฏิเสธพร้อมเหตุผล บันทึกผู้ตรวจและส่งแจ้งเตือน | routes/web.php: /tutors → index(); /tutors/{tutorProfile} → update(); ใช้ UserNotification.php, TutorProfile.php, tutors.blade.php | งาน 5 ร่วมกับงาน 3 |
| `database/migrations/2026_09_29_154823_create_reviews_table.php` | สร้าง reviews คะแนน Comment นัดหมายและติวเตอร์ พร้อม soft delete | artisan migrate → reviews → Models/Controllers ที่อ่านตาราง; ไม่ส่งผู้ใช้ไปหน้าเว็บโดยตรง | ไฟล์เปลี่ยนโครงสร้าง DB |
| `database/migrations/2026_09_29_163329_create_reports_table.php` | สร้าง reports ผู้รายงาน เหตุผล สถานะ และรายละเอียด; FK report_reasons อยู่ใน migration ที่เวลาถัดไป ต้องระวังติดตั้งใหม่บางฐานข้อมูล | artisan migrate → reports → Models/Controllers ที่อ่านตาราง; ไม่ส่งผู้ใช้ไปหน้าเว็บโดยตรง | ไฟล์เปลี่ยนโครงสร้าง DB |
| `database/migrations/2026_09_29_163335_create_report_reasons_table.php` | สร้าง report_reasons เก็บเหตุผลให้ผู้ใช้เลือก | artisan migrate → report_reasons → Models/Controllers ที่อ่านตาราง; ไม่ส่งผู้ใช้ไปหน้าเว็บโดยตรง | ไฟล์เปลี่ยนโครงสร้าง DB |
| `database/migrations/2026_09_29_163340_create_report_evidence_table.php` | สร้าง report_evidences เก็บ path ชนิดไฟล์และรหัสรายงาน; ยังไม่มี flow อัปโหลดในหน้าเว็บ | artisan migrate → report_evidences → Models/Controllers ที่อ่านตาราง; ไม่ส่งผู้ใช้ไปหน้าเว็บโดยตรง | ไฟล์เปลี่ยนโครงสร้าง DB |
| `database/migrations/2026_10_11_000001_add_report_target_to_reports_table.php` | เพิ่ม Reported_users_user_id แยกผู้ถูกรายงานออกจากผู้รายงาน | artisan migrate → reports → Models/Controllers ที่อ่านตาราง; ไม่ส่งผู้ใช้ไปหน้าเว็บโดยตรง | ไฟล์เปลี่ยนโครงสร้าง DB |
| `database/migrations/2026_10_11_000002_add_tutor_approval_and_lesson_details.php` | เพิ่มสถานะใบสมัคร ผู้ตรวจ เวลา/เหตุผลตรวจ สถานที่ใน contacts และห้องเรียน/เหตุผลปฏิเสธใน appointments | artisan migrate → tutor_profiles, contacts, appointments → Models/Controllers ที่อ่านตาราง; ไม่ส่งผู้ใช้ไปหน้าเว็บโดยตรง | ไฟล์เปลี่ยนโครงสร้าง DB |
| `resources/views/components/admin-navbar.blade.php` | เมนู admin รายงาน ผู้ใช้ ใบสมัครพร้อมจำนวนรอตรวจ และ logout/login | รับจาก dashboard.blade.php, index.blade.php, show.blade.php; ปุ่ม/ฟอร์มไป route admin.dashboard, admin.reports.index, admin.users.index, admin.tutors.index, logout, login | โค้ดในระบบ |
| `resources/views/domains/reportreview/admin/dashboard.blade.php` | สรุปจำนวนรายงาน ผู้ใช้ และบัญชีปิดใช้งาน พร้อมลิงก์จัดการ | รับจาก ReportController.php; ปุ่ม/ฟอร์มไป route admin.reports.index, admin.users.index; ใช้ admin-navbar.blade.php | โค้ดในระบบ |
| `resources/views/domains/reportreview/admin/reports/index.blade.php` | รายการรายงานพร้อมผู้เกี่ยวข้องและลิงก์ดูรายละเอียด | รับจาก ReportController.php; ปุ่ม/ฟอร์มไป route admin.reports.show; ใช้ admin-navbar.blade.php | โค้ดในระบบ |
| `resources/views/domains/reportreview/admin/reports/show.blade.php` | รายละเอียดผู้รายงาน/ผู้ถูกรายงานและฟอร์มเปลี่ยนสถานะ; ยังไม่ได้แสดงไฟล์หลักฐาน | รับจาก ReportController.php; ปุ่ม/ฟอร์มไป route admin.reports.index, admin.reports.update; ใช้ admin-navbar.blade.php | โค้ดในระบบ |
| `resources/views/domains/reportreview/admin/tutors.blade.php` | ดูใบสมัคร ประวัติ วิชา ข้อมูลติดต่อ และอนุมัติ/ปฏิเสธพร้อมเหตุผล | รับจาก AdminTutorController.php; ปุ่ม/ฟอร์มไป route admin.tutors.update; ใช้ admin-navbar.blade.php | โค้ดในระบบ |
| `resources/views/domains/reportreview/admin/users/index.blade.php` | ตารางผู้ใช้ ปุ่มเปิด/ปิดบัญชี และช่องเหตุผลที่ผู้ถูกปิดจะเห็น | รับจาก ReportController.php; ปุ่ม/ฟอร์มไป route admin.users.toggle-status; ใช้ admin-navbar.blade.php | โค้ดในระบบ |
| `resources/views/domains/reportreview/reports/create.blade.php` | ฟอร์มเหตุผลและรายละเอียดรายงานติวเตอร์ ส่ง reports.store; ยังไม่มีอัปโหลดหลักฐาน | รับจาก ReportController.php; ปุ่ม/ฟอร์มไป route tutor.show, reports.store | โค้ดในระบบ |
| `resources/views/domains/reportreview/reviews/create.blade.php` | ฟอร์มรีวิวเดิมมีรหัสตัวอย่างคงที่ ปัจจุบัน create redirect ไปนัดหมายเพื่อเลือกรีวิวที่ถูกต้อง | รับข้อมูลจาก layout/Controller ตามสถานะที่ระบุ; ปุ่ม/ฟอร์มไป route reviews.store | ไม่ใช้สร้างรีวิวหลัก |
| `resources/views/domains/reportreview/reviews/edit.blade.php` | ฟอร์มแก้คะแนน/ข้อความส่ง reviews.update หลังตรวจสิทธิ์ | รับจาก ReviewController.php; ปุ่ม/ฟอร์มไป route reviews.update, reviews.index | โค้ดในระบบ |
| `resources/views/domains/reportreview/reviews/index.blade.php` | หน้ารวมรีวิวเดิม ปัจจุบัน reviews.index redirect ไป dashboard/นัดหมาย | รับข้อมูลจาก layout/Controller ตามสถานะที่ระบุ; ปุ่ม/ฟอร์มไป route reviews.edit, reviews.destroy | ไม่ถูกเรียกโดย index ปัจจุบัน |


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


# 7. รายการเปลี่ยนแปลงแยกตามงาน และจุดที่ควรเตรียมก่อนพรีเซนต์

## 7.1 สรุปส่วนเพิ่มและเปลี่ยน

| งาน | เพิ่ม/ปรับ | ย้าย/เลิกใช้ในทางหลัก |
| --- | --- | --- |
| 1 | ใช้ User ใน Domains, ฟอร์มติวเตอร์ร่วม, อนุมัติก่อนเปิด role, active middleware, เหตุผลปิดบัญชี | login/register ใช้ modal ที่ home แทนหน้าฉบับเต็มเป็นทางหลัก |
| 2 | ช่วงเวลาสอนตรวจทับ/เจ้าของ/นัดหมาย เลือก availability_id และตรวจ DB ซ้ำ | ทางหลักไม่กรอกวันอิสระ; ประวัติการสอนไม่เพิ่มใน dashboard ใหม่ แต่โค้ดเดิมยังอยู่ |
| 3 | home ดึงข้อมูลจริง, public guest browsing, profile/search/ranking approved/active, favorites, navbar ร่วม, storage fallback | ปุ่มเครื่องมือติวเตอร์ย้ายขึ้น navbar; เอาช่องค้น navbar และเมนูแจ้งเตือน guest ออก |
| 4 | pending ก่อนรับ, confirmed ก่อนเรียน, completed หลังเวลา, ห้องเรียนเฉพาะนัด, badge แจ้งเตือน, แก้ปุ่ม CSS | ใช้ฟอร์มจองที่โปรไฟล์แทน appointments/create แบบวันอิสระ; รีวิวเปิดเป็น Bootstrap modal |
| 5 | รีวิวผูกนัดจริง, แจ้งติวเตอร์, admin ใบสมัคร/รายงาน/บัญชี, ผู้ถูกรายงานแยกจากผู้รายงาน | หน้า reviews.index/create เดิมเปลี่ยนเป็น redirect แต่ไฟล์ยังอยู่ |
| ส่วนกลาง | DemoWebsiteSeeder ที่สัมพันธ์กัน, คู่มือบัญชี, CODE_GUIDE และ AGENTS แนวเขียนโค้ดปี 2 | DatabaseSeeder ใช้ชุดใหม่; Seeder เก่ายังเรียกแยกได้ |

## 7.2 ประเด็นที่พบจากการอ่านซอร์ส — ยังไม่ได้แก้ในงานรายงานนี้

ตารางนี้ช่วยให้ทีมไม่กล่าวเกินความสามารถปัจจุบัน และเลือกจุดที่จะเตรียมต่อ ไม่ใช่ผลการทดสอบรันจริง

| หมวด | ไฟล์/จุด | สิ่งที่พบและผลต่อการอธิบาย |
| --- | --- | --- |
| 1 | routes/web.php reset-password | handler เปลี่ยนรหัสผ่านด้วยอีเมล/รหัสใหม่โดยไม่มีการตรวจ token; แยกจาก Fortify reset token และควรแก้ก่อนใช้นอกเครื่องทดลอง |
| 1 | routes/settings.php และ Auth settings | pages::settings, App\Concerns และ name ยังเป็นโครงเดิมบางส่วน; อย่าใช้เป็นหน้าสาธิตหลักจนเชื่อมให้ตรง |
| 1 | ContactController.php | ใช้ student_id และ view student-contacts.edit ที่ไม่พบ ขณะที่ contacts ใช้ Users_user_id |
| 1/3 | User.php / StudentProfile.php | User boot สร้างรหัส 12 ตัว แต่ migration กำหนด 10; StudentProfile มี UUID หากไม่ได้กำหนด id เอง ซึ่งยาวกว่า 10; Action/Seeder บางชุดกำหนด 10 เองแล้ว แต่เส้นทางอื่นต้องระวัง |
| 1/3 | User::favorites() | ความสัมพันธ์ใช้ user_id แต่ favorites จริงใช้ Users_user_id; FavoriteController หลัก query คอลัมน์ถูกโดยตรง อย่าใช้ relation นี้โดยไม่ตรวจ |
| 2 | CheckScheduleController/AvailabilityService/LearningHistoryService/ScheduleBookingService | อ้าง tutor user_id/id แบบเก่า ไม่ตรง Users_user_id/tutor_id; การจองหลักไม่ได้เรียกชุดนี้ |
| 2 | check-schedule/booking.blade.php | ปุ่มจอง disabled โดยตั้งใจ ไม่ใช่หน้าจองพร้อมใช้ |
| 2/4 | AppointmentController::store | ตรวจนัดซ้อนติวเตอร์ แต่ยังไม่ได้เช็กนัดซ้อนนักเรียนข้ามติวเตอร์แบบ Service เดิม |
| 3 | TutorProfile total_teaching_seconds | ไม่มีการอัปเดตใน complete ปัจจุบัน; ตัวเลขชั่วโมงบนการ์ดอาจไม่สะท้อนนัดทั้งหมด |
| 4 | appointments/create.blade.php | ฟอร์มเดิมไม่ส่ง availability_id และอ้าง create-appointment.css ที่ไม่พบ จึงไม่ตรง store ล่าสุด |
| 4 | SubjectController + Route::resource | ไม่พบ create/show/edit; CRUD ยังอยู่หลัง auth/active โดยไม่กำหนด role admin เฉพาะ |
| 4 | NotificationController / AppointmentController | routes ลงทะเบียน destroy แต่ไม่มี method นี้ จึงอย่าอ้างว่าลบข้อมูลสองชนิดผ่าน route เหล่านี้ครบแล้ว |
| 4 | tutor/show grade_level | ฟอร์มมีระดับชั้น แต่ store ปัจจุบันไม่บันทึกช่องนี้ |
| 5 | ReviewController::update/destroy | ไม่มีการคำนวณ average_rating ใหม่หลังแก้/ลบ ต่างจาก storeReview |
| 5 | report_evidences | มี schema และไฟล์ตัวอย่าง แต่ยังไม่มีการอัปโหลด/ดูหลักฐานครบใน UI |
| ส่วนกลาง | vite.config.js / partials/head | อ้าง resources/css/app.css แต่ไม่พบไฟล์นี้ใน snapshot; ยังไม่รับรอง build Vite |
| ส่วนกลาง | public/CSS กับ asset('css/...') | ตัวพิมพ์ต่างกัน ต้องปรับให้ตรงก่อนใช้ระบบไฟล์ที่แยกตัวพิมพ์ |
| DB | migration reports ก่อน report_reasons | ลำดับ FK อาจติดปัญหาเมื่อติดตั้งฐานข้อมูลใหม่ที่ตรวจเข้ม เช่น MySQL; ไม่ควรใช้การมี DB เดิมอยู่เป็นหลักฐานว่าติดตั้งใหม่ผ่าน |
| Tests | tests/Feature/Auth และ Scheduling บางส่วน | บาง expectation ยังอิง Starter Kit/schema เดิม; การมี test file ไม่เท่ากับผ่านตาม flow ใหม่ |

## 7.3 ลำดับเดโมที่ทีมเตรียมได้

1. เปิดเว็บแบบยังไม่ล็อกอิน: home → ค้นหา → โปรไฟล์ ตรวจว่าไม่มีแจ้งเตือนและปุ่มจองชวน login
2. ใช้นักเรียน demo.learner@example.com เปิดโปรไฟล์ demo.tutor หรือ demo.english เลือกช่วงที่ยังว่าง แล้วส่งคำขอ pending
3. ออกจากระบบแล้วเข้าบัญชีผู้สอน รับคำขอ และปรับห้องเรียนเฉพาะนัด จากนั้นให้นักเรียนดูสถานะ confirmed
4. สำหรับการสาธิตจบคาบทันที ใช้ DEMOAPP006 ที่ผ่านเวลาจบแล้ว ไม่ต้องแก้เวลานัดอนาคตให้ฝืนเงื่อนไข
5. นักเรียนกดยืนยัน → modal รีวิว → ส่ง → ติวเตอร์ดูรีวิว/แจ้งเตือน
6. admin เปิด /admin/tutors ตรวจ demo.pending จากนั้นผู้สมัครสลับ role ได้เมื่อผ่าน
7. admin เปิด /admin/users ปิดบัญชีตัวอย่างพร้อมเหตุผล แล้วใช้บัญชีนั้นแสดงข้อความถูกปิด หลีกเลี่ยงปิดบัญชีที่เพื่อนกำลังเตรียมเดโมโดยไม่บอกกัน

รายการนี้เป็นแผนเดโม ไม่ใช่บันทึกว่าทำการทดสอบครบแล้ว

## 7.4 คำถามที่ควรตอบได้

- ทำไมต้องตรวจสิทธิ์ทั้งซ่อนปุ่มและ Controller? เพราะผู้ใช้ส่ง HTTP เองได้ การตรวจที่ server จึงเป็นตัวตัดสิน
- ทำไมต้อง pending ก่อน? เพื่อให้ผู้สอนยืนยันว่ารับนัดได้ก่อนเปิดขั้นตอนเรียน
- ทำไม contacts กับ appointments มีลิงก์ทั้งคู่? contacts เป็นค่าเริ่มต้น ส่วน appointments ปรับเฉพาะคาบโดยไม่เปลี่ยนคาบอื่น
- ทำไมใช้ตารางกลางวิชา? เป็น many-to-many ติวเตอร์หลายคนสอนได้หลายวิชา
- ทำไมรีวิวต้องผูกนัด? เพื่อรู้ว่าผู้รีวิวเป็นผู้เรียนของคาบนั้นและจำกัดหนึ่งรีวิวต่อคาบ
- ทำไมไม่ seed sessions/cache/passkeys? เป็นข้อมูลระบบ/ข้อมูลยืนยันตัวตนที่ต้องสร้างตามการใช้งานจริง
- มีระบบจ่ายเงินหรือประชุมออนไลน์ในเว็บหรือไม่? ยังไม่มี เว็บเก็บและแสดงลิงก์ให้ไปใช้บริการภายนอก

## 7.5 วิธีติดตามไฟล์เมื่อรับช่วงงาน

เริ่มจากชื่อ route ในฟอร์ม → หา route ใน routes/web.php หรือ Fortify → อ่าน method ใน Controller → ดู Model และ migration ของตาราง → กลับมาอ่าน view ที่ return. ใช้ตารางรายไฟล์ในหัวข้องาน และ FILE_INVENTORY.csv เพื่อค้นชื่อไฟล์ข้ามหมวด

ไฟล์ร่วมอาจอยู่คนละหมวดตามตำแหน่ง เช่น AdminTutorController อยู่โฟลเดอร์ TutorProfile แต่รายงานจัดในหมวด 5; TutorApplication ใช้ร่วมระหว่างหมวด 1 และ 3. การแบ่งนี้เป็นความรับผิดชอบ ไม่ใช่การย้ายไฟล์จริง
