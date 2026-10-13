<?php

use App\Domains\Auth\Http\Controllers\ContactController;
use App\Domains\Auth\Http\Controllers\RoleController;
use App\Domains\Auth\Models\User;
use App\Domains\Booking\Http\Controllers\AppointmentController;
use App\Domains\Booking\Http\Controllers\NotificationController;
use App\Domains\Booking\Http\Controllers\SubjectController;
use App\Domains\Reportreview\Http\Controllers\Admin\ReportController;
use App\Domains\Reportreview\Http\Controllers\ReviewController;
use App\Domains\Scheduling\Http\Controllers\AvailabilityController;
use App\Domains\Scheduling\Http\Controllers\CheckScheduleController;
use App\Domains\TutorProfile\Http\Controllers\AdminTutorController;
use App\Domains\TutorProfile\Http\Controllers\FavoriteController;
use App\Domains\TutorProfile\Http\Controllers\StudentHomeController;
use App\Domains\TutorProfile\Http\Controllers\StudentProfileController;
use App\Domains\TutorProfile\Http\Controllers\TutorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

// หน้าแรก (/): ตรวจสอบบทบาทผู้ใช้
Route::get('/', function (Request $request) {
    if (auth()->check()) {
        if (auth()->user()->current_role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if (auth()->user()->current_role === 'tutor') {
            return app(TutorController::class)->profile();
        }

        return app(StudentHomeController::class)->index($request);
    }

    return app(StudentHomeController::class)->index($request);
})->name('home');

// อ่านรูปจากพื้นที่ public โดยตรง เพื่อรองรับเครื่อง Windows ที่สร้าง storage link ไม่ได้
Route::get('/storage/{path}', function (string $path) {
    abort_unless(Storage::disk('public')->exists($path), 404);

    return Storage::disk('public')->response($path);
})->where('path', '.*')->name('storage.public');

// =========================
// ลืมรหัสผ่านแบบข้ามการส่งอีเมล (Direct Reset Password)
// =========================

Route::post('/forgot-password', function (Request $request) {
    $request->validate([
        'email' => ['required', 'email', 'exists:users,email'],
    ], [
        'email.exists' => 'ไม่พบอีเมลนี้ในระบบ',
    ]);

    return redirect()->route('password.reset', [
        'token' => 'direct-reset',
        'email' => $request->email,
    ]);
})->name('password.email');

Route::post('/reset-password', function (Request $request) {
    $request->validate([
        'email' => ['required', 'email', 'exists:users,email'],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
    ]);

    $user = User::where('email', $request->email)->first();
    if ($user) {
        $user->forceFill([
            'password' => Hash::make($request->password),
        ])->save();
    }

    return redirect()->route('login')->with('status', 'เปลี่ยนรหัสผ่านสำเร็จแล้ว กรุณาเข้าสู่ระบบ');
})->name('password.update');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/tutor/application-status', [TutorController::class, 'applicationStatus'])->name('tutor.application.status');
    Route::get('/tutor/onboarding', [TutorController::class, 'editProfile'])->name('tutor.onboarding');
    Route::post('/tutor/onboarding', [TutorController::class, 'updateProfile'])->name('tutor.onboarding.store');

    // กันเข้า URL /dashboard ให้วิ่งกลับไปหน้าหลัก
    Route::get('/dashboard', function () {
        return redirect()->route('home');
    })->name('dashboard');

    // =========================
    // โปรไฟล์ติวเตอร์ (Tutor Profile)
    // =========================

    Route::get('/tutor/profile', [TutorController::class, 'profile'])
        ->middleware('role:tutor')
        ->name('tutor.profile');

    Route::get('/tutor/profile/edit', [TutorController::class, 'editProfile'])
        ->middleware('role:tutor')
        ->name('tutor.profile.edit');

    Route::post('/tutor/profile', [TutorController::class, 'updateProfile'])
        ->middleware('role:tutor')
        ->name('tutor.profile.update');

    // =========================
    // รายการโปรดติวเตอร์ (Tutor Favorite)
    // =========================

    Route::post('/tutor/{tutorProfile}/favorite', [FavoriteController::class, 'storeTutor'])
        ->name('tutor.favorite.store');

    Route::post('/tutor/{tutorProfile}/favorite/remove', [FavoriteController::class, 'destroyTutor'])
        ->name('tutor.favorite.destroy');

    // =========================
    // ค้นหา / จัดอันดับติวเตอร์ (Search / Ranking / Tutor)
    // =========================

    Route::get('/tutors/favorites', [FavoriteController::class, 'tutorFavorites'])
        ->name('tutor.favorites');

    // =========================
    // จัดการรายวิชา (Subjects)
    // =========================

    // หน้าแสดงรายการใช้แบบใหม่ ส่วนการจัดการวิชายังคง Route เดิม
    Route::get('/subjects', [StudentHomeController::class, 'allSubjects'])->name('subjects.index');
    Route::get('/student/tutors', [StudentHomeController::class, 'allTutors'])->name('student.tutors.index');
    Route::resource('subjects', SubjectController::class)->except(['index']);

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
        ->except(['edit', 'update']);

    Route::patch('/appointments/{appointment}/lesson', [AppointmentController::class, 'updateLesson'])->middleware('role:tutor')->name('appointments.lesson');
    Route::post('/appointments/{id}/status', [AppointmentController::class, 'updateStatus']);
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

    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);

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

    Route::post('/student/profile', [StudentProfileController::class, 'updateProfile'])
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

    Route::prefix('admin')->middleware('role:admin')->name('admin.')->group(function () {
        Route::get('/tutors', [AdminTutorController::class, 'index'])->name('tutors.index');
        Route::patch('/tutors/{tutorProfile}', [AdminTutorController::class, 'update'])->name('tutors.update');
        Route::get('/', [ReportController::class, 'dashboard'])->name('dashboard');
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/{report}', [ReportController::class, 'show'])->name('reports.show');
        Route::patch('/reports/{report}', [ReportController::class, 'updateStatus'])->name('reports.update');
        Route::get('/users', [ReportController::class, 'users'])->name('users.index');
        Route::patch('/users/{user}/status', [ReportController::class, 'toggleUserStatus'])->name('users.toggle-status');
    });
});

// ผู้เยี่ยมชมดูข้อมูลได้ ส่วนการจอง รายการโปรด และรายงานยังต้องเข้าสู่ระบบ
Route::get('/tutors', [TutorController::class, 'search'])
    ->name('tutor.search');

Route::get('/tutors/ranking', [TutorController::class, 'ranking'])
    ->name('tutor.ranking');

Route::get('/tutors/{tutorProfile}', [TutorController::class, 'show'])
    ->name('tutor.show');

require __DIR__.'/settings.php';
