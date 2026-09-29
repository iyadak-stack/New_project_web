@extends('layouts.tutor')

@section('title', 'โปรไฟล์นักเรียน')

@section('content')

    <div class="profile-header mb-4">
        <h1>โปรไฟล์นักเรียน</h1>
        <p class="profile-description">
            จัดการและดูข้อมูลโปรไฟล์ส่วนตัวของคุณ
        </p>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card profile-card">
        <div class="card-body">
            <h2 class="section-title h4 mb-3">ข้อมูลโปรไฟล์</h2>

            <div class="mb-3">
                <span class="info-label text-muted d-block">ชื่อ-นามสกุล</span>
                <strong>{{ auth()->user()->name }}</strong>
            </div>

            <div class="mb-3">
                <span class="info-label text-muted d-block">ประวัติส่วนตัว (Bio)</span>
                @if ($studentProfile)
                    <strong>{{ $studentProfile->bio ?: 'ยังไม่มีข้อมูลประวัติส่วนตัว' }}</strong>
                @else
                    <p class="text-muted mb-0">คุณยังไม่มีข้อมูลโปรไฟล์นักเรียน</p>
                @endif
            </div>

            <div class="profile-actions mt-4">
                <a href="{{ route('student.profile.edit') }}" class="btn btn-primary">
                    แก้ไขโปรไฟล์
                </a>
            </div>
        </div>
    </div>

@endsection