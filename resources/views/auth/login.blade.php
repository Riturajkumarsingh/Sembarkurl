<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – URL Shortener</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f4f4f6; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .login-wrap { background: #fff; border: 1px solid #e0e0e8; border-radius: 8px; padding: 2rem 2.25rem; width: 360px; box-shadow: 0 2px 8px rgba(0,0,0,.06); }
        .brand { text-align: center; font-size: .85rem; font-weight: 700; color: #1a1a2e; letter-spacing: .5px; margin-bottom: 1.5rem; }
        h1 { font-size: 1.2rem; font-weight: 700; text-align: center; margin-bottom: 1.5rem; color: #1a1a2e; }
        .field { margin-bottom: 1rem; }
        label { display: block; font-size: .82rem; font-weight: 600; margin-bottom: .28rem; color: #333; }
        input { width: 100%; padding: .5rem .75rem; border: 1px solid #ccc; border-radius: 4px; font-size: .9rem; }
        input:focus { outline: 2px solid #1a1a2e; border-color: #1a1a2e; }
        .btn { display: block; width: 100%; padding: .58rem; background: #1a1a2e; color: #fff; border: none; border-radius: 4px; font-size: .92rem; font-weight: 600; cursor: pointer; margin-top: 1.25rem; }
        .btn:hover { background: #2d2d50; }
        .alert { padding: .65rem .85rem; border-radius: 4px; margin-bottom: 1rem; font-size: .84rem; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
<div class="login-wrap">
    <p class="brand">URL Shortener</p>
    <h1>Sign In</h1>
    @if($errors->any())
        <div class="alert">{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="field">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">
        </div>
        <div class="field">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required autocomplete="current-password">
        </div>
        <button type="submit" class="btn">Login</button>
    </form>
</div>
</body>
</html>
