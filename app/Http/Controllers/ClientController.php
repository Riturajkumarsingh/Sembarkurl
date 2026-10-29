<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->isSuperAdmin(), 403);

        $companies = Company::withCount('users')
            ->withSum('shortUrls as total_urls', 'id')
            ->withSum('shortUrls as total_hits', 'hits')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('clients.index', compact('companies'));
    }
}
