@extends('layouts.app')
@section('title', 'Tambah User')
@section('content')
<div class="card">
    <h2>Tambah User</h2>
    <form method="POST" action="{{ route('admin.users.store') }}" autocomplete="off">
        @csrf
        @include('admin.users._form')
        <div class="actions" style="margin-top:14px;">
            <button type="submit" class="btn-primary">Simpan</button>
            <a href="{{ route('admin.users.index') }}" class="btn-secondary">Batal</a>
        </div>
    </form>
</div>
@endsection
