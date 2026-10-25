@extends('layouts.app')

@section('title', 'Pembelian baru')
@section('subtitle', 'Stok bertambah otomatis setelah transaksi tersimpan')

@section('content')
<form method="POST" action="{{ route('purchases.store') }}">
    @csrf
    <section class="card card-pad form-narrow" style="margin-bottom:20px">
        <div class="form-grid">
            <div class="field">
                <label for="supplier_id">Pemasok</label>
                <select id="supplier_id" name="supplier_id" class="input @error('supplier_id') invalid @enderror" required>
                    <option value="">Pilih pemasok</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected((string) old('supplier_id') === (string) $supplier->id)>{{ $supplier->name }}</option>
                    @endforeach
                </select>
                @error('supplier_id') <div class="error">{{ $message }}</div> @enderror
            </div>
            <div class="field">
                <label for="notes">Catatan <span class="muted">(opsional)</span></label>
                <input id="notes" name="notes" class="input @error('notes') invalid @enderror" value="{{ old('notes') }}" maxlength="500">
                @error('notes') <div class="error">{{ $message }}</div> @enderror
            </div>
        </div>
    </section>

    <section class="card">
        <div class="card-head"><h2>Item pembelian</h2></div>
        @include('transactions._items', ['mode' => 'purchase', 'products' => $products])
    </section>

    <div class="form-actions" style="padding-top:18px">
        <button class="btn" type="submit">Simpan pembelian</button>
        <a href="{{ route('purchases.index') }}" class="btn btn-ghost">Batal</a>
    </div>
</form>
@endsection
