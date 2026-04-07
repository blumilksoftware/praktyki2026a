<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Game;
use Illuminate\Database\Seeder;

class GameSeeder extends Seeder
{
    public function run(): void
    {
        $games = [
            ["name" => "Osadnicy z Catanu", "min_players" => 3, "max_players" => 4, "is_shared" => true, "user_id" => null],
            ["name" => "Pandemia", "min_players" => 2, "max_players" => 4, "is_shared" => true, "user_id" => null],
            ["name" => "Carcassonne", "min_players" => 2, "max_players" => 5, "is_shared" => true, "user_id" => null],
            ["name" => "Wsieci", "min_players" => 2, "max_players" => 6, "is_shared" => true, "user_id" => null],
            ["name" => "Dixit", "min_players" => 3, "max_players" => 8, "is_shared" => true, "user_id" => null],
            ["name" => "Splendor", "min_players" => 2, "max_players" => 4, "is_shared" => true, "user_id" => null],
            ["name" => "Azul", "min_players" => 2, "max_players" => 4, "is_shared" => true, "user_id" => null],
            ["name" => "Na Skrzydłach", "min_players" => 1, "max_players" => 5, "is_shared" => true, "user_id" => null],
            ["name" => "7 Cudów Świata", "min_players" => 2, "max_players" => 7, "is_shared" => true, "user_id" => null],
            ["name" => "Terraformacja Marsa", "min_players" => 1, "max_players" => 5, "is_shared" => true, "user_id" => null],
            ["name" => "Gloomhaven", "min_players" => 1, "max_players" => 4, "is_shared" => true, "user_id" => null],
            ["name" => "Kosa", "min_players" => 1, "max_players" => 5, "is_shared" => true, "user_id" => null],
            ["name" => "Kolejka", "min_players" => 2, "max_players" => 5, "is_shared" => true, "user_id" => null],
            ["name" => "Wsiada i Jedzie", "min_players" => 2, "max_players" => 5, "is_shared" => true, "user_id" => null],
            ["name" => "Tajniacy", "min_players" => 2, "max_players" => 8, "is_shared" => true, "user_id" => null],
            ["name" => "Szachy", "min_players" => 2, "max_players" => 2, "is_shared" => true, "user_id" => null],
            ["name" => "Poker", "min_players" => 2, "max_players" => 10, "is_shared" => true, "user_id" => null],
            ["name" => "Makao", "min_players" => 2, "max_players" => 7, "is_shared" => true, "user_id" => null],
            ["name" => "Tysiąc", "min_players" => 3, "max_players" => 4, "is_shared" => true, "user_id" => null],
            ["name" => "Uno", "min_players" => 2, "max_players" => 10, "is_shared" => true, "user_id" => null],
        ];

        foreach ($games as $game) {
            Game::firstOrCreate(
                ["name" => $game["name"]],
                $game,
            );
        }
    }
}
