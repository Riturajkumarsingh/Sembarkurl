<?php

namespace App\Http\Controllers;

use App\Models\ShortUrl;
use Illuminate\Http\RedirectResponse;

class RedirectController extends Controller
{
    public function __invoke(string $code): RedirectResponse
    {
        $shortUrl = ShortUrl::where('short_code', $code)->firstOrFail();

        // Atomic increment so concurrent hits don't lose counts.
        $shortUrl->increment('hits');

        return redirect()->away($shortUrl->original_url);
    }
}
