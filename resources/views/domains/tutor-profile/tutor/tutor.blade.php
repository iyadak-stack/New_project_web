<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        PeerTutor: @yield('title', 'Home')
    </title>

    @vite(['resources/css/app.css'])

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    @yield('styles')
</head>

<body>
  
    <x-tutor-navbar />

    <main class="page-content">
        @yield('content')
    </main>

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
    </script>

    @yield('scripts')

</body>

</html>