<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
@php
    $user = auth()->user();
    $lowStockCount = \App\Models\Product::lowStock()->count();
@endphp
<div class="app">
    <aside class="sidebar" id="sidebar">
        <a href="{{ route('dashboard') }}" class="brand">
            <span class="brand-mark"></span>
            <span><strong>Stockify</strong><span>Inventory &amp; Sales</span></span>
        </a>

        <nav class="nav">
            <div class="nav-group">
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">@include('partials.icon', ['name' => 'home']) Dashboard</a>
            </div>

            <div class="nav-group">
                <div class="nav-title">Data master</div>
                <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') && ! request()->routeIs('products.low-stock') ? 'active' : '' }}">@include('partials.icon', ['name' => 'box']) Produk</a>
                <a href="{{ route('products.low-stock') }}" class="{{ request()->routeIs('products.low-stock') ? 'active' : '' }}">@include('partials.icon', ['name' => 'alert']) Stok rendah
                    @if($lowStockCount) <span class="count">{{ $lowStockCount }}</span> @endif
                </a>
                @can('manage-master')
                    <a href="{{ route('categories.index') }}" class="{{ request()->routeIs('categories.*') ? 'active' : '' }}">@include('partials.icon', ['name' => 'tag']) Kategori</a>
                    <a href="{{ route('suppliers.index') }}" class="{{ request()->routeIs('suppliers.*') ? 'active' : '' }}">@include('partials.icon', ['name' => 'truck']) Pemasok</a>
                @endcan
            </div>

            @can('manage-transactions')
            <div class="nav-group">
                <div class="nav-title">Transaksi</div>
                <a href="{{ route('purchases.index') }}" class="{{ request()->routeIs('purchases.*') ? 'active' : '' }}">@include('partials.icon', ['name' => 'purchase']) Pembelian</a>
                <a href="{{ route('sales.index') }}" class="{{ request()->routeIs('sales.*') ? 'active' : '' }}">@include('partials.icon', ['name' => 'sale']) Penjualan</a>
            </div>
            @endcan

            @can('manage-users')
                <div class="nav-group">
                    <div class="nav-title">Administrasi</div>
                    <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">@include('partials.icon', ['name' => 'users']) Pengguna</a>
                </div>
            @endcan
        </nav>

        <div class="sidebar-foot">Masuk sebagai {{ $user->roleLabel() }}</div>
    </aside>

    <div class="main">
        <header class="topbar">
            <button class="menu-btn" type="button" id="menu-btn" aria-label="Buka menu">☰</button>
            <div class="page-title">
                <h1>@yield('title', 'Dashboard')</h1>
                @hasSection('subtitle') <p>@yield('subtitle')</p> @endif
            </div>
            <div class="topbar-right">
                @yield('actions')
                <div class="user-chip">
                    <span class="avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    <div><b>{{ $user->name }}</b><small>{{ $user->email }}</small></div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-ghost btn-sm" type="submit">Keluar</button>
                </form>
            </div>
        </header>

        <main class="content">
            @if(session('success'))
                <div class="alert alert-success" role="status">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-error" role="alert">
                    <ul>
                        @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script>
    document.getElementById('menu-btn').addEventListener('click', function () { document.body.classList.toggle('nav-open'); });
    document.querySelector('.main').addEventListener('click', function () { document.body.classList.remove('nav-open'); });
</script>
@stack('scripts')
</body>
</html>
