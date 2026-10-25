@extends('layouts.app')

@section('title', 'Kategori')
@section('subtitle', 'Pengelompokan produk')
@section('actions') <a href="{{ route('categories.create') }}" class="btn btn-sm">Tambah kategori</a> @endsection

@section('content')
<section class="card">
    <form class="toolbar" method="GET">
        <input class="input grow" type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama kategori">
        <button class="btn btn-ghost btn-sm" type="submit">Cari</button>
        @if(request('q')) <a href="{{ route('categories.index') }}" class="btn btn-ghost btn-sm">Reset</a> @endif
    </form>
    @if($categories->isEmpty())
        <div class="empty"><b>Kategori tidak ditemukan</b>Tambahkan kategori pertama untuk mengelompokkan produk.</div>
    @else
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Nama</th><th>Deskripsi</th><th class="num">Jumlah produk</th><th></th></tr></thead>
            <tbody>
            @foreach($categories as $category)
                <tr>
                    <td><b>{{ $category->name }}</b></td>
                    <td class="muted">{{ $category->description ?: '—' }}</td>
                    <td class="num">{{ $category->products_count }}</td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('categories.edit', $category) }}" class="btn btn-ghost btn-sm">Ubah</a>
                            <form method="POST" action="{{ route('categories.destroy', $category) }}" onsubmit="return confirm('Hapus kategori ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm" type="submit">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    {{ $categories->links() }}
    @endif
</section>
@endsection
