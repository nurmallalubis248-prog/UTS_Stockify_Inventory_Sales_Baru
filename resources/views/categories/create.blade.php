@extends('layouts.app')

@section('title', 'Tambah kategori')

@section('content')
<form method="POST" action="{{ route('categories.store') }}" class="card card-pad form-narrow">
    @csrf
    @include('categories._form')
    <div class="form-actions">
        <button class="btn" type="submit">Simpan kategori</button>
        <a href="{{ route('categories.index') }}" class="btn btn-ghost">Batal</a>
    </div>
</form>
@endsection
