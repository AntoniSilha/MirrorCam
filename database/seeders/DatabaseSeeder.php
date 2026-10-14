<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::firstOrCreate(
            ['email' => 'antoni@mirrorcam.com'],
            [
                'name' => 'Antoni Silha',
                'password' => 'Password123!',
                'email_verified_at' => now(),
            ]
        );
    }
}
