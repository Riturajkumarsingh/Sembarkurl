<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ShortUrlFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'user_id' => User::factory(),
            'original_url' => $this->faker->url(),
            'short_code' => Str::random(6),
            'hits' => $this->faker->numberBetween(0, 200),
        ];
    }

    /**
     * Assign this URL to a specific user (and their company automatically).
     */
    public function forUser(User $user): static
    {
        return $this->state([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
        ]);
    }
}
