@extends('layouts.app')

@section('title', 'Produk')
@section('subtitle', 'Daftar produk dan posisi stok')
@can('manage-master')
    @section('actions') <a href="{{ route('products.create') }}" class="btn btn-sm">Tambah produk</a> @endsection
@endcan

@section('content')
<section class="card">
    <form class="toolbar" method="GET">
        <input class="input grow" type="search" name="q" value="{{ request('q') }}" placeholder="Cari SKU atau nama produk">
        <select class="input" name="category_id" aria-label="Filter kategori">
            <option value="">Semua kategori</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        <button class="btn btn-ghost btn-sm" type="submit">Terapkan</button>
        @if(request()->hasAny(['q', 'category_id'])) <a href="{{ route('products.index') }}" class="btn btn-ghost btn-sm">Reset</a> @endif
    </form>

    @if($products->isEmpty())
        <div class="empty"><b>Produk tidak ditemukan</b>Coba kata kunci atau kategori lain.</div>
    @else
        @include('products._table')
        {{ $products->links() }}
    @endif
</section>
@endsection
