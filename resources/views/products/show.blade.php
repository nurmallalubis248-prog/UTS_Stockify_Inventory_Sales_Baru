@extends('layouts.app')

@section('title', $product->name)
@section('subtitle', 'SKU '.$product->sku)
@can('manage-master')
    @section('actions') <a href="{{ route('products.edit', $product) }}" class="btn btn-sm">Ubah produk</a> @endsection
@endcan

@section('content')
<div class="grid-2">
    <section class="card card-pad">
        <dl class="kv">
            <dt>Kategori</dt><dd>{{ $product->category->name }}</dd>
            <dt>Harga beli</dt><dd>@rupiah($product->purchase_price)</dd>
            <dt>Harga jual</dt><dd>@rupiah($product->selling_price)</dd>
            <dt>Stok saat ini</dt><dd>{{ $product->stock }} @if($product->isLowStock()) <span class="badge bad">Stok rendah</span> @else <span class="badge ok">Aman</span> @endif</dd>
            <dt>Stok minimum</dt><dd>{{ $product->minimum_stock }}</dd>
        </dl>
    </section>
    <section class="card card-pad">
        <dl class="kv">
            <dt>Total terjual</dt><dd>{{ number_format($sold, 0, ',', '.') }} unit</dd>
            <dt>Total dibeli</dt><dd>{{ number_format($bought, 0, ',', '.') }} unit</dd>
            <dt>Dibuat</dt><dd>{{ $product->created_at->format('d M Y') }}</dd>
        </dl>
        <p class="muted" style="margin-bottom:0">Angka di atas hanya menghitung transaksi yang tidak dibatalkan.</p>
    </section>
</div>
<p><a href="{{ route('products.index') }}" class="btn btn-ghost btn-sm">Kembali ke daftar produk</a></p>
@endsection
