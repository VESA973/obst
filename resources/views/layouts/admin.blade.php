<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Administration | La Quinzaine Obstetricale')</title>
    <meta name="robots" content="noindex, nofollow">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-app-body">
    <main class="admin-app">
        @if (session('status'))
            <div class="flash-message admin-flash">{{ session('status') }}</div>
        @endif

        @yield('content')
    </main>
</body>
</html>
