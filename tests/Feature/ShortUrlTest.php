<?php

use App\Models\Company;
use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('admin can create a short url', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->admin($company)->create();

    $this->actingAs($admin)
        ->post(route('short-urls.store'), ['original_url' => 'https://example.com/admin-url'])
        ->assertRedirect(route('dashboard'));

    $this->assertDatabaseHas('short_urls', ['original_url' => 'https://example.com/admin-url']);
});

it('member can create a short url', function () {
    $company = Company::factory()->create();
    $member = User::factory()->member($company)->create();

    $this->actingAs($member)
        ->post(route('short-urls.store'), ['original_url' => 'https://example.com/member-url'])
        ->assertRedirect(route('dashboard'));

    $this->assertDatabaseHas('short_urls', ['original_url' => 'https://example.com/member-url']);
});

it('superadmin cannot create a short url', function () {
    $super = User::factory()->superAdmin()->create();

    $this->actingAs($super)
        ->post(route('short-urls.store'), ['original_url' => 'https://example.com'])
        ->assertForbidden();

    $this->actingAs($super)
        ->get(route('short-urls.create'))
        ->assertForbidden();
});

it('sales and manager cannot create short urls', function () {
    $company = Company::factory()->create();
    $sales = User::factory()->sales($company)->create();
    $manager = User::factory()->manager($company)->create();

    $this->actingAs($sales)
        ->post(route('short-urls.store'), ['original_url' => 'https://example.com'])
        ->assertForbidden();

    $this->actingAs($manager)
        ->post(route('short-urls.store'), ['original_url' => 'https://example.com'])
        ->assertForbidden();
});

it('superadmin sees short urls from all companies but cannot create', function () {
    $company1 = Company::factory()->create();
    $company2 = Company::factory()->create();
    $admin1 = User::factory()->admin($company1)->create();
    $admin2 = User::factory()->admin($company2)->create();
    $url1 = ShortUrl::factory()->forUser($admin1)->create(['short_code' => 'sup001a']);
    $url2 = ShortUrl::factory()->forUser($admin2)->create(['short_code' => 'sup002b']);
    $super = User::factory()->superAdmin()->create();

    $response = $this->actingAs($super)->get(route('short-urls.index'));
    $response->assertOk()
        ->assertSee('sup001a')
        ->assertSee('sup002b');

    $this->actingAs($super)->get(route('short-urls.create'))->assertForbidden();
});

it('admin sees only their own company short urls', function () {
    $company1 = Company::factory()->create();
    $company2 = Company::factory()->create();
    $admin1 = User::factory()->admin($company1)->create();
    $admin2 = User::factory()->admin($company2)->create();
    $url1 = ShortUrl::factory()->forUser($admin1)->create(['short_code' => 'adm001a']);
    $url2 = ShortUrl::factory()->forUser($admin2)->create(['short_code' => 'adm002b']);

    $this->actingAs($admin1)
        ->get(route('short-urls.index'))
        ->assertOk()
        ->assertSee('adm001a')
        ->assertDontSee('adm002b');
});

it('member sees only their own short urls', function () {
    $company = Company::factory()->create();
    $member1 = User::factory()->member($company)->create();
    $member2 = User::factory()->member($company)->create();
    $url1 = ShortUrl::factory()->forUser($member1)->create(['short_code' => 'mem001a']);
    $url2 = ShortUrl::factory()->forUser($member2)->create(['short_code' => 'mem002b']);

    $this->actingAs($member1)
        ->get(route('short-urls.index'))
        ->assertOk()
        ->assertSee('mem001a')
        ->assertDontSee('mem002b');
});

it('short url is publicly resolvable and redirects to the original url', function () {
    $company = Company::factory()->create();
    $user = User::factory()->member($company)->create();

    ShortUrl::factory()->forUser($user)->create([
        'original_url' => 'https://example.com/target-page',
        'short_code' => 'pub001',
    ]);

    $this->get(route('redirect', 'pub001'))
        ->assertRedirect('https://example.com/target-page');
});

it('unknown short code returns 404', function () {
    $this->get(route('redirect', 'no-such-code'))->assertNotFound();
});

it('redirect increments hits by 1 per visit', function () {
    $company = Company::factory()->create();
    $user = User::factory()->member($company)->create();
    $url = ShortUrl::factory()->forUser($user)->create(['short_code' => 'hit001', 'hits' => 0]);

    $this->get(route('redirect', 'hit001'));
    expect($url->fresh()->hits)->toBe(1);

    $this->get(route('redirect', 'hit001'));
    expect($url->fresh()->hits)->toBe(2);
});
