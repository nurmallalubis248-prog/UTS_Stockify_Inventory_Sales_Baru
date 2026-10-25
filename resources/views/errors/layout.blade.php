<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') · {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        .err-wrap { min-height: 100vh; display: grid; place-items: center; padding: 24px; }
        .err-card { max-width: 460px; width: 100%; text-align: center; padding: 40px 28px; }
        .err-code { font-size: 64px; font-weight: 700; color: var(--matcha); line-height: 1; margin-bottom: 12px; }
        .err-card h1 { font-size: 20px; margin-bottom: 8px; }
        .err-card p { color: var(--ink-soft); margin: 0 0 24px; }
        .err-actions { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
    </style>
</head>
<body>
    <div class="err-wrap">
        <div class="card err-card">
            <div class="err-code">@yield('code')</div>
            <h1>@yield('title')</h1>
            <p>@yield('message')</p>
            <div class="err-actions">
                <a class="btn" href="{{ url('/') }}">Kembali ke Dashboard</a>
                <a class="btn btn-ghost" href="javascript:history.back()">Halaman sebelumnya</a>
            </div>
        </div>
    </div>
</body>
</html>
