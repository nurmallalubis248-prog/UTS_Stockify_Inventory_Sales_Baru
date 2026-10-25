@extends('layouts.app')

@section('title', 'Tambah pengguna')

@section('content')
<form method="POST" action="{{ route('users.store') }}" class="card card-pad form-narrow">
    @csrf
    @include('users._form')
    <div class="form-actions">
        <button class="btn" type="submit">Simpan pengguna</button>
        <a href="{{ route('users.index') }}" class="btn btn-ghost">Batal</a>
    </div>
</form>
@endsection
