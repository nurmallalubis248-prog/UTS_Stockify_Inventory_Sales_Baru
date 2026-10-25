@extends('layouts.app')

@section('title', 'Ubah kategori')

@section('content')
<form method="POST" action="{{ route('categories.update', $category) }}" class="card card-pad form-narrow">
    @csrf @method('PUT')
    @include('categories._form')
    <div class="form-actions">
        <button class="btn" type="submit">Simpan perubahan</button>
        <a href="{{ route('categories.index') }}" class="btn btn-ghost">Batal</a>
    </div>
</form>
@endsection
