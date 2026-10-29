<?php

namespace App\Providers;

use App\Models\Invitation;
use App\Models\ShortUrl;
use App\Policies\InvitationPolicy;
use App\Policies\ShortUrlPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Gate::policy(ShortUrl::class, ShortUrlPolicy::class);
        Gate::policy(Invitation::class, InvitationPolicy::class);
    }
}
