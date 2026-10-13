<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

use App\Domains\TutorProfile\Http\Controllers\StudentHomeController;
use App\Domains\TutorProfile\Http\Controllers\StudentProfileController;
use App\Domains\TutorProfile\Http\Controllers\TutorProfileController;
use App\Domains\TutorProfile\Http\Controllers\TutorSearchController;
use App\Domains\TutorProfile\Http\Controllers\TutorTeachingController;
use App\Domains\TutorProfile\Http\Controllers\TutorApplicationController;
use App\Http\Controllers\AdminTutorApplicationController;

use App\Domains\TutorProfile\Http\Controllers\FavoriteController;
use App\Domains\Booking\Http\Controllers\SubjectController;
use App\Domains\Booking\Http\Controllers\AppointmentController;
use App\Domains\Scheduling\Http\Controllers\AvailabilityController;
use App\Domains\Scheduling\Http\Controllers\CheckScheduleController;
use App\Domains\Booking\Http\Controllers\NotificationController;
use App\Domains\Auth\Http\Controllers\ContactController;
use App\Domains\Auth\Http\Controllers\RoleController;
use App\Domains\Reportreview\Http\Controllers\ReviewController;
use App\Domains\Reportreview\Http\Controllers\Admin\ReportController;
use App\Domains\Auth\Models\User;
use App\Http\Controllers\AdminController;
use App\Domains\Booking\Models\Appointment;

