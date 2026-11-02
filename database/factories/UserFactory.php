<?php

namespace Database\Factories;

use App\Models\AccountGoogle;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $googleId = 'test-'.Str::uuid();

        AccountGoogle::query()->create([
            'id_user_google' => $googleId,
            'email' => fake()->unique()->safeEmail(),
        ]);

        return [
            'id_user_google' => $googleId,
            'role' => 'customer',
            'nama_lengkap_ktp' => fake()->name(),
            'nik_ktp' => fake()->numerify('################'),
            'no_whatsapp' => fake()->numerify('08##########'),
            'foto_ktp' => 'ktp/test.jpg',
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this;
    }

    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static
    {
        return $this;
    }
}
