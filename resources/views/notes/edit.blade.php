@extends('layouts.app')
@section('title', 'Edit Catatan')
@section('content')
<div class="card">
    <h2>Edit Catatan</h2>
    <form method="POST" action="{{ route('notes.update', $note) }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        @include('notes._form')
        <div class="actions" style="margin-top:14px;">
            <button type="submit" class="btn-primary">Simpan</button>
            <a href="{{ route('notes.show', $note) }}" class="btn-secondary">Batal</a>
        </div>
    </form>
</div>
<script src="{{ asset('js/notes.js') }}" defer></script>
@endsection
