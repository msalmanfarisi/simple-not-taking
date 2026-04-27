@extends('layouts.app')
@section('title', 'Edit User')
@section('content')
<div class="card">
    <h2>Edit User</h2>
    <form method="POST" action="{{ route('admin.users.update', $user) }}" autocomplete="off">
        @csrf @method('PUT')
        @include('admin.users._form')
        <div class="actions" style="margin-top:14px;">
            <button type="submit" class="btn-primary">Simpan</button>
            <a href="{{ route('admin.users.index') }}" class="btn-secondary">Batal</a>
        </div>
    </form>
</div>
@endsection
