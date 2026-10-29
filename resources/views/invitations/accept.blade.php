<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accept Invitation – URL Shortener</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f4f4f6; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .box { background: #fff; border: 1px solid #e0e0e8; border-radius: 8px; padding: 2rem 2.25rem; width: 400px; box-shadow: 0 2px 8px rgba(0,0,0,.06); }
        .brand { text-align: center; font-size: .85rem; font-weight: 700; color: #1a1a2e; margin-bottom: 1.5rem; }
        h1 { font-size: 1.2rem; font-weight: 700; margin-bottom: .4rem; color: #1a1a2e; }
        .meta { color: #666; font-size: .84rem; margin-bottom: 1.5rem; line-height: 1.5; }
        .field { margin-bottom: 1rem; }
        label { display: block; font-size: .82rem; font-weight: 600; margin-bottom: .28rem; color: #333; }
        input { width: 100%; padding: .5rem .75rem; border: 1px solid #ccc; border-radius: 4px; font-size: .9rem; }
        input:focus { outline: 2px solid #1a1a2e; border-color: #1a1a2e; }
        .btn { display: block; width: 100%; padding: .58rem; background: #1a1a2e; color: #fff; border: none; border-radius: 4px; font-size: .92rem; font-weight: 600; cursor: pointer; margin-top: 1.25rem; }
        .btn:hover { background: #2d2d50; }
        .field-error { color: #c0392b; font-size: .8rem; margin-top: .25rem; }
        .alert { padding: .65rem .85rem; border-radius: 4px; margin-bottom: 1rem; font-size: .84rem; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
<div class="box">
    <p class="brand">URL Shortener</p>
    <h1>Set Your Password</h1>
    <p class="meta">
        Creating account for <strong>{{ $invitation->email }}</strong><br>
        Company: <strong>{{ $invitation->company->name }}</strong>
    </p>

    @if($errors->any())
        <div class="alert">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('invitations.accept.store', $invitation->token) }}">
        @csrf
        <div class="field">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required autofocus autocomplete="new-password">
            @error('password')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>
        <div class="field">
            <label for="password_confirmation">Confirm Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
        </div>
        <button type="submit" class="btn">Create Account</button>
    </form>
</div>
</body>
</html>

