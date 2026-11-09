<?php

namespace Database\Seeders;

use App\Models\AccountGoogle;
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
        $account = AccountGoogle::query()->updateOrCreate(
            ['id_user_google' => 'seed-owner-mirrorcam'],
            ['email' => 'antoni@mirrorcam.com']
        );

        User::query()->updateOrCreate(
            ['id_user_google' => $account->id_user_google],
            [
                'role' => 'tenant',
                'nama_lengkap_ktp' => 'Antoni Silha',
                'nik_ktp' => '3171010101010000',
                'no_whatsapp' => '081234567890',
                'foto_ktp' => 'ktp/seed-owner.jpg',
            ]
        );
    }
}
