<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvitationRequest;
use App\Mail\InvitationMail;
use App\Models\Company;
use App\Models\Invitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InvitationController extends Controller
{
    public function create(Request $request): View
    {
        $this->authorize('create', Invitation::class);

        return view('invitations.create', ['user' => $request->user()]);
    }

    public function store(StoreInvitationRequest $request): RedirectResponse
    {
        $sender = $request->user();
        $data = $request->validated();

        $invitation = DB::transaction(function () use ($sender, $data) {
            $companyId = $sender->company_id;

            if ($sender->isSuperAdmin()) {
                $company = Company::create(['name' => $data['company_name']]);
                $companyId = $company->id;
            }

            return Invitation::create([
                'company_id' => $companyId,
                'invited_by' => $sender->id,
                'name' => $data['name'] ?? null,
                'email' => $data['email'],
                'role' => $data['role'],
                'token' => Str::random(64),
                'expires_at' => now()->addDays(7),
            ]);
        });

        $acceptUrl = route('invitations.accept', $invitation->token);

        Mail::to($invitation->email)->send(new InvitationMail($invitation, $acceptUrl));

        return redirect()->route('dashboard')
            ->with('invitation_link', $acceptUrl)
            ->with('success', 'Invitation sent successfully.');
    }
}
