@extends('layouts.app')

@section('title', 'Penjualan baru')
@section('subtitle', 'Stok berkurang otomatis setelah transaksi berhasil')

@section('content')
<form method="POST" action="{{ route('sales.store') }}">
    @csrf
    <section class="card card-pad form-narrow" style="margin-bottom:20px">
        <div class="field" style="margin:0">
            <label for="customer_name">Nama pelanggan <span class="muted">(opsional)</span></label>
            <input id="customer_name" name="customer_name" class="input @error('customer_name') invalid @enderror" value="{{ old('customer_name') }}" maxlength="150">
            @error('customer_name') <div class="error">{{ $message }}</div> @enderror
        </div>
    </section>

    <section class="card">
        <div class="card-head"><h2>Item penjualan</h2><span class="muted">Hanya produk yang masih memiliki stok</span></div>
        @include('transactions._items', ['mode' => 'sale', 'products' => $products])
    </section>

    <div class="form-actions" style="padding-top:18px">
        <button class="btn" type="submit">Simpan penjualan</button>
        <a href="{{ route('sales.index') }}" class="btn btn-ghost">Batal</a>
    </div>
    <p class="muted">Harga dan total dihitung ulang oleh server saat disimpan; angka di atas hanya perkiraan.</p>
</form>
@endsection
