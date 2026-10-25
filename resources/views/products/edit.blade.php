@extends('layouts.app')

@section('title', 'Ubah produk')

@section('content')
<form method="POST" action="{{ route('products.update', $product) }}" class="card card-pad form-narrow">
    @csrf @method('PUT')
    @include('products._form')
    <div class="form-actions">
        <button class="btn" type="submit">Simpan perubahan</button>
        <a href="{{ route('products.index') }}" class="btn btn-ghost">Batal</a>
    </div>
</form>
@endsection
