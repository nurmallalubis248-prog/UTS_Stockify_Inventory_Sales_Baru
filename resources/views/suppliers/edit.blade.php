@extends('layouts.app')

@section('title', 'Ubah pemasok')

@section('content')
<form method="POST" action="{{ route('suppliers.update', $supplier) }}" class="card card-pad form-narrow">
    @csrf @method('PUT')
    @include('suppliers._form')
    <div class="form-actions">
        <button class="btn" type="submit">Simpan perubahan</button>
        <a href="{{ route('suppliers.index') }}" class="btn btn-ghost">Batal</a>
    </div>
</form>
@endsection
