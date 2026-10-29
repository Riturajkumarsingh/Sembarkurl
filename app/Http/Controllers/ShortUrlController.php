<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShortUrlRequest;
use App\Models\ShortUrl;
use App\Services\ShortCodeGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShortUrlController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', ShortUrl::class);

        $user = $request->user();
        $interval = $request->query('interval');

        $urls = ShortUrl::with(['user', 'company'])
            ->visibleTo($user)
            ->inInterval($interval)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('short-urls.index', compact('urls', 'interval'));
    }

    public function create(): View
    {
        $this->authorize('create', ShortUrl::class);

        return view('short-urls.create');
    }

    public function store(StoreShortUrlRequest $request, ShortCodeGenerator $generator): RedirectResponse
    {
        $code = $generator->generate();

        ShortUrl::create([
            'company_id' => $request->user()->company_id,
            'user_id' => $request->user()->id,
            'original_url' => $request->validated('original_url'),
            'short_code' => $code,
        ]);

        $shortLink = url('/s/'.$code);

        return redirect()->route('dashboard')->with('success', "Short URL created: {$shortLink}");
    }
}
