@extends('layouts.app')
@section('title', 'Catatan')
@section('content')
<div class="card">
    <div class="actions">
        <h2 style="flex:1; margin:0;">Catatan</h2>
        <a class="btn-primary" href="{{ route('notes.create') }}">+ Catatan</a>
    </div>

    <form method="GET" action="{{ route('notes.index') }}" class="actions" style="margin-top:12px;">
        <input name="q" type="text" placeholder="Cari…" value="{{ $search }}" maxlength="200" style="flex:1;">
        <select name="category">
            <option value="0">— Semua kategori —</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" @selected($category === $cat->id)>{{ $cat->name }}</option>
            @endforeach
        </select>
        <button class="btn-secondary" type="submit">Filter</button>
    </form>
</div>

@if($notes->isEmpty())
    <div class="card"><p class="muted">Tidak ada catatan.</p></div>
@else
    <div class="notes-grid">
        @foreach($notes as $note)
            <article class="note-card">
                @if($note->thumbnail_path)
                    <a href="{{ route('notes.show', $note) }}" class="thumb">
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($note->thumbnail_path) }}" alt="" loading="lazy">
                    </a>
                @endif
                <div class="body">
                    <h3><a href="{{ route('notes.show', $note) }}">{{ $note->title }}</a></h3>
                    <div class="meta">
                        {{ $note->created_at->format('d M Y') }}
                        @if($note->category)
                            &middot; <span class="tag" @if($note->category->color) style="background: {{ $note->category->color }}; color:#fff;" @endif>{{ $note->category->name }}</span>
                        @endif
                    </div>
                </div>
            </article>
        @endforeach
    </div>
    <div style="margin-top:14px;">{{ $notes->links() }}</div>
@endif
@endsection
