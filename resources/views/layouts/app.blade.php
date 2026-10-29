<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>URL Shortener – @yield('title', 'Dashboard')</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f4f4f6; color: #1a1a2e; min-height: 100vh; }
        header { background: #1a1a2e; color: #fff; padding: 0 2rem; display: flex; align-items: center; justify-content: space-between; height: 56px; box-shadow: 0 2px 4px rgba(0,0,0,.2); }
        header .brand { font-size: 1rem; font-weight: 700; letter-spacing: .5px; color: #fff; text-decoration: none; }
        header .page-title { font-size: .8rem; color: #8888aa; }
        header nav { display: flex; align-items: center; gap: 1rem; }
        header nav span { font-size: .85rem; color: #ccc; }
        main { max-width: 1100px; margin: 2rem auto; padding: 0 1.5rem; }
        h1 { font-size: 1.35rem; font-weight: 700; margin-bottom: 1.25rem; color: #1a1a2e; }
        h2 { font-size: 1rem; font-weight: 700; color: #333; margin-bottom: .5rem; }
        .alert { padding: .75rem 1rem; border-radius: 5px; margin-bottom: 1rem; font-size: .88rem; line-height: 1.4; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error   { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-info    { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .card { background: #fff; border-radius: 6px; border: 1px solid #e0e0e8; padding: 1.25rem 1.5rem; margin-bottom: 1.5rem; }
        table { width: 100%; border-collapse: collapse; font-size: .88rem; }
        th { text-align: left; padding: .55rem .75rem; background: #f0f0f5; font-weight: 600; font-size: .8rem; text-transform: uppercase; letter-spacing: .4px; color: #555; border-bottom: 2px solid #ddd; }
        td { padding: .6rem .75rem; border-bottom: 1px solid #eee; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #fafafd; }
        .btn { display: inline-block; padding: .42rem 1rem; border-radius: 4px; font-size: .83rem; font-weight: 600; text-decoration: none; border: none; cursor: pointer; line-height: 1.4; }
        .btn-primary   { background: #1a1a2e; color: #fff; }
        .btn-primary:hover { background: #2d2d50; }
        .btn-secondary { background: #e4e4ec; color: #333; }
        .btn-secondary:hover { background: #d0d0dc; }
        .btn-sm { padding: .3rem .7rem; font-size: .78rem; }
        .field { margin-bottom: 1rem; }
        label { display: block; font-size: .82rem; font-weight: 600; margin-bottom: .28rem; color: #333; }
        input[type=text], input[type=email], input[type=password], input[type=url], select {
            width: 100%; padding: .48rem .75rem; border: 1px solid #ccc; border-radius: 4px; font-size: .9rem; color: #222;
        }
        input:focus, select:focus { outline: 2px solid #1a1a2e; outline-offset: 1px; border-color: #1a1a2e; }
        .field-error { color: #c0392b; font-size: .8rem; margin-top: .25rem; }
        .pagination { display: flex; align-items: center; gap: .4rem; margin-top: 1rem; font-size: .82rem; flex-wrap: wrap; }
        .pagination a, .pagination span { padding: .28rem .6rem; border: 1px solid #ddd; border-radius: 3px; text-decoration: none; color: #444; }
        .pagination a:hover { background: #eee; }
        .pagination .pg-disabled { color: #bbb; background: #fafafa; }
        .toolbar { display: flex; align-items: center; gap: .65rem; margin-bottom: 1rem; flex-wrap: wrap; }
        .section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: .75rem; }
        .section-header-actions { display: flex; gap: .5rem; }
        .truncate { max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: inline-block; vertical-align: bottom; }
        .text-muted { color: #888; font-size: .8rem; }
        .badge { display: inline-block; padding: .15rem .5rem; border-radius: 3px; font-size: .72rem; font-weight: 700; }
        .badge-admin     { background: #cfe2ff; color: #084298; }
        .badge-member    { background: #d1e7dd; color: #0a3622; }
        .badge-superadmin { background: #f8d7da; color: #842029; }
        .badge-sales, .badge-manager { background: #e8e8e8; color: #555; }
        .empty-row td { text-align: center; color: #999; padding: 2rem; font-style: italic; }
    </style>
</head>
<body>
<header>
    <a href="{{ route('dashboard') }}" class="brand">URL Shortener</a>
    <span class="page-title">@yield('title')</span>
    <nav>
        <span>{{ auth()->user()->name }}</span>
        <form method="POST" action="{{ route('logout') }}" style="margin:0">
            @csrf
            <button type="submit" class="btn btn-sm btn-secondary">Logout</button>
        </form>
    </nav>
</header>
<main>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('invitation_link'))
        <div class="alert alert-info">
            Invitation link — copy this to share or open in an incognito window to test:<br>
            <a href="{{ session('invitation_link') }}">{{ session('invitation_link') }}</a>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-error">
            <ul style="list-style:none;padding:0;margin:0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @yield('content')
</main>
</body>
</html>
