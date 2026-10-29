<?php

use App\Models\Company;
use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('member csv export contains only their own urls', function () {
    $company = Company::factory()->create();
    $member1 = User::factory()->member($company)->create();
    $member2 = User::factory()->member($company)->create();

    ShortUrl::factory()->forUser($member1)->create(['short_code' => 'csv001a']);
    ShortUrl::factory()->forUser($member2)->create(['short_code' => 'csv002b']);

    $response = $this->actingAs($member1)->get(route('short-urls.export'));
    $response->assertOk();

    $content = $response->streamedContent();
    $this->assertStringContainsString('csv001a', $content);
    $this->assertStringNotContainsString('csv002b', $content);
});

it('admin csv export excludes other company urls', function () {
    $company1 = Company::factory()->create();
    $company2 = Company::factory()->create();
    $admin1 = User::factory()->admin($company1)->create();
    $admin2 = User::factory()->admin($company2)->create();

    ShortUrl::factory()->forUser($admin1)->create(['short_code' => 'cmp1001']);
    ShortUrl::factory()->forUser($admin2)->create(['short_code' => 'cmp2002']);

    $response = $this->actingAs($admin1)->get(route('short-urls.export'));
    $response->assertOk();

    $content = $response->streamedContent();
    $this->assertStringContainsString('cmp1001', $content);
    $this->assertStringNotContainsString('cmp2002', $content);
});
