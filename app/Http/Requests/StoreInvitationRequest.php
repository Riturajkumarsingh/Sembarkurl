<?php

namespace App\Http\Requests;

use App\Models\Invitation;
use Illuminate\Foundation\Http\FormRequest;

class StoreInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Invitation::class);
    }

    public function rules(): array
    {
        $user = $this->user();

        // SuperAdmin always invites an Admin for a brand-new company.
        // Admin may invite Admin or Member into their own company.
        $allowedRoles = $user->isSuperAdmin() ? 'Admin' : 'Admin,Member';

        $rules = [
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email', 'unique:invitations,email'],
            'role' => ['required', 'string', 'in:'.$allowedRoles],
        ];

        if ($user->isSuperAdmin()) {
            $rules['company_name'] = ['required', 'string', 'max:255'];
        }

        return $rules;
    }
}
