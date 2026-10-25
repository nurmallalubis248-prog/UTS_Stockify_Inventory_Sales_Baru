@extends('layouts.app')

@section('title', 'Pembelian')
@section('subtitle', auth()->user()->isAdmin() ? 'Semua transaksi stok masuk' : 'Transaksi stok masuk Anda')
@section('actions') <a href="{{ route('purchases.create') }}" class="btn btn-sm">Pembelian baru</a> @endsection

@section('content')
<section class="card">
    <form class="toolbar" method="GET">
        <input class="input grow" type="search" name="q" value="{{ request('q') }}" placeholder="Cari nomor atau pemasok">
        <select class="input" name="status" aria-label="Filter status">
            <option value="">Semua status</option>
            <option value="confirmed" @selected(request('status') === 'confirmed')>Terkonfirmasi</option>
            <option value="cancelled" @selected(request('status') === 'cancelled')>Dibatalkan</option>
        </select>
        <input class="input" type="date" name="from" value="{{ request('from') }}" aria-label="Dari tanggal">
        <input class="input" type="date" name="to" value="{{ request('to') }}" aria-label="Sampai tanggal">
        <button class="btn btn-ghost btn-sm" type="submit">Terapkan</button>
        @if(request()->hasAny(['q', 'status', 'from', 'to'])) <a href="{{ route('purchases.index') }}" class="btn btn-ghost btn-sm">Reset</a> @endif
    </form>

    @if($purchases->isEmpty())
        <div class="empty"><b>Belum ada transaksi yang cocok</b>Ubah filter atau catat pembelian baru.</div>
    @else
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Nomor</th><th>Tanggal</th><th>Pemasok</th><th>Petugas</th><th>Status</th><th class="num">Total</th><th></th></tr></thead>
            <tbody>
            @foreach($purchases as $purchase)
                <tr>
                    <td><a href="{{ route('purchases.show', $purchase) }}"><b>{{ $purchase->purchase_number }}</b></a></td>
                    <td>{{ $purchase->purchased_at->format('d M Y H:i') }}</td>
                    <td>{{ $purchase->supplier->name }}</td>
                    <td>{{ $purchase->user->name }}</td>
                    <td>@include('partials.badge', ['status' => $purchase->status])</td>
                    <td class="num">@rupiah($purchase->total)</td>
                    <td><div class="actions"><a href="{{ route('purchases.show', $purchase) }}" class="btn btn-ghost btn-sm">Detail</a></div></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    {{ $purchases->links() }}
    @endif
</section>
@endsection
