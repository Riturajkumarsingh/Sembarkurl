<?php

use App\Models\Company;
use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('date filter returns only urls inside the selected interval', function () {
    $company = Company::factory()->create();
    $member = User::factory()->member($company)->create();

    $thisMonth = ShortUrl::factory()->forUser($member)->create([
        'short_code' => 'dtTM001x',
        'created_at' => now()->startOfMonth()->addDays(2),
    ]);

    $lastMonth = ShortUrl::factory()->forUser($member)->create([
        'short_code' => 'dtLM002y',
        'created_at' => now()->subMonth()->startOfMonth()->addDays(2),
    ]);

    $this->actingAs($member)
        ->get(route('short-urls.index', ['interval' => 'this_month']))
        ->assertOk()
        ->assertSee('dtTM001x')
        ->assertDontSee('dtLM002y');

    $this->actingAs($member)
        ->get(route('short-urls.index', ['interval' => 'last_month']))
        ->assertOk()
        ->assertSee('dtLM002y')
        ->assertDontSee('dtTM001x');
});
