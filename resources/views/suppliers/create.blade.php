@extends('layouts.app')

@section('title', 'Tambah pemasok')

@section('content')
<form method="POST" action="{{ route('suppliers.store') }}" class="card card-pad form-narrow">
    @csrf
    @include('suppliers._form')
    <div class="form-actions">
        <button class="btn" type="submit">Simpan pemasok</button>
        <a href="{{ route('suppliers.index') }}" class="btn btn-ghost">Batal</a>
    </div>
</form>
@endsection
