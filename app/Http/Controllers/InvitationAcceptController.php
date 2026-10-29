<?php

namespace App\Http\Controllers;

use App\Http\Requests\AcceptInvitationRequest;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InvitationAcceptController extends Controller
{
    public function show(string $token): View|RedirectResponse
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();

        if (! $invitation->isPending()) {
            abort(410, 'This invitation has expired or has already been accepted.');
        }

        return view('invitations.accept', compact('invitation'));
    }

    public function store(AcceptInvitationRequest $request, string $token): RedirectResponse
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();

        if (! $invitation->isPending()) {
            abort(410, 'This invitation has expired or has already been accepted.');
        }

        if (User::where('email', $invitation->email)->exists()) {
            return redirect()->route('invitations.accept', $token)
                ->withErrors(['email' => 'An account with this email already exists.']);
        }

        $user = DB::transaction(function () use ($invitation, $request) {
            $name = $invitation->name ?? explode('@', $invitation->email)[0];

            $user = User::create([
                'name' => $name,
                'email' => $invitation->email,
                'password' => $request->validated('password'),
                'role' => $invitation->role->value,
                'company_id' => $invitation->company_id,
            ]);

            $invitation->update(['accepted_at' => now()]);

            return $user;
        });

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Welcome! Your account has been created.');
    }
}
