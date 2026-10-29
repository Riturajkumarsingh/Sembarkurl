<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless($user->isAdmin(), 403);

        $teamMembers = $user->company->users()
            ->withCount('shortUrls as total_urls')
            ->withSum('shortUrls as total_hits', 'hits')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('team.index', compact('teamMembers'));
    }
}
