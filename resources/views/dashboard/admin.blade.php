@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')

<div class="section-header">
    <h1>Generated Short URLs</h1>
    <div class="section-header-actions">
        <a href="{{ route('short-urls.export', request()->query()) }}" class="btn btn-secondary btn-sm">Download CSV</a>
        <a href="{{ route('short-urls.index') }}" class="btn btn-secondary btn-sm">View All</a>
        <a href="{{ route('short-urls.create') }}" class="btn btn-primary">Generate</a>
    </div>
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
    </div>

    <p class="text-muted" style="margin-bottom:.75rem">Showing {{ $urls->count() }} of {{ $urls->total() }}</p>

    <table>
        <thead>
            <tr>
                <th>Short URL</th>
                <th>Long URL</th>
                <th>Created By</th>
                <th>Hits</th>
                <th>Created On</th>
            </tr>
        </thead>
        <tbody>
        @forelse($urls as $url)
            <tr>
                <td><a href="{{ url('/s/' . $url->short_code) }}" target="_blank">{{ url('/s/' . $url->short_code) }}</a></td>
                <td><span class="truncate" title="{{ $url->original_url }}">{{ $url->original_url }}</span></td>
                <td>{{ $url->user->name ?? '—' }}</td>
                <td>{{ $url->hits }}</td>
                <td>{{ $url->created_at->format("d M 'y") }}</td>
            </tr>
        @empty
            <tr class="empty-row"><td colspan="5">No short URLs yet.</td></tr>
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

<div class="section-header" style="margin-top:2rem">
    <h2>Team Members</h2>
    <div class="section-header-actions">
        <a href="{{ route('team.index') }}" class="btn btn-secondary btn-sm">View All</a>
        <a href="{{ route('invitations.create') }}" class="btn btn-primary btn-sm">Invite</a>
    </div>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Total URLs</th>
                <th>Total Hits</th>
            </tr>
        </thead>
        <tbody>
        @forelse($teamMembers as $member)
            <tr>
                <td>{{ $member->name }}</td>
                <td>{{ $member->email }}</td>
                <td><span class="badge badge-{{ strtolower($member->role->value) }}">{{ $member->role->value }}</span></td>
                <td>{{ $member->total_urls ?? 0 }}</td>
                <td>{{ $member->total_hits ?? 0 }}</td>
            </tr>
        @empty
            <tr class="empty-row"><td colspan="5">No team members yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@endsection

