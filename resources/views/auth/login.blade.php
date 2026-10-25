@extends('layouts.guest')

@section('title', 'Masuk')

@section('content')
<div class="auth">
    <section class="auth-art">
        <div class="brand">
            <span class="brand-mark"></span>
            <span><strong>Stockify</strong><span>Inventory &amp; Sales</span></span>
        </div>
        <div>
            <h2>Stok akurat, penjualan tercatat rapi.</h2>
            <p>Kelola produk, pemasok, pembelian, dan penjualan dalam satu tempat. Stok berubah otomatis setiap transaksi berhasil.</p>
        </div>
        <small style="color:var(--matcha-soft)">Web Programming 2 · UTS Integrated Laravel Project</small>
    </section>

    <section class="auth-form">
        <form method="POST" action="{{ route('login.attempt') }}" novalidate>
            @csrf
            <h1>Masuk ke akun Anda</h1>
            <p class="lead">Gunakan email dan kata sandi yang terdaftar.</p>

            <div class="field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" class="input @error('email') invalid @enderror" autocomplete="username" autofocus required>
                @error('email') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label for="password">Kata sandi</label>
                <input id="password" name="password" type="password" class="input @error('password') invalid @enderror" autocomplete="current-password" required>
                @error('password') <div class="error">{{ $message }}</div> @enderror
            </div>

            <label class="check"><input type="checkbox" name="remember" value="1"> Ingat saya di perangkat ini</label>

            <button class="btn" type="submit" style="width:100%">Masuk</button>

            @if(app()->environment('local'))
                <div class="demo-hint">
                    Akun demo (lingkungan lokal):<br>
                    <b>admin@example.com</b> / password<br>
                    <b>staff@example.com</b> / password<br>
                    <b>user@example.com</b> / password
                </div>
            @endif
        </form>
    </section>
</div>
@endsection
