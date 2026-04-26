@extends('layouts.app')

@section('title', 'Kategori')

@section('content')
<div class="card">
    <div class="actions">
        <h2 style="flex:1; margin:0;">Kategori</h2>
        <a class="btn-primary" href="{{ route('categories.create') }}">Tambah</a>
    </div>

    @if($categories->isEmpty())
        <p class="muted">Belum ada kategori.</p>
    @else
        <table class="table">
            <thead>
                <tr><th>Nama</th><th>Warna</th><th>Catatan</th><th></th></tr>
            </thead>
            <tbody>
                @foreach($categories as $cat)
                    <tr>
                        <td>{{ $cat->name }}</td>
                        <td>
                            @if($cat->color)
                                <span class="tag" style="background: {{ $cat->color }}; color: #fff;">{{ $cat->color }}</span>
                            @else
                                <span class="muted">—</span>
                            @endif
                        </td>
                        <td>{{ $cat->notes_count }}</td>
                        <td class="actions">
                            <a class="btn-secondary" href="{{ route('categories.edit', $cat) }}">Edit</a>
                            <form method="POST" action="{{ route('categories.destroy', $cat) }}" class="inline" onsubmit="return confirm('Hapus kategori ini?');">
                                @csrf @method('DELETE')
                                <button class="btn-danger" type="submit">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $categories->links() }}
    @endif
</div>
@endsection
