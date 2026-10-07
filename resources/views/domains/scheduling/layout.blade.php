<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PeerTutor: @yield('title')</title>
    @vite(['resources/css/app.css'])
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="peer-navbar">
        <div class="peer-navbar-left">
            <a class="peer-logo" href="{{ route('home') }}">PeerTutor</a>
            <a class="peer-nav-link" href="{{ route('availabilities.index') }}">เวลาว่างของฉัน</a>
            <a class="peer-nav-link" href="{{ route('schedule.check') }}">เช็กเวลาตรงกัน</a>
            <a class="peer-nav-link" href="{{ route('schedule.history') }}">ประวัติการเรียนและการสอน</a>
        </div>
    </nav>
    <main class="page-content">
        @yield('content')
    </main>
</body>
</html>
