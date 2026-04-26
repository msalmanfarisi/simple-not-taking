<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Catatan Terproteksi</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="app">
<div class="container">
    <div class="auth-card">
        <div class="auth-header">
            <img src="{{ asset('favicon.svg') }}" alt="" width="48" height="48">
            <h1>Catatan Terproteksi</h1>
            <p class="muted">Masukkan password untuk melihat catatan ini.</p>
        </div>

        @if($errors->any())
            <div class="alert alert-error" role="alert">
                <ul>@foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('share.unlock', ['id' => $note->id, 'slug' => $note->slug]) }}" autocomplete="off">
            @csrf
            <label for="password">Password</label>
            <input id="password" name="password" type="password" required maxlength="128" autocomplete="off" autofocus>
            <button type="submit" class="btn-primary" style="margin-top:12px;">Buka</button>
        </form>
    </div>
</div>
</body>
</html>
