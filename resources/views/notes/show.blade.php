@extends('layouts.app')
@section('title', $note->title)
@section('content')
<div class="card">
    <div class="actions">
        <h2 style="flex:1;margin:0;">{{ $note->title }}</h2>
        <a class="btn-secondary" href="{{ route('notes.edit', $note) }}">Edit</a>
        <form method="POST" action="{{ route('notes.destroy', $note) }}" class="inline" onsubmit="return confirm('Hapus catatan ini?');">
            @csrf @method('DELETE')
            <button type="submit" class="btn-danger">Hapus</button>
        </form>
    </div>
    <p class="muted">
        <time datetime="{{ $note->created_at->toIso8601String() }}">{{ $note->created_at->format('d M Y H:i') }}</time>
        @if($note->category) &middot; <span class="tag">{{ $note->category->name }}</span> @endif
    </p>

    @if($note->thumbnail_path)
        <p><img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($note->thumbnail_path) }}" alt="" style="max-width:100%;height:auto;border-radius:10px;"></p>
    @endif

    <div class="note-body">{!! $note->body !!}</div>

    @if($note->reference_url)
        <p>Referensi: <a href="{{ $note->reference_url }}" rel="noopener noreferrer nofollow ugc external" target="_blank">{{ $note->reference_url }}</a></p>
    @endif

    @if($note->attachments->isNotEmpty())
        <h3>Lampiran</h3>
        <ul>
            @foreach($note->attachments as $att)
                <li>
                    <a href="{{ route('notes.attachments.download', [$note, $att]) }}">{{ $att->original_name }}</a>
                    <span class="muted">({{ number_format($att->size_bytes / 1024, 1) }} KB)</span>
                </li>
            @endforeach
        </ul>
    @endif
</div>

<div class="card">
    <h3>Bagikan</h3>
    <p>
        <code>{{ $note->share_link }}</code>
        @if($note->requiresPassword()) <span class="tag">password</span> @endif
        @if($note->share_expires_at)
            <span class="tag">kedaluwarsa: {{ $note->share_expires_at->format('d M Y H:i') }}</span>
        @endif
    </p>
</div>
@endsection
