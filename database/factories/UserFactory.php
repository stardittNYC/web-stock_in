<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Factory untuk menghasilkan data dummy pengguna (User).
 *
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Cache kata sandi bawaan yang digunakan oleh factory.
     */
    protected static ?string $password;

    /**
     * Mendefinisikan status data atribut default model.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Nama acak pengguna
            'name' => fake()->name(),
            // Email unik acak yang valid
            'email' => fake()->unique()->safeEmail(),
            // Hash kata sandi bawaan
            'password' => static::$password ??= Hash::make('password'),
            // Token remember_me acak sepanjang 10 karakter
            'remember_token' => Str::random(10),
        ];
    }
}
