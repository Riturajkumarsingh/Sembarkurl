<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;

class ShortUrlPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [Role::SuperAdmin, Role::Admin, Role::Member], true);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [Role::Admin, Role::Member], true);
    }

    public function export(User $user): bool
    {
        return $this->viewAny($user);
    }
}
