@extends('layouts.app')
@section('title', auth()->user()->isSuperAdmin() ? 'Invite New Client' : 'Invite New Team Member')
@section('content')

<h1>{{ auth()->user()->isSuperAdmin() ? 'Invite New Client' : 'Invite New Team Member' }}</h1>

<div class="card" style="max-width:480px">
    <form method="POST" action="{{ route('invitations.store') }}">
        @csrf

        @if(auth()->user()->isSuperAdmin())
            <div class="field">
                <label for="company_name">Client Name</label>
                <input type="text" id="company_name" name="company_name"
                       value="{{ old('company_name') }}" required autofocus>
                @error('company_name')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>
        @endif

        <div class="field">
            <label for="name">Name <span style="font-weight:400;color:#888">(optional)</span></label>
            <input type="text" id="name" name="name" value="{{ old('name') }}"
                   @if(!auth()->user()->isSuperAdmin()) autofocus @endif>
            @error('name')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="field">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required>
            @error('email')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        @if(auth()->user()->isSuperAdmin())
            {{-- SuperAdmin always invites an Admin for a new company. --}}
            <input type="hidden" name="role" value="Admin">
        @else
            <div class="field">
                <label for="role">Role</label>
                <select id="role" name="role">
                    <option value="Member" @selected(old('role', 'Member') === 'Member')>Member</option>
                    <option value="Admin"  @selected(old('role') === 'Admin')>Admin</option>
                </select>
                @error('role')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>
        @endif

        <div style="display:flex;gap:.5rem;margin-top:.5rem">
            <button type="submit" class="btn btn-primary">Send Invitation</button>
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

@endsection

