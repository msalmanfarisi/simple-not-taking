<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Tautan Kedaluwarsa</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="app">
<div class="container">
    <div class="auth-card">
        <div class="auth-header">
            <img src="{{ asset('favicon.svg') }}" alt="" width="48" height="48">
            <h1>Tautan Kedaluwarsa</h1>
            <p class="muted">Tautan berbagi untuk catatan ini sudah kedaluwarsa pada {{ $note->share_expires_at?->format('d M Y H:i') }}.</p>
        </div>
    </div>
</div>
</body>
</html>
