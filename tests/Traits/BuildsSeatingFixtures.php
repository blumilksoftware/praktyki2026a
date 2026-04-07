<?php

declare(strict_types=1);

namespace Tests\Traits;

use App\Models\Friend;
use App\Models\Game;
use Illuminate\Database\Eloquent\Collection;
use stdClass;

trait BuildsSeatingFixtures
{
    private int $nextId = 1;

    private function resetFixtureIds(): void
    {
        $this->nextId = 1;
    }

    private function nextId(): int
    {
        return $this->nextId++;
    }

    private function makeFriend(array $games = []): Friend
    {
        $friend = Friend::factory()->make(["user_id" => 1]);
        $friend->id = $this->nextId();

        $friend->setRelation("games", new Collection($games));

        return $friend;
    }

    private function makeGame(int $min, int $max): Game
    {
        $game = Game::factory()->make([
            "user_id" => 1,
            "min_players" => $min,
            "max_players" => $max,
        ]);

        $game->id = $this->nextId();

        return $game;
    }

    private function withRating(Game $game, int $rating): Game
    {
        $clone = clone $game;

        $pivot = new stdClass();
        $pivot->rating = $rating;
        $clone->setRelation("pivot", $pivot);

        return $clone;
    }
}
