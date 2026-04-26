@extends('layouts.app')
@section('title', 'Buat Kategori')
@section('content')
<div class="card">
    <h2>Buat Kategori</h2>
    <form method="POST" action="{{ route('categories.store') }}">
        @csrf
        @include('categories._form', ['category' => null])
        <div class="actions" style="margin-top:14px;">
            <button type="submit" class="btn-primary">Simpan</button>
            <a href="{{ route('categories.index') }}" class="btn-secondary">Batal</a>
        </div>
    </form>
</div>
@endsection
