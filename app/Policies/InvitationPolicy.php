<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;

class InvitationPolicy
{
    public function create(User $user): bool
    {
        return in_array($user->role, [Role::SuperAdmin, Role::Admin], true);
    }
}
