<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <meta name="robots" content="noindex,nofollow">
    <title>@yield('title', config('app.name'))</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="app">
    @auth
    <nav class="topbar">
        <a class="brand" href="{{ route('dashboard') }}">
            <img src="{{ asset('favicon.svg') }}" alt="" width="24" height="24">
            <span>{{ config('app.name') }}</span>
        </a>
        <ul class="nav">
            <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li><a href="{{ route('notes.index') }}">Catatan</a></li>
            <li><a href="{{ route('categories.index') }}">Kategori</a></li>
            @if(auth()->user()->isAdmin())
                <li><a href="{{ route('admin.users.index') }}">Users</a></li>
            @endif
        </ul>
        <div class="nav-right">
            <a href="{{ route('profile.edit') }}" class="profile">
                @if(auth()->user()->profile_photo_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url(auth()->user()->profile_photo_path) }}" alt="" width="28" height="28">
                @endif
                <span>{{ auth()->user()->name }}</span>
            </a>
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="btn-link">Logout</button>
            </form>
        </div>
    </nav>
    @endauth

    <main class="container">
        @if(session('status'))
            <div class="alert alert-success" role="status">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-error" role="alert">
                <ul>
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