// =========================
// หน้าแรก (/): ตรวจสอบบทบาทผู้ใช้ และดึงข้อมูลจริงจาก DB สำหรับ Dashboard ติวเตอร์
// =========================
Route::get('/', function (Request $request) {
    if (auth()->check()) {
        if (auth()->user()->role === 'admin' || auth()->user()->current_role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        if (auth()->user()->current_role === 'tutor') {
            $user = auth()->user();
            $tutorId = $user->tutorProfile->tutor_id ?? null;

            // ดึงข้อมูลการนัดหมายทั้งหมดของติวเตอร์คนนี้พร้อม Relationship
            $appointmentsQuery = Appointment::with([
                'subject',
                'studentProfile.user',
                'studentUser.studentProfile',
            ])
                ->where('Tutor_profiles_tutor_id', $tutorId);

            // คำนวณยอดนับแต่ละสถานะ
            $pendingCount = (clone $appointmentsQuery)->where('status', 'pending')->count();
            $confirmedCount = (clone $appointmentsQuery)->where('status', 'confirmed')->count();
            $cancelledCount = (clone $appointmentsQuery)->where('status', 'cancelled')->count();

            // ดึงคำขอจองที่รอการตรวจสอบ (Pending)
            $pendingAppointments = (clone $appointmentsQuery)
                ->where('status', 'pending')
                ->orderBy('start_datetime', 'asc')
                ->get();

            // ดึงตารางสอนล่าสุด (Confirmed / Pending)
            $todaySchedules = (clone $appointmentsQuery)
                ->whereIn('status', ['confirmed', 'pending'])
                ->orderBy('start_datetime', 'asc')
                ->take(5)
                ->get();

            // นัดหมายเก่าใช้รหัสโปรไฟล์ แต่นัดหมายใหม่ใช้รหัสผู้ใช้
            foreach ($pendingAppointments as $appointment) {
                $appointment->loadStudentProfile();
            }
            foreach ($todaySchedules as $appointment) {
                $appointment->loadStudentProfile();
            }

            return view('domains.tutor-profile.tutor.tutor', compact(
                'pendingCount',
                'confirmedCount',
                'cancelledCount',
                'pendingAppointments',
                'todaySchedules'
            ));
        }

        return app(StudentHomeController::class)->index($request);
    }

    return app(StudentHomeController::class)->index($request);
})->name('home');

// รองรับเครื่อง Windows ที่ไม่มี storage link อ่านเฉพาะไฟล์ใน public disk
Route::get('/storage/{path}', function (string $path) {
    $segments = explode('/', str_replace('\\', '/', $path));
    abort_if(in_array('..', $segments, true), 404);
    abort_unless(Storage::disk('public')->exists($path), 404);

    return Storage::disk('public')->response($path);
})->where('path', '.*')->name('storage.public');


// ลืมรหัสผ่านใช้ route ของ Fortify เพื่อให้ตรวจ token ก่อนเปลี่ยนรหัสผ่าน

// =========================
// ค้นหา / ดูโปรไฟล์ติวเตอร์ (Public Views Access - ทั้งติวเตอร์และนักเรียนเข้าดูได้)
// =========================
Route::get('/tutors', [TutorSearchController::class, 'search'])
    ->name('tutor.search');

Route::get('/tutors/ranking', [TutorSearchController::class, 'ranking'])
    ->name('tutor.ranking');

// ผู้เยี่ยมชมดูรายการและรายละเอียดได้ แต่การจัดการยังต้องเข้าสู่ระบบ
Route::get('/subjects', [StudentHomeController::class, 'allSubjects'])->name('subjects.index');
Route::get('/subjects/{subject}', [SubjectController::class, 'show'])->name('subjects.show');
Route::get('/student/tutors', [StudentHomeController::class, 'allTutors'])->name('student.tutors.index');
Route::get('/all-tutors', [StudentHomeController::class, 'allTutors'])->name('tutor.tutors');

// 🟢 รายการโปรดของฉัน (ต้องล็อกอิน) — กำหนดทั้ง /tutors/favorites และ /favorites ให้รองรับการกดจาก Navbar ทุกจุด
Route::get('/tutors/favorites', [FavoriteController::class, 'tutorFavorites'])
    ->middleware('auth')
    ->name('tutor.favorites');

Route::get('/favorites', [FavoriteController::class, 'tutorFavorites'])
    ->middleware('auth')
    ->name('favorites.index');

// 🔴 หน้าโปรไฟล์ติวเตอร์รายบุคคล (ต้องวางไว้ใต้ Route แบบเจาะจงเสมอ เพื่อป้องกัน /tutors/favorites วิ่งมาตกที่นี่)
Route::get('/tutors/{id}', [TutorProfileController::class, 'show'])
    ->name('tutor.show');


// =========================
// กลุ่ม Route ที่ต้องล็อกอิน (Auth Middleware)
// =========================
Route::middleware(['auth'])->group(function () {
    // ผู้สมัครยังเป็นนักเรียน จึงไม่ครอบฟอร์มคำขอด้วย role:tutor
    Route::get('/tutor/application', [TutorApplicationController::class, 'index'])->name('tutor.application.index');
    Route::middleware('role:tutor')->group(function () {
        Route::get('/tutor/subjects', [TutorTeachingController::class, 'index'])->name('tutor.subjects.index');
        Route::put('/tutor/subjects', [TutorTeachingController::class, 'update'])->name('tutor.subjects.update');
        // ลิงก์จัดเวลาเดิมให้เปิดตัวจัดเวลาของ branch เพื่อน
        Route::get('/tutor/schedule', function () {
            return redirect()->route('availabilities.index');
        })->name('tutor.schedule.index');
    });

    // กันเข้า URL /dashboard ให้วิ่งกลับไปหน้าหลัก
    Route::get('/dashboard', function () {
        return redirect()->route('home');
    })->name('dashboard');


    // =========================
    // หน้ารวมวิชาทั้งหมด & ติวเตอร์ทั้งหมด (เข้าได้ทุก Role ทั้ง Student / Tutor)
    // =========================

    // หน้ารายชื่อติวเตอร์ทั้งหมด

    // รองรับ Alias ชื่อ route tutor.tutors เผื่อถูกเรียกใช้ใน Navbar


    // =========================
    // โปรไฟล์ติวเตอร์ส่วนตัว (Tutor Profile - Private/Management)
    // =========================
    Route::get('/tutor/profile', [TutorProfileController::class, 'profile'])
        ->middleware('role:tutor')
        ->name('tutor.profile');

    Route::get('/tutor/profile/edit', [TutorProfileController::class, 'editProfile'])
        ->middleware('role:tutor')
        ->name('tutor.profile.edit');

    Route::post('/tutor/profile', [TutorProfileController::class, 'updateProfile'])
        ->middleware('role:tutor')
        ->name('tutor.profile.update');

    Route::post('/tutor/application/submit', [TutorProfileController::class, 'submitApplication'])
        ->name('tutor.application.submit');


    // =========================
    // รายการโปรดติวเตอร์ (Tutor Favorite) — POST/DELETE เพิ่ม-ลบรายการโปรด
    // =========================
    Route::post('/tutor/{id}/favorite', [FavoriteController::class, 'storeTutor'])
        ->name('tutor.favorite.store');

    Route::post('/tutor/{id}/favorite/remove', [FavoriteController::class, 'destroyTutor'])
        ->name('tutor.favorite.destroy');


    // =========================
    // จัดการรายวิชา (Subjects Resource)
    // =========================
    Route::resource('subjects', SubjectController::class)->except(['index', 'show'])->middleware('admin');


    // =========================
    // ตารางเวลาและความพร้อม (Availability & Schedule Check)
    // =========================

    Route::get('/availabilities', [AvailabilityController::class, 'index'])->name('availabilities.index');
    Route::post('/availabilities', [AvailabilityController::class, 'store'])->name('availabilities.store');
    Route::get('/availabilities/history', [AvailabilityController::class, 'history'])->name('availabilities.history');
    Route::get('/availabilities/{availability}/edit', [AvailabilityController::class, 'edit'])->name('availabilities.edit');
    Route::put('/availabilities/{availability}', [AvailabilityController::class, 'update'])->name('availabilities.update');
    Route::delete('/availabilities/{availability}', [AvailabilityController::class, 'destroy'])->name('availabilities.destroy');

    Route::get('/schedule/check', [CheckScheduleController::class, 'index'])->name('schedule.check');
    Route::get('/schedule/history', [CheckScheduleController::class, 'history'])->name('schedule.history');
    Route::get('/schedule/booking', [CheckScheduleController::class, 'booking'])->name('schedule.booking');
    Route::get('/schedule/availability', [CheckScheduleController::class, 'availability'])->name('schedule.availability');
    Route::post('/schedule/check', [CheckScheduleController::class, 'check'])->name('schedule.check.results');

    // =========================
    // การนัดหมาย (Appointments)
    // =========================

    Route::resource('appointments', AppointmentController::class)
        ->except(['edit', 'update', 'destroy']);

    Route::patch('/appointments/{appointment}/lesson', [AppointmentController::class, 'updateLesson'])->middleware('role:tutor')->name('appointments.lesson');
    Route::post('/appointments/{id}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.status');
    Route::post('/appointments/{appointment}/complete', [AppointmentController::class, 'complete'])->name('appointments.complete');
    Route::post('/appointments/{appointment}/review', [AppointmentController::class, 'storeReview'])->name('appointments.review');

    Route::post('/appointments/{appointment}/confirm', [AppointmentController::class, 'confirm'])
        ->name('appointments.confirm');

    Route::post('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])
        ->name('appointments.cancel');

    // =========================
    // ระบบแจ้งเตือน (Notifications)
    // =========================
    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');

    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])
        ->name('notifications.destroy');


    // =========================
    // โปรไฟล์นักเรียน (Student Profile)
    // =========================
    Route::get('/student/profile', [StudentProfileController::class, 'profile'])
        ->middleware('role:student')
        ->name('student.profile');

    Route::get('/student/profile/edit', [StudentProfileController::class, 'editProfile'])
        ->middleware('role:student')
        ->name('student.profile.edit');

    Route::post('/student/profile/update', [StudentProfileController::class, 'updateProfile'])
        ->middleware('role:student')
        ->name('student.profile.update');


    // =========================
    // ข้อมูลติดต่อของนักเรียน (Student Contacts)
    // =========================
    Route::get('/student-contacts/{studentId}/edit', [ContactController::class, 'edit'])
        ->name('student-contacts.edit');

    Route::put('/student-contacts/{studentId}', [ContactController::class, 'update'])
        ->name('student-contacts.update');


    // =========================
    // สลับบทบาทผู้ใช้ (Role Switching)
    // =========================
    Route::post('/switch-role', [RoleController::class, 'switchRole'])
        ->name('role.switch');


    // =========================
    // รีวิว (Reviews)
    // =========================
    Route::get('/reviews', [ReviewController::class, 'index'])
        ->name('reviews.index');

    Route::get('/reviews/create', [ReviewController::class, 'create'])
        ->name('reviews.create');

    Route::post('/reviews', [ReviewController::class, 'store'])
        ->name('reviews.store');

    Route::get('/reviews/{review}/edit', [ReviewController::class, 'edit'])
        ->name('reviews.edit');

    Route::put('/reviews/{review}', [ReviewController::class, 'update'])
        ->name('reviews.update');

    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])
        ->name('reviews.destroy');


    // =========================
    // รายงาน (Reports)
    // =========================
    Route::get('/reports/create', [ReportController::class, 'create'])
        ->name('reports.create');

    Route::post('/reports', [ReportController::class, 'store'])
        ->name('reports.store');
});

