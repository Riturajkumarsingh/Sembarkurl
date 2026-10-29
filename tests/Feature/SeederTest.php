<?php

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('superadmin exists after seeding with role SuperAdmin and null company_id', function () {
    $this->artisan('db:seed')->assertExitCode(0);

    $email = config('superadmin.email');
    $superadmin = User::where('email', $email)->first();

    expect($superadmin)->not->toBeNull();
    expect($superadmin->role)->toBe(Role::SuperAdmin);
    expect($superadmin->company_id)->toBeNull();
});
