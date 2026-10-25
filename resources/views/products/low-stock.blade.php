@extends('layouts.app')

@section('title', 'Stok rendah')
@section('subtitle', 'Produk dengan stok sama dengan atau di bawah batas minimum')
@section('actions') <a href="{{ route('purchases.create') }}" class="btn btn-sm">Catat pembelian</a> @endsection

@section('content')
<section class="card">
    @if($products->isEmpty())
        <div class="empty"><b>Semua stok aman</b>Tidak ada produk yang perlu di-restock saat ini.</div>
    @else
        @include('products._table')
        {{ $products->links() }}
    @endif
</section>
@endsection
