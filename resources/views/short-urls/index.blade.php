@extends('layouts.app')
@section('title', 'All Short URLs')
@section('content')

<div class="section-header">
    <h1>All Short URLs</h1>
    <div class="section-header-actions">
        <a href="{{ route('short-urls.export', request()->query()) }}" class="btn btn-secondary btn-sm">Download CSV</a>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm">Back to Dashboard</a>
        @can('create', App\Models\ShortUrl::class)
            <a href="{{ route('short-urls.create') }}" class="btn btn-primary">Generate</a>
        @endcan
    </div>
</div>

<div class="card">
    <div class="toolbar">
        <form method="GET" action="{{ route('short-urls.index') }}" style="display:flex;gap:.5rem;align-items:center">
            <select name="interval" onchange="this.form.submit()">
                <option value="">All Time</option>
                <option value="this_month" @selected($interval === 'this_month')>This Month</option>
                <option value="last_month" @selected($interval === 'last_month')>Last Month</option>
                <option value="last_week"  @selected($interval === 'last_week')>Last Week</option>
                <option value="today"      @selected($interval === 'today')>Today</option>
            </select>
        </form>
    </div>

    <p class="text-muted" style="margin-bottom:.75rem">Showing {{ $urls->count() }} of {{ $urls->total() }}</p>

    <table>
        <thead>
            <tr>
                <th>Short URL</th>
                <th>Long URL</th>
                @if(auth()->user()->isSuperAdmin())
                    <th>Client</th>
                @elseif(auth()->user()->isAdmin())
                    <th>Created By</th>
                @endif
                <th>Hits</th>
                <th>Created On</th>
            </tr>
        </thead>
        <tbody>
        @forelse($urls as $url)
            <tr>
                <td><a href="{{ url('/s/' . $url->short_code) }}" target="_blank">{{ url('/s/' . $url->short_code) }}</a></td>
                <td><span class="truncate" title="{{ $url->original_url }}">{{ $url->original_url }}</span></td>
                @if(auth()->user()->isSuperAdmin())
                    <td>{{ $url->company->name ?? '—' }}</td>
                @elseif(auth()->user()->isAdmin())
                    <td>{{ $url->user->name ?? '—' }}</td>
                @endif
                <td>{{ $url->hits }}</td>
                <td>{{ $url->created_at->format("d M 'y") }}</td>
            </tr>
        @empty
            <tr class="empty-row"><td colspan="6">No short URLs found.</td></tr>
        @endforelse
        </tbody>
    </table>

    <div class="pagination">
        @if($urls->onFirstPage())
            <span class="pg-disabled">Prev</span>
        @else
            <a href="{{ $urls->previousPageUrl() }}">Prev</a>
        @endif
        <span>Page {{ $urls->currentPage() }} of {{ $urls->lastPage() }}</span>
        @if($urls->hasMorePages())
            <a href="{{ $urls->nextPageUrl() }}">Next</a>
        @else
            <span class="pg-disabled">Next</span>
        @endif
    </div>
</div>

@endsection

