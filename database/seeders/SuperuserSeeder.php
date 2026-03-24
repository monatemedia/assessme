<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperuserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Idempotent — only creates the superuser if no users exist.
     * Credentials are pulled from environment variables so they
     * are configurable per environment without code changes.
     */
    public function run(): void
    {
        if (User::exists()) {
            $this->command->info('Superuser already exists — skipping.');
            return;
        }

        User::create([
            'name'              => env('SUPERUSER_NAME', 'Admin'),
            'email'             => env('SUPERUSER_EMAIL', 'admin@assessme.local'),
            'password'          => Hash::make(env('SUPERUSER_PASSWORD', 'password')),
            'email_verified_at' => now(),
        ]);

        $this->command->info('Superuser created: ' . env('SUPERUSER_EMAIL', 'admin@assessme.local'));
    }
}
