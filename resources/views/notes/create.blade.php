@extends('layouts.app')
@section('title', 'Catatan Baru')
@section('content')
<div class="card">
    <h2>Catatan Baru</h2>
    <form method="POST" action="{{ route('notes.store') }}" enctype="multipart/form-data">
        @csrf
        @include('notes._form')
        <div class="actions" style="margin-top:14px;">
            <button type="submit" class="btn-primary">Simpan</button>
            <a href="{{ route('notes.index') }}" class="btn-secondary">Batal</a>
        </div>
    </form>
</div>
<script src="{{ asset('js/notes.js') }}" defer></script>
@endsection
