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
            ["name" => "Osadnicy z Catanu", "min_players" => 3, "max_players" => 4],
            ["name" => "Pandemia", "min_players" => 2, "max_players" => 4],
            ["name" => "Carcassonne", "min_players" => 2, "max_players" => 5],
            ["name" => "Wsieci", "min_players" => 2, "max_players" => 6],
            ["name" => "Dixit", "min_players" => 3, "max_players" => 8],
            ["name" => "Splendor", "min_players" => 2, "max_players" => 4],
            ["name" => "Azul", "min_players" => 2, "max_players" => 4],
            ["name" => "Na Skrzydlach", "min_players" => 1, "max_players" => 5],
            ["name" => "7 Cudow Swiata", "min_players" => 2, "max_players" => 7],
            ["name" => "Terraformacja Marsa", "min_players" => 1, "max_players" => 5],
            ["name" => "Gloomhaven", "min_players" => 1, "max_players" => 4],
            ["name" => "Kosa", "min_players" => 1, "max_players" => 5],
            ["name" => "Kolejka", "min_players" => 2, "max_players" => 5],
            ["name" => "Wsiada i Jedzie", "min_players" => 2, "max_players" => 5],
            ["name" => "Tajniacy", "min_players" => 2, "max_players" => 8],
            ["name" => "Szachy", "min_players" => 2, "max_players" => 2],
            ["name" => "Poker", "min_players" => 2, "max_players" => 10],
            ["name" => "Makao", "min_players" => 2, "max_players" => 7],
            ["name" => "Tysiac", "min_players" => 3, "max_players" => 4],
            ["name" => "Uno", "min_players" => 2, "max_players" => 10],
        ];

        foreach ($games as $game) {
            Game::firstOrCreate(
                ["name" => $game["name"]],
                $game,
            );
        }
    }
}
