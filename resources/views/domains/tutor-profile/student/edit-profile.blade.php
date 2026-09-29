@extends('layouts.tutor')

@section('title', 'แก้ไขโปรไฟล์นักเรียน')

@section('content')

    <div class="profile-header mb-4">
        <h1>แก้ไขโปรไฟล์นักเรียน</h1>
        <p class="profile-description">
            อัปเดตข้อมูลประวัติส่วนตัวของคุณ
        </p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>
                        {{ $error }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card profile-card">
        <div class="card-body">
            <form
                action="{{ route('student.profile.update') }}"
                method="POST"
            >
                @csrf

                <div class="mb-3">
                    <label for="bio" class="form-label">
                        ประวัติส่วนตัว (Bio)
                    </label>

                    <textarea
                        id="bio"
                        name="bio"
                        class="form-control"
                        rows="5"
                    >{{ old('bio', $studentProfile->bio ?? '') }}</textarea>
                </div>

                <div class="profile-actions mt-4">
                    <button type="submit" class="btn btn-primary">
                        บันทึก
                    </button>

                    <a href="{{ route('student.profile') }}" class="btn btn-outline-secondary">
                        ยกเลิก
                    </a>
                </div>
            </form>
        </div>
    </div>

@endsection