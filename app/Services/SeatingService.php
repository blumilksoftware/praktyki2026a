<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Friend;
use App\Models\Game;
use Illuminate\Support\Collection;

class SeatingService
{
    public function arrangeFormatted(Collection $friends, Collection $games): array
    {
        $raw = $this->arrange($friends, $games);

        return [
            "tables" => collect($raw["tables"])->map(fn($table) => [
                "game" => [
                    "id" => $table["game"]->id,
                    "name" => $table["game"]->name,
                    "min_players" => $table["game"]->min_players,
                    "max_players" => $table["game"]->max_players,
                ],
                "friends" => $table["friends"]->map(fn($friend) => [
                    "id" => $friend->id,
                    "first_name" => $friend->first_name,
                    "last_name" => $friend->last_name,
                ])->values(),
                "avg_rating" => $table["avg_rating"],
            ]),
            "unseated" => $raw["unseated"]->map(fn($friend) => [
                "id" => $friend->id,
                "first_name" => $friend->first_name,
                "last_name" => $friend->last_name,
            ])->values(),
        ];
    }

    public function arrange(Collection $friends, Collection $games): array
    {
        $friends->loadMissing("games");

        $unseated = collect();
        $tables = [];
        $remaining = $friends->keyBy("id");

        while ($remaining->isNotEmpty()) {
            $best = $this->findBestTable($remaining, $games);

            if ($best === null) {
                $unseated = $unseated->merge($remaining);

                break;
            }

            $tables[] = $best;

            foreach ($best["friends"] as $friend) {
                $remaining->forget($friend->id);
            }
        }

        return ["tables" => $tables, "unseated" => $unseated];
    }

    private function findBestTable(Collection $remaining, Collection $games): ?array
    {
        $candidates = [];

        foreach ($games as $game) {
            $eligible = $this->eligibleFriends($remaining, $game);
            $count = $eligible->count();

            if ($count < $game->min_players) {
                continue;
            }

            $maxSize = min($count, $game->max_players);

            for ($size = $game->min_players; $size <= $maxSize; $size++) {
                $selected = $count > $size
                    ? $this->topRatedFriends($eligible, $game, $size)
                    : $eligible;

                $avgRating = $this->averageRating($selected, $game);
                $coverageWeight = 0.7;
                $satisfactionWeight = 0.3;
                $score = $this->compositeScore($selected->count(), $avgRating, $remaining->count(), $coverageWeight, $satisfactionWeight);

                $candidates[] = [
                    "game" => $game,
                    "friends" => $selected,
                    "avg_rating" => round($avgRating, 2),
                    "score" => $score,
                ];
            }
        }

        if ($candidates === []) {
            return $this->forceAssign($remaining, $games);
        }

        $bestScore = max(array_column($candidates, "score"));
        $tied = array_values(array_filter($candidates, fn($c) => abs($c["score"] - $bestScore) < 0.0001));
        $winner = $tied[array_rand($tied)];
        unset($winner["score"]);

        return $winner;
    }

    private function compositeScore(int $count, float $avgRating, int $totalRemaining, float $coverageWeight, float $satisfactionWeight): float
    {
        $coverageScore = $totalRemaining > 0 ? $count / $totalRemaining : 0;
        $satisfactionScore = $avgRating / 10.0;

        return ($coverageWeight * $coverageScore) + ($satisfactionWeight * $satisfactionScore);
    }

    private function eligibleFriends(Collection $remaining, Game $game): Collection
    {
        return $remaining->filter(function (Friend $friend) use ($game) {
            if ($friend->games->isEmpty()) {
                return true;
            }

            return $friend->games->contains("id", $game->id);
        });
    }

    private function topRatedFriends(Collection $eligible, Game $game, int $limit): Collection
    {
        return $eligible
            ->sortByDesc(fn(Friend $f) => $this->ratingFor($f, $game))
            ->take($limit)
            ->values();
    }

    private function forceAssign(Collection $remaining, Collection $games): ?array
    {
        $bestGame = null;
        $bestFriends = collect();

        foreach ($games as $game) {
            $eligible = $this->eligibleFriends($remaining, $game);

            if ($eligible->count() > $bestFriends->count()) {
                $bestGame = $game;
                $bestFriends = $eligible;
            }
        }

        if ($bestGame === null || $bestFriends->isEmpty()) {
            return null;
        }

        if ($bestFriends->count() > $bestGame->max_players) {
            $bestFriends = $this->topRatedFriends($bestFriends, $bestGame, $bestGame->max_players);
        }

        return [
            "game" => $bestGame,
            "friends" => $bestFriends,
            "avg_rating" => round($this->averageRating($bestFriends, $bestGame), 2),
        ];
    }

    private function averageRating(Collection $friends, Game $game): float
    {
        if ($friends->isEmpty()) {
            return 0.0;
        }

        $total = $friends->sum(fn(Friend $f) => $this->ratingFor($f, $game));

        return $total / $friends->count();
    }

    private function ratingFor(Friend $friend, Game $game): int
    {
        $pivot = $friend->games->find($game->id)?->pivot;

        if ($pivot !== null) {
            return $pivot->rating ?? 0;
        }

        if ($friend->games->isEmpty()) {
            return 5;
        }

        return 0;
    }
}
