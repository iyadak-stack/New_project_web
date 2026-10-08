<?php

namespace App\Domains\TutorProfile\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\TutorProfile\Models\TutorProfile;
use App\Domains\Booking\Models\Subject;
use App\Domains\TutorProfile\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TutorController extends Controller
{
    public function home()
    {
        $tutors = TutorProfile::with(['user', 'subjects'])
            ->withCount('reviews')
            ->orderByDesc('average_rating')
            ->orderByDesc('experience_years')
            ->take(3)
            ->get();

        $subjects = Subject::withCount('tutors')
            ->orderByDesc('tutors_count')
            ->take(3)
            ->get();

        // ชื่อ view ให้ตรงกับไฟล์ blade หน้าแรกจริง
        return view('welcome', compact('tutors', 'subjects'));
    }

    public function profile()
    {
        $user = Auth::user();

        // เช็กบทบาทปัจจุบันของผู้ใช้
        if ($user->current_role === 'tutor') {
            $tutorProfile = TutorProfile::where(
                'Users_user_id',
                $user->user_id
            )->first();

            // ส่งไปยัง View Dashboard หน้าติวเตอร์
            return view(
                'domains.tutor-profile.tutor.tutor',
                compact('tutorProfile', 'user')
            );
        }

        // กรณีเป็นนักเรียน (student)
        return view(
            'domains.tutor-profile.student.profile',
            compact('user')
        );
    }

    public function editProfile()
    {
        $tutorProfile = TutorProfile::firstOrNew(['Users_user_id' => Auth::id()]);

        return view(
            'domains.tutor-profile.tutor.edit-profile',
            compact('tutorProfile')
        );
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'avatar'           => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'bio'              => ['nullable', 'string'],
            'experience_years' => ['required', 'integer', 'min:0'],
            'teaching_mode'    => ['required', 'in:online,onsite,both'],
            'line_id'          => ['nullable', 'string', 'max:255'],
            'discord_id'       => ['nullable', 'string', 'max:255'],
            'zoom_link'        => ['nullable', 'url', 'max:255'],
        ]);

        // ดึงโปรไฟล์เดิม หรือสร้างขึ้นใหม่หากยังไม่มีในฐานข้อมูล
        $tutorProfile = TutorProfile::firstOrCreate([
            'Users_user_id' => Auth::id(),
        ]);

        $dataToUpdate = [
            'bio'              => $request->bio,
            'experience_years' => $request->experience_years,
            'teaching_mode'    => $request->teaching_mode,
            'line_id'          => $request->line_id,
            'discord_id'       => $request->discord_id,
            'zoom_link'        => $request->zoom_link,
        ];

        // จัดการอัปโหลดรูปโปรไฟล์
        if ($request->hasFile('avatar')) {
            // ลบรูปภาพเก่าใน Disk ออกก่อน (ถ้ามี)
            if ($tutorProfile->avatar && Storage::disk('public')->exists($tutorProfile->avatar)) {
                Storage::disk('public')->delete($tutorProfile->avatar);
            }

            // อัปโหลดรูปใหม่
            $path = $request->file('avatar')->store('avatars', 'public');
            $dataToUpdate['avatar'] = $path;

            // อัปเดตไปยัง User หลักด้วย (กรณีมี profile_photo_path)
            $user = Auth::user();
            if ($user && isset($user->profile_photo_path)) {
                $user->update(['profile_photo_path' => $path]);
            }
        }

        $tutorProfile->update($dataToUpdate);

        return redirect()
            ->route('tutor.profile')
            ->with('success', 'อัปเดตโปรไฟล์ติวเตอร์เรียบร้อยแล้ว');
    }

    public function search(Request $request)
    {
        $search = trim($request->input('search', ''));

        $tutors = TutorProfile::with(['user', 'subjects'])
            ->withCount('reviews')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {

                    // ค้นหาจากชื่อหรือนามสกุล Tutor
                    $q->whereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where(function ($nameQuery) use ($search) {
                            $nameQuery->where(
                                'first_name',
                                'like',
                                "%{$search}%"
                            )->orWhere(
                                'last_name',
                                'like',
                                "%{$search}%"
                            );
                        });
                    })

                    // ค้นหาจากชื่อวิชา
                    ->orWhereHas('subjects', function ($subjectQuery) use ($search) {
                        $subjectQuery->where(
                            'subject_name',
                            'like',
                            "%{$search}%"
                        );
                    });
                });
            })
            ->orderByDesc('average_rating')
            ->orderByDesc('experience_years')
            ->get();

        $subjects = $tutors
            ->flatMap(function ($tutor) {
                return $tutor->subjects;
            })
            ->unique('subject_id')
            ->values();

        if ($search !== '') {
            $matchedSubjects = Subject::with('tutors.user')
                ->where(
                    'subject_name',
                    'like',
                    "%{$search}%"
                )
                ->get();

            $subjects = $subjects
                ->merge($matchedSubjects)
                ->unique('subject_id')
                ->values();
        }

        return view(
            'domains.tutor-profile.tutor.search',
            compact(
                'tutors',
                'subjects',
                'search'
            )
        );
    }

    public function ranking()
    {
        $tutors = TutorProfile::with(['user', 'subjects'])
            ->withCount('reviews')
            ->orderByDesc('average_rating')
            ->orderByDesc('experience_years')
            ->get();

        return view(
            'domains.tutor-profile.tutor.ranking',
            compact('tutors')
        );
    }

    public function show(TutorProfile $tutorProfile)
    {
        $tutorProfile->load([
            'user',
            'subjects',
            'reviews',
            'appointments',
        ]);

        $isFavorite = Favorite::where(
            'Users_user_id',
            Auth::id()
        )
            ->where(
                'Tutor_profiles_tutor_id',
                $tutorProfile->tutor_id
            )
            ->exists();

        return view(
            'domains.tutor-profile.tutor.show',
            compact(
                'tutorProfile',
                'isFavorite'
            )
        );
    }
}