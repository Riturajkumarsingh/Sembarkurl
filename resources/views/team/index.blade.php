@extends('layouts.app')
@section('title', 'Team Members')
@section('content')

<div class="section-header">
    <h1>Team Members</h1>
    <a href="{{ route('invitations.create') }}" class="btn btn-primary">Invite</a>
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

    <div class="pagination">
        @if($teamMembers->onFirstPage())
            <span class="pg-disabled">Prev</span>
        @else
            <a href="{{ $teamMembers->previousPageUrl() }}">Prev</a>
        @endif
        <span>Page {{ $teamMembers->currentPage() }} of {{ $teamMembers->lastPage() }}</span>
        @if($teamMembers->hasMorePages())
            <a href="{{ $teamMembers->nextPageUrl() }}">Next</a>
        @else
            <span class="pg-disabled">Next</span>
        @endif
    </div>
</div>

@endsection

