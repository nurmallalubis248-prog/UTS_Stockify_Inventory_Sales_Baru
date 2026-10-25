@extends('layouts.app')

@section('title', 'Dashboard')
@section('subtitle', 'Ringkasan stok dan transaksi terkini')

@section('actions')
    @can('manage-transactions')
        <a href="{{ route('sales.create') }}" class="btn btn-sm">Penjualan baru</a>
        <a href="{{ route('purchases.create') }}" class="btn btn-ghost btn-sm">Pembelian baru</a>
    @endcan
@endsection

@section('content')
<div class="stats">
    @if(auth()->user()->isAdmin())
        <div class="card stat lead"><span>Penjualan hari ini</span><strong>@rupiah($stats['sales_today'])</strong></div>
        <div class="card stat"><span>Penjualan bulan ini</span><strong>@rupiah($stats['sales_month'])</strong></div>
    @endif
    <div class="card stat"><span>Total produk</span><strong>{{ number_format($stats['products'], 0, ',', '.') }}</strong></div>
    <div class="card stat {{ $stats['low_stock'] ? 'warn' : '' }}"><span>Produk stok rendah</span><strong>{{ $stats['low_stock'] }}</strong></div>
    @can('manage-transactions')
    <div class="card stat"><span>{{ auth()->user()->isAdmin() ? 'Transaksi penjualan' : 'Penjualan saya' }}</span><strong>{{ $stats['sales_count'] }}</strong></div>
    <div class="card stat"><span>{{ auth()->user()->isAdmin() ? 'Transaksi pembelian' : 'Pembelian saya' }}</span><strong>{{ $stats['purchases_count'] }}</strong></div>
    @endcan
</div>

<div class="grid-2">
    <section class="card">
        <div class="card-head">
            <h2>Perlu restock</h2>
            <a href="{{ route('products.low-stock') }}" class="btn btn-ghost btn-sm">Lihat semua</a>
        </div>
        @if($lowStock->isEmpty())
            <div class="empty"><b>Semua stok aman</b>Tidak ada produk di bawah batas minimum.</div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Produk</th><th class="num">Stok</th><th class="num">Minimum</th></tr></thead>
                    <tbody>
                    @foreach($lowStock as $product)
                        <tr>
                            <td><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a><div class="sub">{{ $product->sku }} · {{ $product->category->name }}</div></td>
                            <td class="num"><span class="badge bad">{{ $product->stock }}</span></td>
                            <td class="num">{{ $product->minimum_stock }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    @can('manage-transactions')
    <section class="card">
        <div class="card-head">
            <h2>Penjualan terbaru</h2>
            <a href="{{ route('sales.index') }}" class="btn btn-ghost btn-sm">Lihat semua</a>
        </div>
        @if($recentSales->isEmpty())
            <div class="empty"><b>Belum ada penjualan</b>Transaksi yang Anda buat akan muncul di sini.</div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Nomor</th><th>Status</th><th class="num">Total</th></tr></thead>
                    <tbody>
                    @foreach($recentSales as $sale)
                        <tr>
                            <td><a href="{{ route('sales.show', $sale) }}">{{ $sale->sale_number }}</a><div class="sub">{{ $sale->sold_at->format('d M Y H:i') }} · {{ $sale->user->name }}</div></td>
                            <td>@include('partials.badge', ['status' => $sale->status])</td>
                            <td class="num">@rupiah($sale->total)</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
    @endcan
</div>

@can('manage-transactions')
<section class="card" style="margin-top:20px">
    <div class="card-head">
        <h2>Pembelian terbaru</h2>
        <a href="{{ route('purchases.index') }}" class="btn btn-ghost btn-sm">Lihat semua</a>
    </div>
    @if($recentPurchases->isEmpty())
        <div class="empty"><b>Belum ada pembelian</b>Catat stok masuk dari pemasok untuk menambah persediaan.</div>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Nomor</th><th>Pemasok</th><th>Status</th><th class="num">Total</th></tr></thead>
                <tbody>
                @foreach($recentPurchases as $purchase)
                    <tr>
                        <td><a href="{{ route('purchases.show', $purchase) }}">{{ $purchase->purchase_number }}</a><div class="sub">{{ $purchase->purchased_at->format('d M Y H:i') }}</div></td>
                        <td>{{ $purchase->supplier->name }}</td>
                        <td>@include('partials.badge', ['status' => $purchase->status])</td>
                        <td class="num">@rupiah($purchase->total)</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
@endcan
@endsection
