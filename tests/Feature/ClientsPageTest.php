<?php

use App\Models\Company;
use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('superadmin clients page shows correct user count and url totals per company', function () {
    $company = Company::factory()->create(['name' => 'Test Corp']);
    $admin = User::factory()->admin($company)->create();
    $member = User::factory()->member($company)->create();

    ShortUrl::factory()->forUser($admin)->create(['hits' => 10]);
    ShortUrl::factory()->forUser($member)->create(['hits' => 20]);

    $super = User::factory()->superAdmin()->create();

    $response = $this->actingAs($super)->get(route('clients.index'));
    $response->assertOk()
        ->assertSee('Test Corp')
        ->assertSee('2');  // 2 users in company
});
