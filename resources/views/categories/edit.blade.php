@extends('layouts.app')
@section('title', 'Edit Kategori')
@section('content')
<div class="card">
    <h2>Edit Kategori</h2>
    <form method="POST" action="{{ route('categories.update', $category) }}">
        @csrf @method('PUT')
        @include('categories._form')
        <div class="actions" style="margin-top:14px;">
            <button type="submit" class="btn-primary">Simpan</button>
            <a href="{{ route('categories.index') }}" class="btn-secondary">Batal</a>
        </div>
    </form>
</div>
@endsection
