@extends('layouts.app')
@section('title', 'Clients')
@section('content')

<div class="section-header">
    <h1>Clients</h1>
    <a href="{{ route('invitations.create') }}" class="btn btn-primary">Invite New Client</a>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Client Name</th>
                <th>Users</th>
                <th>Total URLs</th>
                <th>Total Hits</th>
            </tr>
        </thead>
        <tbody>
        @forelse($companies as $company)
            <tr>
                <td>{{ $company->name }}</td>
                <td>{{ $company->users_count }}</td>
                <td>{{ $company->total_urls ?? 0 }}</td>
                <td>{{ $company->total_hits ?? 0 }}</td>
            </tr>
        @empty
            <tr class="empty-row"><td colspan="4">No clients yet.</td></tr>
        @endforelse
        </tbody>
    </table>

    <div class="pagination">
        @if($companies->onFirstPage())
            <span class="pg-disabled">Prev</span>
        @else
            <a href="{{ $companies->previousPageUrl() }}">Prev</a>
        @endif
        <span>Page {{ $companies->currentPage() }} of {{ $companies->lastPage() }}</span>
        @if($companies->hasMorePages())
            <a href="{{ $companies->nextPageUrl() }}">Next</a>
        @else
            <span class="pg-disabled">Next</span>
        @endif
    </div>
</div>

@endsection

