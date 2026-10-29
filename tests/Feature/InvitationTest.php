<?php

use App\Models\Company;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('superadmin can invite an admin with a new company', function () {
    $super = User::factory()->superAdmin()->create();

    $this->actingAs($super)
        ->post(route('invitations.store'), [
            'company_name' => 'Acme Ltd',
            'name' => 'Jane Admin',
            'email' => 'jane@acme.test',
            'role' => 'Admin',
        ])
        ->assertRedirect(route('dashboard'));

    $this->assertDatabaseHas('companies', ['name' => 'Acme Ltd']);
    $this->assertDatabaseHas('invitations', ['email' => 'jane@acme.test', 'role' => 'Admin']);
});

it('admin can invite admin or member in their own company', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->admin($company)->create();

    $this->actingAs($admin)
        ->post(route('invitations.store'), [
            'email' => 'newmember@test.test',
            'name' => 'New Member',
            'role' => 'Member',
        ])
        ->assertRedirect(route('dashboard'));

    $this->assertDatabaseHas('invitations', [
        'email' => 'newmember@test.test',
        'role' => 'Member',
        'company_id' => $company->id,
    ]);
});

it('member cannot invite', function () {
    $company = Company::factory()->create();
    $member = User::factory()->member($company)->create();

    $this->actingAs($member)
        ->post(route('invitations.store'), ['email' => 'x@test.test', 'role' => 'Member'])
        ->assertForbidden();
});

it('admin invitation always belongs to their own company regardless of input', function () {
    $company1 = Company::factory()->create();
    $company2 = Company::factory()->create();
    $admin = User::factory()->admin($company1)->create();

    $this->actingAs($admin)
        ->post(route('invitations.store'), [
            'email' => 'target@test.test',
            'role' => 'Member',
        ])
        ->assertRedirect(route('dashboard'));

    $this->assertDatabaseHas('invitations', [
        'email' => 'target@test.test',
        'company_id' => $company1->id,
    ]);

    $this->assertDatabaseMissing('invitations', [
        'email' => 'target@test.test',
        'company_id' => $company2->id,
    ]);
});

it('accepting an invitation creates the user with the correct role and company', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->admin($company)->create();
    $invitation = Invitation::factory()->create([
        'company_id' => $company->id,
        'invited_by' => $admin->id,
        'email' => 'newuser@test.test',
        'role' => 'Member',
        'token' => Str::random(64),
        'expires_at' => now()->addDays(7),
    ]);

    $this->post(route('invitations.accept.store', $invitation->token), [
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertRedirect(route('dashboard'));

    $this->assertDatabaseHas('users', [
        'email' => 'newuser@test.test',
        'company_id' => $company->id,
        'role' => 'Member',
    ]);

    expect($invitation->fresh()->accepted_at)->not->toBeNull();
});

it('expired token is rejected with 410', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->admin($company)->create();
    $invitation = Invitation::factory()->create([
        'company_id' => $company->id,
        'invited_by' => $admin->id,
        'email' => 'expired@test.test',
        'role' => 'Member',
        'token' => Str::random(64),
        'expires_at' => now()->subDay(),
    ]);

    $this->get(route('invitations.accept', $invitation->token))->assertStatus(410);
});

it('already accepted token is rejected with 410', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->admin($company)->create();
    $invitation = Invitation::factory()->create([
        'company_id' => $company->id,
        'invited_by' => $admin->id,
        'email' => 'used@test.test',
        'role' => 'Member',
        'token' => Str::random(64),
        'expires_at' => now()->addDays(7),
        'accepted_at' => now()->subHour(),
    ]);

    $this->get(route('invitations.accept', $invitation->token))->assertStatus(410);
});

it('existing email is rejected when accepting an invitation', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->admin($company)->create();
    User::factory()->create(['email' => 'taken@test.test']);

    $invitation = Invitation::factory()->create([
        'company_id' => $company->id,
        'invited_by' => $admin->id,
        'email' => 'taken@test.test',
        'role' => 'Member',
        'token' => Str::random(64),
        'expires_at' => now()->addDays(7),
    ]);

    $countBefore = User::count();

    $this->post(route('invitations.accept.store', $invitation->token), [
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertRedirect();

    // No new user should have been created.
    expect(User::count())->toBe($countBefore);
});
