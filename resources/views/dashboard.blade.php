@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="card">
    <h2>Halo, {{ auth()->user()->name }}!</h2>
    <p class="muted">Anda memiliki <strong>{{ $noteCount }}</strong> catatan dalam <strong>{{ $categoryCount }}</strong> kategori.</p>
    <div class="actions">
        <a class="btn-primary" href="{{ route('notes.create') }}">Buat Catatan</a>
        <a class="btn-secondary" href="{{ route('categories.create') }}">Tambah Kategori</a>
    </div>
</div>

<div class="card">
    <h2>Catatan Terbaru</h2>
    @if($recentNotes->isEmpty())
        <p class="muted">Belum ada catatan.</p>
    @else
        <ul>
            @foreach($recentNotes as $note)
                <li>
                    <a href="{{ route('notes.show', $note) }}">{{ $note->title }}</a>
                    <span class="muted"> &middot; {{ optional($note->category)->name ?? 'Tanpa kategori' }} &middot; {{ $note->created_at->format('d M Y') }}</span>
                </li>
            @endforeach
        </ul>
    @endif
</div>
@endsection
