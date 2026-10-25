@extends('layouts.app')

@section('title', 'Ubah pengguna')

@section('content')
<form method="POST" action="{{ route('users.update', $user) }}" class="card card-pad form-narrow">
    @csrf @method('PUT')
    @include('users._form')
    <div class="form-actions">
        <button class="btn" type="submit">Simpan perubahan</button>
        <a href="{{ route('users.index') }}" class="btn btn-ghost">Batal</a>
    </div>
</form>
@endsection
