@extends('layouts.app')

@section('title', 'Pengguna')
@section('subtitle', 'Akun dan peran akses')
@section('actions') <a href="{{ route('users.create') }}" class="btn btn-sm">Tambah pengguna</a> @endsection

@section('content')
<section class="card">
    <form class="toolbar" method="GET">
        <input class="input grow" type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama atau email">
        <button class="btn btn-ghost btn-sm" type="submit">Cari</button>
        @if(request('q')) <a href="{{ route('users.index') }}" class="btn btn-ghost btn-sm">Reset</a> @endif
    </form>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Nama</th><th>Email</th><th>Peran</th><th></th></tr></thead>
            <tbody>
            @foreach($users as $item)
                <tr>
                    <td><b>{{ $item->name }}</b> @if($item->is(auth()->user())) <span class="badge">Anda</span> @endif</td>
                    <td>{{ $item->email }}</td>
                    <td><span class="badge {{ $item->isAdmin() ? 'ok' : '' }}">{{ $item->roleLabel() }}</span></td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('users.edit', $item) }}" class="btn btn-ghost btn-sm">Ubah</a>
                            @unless($item->is(auth()->user()))
                                <form method="POST" action="{{ route('users.destroy', $item) }}" onsubmit="return confirm('Hapus pengguna ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm" type="submit">Hapus</button>
                                </form>
                            @endunless
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    {{ $users->links() }}
</section>
@endsection
