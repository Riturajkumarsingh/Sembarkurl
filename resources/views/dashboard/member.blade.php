@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')

<div class="section-header">
    <h1>Generated Short URLs</h1>
    <a href="{{ route('short-urls.create') }}" class="btn btn-primary">Generate</a>
</div>

<div class="card">
    <div class="toolbar">
        <form method="GET" action="{{ route('dashboard') }}" style="display:flex;gap:.5rem;align-items:center">
            <select name="interval" onchange="this.form.submit()">
                <option value="">All Time</option>
                <option value="this_month" @selected($interval === 'this_month')>This Month</option>
                <option value="last_month" @selected($interval === 'last_month')>Last Month</option>
                <option value="last_week"  @selected($interval === 'last_week')>Last Week</option>
                <option value="today"      @selected($interval === 'today')>Today</option>
            </select>
        </form>
        <a href="{{ route('short-urls.export', request()->query()) }}" class="btn btn-secondary btn-sm">Download CSV</a>
    </div>

    <p class="text-muted" style="margin-bottom:.75rem">Showing {{ $urls->count() }} of {{ $urls->total() }}</p>

    <table>
        <thead>
            <tr>
                <th>Short URL</th>
                <th>Long URL</th>
                <th>Hits</th>
                <th>Created On</th>
            </tr>
        </thead>
        <tbody>
        @forelse($urls as $url)
            <tr>
                <td><a href="{{ url('/s/' . $url->short_code) }}" target="_blank">{{ url('/s/' . $url->short_code) }}</a></td>
                <td><span class="truncate" title="{{ $url->original_url }}">{{ $url->original_url }}</span></td>
                <td>{{ $url->hits }}</td>
                <td>{{ $url->created_at->format("d M 'y") }}</td>
            </tr>
        @empty
            <tr class="empty-row"><td colspan="4">No short URLs yet. Click Generate to create one.</td></tr>
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

