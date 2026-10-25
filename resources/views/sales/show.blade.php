@extends('layouts.app')

@section('title', $sale->sale_number)
@section('subtitle', 'Detail penjualan')

@section('content')
<div class="grid-2" style="margin-bottom:20px">
    <section class="card card-pad">
        <dl class="kv">
            <dt>Status</dt><dd>@include('partials.badge', ['status' => $sale->status])</dd>
            <dt>Tanggal</dt><dd>{{ $sale->sold_at->format('d M Y H:i') }}</dd>
            <dt>Pelanggan</dt><dd>{{ $sale->customer_name ?: '—' }}</dd>
            <dt>Petugas</dt><dd>{{ $sale->user->name }}</dd>
            @if($sale->cancelled_at)<dt>Dibatalkan</dt><dd>{{ $sale->cancelled_at->format('d M Y H:i') }}</dd>@endif
        </dl>
    </section>
    <section class="card card-pad">
        <span class="muted">Total penjualan</span>
        <div style="font-size:28px;font-weight:700;margin:4px 0 14px">@rupiah($sale->total)</div>
        @can('cancel-transaction')
            @if($sale->status === 'completed')
                <form method="POST" action="{{ route('sales.cancel', $sale) }}" onsubmit="return confirm('Batalkan penjualan ini? Stok akan dikembalikan.')">
                    @csrf
                    <button class="btn btn-danger btn-sm" type="submit">Batalkan penjualan</button>
                </form>
            @endif
        @endcan
    </section>
</div>

<section class="card">
    <div class="card-head"><h2>Item</h2></div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Produk</th><th class="num">Jumlah</th><th class="num">Harga satuan</th><th class="num">Subtotal</th></tr></thead>
            <tbody>
            @foreach($sale->items as $item)
                <tr>
                    <td><b>{{ $item->product->name }}</b><div class="sub">{{ $item->product->sku }}</div></td>
                    <td class="num">{{ $item->quantity }}</td>
                    <td class="num">@rupiah($item->unit_price)</td>
                    <td class="num">@rupiah($item->subtotal)</td>
                </tr>
            @endforeach
            <tr class="total-row"><td colspan="3" class="num">Total</td><td class="num">@rupiah($sale->total)</td></tr>
            </tbody>
        </table>
    </div>
</section>
<p style="margin-top:18px"><a href="{{ route('sales.index') }}" class="btn btn-ghost btn-sm">Kembali ke daftar penjualan</a></p>
@endsection
