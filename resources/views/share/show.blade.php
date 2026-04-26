<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ $note->title }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="app">
<div class="container share-page">
    <article>
        @if($note->thumbnail_path)
            <div class="thumb">
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($note->thumbnail_path) }}" alt="">
            </div>
        @endif

        <div class="content">
            <h1 style="margin-top:0;">{{ $note->title }}</h1>
            <p>
                <time datetime="{{ $note->created_at->toIso8601String() }}">{{ $note->created_at->format('d M Y H:i') }}</time>
                @if($note->category) &middot; <span class="tag">{{ $note->category->name }}</span> @endif
            </p>

            <div class="note-body">{!! $note->body !!}</div>

            @if($note->reference_url)
                <p>Referensi:
                    <a href="{{ $note->reference_url }}" rel="noopener noreferrer nofollow ugc external" target="_blank">
                        {{ $note->reference_url }}
                    </a>
                </p>
            @endif
        </div>
    </article>

    <footer class="share-footer">
        <small>Dibagikan via {{ config('app.name') }}.</small>
    </footer>
</div>
</body>
</html>
