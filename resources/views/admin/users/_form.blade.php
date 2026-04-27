<label for="name">Nama</label>
<input id="name" name="name" type="text" maxlength="120" required value="{{ old('name', $user->name) }}">

<label for="email">Email</label>
<input id="email" name="email" type="email" maxlength="191" required value="{{ old('email', $user->email) }}">

<label for="role">Role</label>
<select id="role" name="role" required>
    @foreach(['user' => 'User', 'admin' => 'Admin'] as $value => $label)
        <option value="{{ $value }}" @selected(old('role', $user->role) === $value)>{{ $label }}</option>
    @endforeach
</select>

<label for="password">Password @if($user->exists)<span class="muted">(kosongkan jika tidak diubah)</span>@endif</label>
<input id="password" name="password" type="password" minlength="12" maxlength="255" autocomplete="new-password" @if(!$user->exists) required @endif>

<label for="password_confirmation">Konfirmasi password</label>
<input id="password_confirmation" name="password_confirmation" type="password" minlength="12" maxlength="255" autocomplete="new-password" @if(!$user->exists) required @endif>
