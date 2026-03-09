<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Game;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Game>
 */
class GameFactory extends Factory
{
    public function definition(): array
    {
        return [
            "name" => fake()->words(3, true),
            "user_id" => null,
            "is_shared" => false,
            "min_players" => 2,
            "max_players" => 4,
        ];
    }

    public function userAdded(): static
    {
        return $this->state(fn(array $attributes): array => [
            "user_id" => User::factory(),
        ]);
    }

    public function shared(): static
    {
        return $this->state(fn(array $attributes): array => [
            "is_shared" => true,
        ]);
    }
}
