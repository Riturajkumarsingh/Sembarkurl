@extends('layouts.app')
@section('title', 'Generate Short URL')
@section('content')

<h1>Generate Short URL</h1>

<div class="card" style="max-width:560px">
    <form method="POST" action="{{ route('short-urls.store') }}">
        @csrf
        <div class="field">
            <label for="original_url">Long URL</label>
            <input type="url" id="original_url" name="original_url"
                   value="{{ old('original_url') }}"
                   placeholder="https://example.com/some/very/long/path/to/shorten"
                   required autofocus>
            @error('original_url')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>
        <div style="display:flex;gap:.5rem;margin-top:.5rem">
            <button type="submit" class="btn btn-primary">Generate</button>
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

@endsection

