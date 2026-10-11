<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'fname' => fake()->name(),
            'onames' => fake()->name(),
            'lname' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'dob' => fake()->date(),
            'usertype_id' => 1,
            'title_id' => 1,
            'email_verified_at' => now(),
            'password' => '$2y$10$BGmCMgaoAzx41lWcOwet2esVFgsJa1ObY5hK8Zn9vBiM.9SZIUNI.', // password
            'remember_token' => Str::random(10)
        ];
    }
}
