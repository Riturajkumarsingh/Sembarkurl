<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('superadmin.email');
        $password = config('superadmin.password');
        $now = now()->toDateTimeString();

        // Raw SQL as required — no Eloquent or factories for the bootstrap account.
        DB::insert(
            'INSERT INTO users (name, email, password, role, company_id, created_at, updated_at)
             VALUES (?, ?, ?, ?, NULL, ?, ?)',
            ['Super Admin', $email, Hash::make($password), 'SuperAdmin', $now, $now]
        );
    }
}
