@extends('layouts.app')

@section('title', 'Pemasok')
@section('subtitle', 'Sumber barang untuk pembelian stok')
@section('actions') <a href="{{ route('suppliers.create') }}" class="btn btn-sm">Tambah pemasok</a> @endsection

@section('content')
<section class="card">
    <form class="toolbar" method="GET">
        <input class="input grow" type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama atau telepon pemasok">
        <button class="btn btn-ghost btn-sm" type="submit">Cari</button>
        @if(request('q')) <a href="{{ route('suppliers.index') }}" class="btn btn-ghost btn-sm">Reset</a> @endif
    </form>
    @if($suppliers->isEmpty())
        <div class="empty"><b>Pemasok tidak ditemukan</b>Tambahkan pemasok sebelum mencatat pembelian.</div>
    @else
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Nama</th><th>Kontak</th><th class="num">Pembelian</th><th></th></tr></thead>
            <tbody>
            @foreach($suppliers as $supplier)
                <tr>
                    <td><b>{{ $supplier->name }}</b><div class="sub">{{ $supplier->address ?: '—' }}</div></td>
                    <td>{{ $supplier->phone ?: '—' }}<div class="sub">{{ $supplier->email ?: '' }}</div></td>
                    <td class="num">{{ $supplier->purchases_count }}</td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-ghost btn-sm">Ubah</a>
                            <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" onsubmit="return confirm('Hapus pemasok ini?')">
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
    {{ $suppliers->links() }}
    @endif
</section>
@endsection
