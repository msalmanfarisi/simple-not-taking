@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="auth-card">
    <div class="auth-header">
        <img src="{{ asset('favicon.svg') }}" alt="" width="48" height="48">
        <h1>{{ config('app.name') }}</h1>
        <p class="muted">Catatan kategorikal yang aman.</p>
    </div>

    <form method="POST" action="{{ route('login') }}" autocomplete="off" novalidate>
        @csrf

        <label for="email">Email</label>
        <input id="email" name="email" type="email" required maxlength="191"
               value="{{ old('email') }}" autocomplete="username" autofocus>

        <label for="password">Password</label>
        <input id="password" name="password" type="password" required maxlength="255"
               autocomplete="current-password">

        <label for="captcha">Captcha (8 karakter, huruf besar &amp; kecil + angka)</label>
        <div class="captcha-row">
            <img id="captchaImage" src="{{ route('captcha.image') }}?_={{ now()->timestamp }}"
                 alt="Captcha" width="220" height="70">
            <button type="button" id="captchaReload" class="btn-link" aria-label="Muat ulang captcha">&#x21bb;</button>
        </div>
        <input id="captcha" name="captcha" type="text" required minlength="8" maxlength="8"
               pattern="[A-Za-z0-9]{8}" autocomplete="off" inputmode="text">

        <button type="submit" class="btn-primary">Login</button>
    </form>
</div>
<script src="{{ asset('js/captcha.js') }}" defer></script>
@endsection
