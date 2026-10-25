@extends('layouts.app')

@section('title', 'Tambah produk')

@section('content')
<form method="POST" action="{{ route('products.store') }}" class="card card-pad form-narrow">
    @csrf
    @include('products._form')
    <div class="form-actions">
        <button class="btn" type="submit">Simpan produk</button>
        <a href="{{ route('products.index') }}" class="btn btn-ghost">Batal</a>
    </div>
</form>
@endsection
