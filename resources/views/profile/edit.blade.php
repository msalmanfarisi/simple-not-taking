@extends('layouts.app')
@section('title', 'Profil')
@section('content')
<div class="card">
    <h2>Profil</h2>
    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
        @csrf @method('PATCH')
        <label for="name">Nama</label>
        <input id="name" name="name" type="text" maxlength="120" required value="{{ old('name', $user->name) }}">

        <label for="email">Email</label>
        <input id="email" name="email" type="email" maxlength="191" required value="{{ old('email', $user->email) }}">

        <label for="photo">Foto profil (jpg/jpeg/png, max 4 MB)</label>
        <input id="photo" name="photo" type="file" accept="image/png,image/jpeg">
        @if($user->profile_photo_path)
            <p><img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($user->profile_photo_path) }}" alt="" style="max-width:120px;border-radius:50%;"></p>
        @endif

        <button type="submit" class="btn-primary">Simpan</button>
    </form>
</div>

<div class="card">
    <h2>Ganti Password</h2>
    <form method="POST" action="{{ route('profile.password') }}" autocomplete="off">
        @csrf @method('PATCH')
        <label for="current_password">Password saat ini</label>
        <input id="current_password" name="current_password" type="password" required maxlength="255" autocomplete="current-password">

        <label for="password">Password baru (min 12 karakter, huruf besar+kecil, angka, simbol)</label>
        <input id="password" name="password" type="password" required minlength="12" maxlength="255" autocomplete="new-password">

        <label for="password_confirmation">Konfirmasi password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" required minlength="12" maxlength="255" autocomplete="new-password">

        <button type="submit" class="btn-primary">Ganti Password</button>
    </form>
</div>
@endsection