require __DIR__.'/settings.php';

// เมนูแอดมินทั้งหมดตรวจ auth + admin ทั้งหน้าอ่านและคำสั่งบันทึก
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/tutor-applications', [AdminTutorApplicationController::class, 'index'])->name('tutor-applications.index');
    Route::get('/tutor-applications/{id}', [AdminTutorApplicationController::class, 'show'])->name('tutor-applications.show');
    Route::patch('/tutor-applications/{id}', [AdminTutorApplicationController::class, 'decide'])->name('tutor-applications.decide');
    Route::get('/users', [AdminController::class, 'users'])->name('users.index');
    Route::get('/users/{id}', [AdminController::class, 'user'])->name('users.show');
    Route::patch('/users/{id}/status', [AdminController::class, 'updateUserStatus'])->name('users.status');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::patch('/reports/{id}/status', [AdminController::class, 'updateReport'])->name('reports.status');
    Route::get('/report-reasons', [AdminController::class, 'reasons'])->name('reasons.index');
    Route::post('/report-reasons', [AdminController::class, 'saveReason'])->name('reasons.store');
    Route::put('/report-reasons/{id}', [AdminController::class, 'saveReason'])->name('reasons.update');
    Route::delete('/report-reasons/{id}', [AdminController::class, 'deleteReason'])->name('reasons.destroy');
    Route::get('/subjects', [AdminController::class, 'subjects'])->name('subjects.index');
    Route::post('/subjects', [AdminController::class, 'saveSubject'])->name('subjects.store');
    Route::put('/subjects/{id}', [AdminController::class, 'saveSubject'])->name('subjects.update');
    Route::delete('/subjects/{id}', [AdminController::class, 'deleteSubject'])->name('subjects.destroy');
    Route::get('/reviews', [AdminController::class, 'reviews'])->name('reviews.index');
    Route::patch('/reviews/{id}/visibility', [AdminController::class, 'updateReviewVisibility'])->name('reviews.visibility');
    Route::get('/appointments', [AdminController::class, 'appointments'])->name('appointments.index');
});
