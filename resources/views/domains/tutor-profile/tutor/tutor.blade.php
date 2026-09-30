<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>PeerTutor: @yield('title', 'Home')</title>

    @yield('styles')
</head>

<body>

    <x-tutor-navbar />

    <main class="page-content">
        @yield('content')
    </main>

    @yield('scripts')

</body>

</html>