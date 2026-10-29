<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'company_id' => null,
            'role' => Role::Member->value,
        ];
    }

    public function superAdmin(): static
    {
        return $this->state([
            'role' => Role::SuperAdmin->value,
            'company_id' => null,
        ]);
    }

    public function admin(?Company $company = null): static
    {
        return $this->state(fn () => [
            'role' => Role::Admin->value,
            'company_id' => $company?->id ?? Company::factory(),
        ]);
    }

    public function member(?Company $company = null): static
    {
        return $this->state(fn () => [
            'role' => Role::Member->value,
            'company_id' => $company?->id ?? Company::factory(),
        ]);
    }

    public function sales(?Company $company = null): static
    {
        return $this->state(fn () => [
            'role' => Role::Sales->value,
            'company_id' => $company?->id ?? Company::factory(),
        ]);
    }

    public function manager(?Company $company = null): static
    {
        return $this->state(fn () => [
            'role' => Role::Manager->value,
            'company_id' => $company?->id ?? Company::factory(),
        ]);
    }
}
