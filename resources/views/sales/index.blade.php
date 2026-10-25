@extends('layouts.app')

@section('title', 'Penjualan')
@section('subtitle', auth()->user()->isAdmin() ? 'Semua transaksi penjualan' : 'Transaksi penjualan Anda')
@section('actions') <a href="{{ route('sales.create') }}" class="btn btn-sm">Penjualan baru</a> @endsection

@section('content')
<section class="card">
    <form class="toolbar" method="GET">
        <input class="input grow" type="search" name="q" value="{{ request('q') }}" placeholder="Cari nomor atau pelanggan">
        <select class="input" name="status" aria-label="Filter status">
            <option value="">Semua status</option>
            <option value="completed" @selected(request('status') === 'completed')>Selesai</option>
            <option value="cancelled" @selected(request('status') === 'cancelled')>Dibatalkan</option>
        </select>
        <input class="input" type="date" name="from" value="{{ request('from') }}" aria-label="Dari tanggal">
        <input class="input" type="date" name="to" value="{{ request('to') }}" aria-label="Sampai tanggal">
        <button class="btn btn-ghost btn-sm" type="submit">Terapkan</button>
        @if(request()->hasAny(['q', 'status', 'from', 'to'])) <a href="{{ route('sales.index') }}" class="btn btn-ghost btn-sm">Reset</a> @endif
    </form>

    @if($sales->isEmpty())
        <div class="empty"><b>Belum ada transaksi yang cocok</b>Ubah filter atau buat penjualan baru.</div>
    @else
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Nomor</th><th>Tanggal</th><th>Pelanggan</th><th>Petugas</th><th>Status</th><th class="num">Total</th><th></th></tr></thead>
            <tbody>
            @foreach($sales as $sale)
                <tr>
                    <td><a href="{{ route('sales.show', $sale) }}"><b>{{ $sale->sale_number }}</b></a></td>
                    <td>{{ $sale->sold_at->format('d M Y H:i') }}</td>
                    <td>{{ $sale->customer_name ?: '—' }}</td>
                    <td>{{ $sale->user->name }}</td>
                    <td>@include('partials.badge', ['status' => $sale->status])</td>
                    <td class="num">@rupiah($sale->total)</td>
                    <td><div class="actions"><a href="{{ route('sales.show', $sale) }}" class="btn btn-ghost btn-sm">Detail</a></div></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    {{ $sales->links() }}
    @endif
</section>
@endsection
