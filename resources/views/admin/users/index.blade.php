@extends('layouts.app')
@section('title', 'Users')
@section('content')
<div class="card">
    <div class="actions">
        <h2 style="flex:1;margin:0;">Users</h2>
        <a class="btn-primary" href="{{ route('admin.users.create') }}">Tambah</a>
    </div>
    <table class="table">
        <thead><tr><th>Nama</th><th>Email</th><th>Role</th><th></th></tr></thead>
        <tbody>
            @foreach($users as $u)
                <tr>
                    <td>{{ $u->name }}</td>
                    <td>{{ $u->email }}</td>
                    <td><span class="tag">{{ $u->role }}</span></td>
                    <td class="actions">
                        <a class="btn-secondary" href="{{ route('admin.users.edit', $u) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.users.destroy', $u) }}" class="inline" onsubmit="return confirm('Hapus user ini?');">
                            @csrf @method('DELETE')
                            <button class="btn-danger" type="submit">Hapus</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $users->links() }}
</div>
@endsection
