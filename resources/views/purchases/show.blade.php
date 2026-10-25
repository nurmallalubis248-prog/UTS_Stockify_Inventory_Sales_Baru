@extends('layouts.app')

@section('title', $purchase->purchase_number)
@section('subtitle', 'Detail pembelian')

@section('content')
<div class="grid-2" style="margin-bottom:20px">
    <section class="card card-pad">
        <dl class="kv">
            <dt>Status</dt><dd>@include('partials.badge', ['status' => $purchase->status])</dd>
            <dt>Tanggal</dt><dd>{{ $purchase->purchased_at->format('d M Y H:i') }}</dd>
            <dt>Pemasok</dt><dd>{{ $purchase->supplier->name }}</dd>
            <dt>Petugas</dt><dd>{{ $purchase->user->name }}</dd>
            <dt>Catatan</dt><dd>{{ $purchase->notes ?: '—' }}</dd>
            @if($purchase->cancelled_at)<dt>Dibatalkan</dt><dd>{{ $purchase->cancelled_at->format('d M Y H:i') }}</dd>@endif
        </dl>
    </section>
    <section class="card card-pad">
        <span class="muted">Total pembelian</span>
        <div style="font-size:28px;font-weight:700;margin:4px 0 14px">@rupiah($purchase->total)</div>
        @can('cancel-transaction')
            @if($purchase->status === 'confirmed')
                <form method="POST" action="{{ route('purchases.cancel', $purchase) }}" onsubmit="return confirm('Batalkan pembelian ini? Stok akan ditarik kembali.')">
                    @csrf
                    <button class="btn btn-danger btn-sm" type="submit">Batalkan pembelian</button>
                </form>
            @endif
        @endcan
    </section>
</div>

<section class="card">
    <div class="card-head"><h2>Item</h2></div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Produk</th><th class="num">Jumlah</th><th class="num">Harga beli</th><th class="num">Subtotal</th></tr></thead>
            <tbody>
            @foreach($purchase->items as $item)
                <tr>
                    <td><b>{{ $item->product->name }}</b><div class="sub">{{ $item->product->sku }}</div></td>
                    <td class="num">{{ $item->quantity }}</td>
                    <td class="num">@rupiah($item->unit_cost)</td>
                    <td class="num">@rupiah($item->subtotal)</td>
                </tr>
            @endforeach
            <tr class="total-row"><td colspan="3" class="num">Total</td><td class="num">@rupiah($purchase->total)</td></tr>
            </tbody>
        </table>
    </div>
</section>
<p style="margin-top:18px"><a href="{{ route('purchases.index') }}" class="btn btn-ghost btn-sm">Kembali ke daftar pembelian</a></p>
@endsection
