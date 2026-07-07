<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'AbsenDJJ') }}</title>

    @vite('resources/css/welcome.css')
</head>
<body>
    <main class="welcome-page">
        <h1>AbsenDJJ</h1>
        <p>Sistem Absensi & Logbook Praktik Data Jalan & Jembatan</p>

        <a href="{{ route('login') }}">Masuk Aplikasi</a>
    </main>
    @vite('resources/js/welcome.js')
</body>
</html>
