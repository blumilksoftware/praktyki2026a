<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Friend;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Friend>
 */
class FriendFactory extends Factory
{
    public function definition(): array
    {
        return [
            "user_id" => User::factory(),
            "first_name" => fake()->firstName(),
            "last_name" => fake()->lastName(),
            "email" => fake()->unique()->safeEmail(),
        ];
    }
}
