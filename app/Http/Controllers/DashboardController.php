<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\ShortUrl;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $interval = $request->query('interval');

        if ($user->isSuperAdmin()) {
            $urls = ShortUrl::with(['user', 'company'])
                ->visibleTo($user)
                ->inInterval($interval)
                ->latest()
                ->paginate(10)
                ->withQueryString();

            $companies = Company::withCount('users')
                ->withSum('shortUrls as total_urls', 'id')
                ->withSum('shortUrls as total_hits', 'hits')
                ->orderBy('name')
                ->paginate(10)
                ->withQueryString();

            return view('dashboard.superadmin', compact('urls', 'companies', 'interval'));
        }

        if ($user->isAdmin()) {
            $urls = ShortUrl::with('user')
                ->visibleTo($user)
                ->inInterval($interval)
                ->latest()
                ->paginate(10)
                ->withQueryString();

            $teamMembers = $user->company->users()
                ->withCount('shortUrls as total_urls')
                ->withSum('shortUrls as total_hits', 'hits')
                ->orderBy('name')
                ->paginate(10)
                ->withQueryString();

            return view('dashboard.admin', compact('urls', 'teamMembers', 'interval'));
        }

        $urls = ShortUrl::visibleTo($user)
            ->inInterval($interval)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('dashboard.member', compact('urls', 'interval'));
    }
}
